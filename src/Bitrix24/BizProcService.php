<?php

declare(strict_types=1);

namespace DoubleTickB24\Bitrix24;

use DoubleTickB24\Core\Logger;
use DoubleTickB24\DoubleTick\DoubleTickClient;
use Exception;

class BizProcService
{
    private BitrixClient $b24;

    public function __construct(BitrixClient $b24)
    {
        $this->b24 = $b24;
    }

    /**
     * Register workflow activities / CRM automation robots
     */
    public function registerActivities(string $activityHandlerUrl): array
    {
        $results = [];

        // Activity 1: Send WhatsApp Template
        $resTemplate = $this->b24->call('bizproc.activity.add', [
            'CODE' => 'dt_send_template',
            'HANDLER' => $activityHandlerUrl,
            'AUTH_USER_ID' => 1,
            'USE_SUBSCRIPTION' => 'Y',
            'NAME' => [
                'en' => 'DoubleTick: Send WhatsApp Template',
                'ru' => 'DoubleTick: Отправить шаблон WhatsApp',
            ],
            'DESCRIPTION' => [
                'en' => 'Send pre-approved WhatsApp template via DoubleTick with dynamic CRM placeholders',
                'ru' => 'Отправка шаблона WhatsApp через DoubleTick',
            ],
            'PROPERTIES' => [
                'phone' => [
                    'Name' => ['en' => 'Phone Number', 'ru' => 'Номер телефона'],
                    'Type' => 'string',
                    'Required' => 'Y',
                    'Multiple' => 'N',
                ],
                'template_name' => [
                    'Name' => ['en' => 'Template Name', 'ru' => 'Имя шаблона'],
                    'Type' => 'string',
                    'Required' => 'Y',
                    'Multiple' => 'N',
                ],
                'language' => [
                    'Name' => ['en' => 'Language Code (e.g. en)', 'ru' => 'Код языка'],
                    'Type' => 'string',
                    'Required' => 'Y',
                    'Default' => 'en',
                ],
                'param_1' => [
                    'Name' => ['en' => 'Placeholder 1 ({{1}})', 'ru' => 'Параметр 1'],
                    'Type' => 'string',
                    'Required' => 'N',
                ],
                'param_2' => [
                    'Name' => ['en' => 'Placeholder 2 ({{2}})', 'ru' => 'Параметр 2'],
                    'Type' => 'string',
                    'Required' => 'N',
                ],
                'param_3' => [
                    'Name' => ['en' => 'Placeholder 3 ({{3}})', 'ru' => 'Параметр 3'],
                    'Type' => 'string',
                    'Required' => 'N',
                ],
            ],
            'RETURN_PROPERTIES' => [
                'status' => [
                    'Name' => ['en' => 'Send Status', 'ru' => 'Статус отправки'],
                    'Type' => 'string',
                ],
                'message_id' => [
                    'Name' => ['en' => 'DoubleTick Message ID', 'ru' => 'ID сообщения'],
                    'Type' => 'string',
                ],
            ],
        ]);
        $results['dt_send_template'] = $resTemplate;

        // Activity 2: Send WhatsApp Quick Text
        $resText = $this->b24->call('bizproc.activity.add', [
            'CODE' => 'dt_send_text',
            'HANDLER' => $activityHandlerUrl,
            'AUTH_USER_ID' => 1,
            'USE_SUBSCRIPTION' => 'Y',
            'NAME' => [
                'en' => 'DoubleTick: Send Direct WhatsApp Message',
                'ru' => 'DoubleTick: Отправить сообщение WhatsApp',
            ],
            'DESCRIPTION' => [
                'en' => 'Send instant WhatsApp text message (within active 24-hr session)',
            ],
            'PROPERTIES' => [
                'phone' => [
                    'Name' => ['en' => 'Phone Number'],
                    'Type' => 'string',
                    'Required' => 'Y',
                ],
                'message' => [
                    'Name' => ['en' => 'Message Text'],
                    'Type' => 'text',
                    'Required' => 'Y',
                ],
            ],
            'RETURN_PROPERTIES' => [
                'status' => ['Name' => ['en' => 'Send Status'], 'Type' => 'string'],
                'message_id' => ['Name' => ['en' => 'Message ID'], 'Type' => 'string'],
            ]
        ]);
        $results['dt_send_text'] = $resText;

        Logger::info("bizproc.activity.add registration", ['results' => $results]);
        return $results;
    }

    /**
     * Handle incoming activity execution request from Bitrix24
     */
    public function handleExecution(array $payload, DoubleTickClient $dtClient): void
    {
        $eventToken = $payload['event_token'] ?? null;
        $code = $payload['code'] ?? '';
        $properties = $payload['properties'] ?? [];

        if (!$eventToken) {
            Logger::error("Missing event_token in bizproc execution request");
            return;
        }

        $phone = (string)($properties['phone'] ?? '');
        $status = 'failed';
        $messageId = '';

        try {
            if ($code === 'dt_send_template') {
                $templateName = (string)($properties['template_name'] ?? '');
                $language = (string)($properties['language'] ?? 'en');
                $placeholders = [];
                foreach (['param_1', 'param_2', 'param_3'] as $idx => $paramKey) {
                    if (!empty($properties[$paramKey])) {
                        $placeholders[] = (string)$properties[$paramKey];
                    }
                }

                $templateData = [];
                if (!empty($placeholders)) {
                    $templateData['body'] = ['placeholders' => $placeholders];
                }

                $res = $dtClient->sendTemplateMessage($phone, $templateName, $language, $templateData);
                if (!empty($res['messageId']) || !empty($res['dtMessageId'])) {
                    $status = 'success';
                    $messageId = (string)($res['messageId'] ?? $res['dtMessageId']);
                }
            } elseif ($code === 'dt_send_text') {
                $text = (string)($properties['message'] ?? '');
                $res = $dtClient->sendTextMessage($phone, $text);
                if (!empty($res['messageId']) || !empty($res['dtMessageId'])) {
                    $status = 'success';
                    $messageId = (string)($res['messageId'] ?? $res['dtMessageId']);
                }
            }
        } catch (Exception $e) {
            Logger::error("Error executing bizproc activity {$code}: " . $e->getMessage());
        }

        // Return output parameters back to Bitrix24 workflow
        $this->b24->call('bizproc.event.send', [
            'event_token' => $eventToken,
            'return_values' => [
                'status' => $status,
                'message_id' => $messageId,
            ],
        ]);
    }
}
