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
            'text' => $text,
        ];
        $fromWaba = $from ?: $this->defaultWaba;
        if ($fromWaba) {
            $payload['from'] = $this->normalizePhone($fromWaba);
        }

        return $this->post('/whatsapp/message/text', $payload);
    }

    /**
     * Send approved WhatsApp template message (V2)
     */
    public function sendTemplateMessage(
        string $to,
        string $templateName,
        string $language,
        array $templateData,
        ?string $from = null
    ): array {
        $payload = [
            'to' => $this->normalizePhone($to),
            'templateName' => $templateName,
            'language' => $language,
            'templateData' => $templateData,
        ];
        $fromWaba = $from ?: $this->defaultWaba;
        if ($fromWaba) {
            $payload['from'] = $this->normalizePhone($fromWaba);
        }

        return $this->post('/v2/whatsapp/message/template', $payload);
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

        $payload = [
            'to' => $this->normalizePhone($to),
            'url' => $mediaUrl,
        ];
        if ($caption !== null && in_array($mediaType, ['image', 'video', 'document'])) {
            $payload['caption'] = $caption;
        }
        if ($filename !== null && $mediaType === 'document') {
            $payload['filename'] = $filename;
        }

        $fromWaba = $from ?: $this->defaultWaba;
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
        $payload = [
            'to' => $this->normalizePhone($to),
            'content' => [
                'type' => 'BUTTON',
                'body' => [
                    'text' => $bodyText,
                ],
                'action' => [
                    'buttons' => $buttons,
                ]
            ]
        ];

        if ($header) {
            $payload['content']['header'] = [
                'type' => 'TEXT',
                'text' => $header,
            ];
        }
        if ($footer) {
            $payload['content']['footer'] = [
                'text' => $footer,
            ];
        }

        $fromWaba = $from ?: $this->defaultWaba;
        if ($fromWaba) {
            $payload['from'] = $this->normalizePhone($fromWaba);
        }

        return $this->post('/whatsapp/message/interactive', $payload);
    }

    /**
     * Generate secure, tokenized Embed URL for frictionless CRM iframe integration
     */
    public function getEmbedUrl(string $phone, ?string $wabaNumber = null, ?string $integrationId = null): string
    {
        $payload = [
            'phone' => $this->normalizePhone($phone),
        ];
        $waba = $wabaNumber ?: $this->defaultWaba;
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
        $params = [
            'customerPhoneNumber' => $this->normalizePhone($phone),
        ];
        if ($this->defaultWaba) {
            $params['wabaNumber'] = $this->normalizePhone($this->defaultWaba);
        }
        if ($startDate && $endDate) {
            $params['startDate'] = $startDate;
            $params['endDate'] = $endDate;
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
     * Register a new webhook endpoint with DoubleTick
     */
    public function registerWebhook(string $webhookUrl, array $events, ?string $wabaNumber = null): array
    {
        $payload = [
            'url' => $webhookUrl,
            'events' => $events,
        ];
        $waba = $wabaNumber ?: $this->defaultWaba;
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
     * Helper: normalize phone number (strip spaces, dashes, parentheses)
     */
    public function normalizePhone(string $phone): string
    {
        return preg_replace('/[^0-9]/', '', $phone);
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
