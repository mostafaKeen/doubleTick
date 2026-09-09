<?php

declare(strict_types=1);

namespace DoubleTickB24\Bitrix24;

use DoubleTickB24\Core\Database;
use DoubleTickB24\Core\Logger;
use DoubleTickB24\DoubleTick\DoubleTickClient;
use Exception;
use PDO;

class ImConnectorService
{
    private BitrixClient $b24;
    private string $connectorId;

    public function __construct(BitrixClient $b24, ?string $connectorId = null)
    {
        $this->b24 = $b24;
        $this->connectorId = $connectorId ?: 'doubletick_whatsapp';
    }

    /**
     * Register Open Channels custom connector
     */
    public function register(string $placementHandlerUrl, string $eventHandlerUrl): array
    {
        // DoubleTick SVG icon
        $svgIcon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#25D366"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91C2.13 13.66 2.59 15.36 3.45 16.86L2.05 22L7.3 20.62C8.75 21.41 10.38 21.83 12.04 21.83C17.5 21.83 21.95 17.38 21.95 11.92C21.95 9.27 20.92 6.78 19.05 4.91C17.18 3.03 14.69 2 12.04 2M12.05 3.67C14.25 3.67 16.31 4.53 17.87 6.09C19.42 7.65 20.28 9.72 20.28 11.92C20.28 16.46 16.58 20.15 12.04 20.15C10.56 20.15 9.11 19.76 7.85 19L7.55 18.83L4.43 19.65L5.26 16.61L5.06 16.3C4.24 14.99 3.8 13.47 3.8 11.91C3.81 7.37 7.5 3.67 12.05 3.67Z"/></svg>';
        $dataImage = 'data:image/svg+xml;charset=US-ASCII,' . rawurlencode($svgIcon);

        $params = [
            'ID' => $this->connectorId,
            'NAME' => 'DoubleTick WhatsApp',
            'ICON' => [
                'DATA_IMAGE' => $dataImage,
                'COLOR' => '#3F906D',
                'SIZE' => '80%',
                'POSITION' => 'center',
            ],
            'PLACEMENT_HANDLER' => $placementHandlerUrl,
            'DEL_EXTERNAL_MESSAGES' => 'Y',
            'EDIT_INTERNAL_MESSAGES' => 'Y',
            'DEL_INTERNAL_MESSAGES' => 'Y',
            'NEWSLETTER' => 'Y',
            'NEED_SYSTEM_MESSAGES' => 'Y',
            'NEED_SIGNATURE' => 'N',
            'CHAT_GROUP' => 'N',
        ];

        $regResult = $this->b24->call('imconnector.register', $params);
        Logger::info("imconnector.register response", ['result' => $regResult]);

        // Subscribe to outgoing operator message event: OnImConnectorMessageAdd
        $eventResult = $this->b24->call('event.bind', [
            'event' => 'OnImConnectorMessageAdd',
            'handler' => $eventHandlerUrl,
        ]);
        Logger::info("event.bind OnImConnectorMessageAdd response", ['result' => $eventResult]);

        return [
            'connector' => $regResult,
            'event' => $eventResult,
        ];
    }

    /**
     * Activate the connector on a specific Open Line
     */
    public function activate(int $lineId, bool $active = true): array
    {
        return $this->b24->call('imconnector.activate', [
            'CONNECTOR' => $this->connectorId,
            'LINE' => $lineId,
            'ACTIVE' => $active ? 1 : 0,
        ]);
    }

    /**
     * Send an incoming WhatsApp message into Bitrix24 Open Lines
     */
    public function sendIncomingMessage(
        int $lineId,
        string $customerPhone,
        string $customerName,
        string $dtMessageId,
        string $text,
        array $files = [],
        ?string $whatsappMessageId = null
    ): array {
        $cleanPhone = preg_replace('/[^0-9]/', '', $customerPhone);

        $messagePayload = [
            'id' => $dtMessageId,
            'date' => time(),
            'text' => $text,
        ];
        if (!empty($files)) {
            $messagePayload['files'] = $files;
        }

        $b24Payload = [
            'CONNECTOR' => $this->connectorId,
            'LINE' => $lineId,
            'MESSAGES' => [
                [
                    'user' => [
                        'id' => $cleanPhone,
                        'name' => $customerName ?: $cleanPhone,
                        'phone' => $cleanPhone,
                        'skip_phone_validate' => 'Y',
                    ],
                    'message' => $messagePayload,
                    'chat' => [
                        'id' => $cleanPhone,
                        'name' => $customerName ? "{$customerName} ({$cleanPhone})" : $cleanPhone,
                    ]
                ]
            ]
        ];

        $res = $this->b24->call('imconnector.send.messages', $b24Payload);
        Logger::info("imconnector.send.messages response", ['res' => $res, 'dt_id' => $dtMessageId]);

        // Save mapping
        if (!empty($res['result']['SUCCESS']) && !empty($res['result']['DATA'])) {
            $db = Database::getInstance();
            $stmt = $db->prepare("
                INSERT INTO message_mappings (
                    portal_id, b24_chat_id, b24_message_id, dt_message_id, whatsapp_message_id,
                    customer_phone, direction, message_type, status, created_at
                ) VALUES (
                    :portal_id, :b24_chat_id, :b24_message_id, :dt_message_id, :whatsapp_message_id,
                    :customer_phone, 'INBOUND', :type, 'delivered', datetime('now')
                )
            ");

            foreach ($res['result']['DATA'] as $sentItem) {
                $b24ChatId = (int)($sentItem['chat']['id'] ?? 0);
                $b24MsgId = (int)($sentItem['message']['id'] ?? 0);
                $stmt->execute([
                    'portal_id' => $this->b24->getPortalId(),
                    'b24_chat_id' => $b24ChatId,
                    'b24_message_id' => $b24MsgId,
                    'dt_message_id' => $dtMessageId,
                    'whatsapp_message_id' => $whatsappMessageId,
                    'customer_phone' => $cleanPhone,
                    'type' => empty($files) ? 'text' : 'media',
                ]);
            }
        }

        return $res;
    }

    /**
     * Process outbound message from Bitrix24 operator (event: OnImConnectorMessageAdd)
     */
    public function processOutboundEvent(array $eventData, DoubleTickClient $dtClient): void
    {
        $messages = $eventData['data']['MESSAGES'] ?? [];
        $lineId = (int)($eventData['data']['LINE'] ?? 0);

        foreach ($messages as $msg) {
            $customerPhone = (string)($msg['chat']['id'] ?? '');
            $rawText = (string)($msg['message']['text'] ?? '');
            $b24ChatId = (int)($msg['im']['chat_id'] ?? 0);
            $b24MsgId = (int)($msg['im']['message_id'] ?? 0);

            if (empty($customerPhone)) {
                continue;
            }

            // Clean BBCode: strip [b]User:[/b], replace [br] with newline, strip tags
            $cleanText = preg_replace('/\[b\].*?\[\/b\]\s*/i', '', $rawText);
            $cleanText = str_ireplace(['[br]', '<br>', '<br/>'], "\n", $cleanText);
            $cleanText = strip_tags(preg_replace('/\[.*?\]/', '', $cleanText));
            $cleanText = trim($cleanText);

            if ($cleanText === '') {
                continue;
            }

            try {
                // Send text message via DoubleTick
                $dtRes = $dtClient->sendTextMessage($customerPhone, $cleanText);
                $dtMessageId = $dtRes['messageId'] ?? $dtRes['dtMessageId'] ?? ('out_' . uniqid());

                // Confirm delivery back to Bitrix24
                $this->b24->call('imconnector.send.status.delivery', [
                    'CONNECTOR' => $this->connectorId,
                    'LINE' => $lineId,
                    'MESSAGES' => [
                        [
                            'im' => [
                                'chat_id' => $b24ChatId,
                                'message_id' => $b24MsgId,
                            ],
                            'message' => [
                                'id' => $dtMessageId,
                            ],
                            'chat' => [
                                'id' => $customerPhone,
                            ],
                        ]
                    ]
                ]);

                // Store in database
                $db = Database::getInstance();
                $stmt = $db->prepare("
                    INSERT INTO message_mappings (
                        portal_id, b24_chat_id, b24_message_id, dt_message_id, whatsapp_message_id,
                        customer_phone, direction, message_type, status, created_at
                    ) VALUES (
                        :portal_id, :b24_chat_id, :b24_message_id, :dt_message_id, NULL,
                        :customer_phone, 'OUTBOUND', 'text', 'sent', datetime('now')
                    )
                ");
                $stmt->execute([
                    'portal_id' => $this->b24->getPortalId(),
                    'b24_chat_id' => $b24ChatId,
                    'b24_message_id' => $b24MsgId,
                    'dt_message_id' => $dtMessageId,
                    'customer_phone' => $customerPhone,
                ]);

                Logger::info("Outbound message successfully sent to DoubleTick", [
                    'to' => $customerPhone,
                    'dtMessageId' => $dtMessageId,
                ]);
            } catch (Exception $e) {
                Logger::error("Failed to send outbound message via DoubleTick", [
                    'error' => $e->getMessage(),
                    'customer' => $customerPhone,
                ]);
            }
        }
    }
}
