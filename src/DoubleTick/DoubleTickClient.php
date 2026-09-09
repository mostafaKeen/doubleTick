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
