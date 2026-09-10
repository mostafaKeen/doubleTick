<?php

declare(strict_types=1);

namespace DoubleTickB24\DoubleTick;

use DoubleTickB24\Bitrix24\BitrixClient;
use DoubleTickB24\Bitrix24\CrmLeadService;
use DoubleTickB24\Bitrix24\ImConnectorService;
use DoubleTickB24\Core\Database;
use DoubleTickB24\Core\Logger;
use Exception;
use PDO;

class WebhookProcessor
{
    private BitrixClient $b24;
    private ImConnectorService $imConnector;
    private CrmLeadService $crmLead;

    public function __construct(BitrixClient $b24)
    {
        $this->b24 = $b24;
        $this->imConnector = new ImConnectorService($b24);
        $this->crmLead = new CrmLeadService($b24);
    }

    /**
     * Ingest and route any incoming DoubleTick webhook
     */
    public function process(array $payload): array
    {
        // DoubleTick webhook event types can be top-level or inside payload
        $eventType = $payload['event'] ?? $payload['eventType'] ?? null;

        // Auto-detect based on payload keys if eventType is omitted
        if (!$eventType) {
            if (isset($payload['newLead']) && $payload['newLead'] === true) {
                $eventType = 'NEW_LEAD';
            } elseif (isset($payload['message']) && isset($payload['from'])) {
                $eventType = 'MESSAGE_RECEIVED';
            } elseif (isset($payload['status']) && (isset($payload['messageId']) || isset($payload['dtMessageId']))) {
                $eventType = 'MESSAGE_STATUS_UPDATE';
            } elseif (isset($payload['conversationCost'])) {
                $eventType = 'CONVERSATION_COST';
            } elseif (isset($payload['slaEscalated']) || isset($payload['slaLevel'])) {
                $eventType = 'SLA_ESCALATED';
            } else {
                $eventType = 'UNKNOWN';
            }
        }

        // Log incoming webhook
        $this->logWebhook('DOUBLETICK', (string)$eventType, $payload);

        switch ($eventType) {
            case 'MESSAGE_RECEIVED':
                return $this->handleMessageReceived($payload);

            case 'MESSAGE_STATUS_UPDATE':
                return $this->handleStatusUpdate($payload);

            case 'NEW_LEAD':
            case 'CALL_TO_WHATSAPP_MESSAGE_RECEIVED':
                return $this->handleNewLead($payload);

            case 'CONVERSATION_COST':
                return $this->handleConversationCost($payload);

            case 'SLA_ESCALATED':
                return $this->handleSlaEscalated($payload);

            default:
                Logger::warning("Unhandled DoubleTick webhook event: {$eventType}", ['payload' => $payload]);
                return ['status' => 'ignored', 'event' => $eventType];
        }
    }

    /**
     * Handle Inbound WhatsApp Message
     */
    private function handleMessageReceived(array $payload): array
    {
        $from = (string)($payload['from'] ?? '');
        $customerName = (string)($payload['contact']['name'] ?? $from);
        $dtMessageId = (string)($payload['dtMessageId'] ?? $payload['messageId'] ?? ('in_' . uniqid()));
        $whatsappMessageId = (string)($payload['messageId'] ?? null);

        $messageObj = $payload['message'] ?? [];
        $type = strtoupper((string)($messageObj['type'] ?? 'TEXT'));
        $text = '';
        $files = [];

        if ($type === 'TEXT') {
            if (is_array($messageObj['text'] ?? null)) {
                $text = (string)($messageObj['text']['body'] ?? $messageObj['text']['text'] ?? $messageObj['text']['content'] ?? '');
            } elseif (isset($messageObj['text'])) {
                $text = (string)$messageObj['text'];
            } elseif (isset($messageObj['body'])) {
                $text = is_array($messageObj['body']) ? ($messageObj['body']['text'] ?? $messageObj['body']['body'] ?? '') : (string)$messageObj['body'];
            }
            if ($text === 'Array' || $text === '[object Object]') {
                $text = '';
            }
        } elseif (in_array($type, ['IMAGE', 'VIDEO', 'AUDIO', 'DOCUMENT'])) {
            $mediaUrl = (string)($messageObj['url'] ?? '');
            $caption = is_array($messageObj['caption'] ?? null) ? ($messageObj['caption']['body'] ?? '') : (string)($messageObj['caption'] ?? '');
            $filename = (string)($messageObj['filename'] ?? (strtolower($type) . '_file'));

            if ($caption !== '' && $caption !== 'Array') {
                $text = $caption;
            }
            if ($mediaUrl !== '') {
                $files[] = [
                    'url' => $mediaUrl,
                    'name' => $filename,
                ];
            }
        } elseif ($type === 'LOCATION') {
            $lat = $messageObj['latitude'] ?? '';
            $lon = $messageObj['longitude'] ?? '';
            $locName = $messageObj['name'] ?? 'Shared Location';
            $text = "📍 {$locName} (Lat: {$lat}, Lon: {$lon})";
        } elseif ($type === 'BUTTON') {
            $text = "Selected button: " . ($messageObj['text'] ?? '');
        } else {
            $text = "[Received {$type} message]";
        }

        // Always record incoming message in local DB for CRM placement tab
        $cleanPhone = preg_replace('/[^0-9]/', '', $from);
        try {
            $db = Database::getInstance();
            $check = $db->prepare("SELECT id FROM message_mappings WHERE dt_message_id = :id LIMIT 1");
            $check->execute(['id' => $dtMessageId]);
            if (!$check->fetch()) {
                $ins = $db->prepare("
                    INSERT INTO message_mappings (
                        portal_id, b24_chat_id, b24_message_id, dt_message_id, whatsapp_message_id,
                        customer_phone, direction, message_type, status, raw_data, created_at
                    ) VALUES (
                        :portal_id, 0, 0, :dt_id, :wa_id,
                        :phone, 'INBOUND', :type, 'delivered', :raw_data, datetime('now')
                    )
                ");
                $ins->execute([
                    'portal_id' => $this->b24->getPortalId(),
                    'dt_id' => $dtMessageId,
                    'wa_id' => $whatsappMessageId,
                    'phone' => $cleanPhone,
                    'type' => empty($files) ? 'text' : 'media',
                    'raw_data' => json_encode([
                        'text' => $text,
                        'files' => $files,
                        'sender_name' => $customerName,
                        'time' => date('Y-m-d H:i:s'),
                    ]),
                ]);
            }
        } catch (\Throwable $ex) {
            Logger::warning("Could not pre-save inbound message: " . $ex->getMessage());
        }

        $openLineId = $this->b24->getOpenLineId();
        if (!$openLineId) {
            // Find active line or use default line 1
            $lines = $this->b24->call('imopenlines.config.get');
            $openLineId = (int)($lines['result']['ID'] ?? 1);
        }

        // Forward to Bitrix24 Open Lines
        $res = $this->imConnector->sendIncomingMessage(
            $openLineId,
            $from,
            $customerName,
            $dtMessageId,
            $text,
            $files,
            $whatsappMessageId
        );

        return ['status' => 'success', 'b24_response' => $res];
    }

    /**
     * Handle Delivery Status Update (Sent, Delivered, Read, Failed)
     */
    private function handleStatusUpdate(array $payload): array
    {
        $dtMessageId = (string)($payload['dtMessageId'] ?? $payload['messageId'] ?? '');
        $status = strtolower((string)($payload['status'] ?? 'delivered'));

        if (empty($dtMessageId)) {
            return ['status' => 'ignored', 'reason' => 'missing messageId'];
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT id FROM message_mappings WHERE dt_message_id = :id LIMIT 1");
        $stmt->execute(['id' => $dtMessageId]);
        $existing = $stmt->fetch();

        if ($existing) {
            $up = $db->prepare("UPDATE message_mappings SET status = :status WHERE dt_message_id = :id");
            $up->execute(['status' => $status, 'id' => $dtMessageId]);
        } else {
            // If this message was sent from DoubleTick app/web, capture it as an OUTBOUND message
            $to = preg_replace('/[^0-9]/', '', (string)($payload['to'] ?? $payload['customerPhone'] ?? ''));
            if ($to) {
                $text = '';
                if (!empty($payload['message'])) {
                    $msgObj = $payload['message'];
                    if (is_array($msgObj)) {
                        $text = (string)($msgObj['text'] ?? $msgObj['body'] ?? '');
                    } elseif (is_string($msgObj)) {
                        $text = $msgObj;
                    }
                }
                $ins = $db->prepare("
                    INSERT INTO message_mappings (
                        portal_id, b24_chat_id, b24_message_id, dt_message_id, whatsapp_message_id,
                        customer_phone, direction, message_type, status, raw_data, created_at
                    ) VALUES (
                        :portal_id, 0, 0, :dt_id, NULL,
                        :phone, 'OUTBOUND', 'text', :status, :raw_data, datetime('now')
                    )
                ");
                $ins->execute([
                    'portal_id' => $this->b24->getPortalId(),
                    'dt_id' => $dtMessageId,
                    'phone' => $to,
                    'status' => $status,
                    'raw_data' => json_encode(['text' => $text, 'time' => date('Y-m-d H:i:s')]),
                ]);
            }
        }

        return ['status' => 'updated', 'dt_message_id' => $dtMessageId, 'new_status' => $status];
    }

    /**
     * Handle First-time Lead & Meta CTWA Ad attribution
     */
    private function handleNewLead(array $payload): array
    {
        $phone = (string)($payload['customerPhone'] ?? $payload['from'] ?? '');
        $name = (string)($payload['customerName'] ?? $payload['contact']['name'] ?? 'WhatsApp Lead');
        $isCtwa = !empty($payload['isCTWA']);
        $referral = $payload['referral'] ?? null;
        $firstText = $payload['message']['text'] ?? null;

        $res = $this->crmLead->createOrUpdateFromWhatsApp($phone, $name, $isCtwa, $referral, $firstText);

        return ['status' => 'lead_processed', 'result' => $res];
    }

    /**
     * Handle WhatsApp conversation fee tracking
     */
    private function handleConversationCost(array $payload): array
    {
        $cost = $payload['amount'] ?? $payload['cost'] ?? 0;
        $phone = $payload['customerPhone'] ?? $payload['phone'] ?? '';
        Logger::info("WhatsApp Conversation Fee Charged: {$cost}", ['phone' => $phone, 'payload' => $payload]);

        return ['status' => 'cost_logged', 'cost' => $cost];
    }

    /**
     * Handle DoubleTick SLA escalation
     */
    private function handleSlaEscalated(array $payload): array
    {
        $customerPhone = (string)($payload['customerPhoneNumber'] ?? $payload['phone'] ?? '');
        $agentName = (string)($payload['agentName'] ?? 'Assigned Agent');

        // Create notification in Bitrix24
        $this->b24->call('im.notify.personal.add', [
            'USER_ID' => 1, // Admin / Manager
            'MESSAGE' => "🚨 KEEN DoubleTick SLA Breach! Conversation with {$customerPhone} (Agent: {$agentName}) has escalated.",
        ]);

        Logger::warning("DoubleTick SLA Escalation Alert Triggered", ['payload' => $payload]);
        return ['status' => 'sla_alert_sent'];
    }

    private function logWebhook(string $source, string $event, array $payload): void
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("
                INSERT INTO webhook_logs (source, event_type, payload, created_at)
                VALUES (:source, :event, :payload, datetime('now'))
            ");
            $stmt->execute([
                'source' => $source,
                'event' => $event,
                'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            ]);
        } catch (Exception $e) {
            // Ignore DB log error
        }
    }
}
