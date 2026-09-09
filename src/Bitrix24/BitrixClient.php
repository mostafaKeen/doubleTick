<?php

declare(strict_types=1);

namespace DoubleTickB24\Bitrix24;

use DoubleTickB24\Core\Database;
use DoubleTickB24\Core\Logger;
use Exception;
use PDO;

class BitrixClient
{
    private array $portal;
    private string $clientId;
    private string $clientSecret;

    public function __construct(array $portal, ?string $clientId = null, ?string $clientSecret = null)
    {
        $this->portal = $portal;
        $this->clientId = $clientId ?: (string)(getenv('B24_CLIENT_ID') ?: '');
        $this->clientSecret = $clientSecret ?: (string)(getenv('B24_CLIENT_SECRET') ?: '');
    }

    public static function getByMemberId(string $memberId): ?self
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM b24_portals WHERE member_id = :member_id AND is_active = 1 LIMIT 1");
        $stmt->execute(['member_id' => $memberId]);
        $portal = $stmt->fetch(PDO::FETCH_ASSOC);

        return $portal ? new self($portal) : null;
    }

    public static function getByDomain(string $domain): ?self
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM b24_portals WHERE domain = :domain AND is_active = 1 LIMIT 1");
        $stmt->execute(['domain' => $domain]);
        $portal = $stmt->fetch(PDO::FETCH_ASSOC);

        return $portal ? new self($portal) : null;
    }

    public static function getFirstActive(): ?self
    {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM b24_portals WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
        $portal = $stmt->fetch(PDO::FETCH_ASSOC);

        return $portal ? new self($portal) : null;
    }

    public function getPortalData(): array
    {
        return $this->portal;
    }

    public function getPortalId(): int
    {
        return (int)($this->portal['id'] ?? 0);
    }

    public function getOpenLineId(): ?int
    {
        return isset($this->portal['open_line_id']) ? (int)$this->portal['open_line_id'] : null;
    }

    public function getDoubleTickApiKey(): ?string
    {
        return $this->portal['dt_api_key'] ?? (getenv('DOUBLETICK_API_KEY') ?: null);
    }

    public function getDoubleTickWaba(): ?string
    {
        return $this->portal['dt_waba_number'] ?? (getenv('DOUBLETICK_DEFAULT_WABA') ?: null);
    }

    /**
     * Check and refresh token if expired
     */
    private function ensureValidToken(): void
    {
        $expiresAt = (int)($this->portal['expires_at'] ?? 0);
        // If token expires in less than 3 minutes, refresh it
        if ($expiresAt > 0 && time() >= ($expiresAt - 180)) {
            $this->refreshToken();
        }
    }

    /**
     * Refresh OAuth Access Token
     */
    public function refreshToken(): bool
    {
        if (empty($this->portal['refresh_token'])) {
            Logger::error("Cannot refresh Bitrix24 token: empty refresh_token", ['portal' => $this->portal['domain']]);
            return false;
        }

        $url = 'https://oauth.bitrix.info/oauth/token/';
        $params = [
            'grant_type' => 'refresh_token',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $this->portal['refresh_token'],
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode((string)$response, true);
        if ($httpCode === 200 && !empty($data['access_token'])) {
            $this->portal['access_token'] = $data['access_token'];
            $this->portal['refresh_token'] = $data['refresh_token'];
            $this->portal['expires_at'] = time() + (int)($data['expires_in'] ?? 3600);

            // Update in DB
            $db = Database::getInstance();
            $stmt = $db->prepare("
                UPDATE b24_portals 
                SET access_token = :access_token, 
                    refresh_token = :refresh_token, 
                    expires_at = :expires_at, 
                    updated_at = datetime('now')
                WHERE id = :id
            ");
            $stmt->execute([
                'access_token' => $this->portal['access_token'],
                'refresh_token' => $this->portal['refresh_token'],
                'expires_at' => $this->portal['expires_at'],
                'id' => $this->portal['id'],
            ]);

            Logger::info("Successfully refreshed Bitrix24 token", ['domain' => $this->portal['domain']]);
            return true;
        }

        Logger::error("Failed to refresh Bitrix24 token", ['response' => $response, 'code' => $httpCode]);
        return false;
    }

    /**
     * Call Bitrix24 REST API method
     */
    public function call(string $method, array $params = []): array
    {
        $this->ensureValidToken();

        $endpoint = rtrim($this->portal['client_endpoint'], '/') . '/' . ltrim($method, '/');
        $params['auth'] = $this->portal['access_token'];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            Logger::error("Bitrix24 cURL error on {$method}: {$curlError}");
            throw new Exception("Bitrix24 cURL error: {$curlError}");
        }

        $decoded = json_decode((string)$response, true);
        if ($httpCode >= 400 || !empty($decoded['error'])) {
            $errCode = (string)($decoded['error'] ?? "HTTP {$httpCode}");
            $errDesc = (string)($decoded['error_description'] ?? $decoded['error'] ?? 'Unknown error');

            // 1. Check for expired token error
            if (in_array($errCode, ['expired_token', 'invalid_token'])) {
                if ($this->refreshToken()) {
                    return $this->call($method, $params);
                }
            }

            // 2. Treat idempotent errors (already binded / already installed / not binded) as success
            if (stripos($errDesc, 'already binded') !== false || stripos($errDesc, 'already installed') !== false || stripos($errDesc, 'not binded') !== false) {
                Logger::info("Bitrix24 {$method} notice: idempotent action", [
                    'method' => $method,
                    'notice' => $errDesc
                ]);
                return [
                    'result' => true,
                    'already_installed' => true,
                    'notice' => $errDesc
                ];
            }

            // 3. Gracefully handle insufficient_scope (e.g. missing bizproc permission in Bitrix24 Local App)
            if ($errCode === 'insufficient_scope' || stripos($errDesc, 'higher privileges') !== false) {
                Logger::warning("Bitrix24 {$method} requires higher privileges/scope. Check permissions in Bitrix24 Local App settings.", [
                    'method' => $method,
                    'code' => $httpCode,
                    'error' => $errDesc
                ]);
                return [
                    'error' => 'insufficient_scope',
                    'error_description' => $errDesc,
                    'result' => null
                ];
            }

            Logger::error("Bitrix24 API error on {$method}", [
                'code' => $httpCode,
                'error' => $errDesc,
                'params' => $params,
            ]);
            return [
                'error' => $errCode,
                'error_description' => $errDesc,
                'result' => null
            ];
        }

        return $decoded ?: ['result' => null];
    }

    /**
     * Execute Batch request
     */
    public function batch(array $commands, bool $haltOnError = false): array
    {
        $params = [
            'halt' => $haltOnError ? 1 : 0,
            'cmd' => $commands,
        ];
        return $this->call('batch', $params);
    }
}
