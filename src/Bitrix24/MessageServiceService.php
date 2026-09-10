<?php

declare(strict_types=1);

namespace DoubleTickB24\Bitrix24;

use DoubleTickB24\Core\Logger;
use DoubleTickB24\DoubleTick\DoubleTickClient;
use Exception;

class MessageServiceService
{
    private BitrixClient $b24;
    private string $senderCode;

    public function __construct(BitrixClient $b24, ?string $senderCode = null)
    {
        $this->b24 = $b24;
        $this->senderCode = $senderCode ?: 'doubletick_wa';
    }

    /**
     * Register Message Provider in Bitrix24
     */
    public function register(string $handlerUrl): array
    {
        $res = $this->b24->call('messageservice.sender.add', [
            'CODE' => $this->senderCode,
            'TYPE' => 'SMS',
            'HANDLER' => $handlerUrl,
            'NAME' => 'KEEN DoubleTick',
            'DESCRIPTION' => 'Official KEEN DoubleTick WhatsApp Business API provider',
        ]);

        Logger::info("messageservice.sender.add response", ['result' => $res]);
        return $res;
    }

    /**
     * Update message delivery status in Bitrix24 CRM Timeline
     */
    public function updateStatus(string $b24MessageId, string $status): array
    {
        // Status mapping: delivered, undelivered, failed, read
        return $this->b24->call('messageservice.message.status.update', [
            'CODE' => $this->senderCode,
            'MESSAGE_ID' => $b24MessageId,
            'STATUS' => $status,
        ]);
    }

    /**
     * Handle outbound message from Bitrix24 CRM timeline
     */
    public function handleSend(array $payload, DoubleTickClient $dtClient): void
    {
        $b24MessageId = (string)($payload['message_id'] ?? '');
        $phone = (string)($payload['message_to'] ?? '');
        $text = (string)($payload['message_body'] ?? '');

        if (empty($phone) || empty($text)) {
            Logger::error("Invalid messageservice payload: empty phone or text");
            return;
        }

        try {
            $trimmed = trim($text);
            if (str_starts_with(strtolower($trimmed), 'template:')) {
                // Operator typed template syntax e.g. "template:welcome_message" or "template:name|param1|param2"
                $rawTpl = substr($trimmed, strlen('template:'));
                $parts = explode('|', $rawTpl);
                $templateName = trim(array_shift($parts));
                $params = array_values(array_filter(array_map('trim', $parts)));
                $res = $dtClient->sendTemplateMessage($phone, $templateName, 'en', $params);
            } else {
                $res = $dtClient->sendTextMessage($phone, $text);
            }

            if (!empty($res['messageId']) || !empty($res['dtMessageId']) || (!empty($res['status']) && $res['status'] === 'SENT')) {
                $this->updateStatus($b24MessageId, 'delivered');

                // Persist outbound message in database for the CRM placement tab
                try {
                    $dtMsgId = (string)($res['messageId'] ?? $res['dtMessageId'] ?? ('timeline_' . uniqid()));
                    $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
                    $db = \DoubleTickB24\Core\Database::getInstance();
                    $stmt = $db->prepare("
                        INSERT INTO message_mappings (
                            portal_id, b24_chat_id, b24_message_id, dt_message_id, whatsapp_message_id,
                            customer_phone, direction, message_type, status, raw_data, created_at
                        ) VALUES (
                            :portal_id, 0, :b24_msg_id, :dt_id, NULL,
                            :phone, 'OUTBOUND', :type, 'delivered', :raw_data, datetime('now')
                        )
                    ");
                    $stmt->execute([
                        'portal_id' => $this->b24->getPortalId(),
                        'b24_msg_id' => (int)$b24MessageId,
                        'dt_id' => $dtMsgId,
                        'phone' => $cleanPhone,
                        'type' => str_starts_with(strtolower($trimmed), 'template:') ? 'template' : 'text',
                        'raw_data' => json_encode(['text' => $text, 'time' => date('Y-m-d H:i:s')]),
                    ]);
                } catch (\Throwable $dbEx) {
                    Logger::warning("Could not save timeline message to message_mappings: " . $dbEx->getMessage());
                }
            } else {
                $errMsg = $res['error'] ?? 'Send failed';
                Logger::warning("Timeline message delivery failed: " . (is_array($errMsg) ? json_encode($errMsg) : $errMsg), [
                    'recipient' => $phone,
                    'b24MessageId' => $b24MessageId,
                ]);
                $this->updateStatus($b24MessageId, 'failed');
            }
        } catch (Exception $e) {
            Logger::error("Failed to send timeline message via DoubleTick: " . $e->getMessage());
            $this->updateStatus($b24MessageId, 'undelivered');
        }
    }
}
