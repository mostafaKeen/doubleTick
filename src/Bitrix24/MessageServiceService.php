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
            'NAME' => 'DoubleTick WhatsApp',
            'DESCRIPTION' => 'Official DoubleTick WhatsApp Business API provider',
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
            $res = $dtClient->sendTextMessage($phone, $text);
            if (!empty($res['messageId']) || !empty($res['dtMessageId'])) {
                $this->updateStatus($b24MessageId, 'delivered');
            } else {
                $this->updateStatus($b24MessageId, 'failed');
            }
        } catch (Exception $e) {
            Logger::error("Failed to send timeline message via DoubleTick: " . $e->getMessage());
            $this->updateStatus($b24MessageId, 'undelivered');
        }
    }
}
