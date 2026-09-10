<?php

declare(strict_types=1);

namespace DoubleTickB24\DoubleTick;

use DoubleTickB24\Core\Logger;
use Exception;

class DoubleTickClient
{
    private string $apiKey;
    private string $baseUrl;
    private ?string $defaultWaba;

    public function __construct(string $apiKey, ?string $defaultWaba = null, ?string $baseUrl = null)
    {
        $this->apiKey = trim($apiKey);
        $this->defaultWaba = $defaultWaba ? trim($defaultWaba) : null;
        $this->baseUrl = rtrim($baseUrl ?: 'https://public.doubletick.io', '/');
    }

    /**
     * Send standard WhatsApp text message
     */
    public function sendTextMessage(string $to, string $text, ?string $from = null): array
    {
        $payload = [
            'to' => $this->normalizePhone($to),
            'content' => [
                'text' => $text,
            ],
        ];

        $fromWaba = $this->resolveFromWaba($from);
        if ($fromWaba) {
            $payload['from'] = $this->normalizePhone($fromWaba);
        }

        return $this->post('/whatsapp/message/text', $payload);
    }

    /**
     * Send approved WhatsApp template message
     */
    public function sendTemplateMessage(
        string $to,
        string $templateName,
        string $language,
        array $templateData = [],
        ?string $from = null
    ): array {
        // If templateData is a simple sequential array of placeholders e.g. ['John', 'Special Offer'], wrap it
        if (!empty($templateData) && array_is_list($templateData)) {
            $templateData = [
                'body' => [
                    'placeholders' => $templateData,
                ],
            ];
        }

        $content = [
            'templateName' => $templateName,
            'language' => $language,
        ];
        if (!empty($templateData)) {
            $content['templateData'] = $templateData;
        }

        $message = [
            'to' => $this->normalizePhone($to),
            'content' => $content,
        ];

        $fromWaba = $this->resolveFromWaba($from);
        if ($fromWaba) {
            $message['from'] = $this->normalizePhone($fromWaba);
        }

        return $this->post('/whatsapp/message/template', [
            'messages' => [$message],
        ]);
    }

    /**
     * Send media message (image, video, audio, document)
     */
    public function sendMediaMessage(
        string $mediaType,
        string $to,
        string $mediaUrl,
        ?string $caption = null,
        ?string $filename = null,
        ?string $from = null
    ): array {
        $mediaType = strtolower($mediaType);
        if (!in_array($mediaType, ['image', 'video', 'audio', 'document'])) {
            throw new Exception("Unsupported media type: {$mediaType}");
        }

        $content = [
            'mediaUrl' => $mediaUrl,
        ];
        if ($caption !== null && in_array($mediaType, ['image', 'video', 'document'])) {
            $content['caption'] = $caption;
        }
        if ($filename !== null && $mediaType === 'document') {
            $content['filename'] = $filename;
        }

        $payload = [
            'to' => $this->normalizePhone($to),
            'content' => $content,
        ];

        $fromWaba = $this->resolveFromWaba($from);
        if ($fromWaba) {
            $payload['from'] = $this->normalizePhone($fromWaba);
        }

        return $this->post("/whatsapp/message/{$mediaType}", $payload);
    }

    /**
     * Send interactive button message
     */
    public function sendInteractiveButtons(
        string $to,
        string $bodyText,
        array $buttons,
        ?string $header = null,
        ?string $footer = null,
        ?string $from = null
    ): array {
        $content = [
            'body' => $bodyText,
            'buttons' => $buttons,
        ];
        if ($header !== null && $header !== '') {
            $content['header'] = $header;
        }
        if ($footer !== null && $footer !== '') {
            $content['footer'] = $footer;
        }

        $payload = [
            'to' => $this->normalizePhone($to),
            'content' => $content,
        ];

        $fromWaba = $this->resolveFromWaba($from);
        if ($fromWaba) {
            $payload['from'] = $this->normalizePhone($fromWaba);
        }

        return $this->post('/whatsapp/message/interactive', $payload);
    }

    /**
     * Send interactive list message
     */
    public function sendInteractiveList(
        string $to,
        string $bodyText,
        string $buttonText,
        array $sections,
        ?string $header = null,
        ?string $footer = null,
        ?string $from = null
    ): array {
        $content = [
            'body' => $bodyText,
            'button' => $buttonText,
            'sections' => $sections,
        ];
        if ($header !== null && $header !== '') {
            $content['header'] = $header;
        }
        if ($footer !== null && $footer !== '') {
            $content['footer'] = $footer;
        }

        $payload = [
            'to' => $this->normalizePhone($to),
            'content' => $content,
        ];

        $fromWaba = $this->resolveFromWaba($from);
        if ($fromWaba) {
            $payload['from'] = $this->normalizePhone($fromWaba);
        }

        return $this->post('/whatsapp/message/interactive-list', $payload);
    }

    /**
     * Generate secure, tokenized Embed URL for CRM iframe integration
     */
    public function getEmbedUrl(string $phone, ?string $wabaNumber = null, ?string $integrationId = null): string
    {
        $payload = [
            'phone' => $this->normalizePhone($phone),
        ];
        $waba = $this->resolveFromWaba($wabaNumber);
        if ($waba) {
            $payload['wabaNumber'] = $this->normalizePhone($waba);
        }
        if ($integrationId) {
            $payload['integrationId'] = $integrationId;
        }

        $res = $this->post('/embed/url', $payload);
        if (!empty($res['url'])) {
            return $res['url'];
        }

        throw new Exception("Failed to obtain DoubleTick embed URL: " . json_encode($res));
    }

    /**
     * Request a short-lived Embed SSO Token via DoubleTick Custom CRM API
     */
    public function getCustomCrmEmbedToken(string $identifier, string $sessionToken): ?string
    {
        $url = 'https://aiapi.doubletick.io/v1/custom-crm/embed/token';
        $payload = [
            'identifier' => $identifier,
            'sessionToken' => $sessionToken,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: ' . $this->apiKey,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            Logger::error("Custom CRM embed token cURL error: {$err}");
            return null;
        }

        $data = json_decode($response ?: '', true);
        if ($httpCode >= 200 && $httpCode < 300 && !empty($data['token'])) {
            return $data['token'];
        }

        Logger::warning("DoubleTick Custom CRM embed token response", [
            'status' => $httpCode,
            'response' => $data,
            'identifier' => $identifier
        ]);
        return null;
    }

    /**
     * Retrieve AI summary of 1:1 chat
     */
    public function getChatAiSummary(string $phone, ?string $startDate = null, ?string $endDate = null): array
    {
        $startDate = $startDate ?: date('Y-m-d', strtotime('-6 days'));
        $endDate = $endDate ?: date('Y-m-d');
        $params = [
            'customerNumber' => $this->normalizePhone($phone),
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];

        $waba = $this->resolveFromWaba($this->defaultWaba);
        if ($waba) {
            $params['wabaNumber'] = $this->normalizePhone($waba);
        }

        return $this->get('/channel/chat/ai-summary', $params);
    }

    /**
     * Retrieve chat messages for a specific customer
     */
    public function getChatMessages(string $phone, ?string $wabaNumber = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $waba = $this->resolveFromWaba($wabaNumber);
        $params = [
            'wabaNumber' => $this->normalizePhone($waba),
            'customerNumber' => $this->normalizePhone($phone),
        ];
        if ($startDate) {
            $params['startDate'] = $startDate;
        }
        if ($endDate) {
            $params['endDate'] = $endDate;
        }

        try {
            return $this->get('/chat-messages', $params);
        } catch (\Throwable $e) {
            Logger::warning("Could not fetch /chat-messages from DoubleTick: " . $e->getMessage());
            return ['success' => false, 'messages' => [], 'error' => $e->getMessage()];
        }
    }

    /**
     * Get 24-hour customer care window status for customer
     */
    public function getChatWindowStatus(string $phone, ?string $wabaNumber = null): array
    {
        $waba = $this->resolveFromWaba($wabaNumber);
        $cleanPhone = $this->normalizePhone($phone);
        $params = [
            'wabaNumber' => '+' . $this->normalizePhone($waba),
            'customerPhoneNumber' => '+' . $cleanPhone,
        ];

        try {
            return $this->get('/chat/status', $params);
        } catch (\Throwable $e) {
            // Try without leading + if failed
            try {
                return $this->get('/chat/status', [
                    'wabaNumber' => $this->normalizePhone($waba),
                    'customerPhoneNumber' => $cleanPhone,
                ]);
            } catch (\Throwable $e2) {
                Logger::warning("Could not fetch /chat/status from DoubleTick: " . $e2->getMessage());
                return ['isOpen' => false, 'error' => $e2->getMessage()];
            }
        }
    }

    /**
     * Fetch list of connected WhatsApp channels/WABAs
     */
    public function listChannels(): array
    {
        return $this->get('/organization/channel/profile');
    }

    /**
     * Auto-detect the primary connected WABA phone number from user's DoubleTick account
     */
    public function getPrimaryWabaNumber(): ?string
    {
        try {
            $res = $this->listChannels();
            if (!empty($res['channels']) && is_array($res['channels'])) {
                foreach ($res['channels'] as $ch) {
                    if (!empty($ch['wabaNumber']) && ($ch['status'] ?? '') === 'CONNECTED') {
                        return (string)$ch['wabaNumber'];
                    }
                }
                if (!empty($res['channels'][0]['wabaNumber'])) {
                    return (string)$res['channels'][0]['wabaNumber'];
                }
            }
        } catch (\Throwable $e) {
            Logger::error("Failed to detect primary WABA: " . $e->getMessage());
        }
        return null;
    }

    /**
     * Resolve sender WABA, safely filtering out dummy/placeholder values
     */
    private function resolveFromWaba(?string $from): ?string
    {
        $candidate = $from ?: $this->defaultWaba;
        if ($candidate) {
            $candidate = trim($candidate);
            // If candidate is the default dummy placeholder, attempt auto-detect
            if ($candidate === '919999999999') {
                $detected = $this->getPrimaryWabaNumber();
                if ($detected) {
                    $this->defaultWaba = $detected;
                    return $detected;
                }
                return null;
            }
            return $candidate;
        }

        // If no candidate, try auto-detect
        $detected = $this->getPrimaryWabaNumber();
        if ($detected) {
            $this->defaultWaba = $detected;
            return $detected;
        }

        return null;
    }

    /**
     * Register a new webhook endpoint with DoubleTick
     */
    public function registerWebhook(string $webhookUrl, array $events, ?string $wabaNumber = null): array
    {
        $payload = [
            'url' => $webhookUrl,
            'events' => $events,
        ];
        $waba = $this->resolveFromWaba($wabaNumber);
        if ($waba) {
            $payload['wabaNumber'] = $this->normalizePhone($waba);
        }

        return $this->post('/v2/webhook/register', $payload);
    }

    /**
     * Get registered webhooks
     */
    public function getWebhooks(): array
    {
        return $this->get('/v2/webhooks');
    }

    /**
     * Get approved WhatsApp templates from DoubleTick
     */
    public function getTemplates(?string $status = 'APPROVED', ?string $language = null, ?string $category = null): array
    {
        $params = [];
        if ($status !== null) {
            $params['status'] = $status;
        }
        if ($language !== null) {
            $params['language'] = $language;
        }
        if ($category !== null) {
            $params['category'] = $category;
        }

        $waba = $this->resolveFromWaba($this->defaultWaba);
        if ($waba) {
            $params['wabaPhoneNumbers'] = $this->normalizePhone($waba);
        }

        return $this->get('/v2/templates', $params);
    }

    /**
     * Helper: normalize phone number (digits only, country code included)
     */
    public function normalizePhone(string $phone, bool $withPlus = false): string
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if ($withPlus && $clean !== '') {
            return '+' . $clean;
        }
        return $clean;
    }

    /**
     * Execute GET request
     */
    public function get(string $endpoint, array $queryParams = []): array
    {
        $url = $this->baseUrl . $endpoint;
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }

        return $this->request('GET', $url);
    }

    /**
     * Execute POST request
     */
    public function post(string $endpoint, array $body = []): array
    {
        $url = $this->baseUrl . $endpoint;
        return $this->request('POST', $url, $body);
    }

    /**
     * Core cURL request method
     */
    private function request(string $method, string $url, ?array $body = null): array
    {
        $ch = curl_init();
        $headers = [
            'Authorization: ' . $this->apiKey,
            'Content-Type: application/json',
            'Accept: application/json',
        ];

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Handle SSL CA certificate configuration across Windows & Unix environments
        if (ini_get('curl.cainfo')) {
            // Standard php.ini CA bundle configured
        } else {
            $caPaths = [
                'C:/php/extras/ssl/cacert.pem',
                'C:/php/cacert.pem',
                'C:/tools/php/cacert.pem',
                '/etc/ssl/certs/ca-certificates.crt',
                '/etc/pki/tls/certs/ca-bundle.crt',
            ];
            $foundCa = null;
            foreach ($caPaths as $path) {
                if (file_exists($path)) {
                    $foundCa = $path;
                    break;
                }
            }
            if ($foundCa) {
                curl_setopt($ch, CURLOPT_CAINFO, $foundCa);
            } elseif (getenv('APP_ENV') === 'local' || (defined('PHP_OS_FAMILY') && PHP_OS_FAMILY === 'Windows')) {
                // Prevent fatal crash in environments lacking local CA bundles
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            }
        }

        if ($body !== null && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            Logger::error("DoubleTick cURL error: {$curlError}", ['url' => $url, 'method' => $method]);
            throw new Exception("DoubleTick cURL error: {$curlError}");
        }

        $decoded = json_decode((string)$response, true);
        if ($httpCode >= 400) {
            Logger::error("DoubleTick API error {$httpCode}", [
                'url' => $url,
                'response' => $response,
                'body' => $body
            ]);
            return [
                'success' => false,
                'status_code' => $httpCode,
                'error' => $decoded['message'] ?? $decoded['error'] ?? "HTTP Error {$httpCode}",
                'raw' => $decoded
            ];
        }

        return is_array($decoded) ? $decoded : ['success' => true, 'data' => $response];
    }
}
