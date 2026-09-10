<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';
spl_autoload_register(function ($class) {
    $prefix = 'DoubleTickB24\\';
    $baseDir = dirname(__DIR__) . '/src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

use DoubleTickB24\Bitrix24\BitrixClient;
use DoubleTickB24\Core\Database;
use DoubleTickB24\Core\Logger;
use DoubleTickB24\DoubleTick\DoubleTickClient;

$config = require dirname(__DIR__) . '/config/config.php';

/**
 * Safely extract text, media URL, and media type from any DoubleTick/WhatsApp message structure.
 * Guaranteed to NEVER return the literal string "Array" or "[object Object]".
 */
function extractDtMessageContent($msg): array {
    $text = '';
    $mediaUrl = null;
    $mediaType = 'text';

    if (is_string($msg)) {
        $trimmed = trim($msg);
        if ($trimmed === 'Array' || $trimmed === '[object Object]') {
            return ['text' => '', 'media_url' => null, 'media_type' => 'text'];
        }
        if ($trimmed !== '' && ($trimmed[0] === '{' || $trimmed[0] === '[')) {
            $decoded = json_decode($trimmed, true);
            if (is_array($decoded)) {
                return extractDtMessageContent($decoded);
            }
        }
        return ['text' => $msg, 'media_url' => null, 'media_type' => 'text'];
    }

    if (!is_array($msg)) {
        return ['text' => (string)$msg, 'media_url' => null, 'media_type' => 'text'];
    }

    // Safe string resolver that recursively traverses nested arrays/objects and ignores metadata
    $strHelper = function($val) use (&$strHelper): string {
        if ($val === null) return '';
        if (is_string($val)) {
            $t = trim($val);
            return ($t === 'Array' || $t === '[object Object]') ? '' : $t;
        }
        if (is_numeric($val)) return (string)$val;
        if (is_array($val)) {
            if (isset($val['body'])) return $strHelper($val['body']);
            if (isset($val['text'])) return $strHelper($val['text']);
            if (isset($val['content'])) return $strHelper($val['content']);
            if (isset($val['value'])) return $strHelper($val['value']);
            if (isset($val['title'])) return $strHelper($val['title']);
            if (isset($val['message'])) return $strHelper($val['message']);
            // Try first valid string in sub-elements
            foreach ($val as $k => $sub) {
                if (in_array($k, ['id', 'messageId', 'sender_name', 'senderName', 'files'])) continue;
                $res = $strHelper($sub);
                if ($res !== '') return $res;
            }
        }
        return '';
    };

    // 1. Direct media URL checks
    foreach (['mediaUrl', 'url', 'fileUrl', 'attachmentUrl', 'link'] as $urlKey) {
        if (!empty($msg[$urlKey]) && is_string($msg[$urlKey])) {
            $mediaUrl = $msg[$urlKey];
            break;
        }
    }

    // 2. Direct message/media type
    if (!empty($msg['messageType']) && is_string($msg['messageType'])) {
        $mediaType = strtolower($msg['messageType']);
    } elseif (!empty($msg['type']) && is_string($msg['type'])) {
        $mediaType = strtolower($msg['type']);
    }

    // 3. Check media sub-objects (image, video, audio, document, file, voice)
    foreach (['image', 'video', 'audio', 'document', 'file', 'media', 'voice'] as $mediaKey) {
        if (!empty($msg[$mediaKey]) && is_array($msg[$mediaKey])) {
            $mediaType = $mediaKey;
            if (empty($mediaUrl)) {
                $mediaUrl = $msg[$mediaKey]['url'] ?? $msg[$mediaKey]['link'] ?? null;
            }
            if (empty($text) && !empty($msg[$mediaKey]['caption'])) {
                $text = $strHelper($msg[$mediaKey]['caption']);
            }
        }
    }

    // 4. Nested 'message' object check (recurse)
    if (empty($text) && isset($msg['message'])) {
        $nested = extractDtMessageContent($msg['message']);
        if ($nested['text'] !== '' && $nested['text'] !== 'Array') $text = $nested['text'];
        if (!$mediaUrl && $nested['media_url']) $mediaUrl = $nested['media_url'];
        if ($nested['media_type'] !== 'text') $mediaType = $nested['media_type'];
    }

    // 5. Common text container fields
    if (empty($text)) {
        foreach (['text', 'body', 'caption', 'content', 'messageText', 'description', 'title'] as $tKey) {
            if (isset($msg[$tKey])) {
                $candidate = $strHelper($msg[$tKey]);
                if ($candidate !== '' && $candidate !== 'Array') {
                    $text = $candidate;
                    break;
                }
            }
        }
    }

    // 6. Interactive / Buttons / Quick replies
    if (empty($text) && !empty($msg['interactive']) && is_array($msg['interactive'])) {
        $inter = $msg['interactive'];
        if (!empty($inter['button_reply']['title'])) {
            $text = "🔘 " . $strHelper($inter['button_reply']['title']);
        } elseif (!empty($inter['list_reply']['title'])) {
            $text = "📋 " . $strHelper($inter['list_reply']['title']);
        } elseif (!empty($inter['body']['text'])) {
            $text = $strHelper($inter['body']['text']);
        }
    }
    if (empty($text) && !empty($msg['button']) && is_array($msg['button'])) {
        $text = "🔘 " . $strHelper($msg['button']['text'] ?? $msg['button']['payload'] ?? '');
    }

    // 7. Template checks
    if (empty($text) && !empty($msg['templateId'])) {
        $text = "📋 Template: " . (is_string($msg['templateId']) ? $msg['templateId'] : json_encode($msg['templateId']));
        $mediaType = 'template';
    } elseif (empty($text) && !empty($msg['template_name'])) {
        $text = "📋 Template: " . (is_string($msg['template_name']) ? $msg['template_name'] : json_encode($msg['template_name']));
        $mediaType = 'template';
    }

    // 8. Location checks
    if (empty($text) && !empty($msg['location']) && is_array($msg['location'])) {
        $locName = $strHelper($msg['location']['name'] ?? 'Location');
        $text = "📍 " . $locName;
    }

    // 9. If still empty, scan any remaining string values (ignoring technical ID and metadata fields)
    if ($text === '' && empty($mediaUrl)) {
        $ignored = [
            'id', 'messageId', 'dtMessageId', 'senderId', 'integrationId', 'type', 'messageType',
            'status', 'direction', 'messageOriginType', 'integrationWabaNumber', 'integrationDisplayName',
            'from', 'to', 'wabaNumber', 'customerNumber', 'sender_name', 'senderName', 'name', 'time',
            'date', 'created_at', 'updated_at', 'files', 'portal_id', 'b24_chat_id', 'b24_message_id'
        ];
        foreach ($msg as $k => $v) {
            if (!in_array($k, $ignored)) {
                $candidate = $strHelper($v);
                if ($candidate !== '' && $candidate !== 'Array' && strlen($candidate) < 2000) {
                    $text = $candidate;
                    break;
                }
            }
        }
    }

    if ($text === 'Array' || $text === '[object Object]') {
        $text = '';
    }

    return [
        'text' => (string)$text,
        'media_url' => $mediaUrl,
        'media_type' => $mediaType,
    ];
}

// -------------------------------------------------------------
// AJAX Action: Fetch Live Chat History & 24h Window Status
// -------------------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'get_chat_history') {
    header('Content-Type: application/json');
    $phone = trim((string)($_GET['phone'] ?? ''));
    $memberId = (string)($_GET['member_id'] ?? '');
    $domain = (string)($_GET['domain'] ?? '');

    $b24 = null;
    if ($memberId) {
        $b24 = BitrixClient::getByMemberId($memberId);
    }
    if (!$b24 && $domain) {
        $b24 = BitrixClient::getByDomain($domain);
    }
    if (!$b24) {
        $b24 = BitrixClient::getFirstActive();
    }
    $apiKey = $b24 ? $b24->getDoubleTickApiKey() : $config['doubletick']['api_key'];
    $waba = $b24 ? $b24->getDoubleTickWaba() : $config['doubletick']['default_waba'];

    if (!$apiKey) {
        echo json_encode(['success' => false, 'error' => 'DoubleTick API Key not configured.']);
        exit;
    }
    if (!$phone) {
        echo json_encode(['success' => false, 'error' => 'No phone number provided.']);
        exit;
    }

    $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
    $intlPhone = $cleanPhone;
    if (str_starts_with($cleanPhone, '0') && strlen($cleanPhone) >= 10) {
        if ($waba && strlen($waba) >= 10) {
            $wabaPrefix = substr(preg_replace('/[^0-9]/', '', $waba), 0, 2);
            $intlPhone = $wabaPrefix . substr($cleanPhone, 1);
        } elseif (strlen($cleanPhone) === 11 && str_starts_with($cleanPhone, '01')) {
            $intlPhone = '20' . substr($cleanPhone, 1);
        }
    }

    $messages = [];
    $seenIds = [];
    $rawDtRes = null;
    $dtFetchError = null;

    $dt = new DoubleTickClient($apiKey, $waba, $config['doubletick']['api_url']);

    // 1. Fetch remote messages from DoubleTick API
    try {
        $dtRes = $dt->getChatMessages($intlPhone, $waba);
        $rawDtRes = $dtRes;

        $remoteItems = [];
        if (!empty($dtRes['messages']) && is_array($dtRes['messages'])) {
            $remoteItems = $dtRes['messages'];
        } elseif (!empty($dtRes['data']['messages']) && is_array($dtRes['data']['messages'])) {
            $remoteItems = $dtRes['data']['messages'];
        } elseif (!empty($dtRes['data']) && is_array($dtRes['data'])) {
            $remoteItems = $dtRes['data'];
        }

        // Fallback: if intlPhone returned no messages and cleanPhone is different, query cleanPhone
        if (empty($remoteItems) && $intlPhone !== $cleanPhone) {
            try {
                $fallbackRes = $dt->getChatMessages($cleanPhone, $waba);
                if (!empty($fallbackRes['messages']) && is_array($fallbackRes['messages'])) {
                    $remoteItems = $fallbackRes['messages'];
                    $rawDtRes = $fallbackRes;
                } elseif (!empty($fallbackRes['data']['messages']) && is_array($fallbackRes['data']['messages'])) {
                    $remoteItems = $fallbackRes['data']['messages'];
                    $rawDtRes = $fallbackRes;
                }
            } catch (\Throwable $fbEx) {
                // Ignore fallback error
            }
        }

        foreach ($remoteItems as $m) {
            $msgId = (string)($m['id'] ?? $m['messageId'] ?? uniqid());

            // Direction: messageOriginType is CUSTOMER (inbound) or USER / ORGANIZATION / SYSTEM (outbound)
            $origin = strtoupper((string)($m['messageOriginType'] ?? ''));
            if ($origin === 'CUSTOMER') {
                $isOutbound = false;
            } elseif ($origin !== '') {
                $isOutbound = true;
            } else {
                $sender = preg_replace('/[^0-9]/', '', (string)($m['sender'] ?? $m['from'] ?? ''));
                $cleanWaba = preg_replace('/[^0-9]/', '', (string)$waba);
                $isOutbound = ($sender && ($sender === $cleanWaba || str_ends_with($cleanWaba, $sender)));
            }

            // Extract content
            $extractInput = $m['message'] ?? $m;
            $content = extractDtMessageContent($extractInput);
            $text = $content['text'];
            $mediaUrl = $content['media_url'] ?: ($m['mediaUrl'] ?? null);

            // Debug: capture extraction input/output for diagnostics
            $extractionDebug = [
                'input_type' => gettype($extractInput),
                'input_keys' => is_array($extractInput) ? array_keys($extractInput) : null,
                'input_text_field' => is_array($extractInput) ? (isset($extractInput['text']) ? (is_array($extractInput['text']) ? 'ARRAY:' . json_encode($extractInput['text']) : 'STRING:' . substr((string)$extractInput['text'], 0, 200)) : 'NOT_SET') : null,
                'input_body_field' => is_array($extractInput) ? (isset($extractInput['body']) ? (is_array($extractInput['body']) ? 'ARRAY:' . json_encode($extractInput['body']) : 'STRING:' . substr((string)$extractInput['body'], 0, 200)) : 'NOT_SET') : null,
                'extract_result_text' => $content['text'],
                'extract_result_media' => $content['media_url'],
                'has_message_key' => isset($m['message']),
                'message_key_type' => isset($m['message']) ? gettype($m['message']) : null,
                'raw_message_snippet' => json_encode($m, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR),
            ];

            // If text is still empty or "Array", check root fields of $m safely
            if ($text === '' || $text === 'Array' || $text === '[object Object]') {
                $fallback = extractDtMessageContent($m);
                $extractionDebug['fallback_text'] = $fallback['text'];
                if ($fallback['text'] !== '' && $fallback['text'] !== 'Array') {
                    $text = $fallback['text'];
                }
            }

            // Final safety filter
            if ($text === 'Array' || $text === '[object Object]') {
                $text = '';
            }
            $extractionDebug['final_text'] = $text;

            // Timestamp: DoubleTick messageTime is epoch milliseconds (e.g. 1737612046032)
            $rawTs = $m['messageTime'] ?? $m['timestamp'] ?? null;
            if (!empty($rawTs)) {
                if (is_numeric($rawTs)) {
                    $ts = ((float)$rawTs > 2000000000) ? (int)round((float)$rawTs / 1000) : (int)$rawTs;
                } else {
                    $ts = strtotime((string)$rawTs) ?: time();
                }
            } else {
                $ts = time();
            }

            // Status
            $status = 'sent';
            if (!empty($m['readCount']) && (int)$m['readCount'] > 0) {
                $status = 'read';
            } elseif (!empty($m['deliveryCount']) && (int)$m['deliveryCount'] > 0) {
                $status = 'delivered';
            } elseif (!empty($m['sentCount']) && (int)$m['sentCount'] > 0) {
                $status = 'sent';
            } elseif (!empty($m['erroredCount']) && (int)$m['erroredCount'] > 0) {
                $status = 'failed';
            } elseif (!empty($m['status'])) {
                $status = strtolower((string)$m['status']);
            }

            $isTemplate = !empty($m['templateId']);

            $seenIds[$msgId] = true;
            $messages[] = [
                'id' => $msgId,
                'direction' => $isOutbound ? 'OUTBOUND' : 'INBOUND',
                'text' => $text,
                'media_url' => $mediaUrl,
                'timestamp' => $ts,
                'time_str' => date('h:i A', $ts),
                'date_str' => date('M j, Y', $ts),
                'status' => $status,
                'type' => $isTemplate ? 'template' : ($mediaUrl ? 'media' : 'text'),
                'raw_message' => $m,
                '_extraction_debug' => $extractionDebug,
            ];
        }
    } catch (\Throwable $e) {
        $dtFetchError = $e->getMessage();
        Logger::warning("Could not fetch remote chat messages: " . $e->getMessage());
    }

    // 2. Fetch local messages from database (sent via Bitrix24 or local tab)
    $localRowsCount = 0;
    try {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT * FROM message_mappings 
            WHERE customer_phone LIKE :phone 
               OR customer_phone LIKE :clean_phone
               OR customer_phone LIKE :intl_phone
            ORDER BY id ASC
        ");
        $phoneSuffix = substr($cleanPhone, -9);
        $stmt->execute([
            'phone' => '%' . $phoneSuffix,
            'clean_phone' => '%' . $cleanPhone . '%',
            'intl_phone' => '%' . $intlPhone . '%',
        ]);
        $localRows = $stmt->fetchAll();
        $localRowsCount = count($localRows);

        foreach ($localRows as $row) {
            $dtId = (string)($row['dt_message_id'] ?? '');
            if ($dtId && isset($seenIds[$dtId])) {
                continue;
            }
            $text = '';
            $mediaUrl = null;
            if (!empty($row['raw_data'])) {
                $content = extractDtMessageContent($row['raw_data']);
                $text = $content['text'];
                $mediaUrl = $content['media_url'];
                if (!$text && !empty($raw['template_name'])) {
                    $text = "📋 Template: " . $raw['template_name'];
                }
            }

            // Auto-recovery from webhook logs if raw_data was empty or corrupted with legacy string ("Array")
            if ($text === '' || $text === 'Array' || $text === '[object Object]') {
                try {
                    $foundPayload = null;
                    // 1. Try finding by dt_message_id if present
                    if (!empty($dtId)) {
                        $whStmt = $db->prepare("SELECT payload FROM webhook_logs WHERE payload LIKE :search ORDER BY id DESC LIMIT 1");
                        $whStmt->execute(['search' => '%' . $dtId . '%']);
                        $whRow = $whStmt->fetch();
                        if ($whRow && !empty($whRow['payload'])) {
                            $foundPayload = $whRow['payload'];
                        }
                    }
                    // 2. Try finding by whatsapp_message_id if present
                    if (!$foundPayload && !empty($row['whatsapp_message_id'])) {
                        $whStmt = $db->prepare("SELECT payload FROM webhook_logs WHERE payload LIKE :search ORDER BY id DESC LIMIT 1");
                        $whStmt->execute(['search' => '%' . $row['whatsapp_message_id'] . '%']);
                        $whRow = $whStmt->fetch();
                        if ($whRow && !empty($whRow['payload'])) {
                            $foundPayload = $whRow['payload'];
                        }
                    }
                    // 3. Fallback: match by phone number in webhook logs
                    if (!$foundPayload) {
                        $whStmt = $db->prepare("
                            SELECT payload FROM webhook_logs 
                            WHERE payload LIKE :phone OR payload LIKE :suffix
                            ORDER BY id DESC LIMIT 10
                        ");
                        $whStmt->execute([
                            'phone' => '%' . $cleanPhone . '%',
                            'suffix' => '%' . $phoneSuffix . '%',
                        ]);
                        $whRows = $whStmt->fetchAll();
                        foreach ($whRows as $whRow) {
                            if (!empty($whRow['payload'])) {
                                $whContent = extractDtMessageContent($whRow['payload']);
                                if ($whContent['text'] !== '' && $whContent['text'] !== 'Array' && $whContent['text'] !== '[object Object]') {
                                    $foundPayload = $whRow['payload'];
                                    break;
                                }
                            }
                        }
                    }

                    if ($foundPayload) {
                        $whContent = extractDtMessageContent($foundPayload);
                        if ($whContent['text'] !== '' && $whContent['text'] !== 'Array' && $whContent['text'] !== '[object Object]') {
                            $text = $whContent['text'];
                            if (!$mediaUrl && $whContent['media_url']) {
                                $mediaUrl = $whContent['media_url'];
                            }
                            // Backfill message_mappings so future queries are clean and instant
                            $upStmt = $db->prepare("UPDATE message_mappings SET raw_data = :rd WHERE id = :id");
                            $upStmt->execute([
                                'rd' => json_encode(['text' => $text, 'recovered_from_webhook' => true, 'time' => date('Y-m-d H:i:s')]),
                                'id' => $row['id'],
                            ]);
                        }
                    }
                } catch (\Throwable $whEx) {
                    // Non-fatal recovery attempt
                }
            }

            if ($text === 'Array' || $text === '[object Object]') {
                $text = '';
            }

            $ts = !empty($row['created_at']) ? strtotime($row['created_at']) : time();
            $messages[] = [
                'id' => $dtId ?: ('local_' . $row['id']),
                'direction' => strtoupper((string)($row['direction'] ?? 'OUTBOUND')),
                'text' => $text,
                'media_url' => $mediaUrl,
                'timestamp' => $ts,
                'time_str' => date('h:i A', $ts),
                'date_str' => date('M j, Y', $ts),
                'status' => strtolower((string)($row['status'] ?? 'sent')),
                'type' => (string)($row['message_type'] ?? 'text'),
                'raw_message' => $row,
            ];
        }
    } catch (\Throwable $e) {
        Logger::warning("Could not fetch local chat history: " . $e->getMessage());
    }

    // Sort all messages chronologically
    usort($messages, function ($a, $b) {
        return $a['timestamp'] <=> $b['timestamp'];
    });

    // 3. Fetch 24-hour customer window status
    $windowStatus = [
        'isOpen' => true, // optimistic default
        'expirationTimestamp' => null,
        'formattedRemaining' => null,
    ];
    try {
        $winRes = $dt->getChatWindowStatus($cleanPhone, $waba);
        if (isset($winRes['isOpen'])) {
            $windowStatus['isOpen'] = (bool)$winRes['isOpen'];
            $windowStatus['expirationTimestamp'] = $winRes['expirationTimestamp'] ?? null;
            if (!empty($winRes['expirationTimestamp'])) {
                $diff = (int)$winRes['expirationTimestamp'] - time();
                if ($diff > 0) {
                    $hours = floor($diff / 3600);
                    $mins = floor(($diff % 3600) / 60);
                    $windowStatus['formattedRemaining'] = "{$hours}h {$mins}m";
                }
            }
        }
    } catch (\Throwable $e) {
        // Window check non-fatal
    }

    echo json_encode([
        'success' => true,
        'messages' => $messages,
        'window_status' => $windowStatus,
        'waba' => $waba,
        'phone' => $cleanPhone,
        'raw_dt_response' => $rawDtRes,
        'dt_fetch_error' => $dtFetchError,
        'debug_info' => [
            'remote_count' => count($remoteItems ?? []),
            'local_count' => $localRowsCount,
            'total_messages' => count($messages),
            'waba' => $waba,
            'phone' => $cleanPhone,
            'timestamp' => date('Y-m-d H:i:s'),
        ],
    ]);
    exit;
}

// -------------------------------------------------------------
// AJAX Action: Send Direct WhatsApp Text Message
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_direct_message') {
    header('Content-Type: application/json');
    $phone = trim((string)($_POST['phone'] ?? ''));
    $text = trim((string)($_POST['text'] ?? ''));
    $memberId = (string)($_POST['member_id'] ?? '');
    $domain = (string)($_POST['domain'] ?? '');

    $b24 = null;
    if ($memberId) {
        $b24 = BitrixClient::getByMemberId($memberId);
    }
    if (!$b24 && $domain) {
        $b24 = BitrixClient::getByDomain($domain);
    }
    if (!$b24) {
        $b24 = BitrixClient::getFirstActive();
    }
    $apiKey = $b24 ? $b24->getDoubleTickApiKey() : $config['doubletick']['api_key'];
    $waba = $b24 ? $b24->getDoubleTickWaba() : $config['doubletick']['default_waba'];

    if (!$apiKey || !$phone || !$text) {
        echo json_encode(['success' => false, 'error' => 'API Key, phone and message text are required.']);
        exit;
    }

    try {
        $dt = new DoubleTickClient($apiKey, $waba, $config['doubletick']['api_url']);
        $res = $dt->sendTextMessage($phone, $text, $waba);
        $msgId = $res['messageId'] ?? ($res['dtMessageId'] ?? ('out_' . uniqid()));

        // Save to database
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("
                INSERT INTO message_mappings (
                    portal_id, b24_chat_id, b24_message_id, dt_message_id, whatsapp_message_id,
                    customer_phone, direction, message_type, status, raw_data, created_at
                ) VALUES (
                    :portal_id, 0, 0, :dt_id, NULL,
                    :phone, 'OUTBOUND', 'text', 'sent', :raw_data, datetime('now')
                )
            ");
            $stmt->execute([
                'portal_id' => $b24 ? $b24->getPortalId() : 1,
                'dt_id' => $msgId,
                'phone' => preg_replace('/[^0-9]/', '', $phone),
                'raw_data' => json_encode(['text' => $text, 'time' => date('Y-m-d H:i:s')]),
            ]);
        } catch (\Throwable $dbEx) {
            Logger::error("Failed to log sent message in database: " . $dbEx->getMessage());
        }

        echo json_encode([
            'success' => true,
            'message_id' => $msgId,
            'text' => $text,
            'time_str' => date('h:i A'),
            'raw_response' => $res,
        ]);
    } catch (\Throwable $e) {
        $msg = $e->getMessage();
        $isClosed = (stripos($msg, 'closed window') !== false || stripos($msg, 'template message') !== false || stripos($msg, 'window is closed') !== false);
        echo json_encode([
            'success' => false,
            'error' => $msg,
            'window_closed' => $isClosed,
            'raw_error' => $e->getTraceAsString(),
        ]);
    }
    exit;
}

// -------------------------------------------------------------
// AJAX Action: Send Approved WhatsApp Template Message
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_template') {
    header('Content-Type: application/json');
    $phone = trim((string)($_POST['phone'] ?? ''));
    $templateName = trim((string)($_POST['template_name'] ?? ''));
    $language = trim((string)($_POST['language'] ?? 'en'));
    $memberId = (string)($_POST['member_id'] ?? '');
    $domain = (string)($_POST['domain'] ?? '');

    $params = [];
    if (!empty($_POST['param1'])) $params[] = trim((string)$_POST['param1']);
    if (!empty($_POST['param2'])) $params[] = trim((string)$_POST['param2']);
    if (!empty($_POST['param3'])) $params[] = trim((string)$_POST['param3']);

    $b24 = null;
    if ($memberId) {
        $b24 = BitrixClient::getByMemberId($memberId);
    }
    if (!$b24 && $domain) {
        $b24 = BitrixClient::getByDomain($domain);
    }
    if (!$b24) {
        $b24 = BitrixClient::getFirstActive();
    }
    $apiKey = $b24 ? $b24->getDoubleTickApiKey() : $config['doubletick']['api_key'];
    $waba = $b24 ? $b24->getDoubleTickWaba() : $config['doubletick']['default_waba'];

    if (!$apiKey || !$phone || !$templateName) {
        echo json_encode(['success' => false, 'error' => 'API Key, phone and template name are required.']);
        exit;
    }

    try {
        $dt = new DoubleTickClient($apiKey, $waba, $config['doubletick']['api_url']);
        $res = $dt->sendTemplateMessage($phone, $templateName, $language, $params, $waba);
        if (!empty($res['error']) || (!empty($res['status_code']) && $res['status_code'] >= 400)) {
            $errDetail = is_array($res['error']) ? json_encode($res['error']) : (string)$res['error'];
            if (!empty($res['raw']['message'])) {
                $msgDetail = is_array($res['raw']['message']) ? implode(', ', $res['raw']['message']) : (string)$res['raw']['message'];
                $errDetail .= " ({$msgDetail})";
            }
            echo json_encode(['success' => false, 'error' => $errDetail, 'raw_response' => $res]);
        } else {
            $msgId = $res['messageId'] ?? ($res['messages'][0]['messageId'] ?? 'OK');

            // Save to database
            try {
                $db = Database::getInstance();
                $stmt = $db->prepare("
                    INSERT INTO message_mappings (
                        portal_id, b24_chat_id, b24_message_id, dt_message_id, whatsapp_message_id,
                        customer_phone, direction, message_type, status, raw_data, created_at
                    ) VALUES (
                        :portal_id, 0, 0, :dt_id, NULL,
                        :phone, 'OUTBOUND', 'template', 'sent', :raw_data, datetime('now')
                    )
                ");
                $stmt->execute([
                    'portal_id' => $b24 ? $b24->getPortalId() : 1,
                    'dt_id' => $msgId,
                    'phone' => preg_replace('/[^0-9]/', '', $phone),
                    'raw_data' => json_encode([
                        'text' => "📋 Template: {$templateName}",
                        'template_name' => $templateName,
                        'params' => $params,
                        'time' => date('Y-m-d H:i:s')
                    ]),
                ]);
            } catch (\Throwable $dbEx) {
                Logger::error("Failed to log template message in database: " . $dbEx->getMessage());
            }

            echo json_encode(['success' => true, 'message_id' => $msgId, 'raw_response' => $res]);
        }
    } catch (\Throwable $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage(), 'raw_error' => $e->getTraceAsString()]);
    }
    exit;
}

// -------------------------------------------------------------
// AJAX Action: Fetch Approved Templates
// -------------------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'get_templates') {
    header('Content-Type: application/json');
    $memberId = (string)($_GET['member_id'] ?? '');
    $domain = (string)($_GET['domain'] ?? '');

    $b24 = null;
    if ($memberId) {
        $b24 = BitrixClient::getByMemberId($memberId);
    }
    if (!$b24 && $domain) {
        $b24 = BitrixClient::getByDomain($domain);
    }
    if (!$b24) {
        $b24 = BitrixClient::getFirstActive();
    }
    $apiKey = $b24 ? $b24->getDoubleTickApiKey() : $config['doubletick']['api_key'];
    $waba = $b24 ? $b24->getDoubleTickWaba() : $config['doubletick']['default_waba'];

    try {
        $dt = new DoubleTickClient($apiKey, $waba, $config['doubletick']['api_url']);
        $tplRes = $dt->getTemplates('APPROVED');
        $templates = isset($tplRes['templates']) && is_array($tplRes['templates']) ? $tplRes['templates'] : (is_array($tplRes) ? $tplRes : []);
        echo json_encode(['success' => true, 'templates' => $templates, 'raw_response' => $tplRes]);
    } catch (\Throwable $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// -------------------------------------------------------------
// AJAX Action: Fetch AI Summary of 1:1 Conversation
// -------------------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'get_ai_summary') {
    header('Content-Type: application/json');
    $phone = (string)($_GET['phone'] ?? '');
    $memberId = (string)($_GET['member_id'] ?? '');
    $domain = (string)($_GET['domain'] ?? '');

    $b24 = null;
    if ($memberId) {
        $b24 = BitrixClient::getByMemberId($memberId);
    }
    if (!$b24 && $domain) {
        $b24 = BitrixClient::getByDomain($domain);
    }
    if (!$b24) {
        $b24 = BitrixClient::getFirstActive();
    }
    $apiKey = $b24 ? $b24->getDoubleTickApiKey() : $config['doubletick']['api_key'];
    $waba = $b24 ? $b24->getDoubleTickWaba() : $config['doubletick']['default_waba'];

    try {
        $dt = new DoubleTickClient($apiKey, $waba, $config['doubletick']['api_url']);
        $summary = $dt->getChatAiSummary($phone);
        echo json_encode(['success' => true, 'data' => $summary, 'raw_response' => $summary]);
    } catch (\Throwable $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

header_remove('X-Frame-Options');
header('Content-Security-Policy: frame-ancestors *');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KEEN DoubleTick</title>
    <script src="//api.bitrix24.com/api/v1/"></script>
    <style>
        :root {
            --primary: #3F906D;
            --primary-hover: #2e6e52;
            --primary-glow: rgba(63, 144, 109, 0.35);
            --wa-green: #25D366;
            --wa-dark-green: #005c4b;
            --wa-bubble-in: #202c33;
            --wa-bubble-out: #005c4b;
            --bg-dark: #0b141a;
            --bg-panel: #111b21;
            --card-bg: #1e293b;
            --card-border: #334155;
            --text-main: #e9edef;
            --text-muted: #8696a0;
            --text-dim: #64748b;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --blue-tick: #53bdeb;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            overflow: hidden;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Top Toolbar */
        .tab-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--bg-panel);
            padding: 10px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            flex-shrink: 0;
            z-index: 10;
        }
        .customer-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .avatar-circle {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3F906D, #1f4f3b);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 15px;
            color: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        .customer-meta {
            display: flex;
            flex-direction: column;
        }
        .customer-title-row {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .customer-name {
            font-weight: 600;
            font-size: 14px;
            color: #fff;
        }
        .phone-badge {
            background: rgba(63, 144, 109, 0.2);
            color: #4ade80;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-family: monospace;
            font-weight: 600;
        }
        .session-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            margin-top: 2px;
            color: var(--text-muted);
        }
        .status-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--success);
            display: inline-block;
        }
        .status-dot.dot-closed {
            background: var(--warning);
            box-shadow: 0 0 6px rgba(245, 158, 11, 0.6);
        }
        .status-dot.dot-active {
            background: var(--success);
            box-shadow: 0 0 6px rgba(16, 185, 129, 0.6);
        }

        .tab-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .btn-primary {
            background: var(--primary);
            color: #fff;
        }
        .btn-primary:hover {
            background: var(--primary-hover);
            box-shadow: 0 0 10px var(--primary-glow);
        }
        .btn-secondary {
            background: rgba(255, 255, 255, 0.08);
            color: var(--text-main);
            border-color: rgba(255, 255, 255, 0.12);
        }
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.14);
        }
        .btn-warning-glow {
            background: rgba(245, 158, 11, 0.2);
            color: #fbbf24;
            border-color: rgba(245, 158, 11, 0.4);
        }
        .btn-warning-glow:hover {
            background: rgba(245, 158, 11, 0.35);
        }

        /* Chat Layout */
        .chat-layout {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            position: relative;
            background-color: #0b141a;
            background-image: radial-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 0);
            background-size: 24px 24px;
        }

        /* Chat Messages Container */
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px 24px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .chat-messages::-webkit-scrollbar {
            width: 6px;
        }
        .chat-messages::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 3px;
        }

        /* Message Bubbles */
        .message-row {
            display: flex;
            width: 100%;
            margin-bottom: 2px;
            animation: fadeInMsg 0.25s ease forwards;
        }
        @keyframes fadeInMsg {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .message-row.inbound {
            justify-content: flex-start;
        }
        .message-row.outbound {
            justify-content: flex-end;
        }

        .bubble {
            max-width: 68%;
            padding: 8px 12px 6px 12px;
            border-radius: 12px;
            position: relative;
            font-size: 13.5px;
            line-height: 1.5;
            word-break: break-word;
            box-shadow: 0 1px 2px rgba(0,0,0,0.3);
        }
        .inbound .bubble {
            background-color: var(--wa-bubble-in);
            color: #e9edef;
            border-top-left-radius: 2px;
        }
        .outbound .bubble {
            background-color: var(--wa-bubble-out);
            color: #ffffff;
            border-top-right-radius: 2px;
        }

        .bubble-template {
            border: 1px solid rgba(255, 255, 255, 0.15);
            background: linear-gradient(135deg, #1b493e, #0e2e26) !important;
        }
        .template-tag {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 600;
            color: #a7f3d0;
            background: rgba(0, 0, 0, 0.25);
            padding: 2px 6px;
            border-radius: 4px;
            margin-bottom: 6px;
        }

        .bubble-footer {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 4px;
            margin-top: 4px;
            font-size: 11px;
            color: rgba(255, 255, 255, 0.6);
        }
        .status-check {
            font-size: 12px;
            line-height: 1;
        }
        .status-check.read {
            color: var(--blue-tick);
        }

        /* Date Divider */
        .date-divider {
            display: flex;
            justify-content: center;
            margin: 14px 0 8px 0;
        }
        .date-badge {
            background: rgba(17, 27, 33, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: var(--text-muted);
            font-size: 11px;
            font-weight: 500;
            padding: 4px 12px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }

        /* Closed Window Warning Banner */
        .window-banner {
            background: linear-gradient(90deg, rgba(245, 158, 11, 0.15), rgba(217, 119, 6, 0.1));
            border-top: 1px solid rgba(245, 158, 11, 0.3);
            border-bottom: 1px solid rgba(245, 158, 11, 0.3);
            padding: 9px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12.5px;
            color: #fef3c7;
        }
        .window-banner-text {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Quick Replies Bar */
        .quick-replies-bar {
            display: flex;
            gap: 8px;
            padding: 8px 16px;
            background: var(--bg-panel);
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            overflow-x: auto;
            flex-shrink: 0;
        }
        .quick-replies-bar::-webkit-scrollbar {
            display: none;
        }
        .quick-pill {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-main);
            padding: 5px 12px;
            border-radius: 16px;
            font-size: 12px;
            white-space: nowrap;
            cursor: pointer;
            transition: all 0.2s;
        }
        .quick-pill:hover {
            background: rgba(63, 144, 109, 0.25);
            border-color: var(--primary);
            color: #fff;
        }

        /* Chat Composer */
        .chat-composer {
            background: var(--bg-panel);
            padding: 10px 16px 14px 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: flex-end;
            gap: 10px;
            flex-shrink: 0;
        }
        .composer-input-wrapper {
            flex: 1;
            position: relative;
            background: #2a3942;
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: border-color 0.2s;
        }
        .composer-input-wrapper:focus-within {
            border-color: var(--primary);
        }
        .composer-textarea {
            width: 100%;
            background: transparent;
            border: none;
            outline: none;
            color: #e9edef;
            padding: 10px 14px;
            font-size: 14px;
            line-height: 1.4;
            resize: none;
            max-height: 120px;
            min-height: 22px;
            font-family: inherit;
        }
        .composer-textarea::placeholder {
            color: var(--text-muted);
        }
        .btn-send {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: var(--primary);
            color: #fff;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            flex-shrink: 0;
        }
        .btn-send:hover {
            background: var(--primary-hover);
            transform: scale(1.05);
            box-shadow: 0 0 12px var(--primary-glow);
        }
        .btn-send:disabled {
            background: #334155;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* Empty & Loading States */
        .empty-chat {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            text-align: center;
            color: var(--text-muted);
            padding: 40px;
        }
        .empty-icon {
            margin-bottom: 16px;
            opacity: 0.7;
        }

        /* Modals */
        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(4px);
            z-index: 100;
        }
        .modal-content {
            background: #1e293b;
            border: 1px solid var(--card-border);
            border-radius: 16px;
            width: 90%;
            max-width: 520px;
            margin: 40px auto;
            padding: 24px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.5);
            animation: modalPop 0.2s ease;
        }
        @keyframes modalPop {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        .form-group { margin-bottom: 14px; }
        .form-group label { display: block; font-size: 12px; color: var(--text-muted); margin-bottom: 6px; font-weight: 500; }
        .form-control { width: 100%; padding: 10px 12px; background: #0f172a; border: 1px solid var(--card-border); border-radius: 8px; color: #fff; font-size: 13.5px; outline: none; }
        .form-control:focus { border-color: var(--primary); }
    </style>
</head>
<body>

<div class="tab-toolbar">
    <div class="customer-info">
        <div class="avatar-circle" id="customer-avatar">WA</div>
        <div class="customer-meta">
            <div class="customer-title-row">
                <span class="customer-name" id="contact-name">Loading contact...</span>
                <span class="phone-badge" id="contact-phone">---</span>
            </div>
            <div class="session-pill" id="session-pill">
                <span class="status-dot dot-active" id="session-dot"></span>
                <span id="session-text">Checking 24h WhatsApp Session...</span>
            </div>
        </div>
    </div>
    <div class="tab-actions">
        <button class="btn btn-secondary" onclick="openDebugModal()" id="btn-open-debug-top" title="View Diagnostics & Copy Request/Response Debug Logs">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            Debug Logs
        </button>
        <button class="btn btn-warning-glow" onclick="openTemplateModal()" id="btn-open-template-top">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            Send Template
        </button>
        <button class="btn btn-secondary" onclick="openAiSummary()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            AI Summary
        </button>
        <button class="btn btn-secondary" onclick="loadChatHistory(true)" title="Refresh Messages">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
        </button>
    </div>
</div>

<div class="chat-layout">
    <!-- Chat messages stream -->
    <div class="chat-messages" id="chat-messages">
        <div class="empty-chat" id="loading-state">
            <div class="avatar-circle" style="width: 48px; height: 48px; margin-bottom: 12px; background: rgba(63, 144, 109, 0.2);">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#3F906D" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            </div>
            <p>Loading WhatsApp conversation...</p>
        </div>
    </div>

    <!-- Closed Window Warning Banner -->
    <div class="window-banner" id="window-banner" style="display: none;">
        <div class="window-banner-text">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <span><strong>24-hour service window closed.</strong> Regular WhatsApp text cannot be delivered until customer replies.</span>
        </div>
        <button class="btn btn-warning-glow" style="padding: 4px 10px; font-size: 11px;" onclick="openTemplateModal()">
            Choose Approved Template
        </button>
    </div>

    <!-- Quick Replies Bar -->
    <div class="quick-replies-bar">
        <div class="quick-pill" onclick="applyQuickReply('Hello! How can I help you today?')">👋 Hello! How can I help?</div>
        <div class="quick-pill" onclick="applyQuickReply('Thank you for reaching out! Let me check this for you.')">⏳ Checking now...</div>
        <div class="quick-pill" onclick="applyQuickReply('Everything has been confirmed successfully. Thank you!')">👍 Confirmed!</div>
        <div class="quick-pill" onclick="applyQuickReply('Could you please provide your order/inquiry number?')">🔢 Need Order #</div>
        <div class="quick-pill" onclick="openTemplateModal()">📋 Send Template Message...</div>
    </div>

    <!-- Message Composer -->
    <div class="chat-composer">
        <button class="btn btn-secondary" style="height: 44px; width: 44px; padding: 0; justify-content: center; border-radius: 50%;" onclick="openTemplateModal()" title="Send WhatsApp Template">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        </button>
        <div class="composer-input-wrapper">
            <textarea id="message-input" class="composer-textarea" rows="1" placeholder="Type a WhatsApp message... (Enter to send, Shift+Enter for new line)"></textarea>
        </div>
        <button id="btn-send" class="btn-send" onclick="sendMessage()" title="Send WhatsApp Message">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="transform: translateX(1px);"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
        </button>
    </div>
</div>

<!-- Template Sender Modal -->
<div id="template-modal" class="modal">
    <div class="modal-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 16px; font-weight: 600; color: #fff; display: flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/></svg>
                Send WhatsApp Template
            </h3>
            <button onclick="closeTemplateModal()" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 20px;">&times;</button>
        </div>
        <p style="font-size: 12.5px; color: var(--text-muted); margin-bottom: 16px;">
            Templates bypass the 24-hour closed session window and reach the customer immediately.
        </p>

        <div class="form-group">
            <label>Select Approved Template</label>
            <select id="crm-template-select" class="form-control" onchange="onCrmTemplateChange(this)">
                <option value="">Loading approved templates...</option>
            </select>
        </div>

        <div style="display: flex; gap: 10px;">
            <div class="form-group" style="flex: 2;">
                <label>Template Name</label>
                <input type="text" id="crm-template-name" class="form-control" placeholder="e.g. order_update_v1">
            </div>
            <div class="form-group" style="flex: 1;">
                <label>Language</label>
                <input type="text" id="crm-template-lang" class="form-control" value="en">
            </div>
        </div>

        <div class="form-group">
            <label>Parameter 1 (Customer Name / Order ID)</label>
            <input type="text" id="crm-param1" class="form-control" placeholder="e.g. Mostafa">
        </div>
        <div class="form-group">
            <label>Parameter 2 (Optional)</label>
            <input type="text" id="crm-param2" class="form-control" placeholder="e.g. Your shipment has arrived">
        </div>
        <div class="form-group">
            <label>Parameter 3 (Optional)</label>
            <input type="text" id="crm-param3" class="form-control" placeholder="e.g. Tracking link or agent name">
        </div>

        <div id="crm-template-status" style="display: none; padding: 10px; border-radius: 8px; font-size: 13px; margin-bottom: 14px;"></div>

        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
            <button class="btn btn-secondary" onclick="closeTemplateModal()">Cancel</button>
            <button id="btn-send-crm-tpl" class="btn btn-primary" onclick="submitCrmTemplate()">Send Template Message</button>
        </div>
    </div>
</div>

<!-- AI Summary Modal -->
<div id="ai-modal" class="modal">
    <div class="modal-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 16px; font-weight: 600; color: #fff; display: flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#a855f7" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                AI Conversation Insights
            </h3>
            <button onclick="closeAiModal()" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 20px;">&times;</button>
        </div>
        <div id="ai-content" style="font-size: 13.5px; line-height: 1.6; color: var(--text-main); background: #0f172a; padding: 16px; border-radius: 10px; max-height: 350px; overflow-y: auto;">
            Generating conversation summary...
        </div>
        <div style="display: flex; justify-content: flex-end; margin-top: 16px;">
            <button class="btn btn-secondary" onclick="closeAiModal()">Close</button>
        </div>
    </div>
</div>

<!-- Diagnostics & Debug Modal -->
<div id="debug-modal" class="modal">
    <div class="modal-content" style="max-width: 680px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <h3 style="font-size: 16px; font-weight: 600; color: #fff; display: flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                Diagnostics & Console Debug Logs
            </h3>
            <button onclick="closeDebugModal()" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 20px;">&times;</button>
        </div>
        <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 12px;">
            Every request, response, and DoubleTick raw payload is recorded below and in your browser's Developer Console (F12). Click <strong>Copy to Clipboard</strong> to copy the JSON and send it back.
        </p>
        <textarea id="debug-textarea" class="form-control" style="height: 320px; font-family: monospace; font-size: 11px; line-height: 1.45; white-space: pre; background: #0b141a; resize: vertical; border-color: rgba(255,255,255,0.15);" readonly></textarea>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 14px;">
            <span id="debug-copy-status" style="font-size: 12px; color: #4ade80; font-weight: 600;"></span>
            <div style="display: flex; gap: 8px;">
                <button class="btn btn-secondary" onclick="closeDebugModal()">Close</button>
                <button class="btn btn-primary" onclick="copyDebugJsonToClipboard()">📋 Copy to Clipboard</button>
            </div>
        </div>
    </div>
</div>

<script>
    // -------------------------------------------------------------
    // Comprehensive Debug Logger & Ring Buffer
    // -------------------------------------------------------------
    window.keenDebugStore = {
        crmInfo: {},
        requests: [],
        lastChatHistoryResponse: null,
        logReq: function(action, details) {
            const entry = {
                timestamp: new Date().toISOString(),
                type: 'REQUEST',
                action: action,
                details: details
            };
            this.requests.push(entry);
            if (this.requests.length > 100) this.requests.shift();

            console.groupCollapsed('%c[KEEN DoubleTick] 🚀 REQUEST: ' + action, 'color: #38bdf8; font-weight: bold; background: #0f172a; padding: 2px 6px; border-radius: 4px;');
            console.log('Action:', action);
            console.log('Payload / Params:', details);
            console.log('Time:', new Date().toLocaleTimeString());
            console.groupEnd();
        },
        logRes: function(action, data, status) {
            const ok = (data && data.success !== false);
            const entry = {
                timestamp: new Date().toISOString(),
                type: 'RESPONSE',
                action: action,
                status: status || 200,
                success: ok,
                data: data
            };
            this.requests.push(entry);
            if (this.requests.length > 100) this.requests.shift();

            const badgeColor = ok ? '#4ade80' : '#f87171';
            console.groupCollapsed('%c[KEEN DoubleTick] 📦 RESPONSE: ' + action + ' [' + (ok ? 'SUCCESS' : 'FAILED') + ']', 'color: ' + badgeColor + '; font-weight: bold; background: #0f172a; padding: 2px 6px; border-radius: 4px;');
            console.log('Action:', action);
            console.log('Status:', status || 200);
            console.log('Response Body:', data);
            if (data && data.messages) {
                console.log('Messages count:', data.messages.length);
                console.table(data.messages.map(m => ({ id: m.id, dir: m.direction, type: m.type, status: m.status, text: m.text })));
            }
            if (data && data.raw_dt_response) {
                console.log('Raw DoubleTick API response:', data.raw_dt_response);
            }
            if (data && data.messages && action.includes('chat_history')) {
                data.messages.forEach(function(msg, idx) {
                    if (msg._extraction_debug) {
                        console.log('%c[MSG #' + idx + '] Extraction Debug:', 'color: #fbbf24; font-weight: bold;', msg._extraction_debug);
                    }
                });
            }
            if (data && data.error) {
                console.warn('Error detail:', data.error);
            }
            console.log('Time:', new Date().toLocaleTimeString());
            console.groupEnd();
        },
        logErr: function(action, err) {
            const entry = {
                timestamp: new Date().toISOString(),
                type: 'ERROR',
                action: action,
                error: (err && err.message) ? err.message : String(err)
            };
            this.requests.push(entry);
            if (this.requests.length > 100) this.requests.shift();

            console.group('%c[KEEN DoubleTick] ❌ ERROR: ' + action, 'color: #ef4444; font-weight: bold;');
            console.error('Error Details:', err);
            console.groupEnd();
        },
        exportJson: function() {
            return JSON.stringify({
                app: 'KEEN DoubleTick Messenger',
                version: '2.5.0',
                exportedAt: new Date().toISOString(),
                crmInfo: this.crmInfo,
                lastChatHistoryResponse: this.lastChatHistoryResponse,
                recentNetworkLogs: this.requests
            }, null, 2);
        }
    };

    console.log('%c[KEEN DoubleTick] WhatsApp Messenger initialized with full debug logging.\nClick "Debug Logs" in the top toolbar or inspect window.keenDebugStore anytime.', 'color: #3F906D; font-weight: bold; font-size: 13px;');

    let currentPhone = '';
    let currentMemberId = '';
    let currentContactName = '';
    let isWindowOpen = true;
    let pollInterval = null;
    let lastRenderedCount = 0;

    BX24.init(function() {
        const info = BX24.placement.info();
        const placement = info.placement;
        const entityId = info.options && info.options.ID ? info.options.ID : null;
        const auth = BX24.getAuth();
        if (auth && auth.member_id) {
            currentMemberId = auth.member_id;
        }

        window.keenDebugStore.crmInfo = {
            placement: placement,
            entityId: entityId,
            memberId: currentMemberId,
            domain: (auth && auth.domain) ? auth.domain : null,
            options: info.options
        };

        window.keenDebugStore.logReq('BX24.init', window.keenDebugStore.crmInfo);

        let entityMethod = 'crm.lead.get';
        if (placement.indexOf('DEAL') !== -1) entityMethod = 'crm.deal.get';
        if (placement.indexOf('CONTACT') !== -1) entityMethod = 'crm.contact.get';
        if (placement.indexOf('COMPANY') !== -1) entityMethod = 'crm.company.get';

        if (!entityId) {
            document.getElementById('loading-state').innerHTML = '<p style="color: var(--danger);">Unable to identify CRM record ID.</p>';
            window.keenDebugStore.logErr('BX24.init', 'No entityId found in placement options');
            return;
        }

        window.keenDebugStore.logReq('BX24.callMethod(' + entityMethod + ')', { id: entityId });

        BX24.callMethod(entityMethod, { id: entityId }, function(res) {
            if (res.error()) {
                window.keenDebugStore.logErr('BX24.callMethod(' + entityMethod + ')', res.error());
                document.getElementById('loading-state').innerHTML = '<p style="color: var(--danger);">Failed to load CRM record: ' + res.error() + '</p>';
                return;
            }
            const data = res.data();
            window.keenDebugStore.logRes('BX24.callMethod(' + entityMethod + ')', data);

            let phone = '';
            let name = data.TITLE || data.NAME || 'Customer';
            if (data.LAST_NAME) name += ' ' + data.LAST_NAME;

            if (data.PHONE && data.PHONE.length > 0) {
                phone = data.PHONE[0].VALUE;
            }

            // If deal without direct phone, fetch linked contact
            if (!phone && data.CONTACT_ID) {
                window.keenDebugStore.logReq('BX24.callMethod(crm.contact.get)', { id: data.CONTACT_ID });
                BX24.callMethod('crm.contact.get', { id: data.CONTACT_ID }, function(contactRes) {
                    if (!contactRes.error()) {
                        const contactData = contactRes.data();
                        window.keenDebugStore.logRes('BX24.callMethod(crm.contact.get)', contactData);
                        name = (contactData.NAME || '') + ' ' + (contactData.LAST_NAME || '');
                        if (contactData.PHONE && contactData.PHONE.length > 0) {
                            initChat(contactData.PHONE[0].VALUE, name.trim() || 'Contact');
                        } else {
                            showNoPhone();
                        }
                    } else {
                        window.keenDebugStore.logErr('BX24.callMethod(crm.contact.get)', contactRes.error());
                        showNoPhone();
                    }
                });
            } else if (phone) {
                initChat(phone, name);
            } else {
                showNoPhone();
            }
        });
    });

    function showNoPhone() {
        document.getElementById('contact-name').innerText = 'No Phone Number';
        document.getElementById('loading-state').innerHTML = '<p style="color: var(--warning);">This CRM entity has no phone number recorded. Please add a phone number to start a WhatsApp chat.</p>';
    }

    function initChat(phone, name) {
        currentPhone = phone;
        currentContactName = name;
        window.keenDebugStore.crmInfo.phone = phone;
        window.keenDebugStore.crmInfo.contactName = name;

        document.getElementById('contact-name').innerText = name;
        document.getElementById('contact-phone').innerText = phone;
        document.getElementById('crm-param1').value = name;

        // Init avatar initials
        const initials = name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase() || 'WA';
        document.getElementById('customer-avatar').innerText = initials;

        // Auto-expand textarea
        const textarea = document.getElementById('message-input');
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });

        // Keypress: Enter to send (Shift+Enter for newline)
        textarea.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Initial fetch
        loadChatHistory(false);

        // Start background polling every 5 seconds
        if (pollInterval) clearInterval(pollInterval);
        pollInterval = setInterval(function() {
            loadChatHistory(false);
        }, 5000);
    }

    function loadChatHistory(forceScroll) {
        if (!currentPhone) return;

        const currentDomain = (window.keenDebugStore && window.keenDebugStore.crmInfo && window.keenDebugStore.crmInfo.domain) ? window.keenDebugStore.crmInfo.domain : '';
        const url = 'placement_tab.php?action=get_chat_history&phone=' + encodeURIComponent(currentPhone) + '&member_id=' + encodeURIComponent(currentMemberId) + '&domain=' + encodeURIComponent(currentDomain);
        window.keenDebugStore.logReq('get_chat_history', { url, phone: currentPhone, memberId: currentMemberId, domain: currentDomain });

        fetch(url)
            .then(res => res.json())
            .then(data => {
                window.keenDebugStore.lastChatHistoryResponse = data;
                window.keenDebugStore.logRes('get_chat_history', data);

                if (!data.success) {
                    if (lastRenderedCount === 0) {
                        document.getElementById('loading-state').innerHTML = '<p style="color: var(--danger);">' + (data.error || 'Failed to load messages') + '</p>';
                    }
                    return;
                }

                // Update 24h window badge
                updateWindowBadge(data.window_status);

                const messages = data.messages || [];
                renderMessages(messages, forceScroll);
            })
            .catch(err => {
                window.keenDebugStore.logErr('get_chat_history', err);
            });
    }

    function updateWindowBadge(win) {
        window.keenDebugStore.crmInfo.windowStatus = win;
        const dot = document.getElementById('session-dot');
        const text = document.getElementById('session-text');
        const banner = document.getElementById('window-banner');

        if (win && win.isOpen) {
            isWindowOpen = true;
            dot.className = 'status-dot dot-active';
            text.innerText = '24h Session Active' + (win.formattedRemaining ? ' (expires in ' + win.formattedRemaining + ')' : '');
            text.style.color = '#4ade80';
            banner.style.display = 'none';
        } else {
            isWindowOpen = false;
            dot.className = 'status-dot dot-closed';
            text.innerText = '24h Window Closed';
            text.style.color = '#fbbf24';
            banner.style.display = 'flex';
        }
    }

    function renderMessages(messages, forceScroll) {
        const container = document.getElementById('chat-messages');

        if (messages.length === 0) {
            container.innerHTML = `
                <div class="empty-chat">
                    <div class="avatar-circle" style="width: 52px; height: 52px; margin-bottom: 12px; background: rgba(63, 144, 109, 0.15);">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#3F906D" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    </div>
                    <strong style="color: #fff; font-size: 15px; margin-bottom: 6px;">No messages exchanged yet</strong>
                    <p style="font-size: 13px; max-width: 320px;">Send a message or an approved template below to start the WhatsApp conversation with ${escapeHtml(currentContactName)}.</p>
                </div>
            `;
            lastRenderedCount = 0;
            return;
        }

        // Only rebuild if count changed or forceScroll
        if (messages.length === lastRenderedCount && !forceScroll) {
            return;
        }

        const isScrolledToBottom = (container.scrollHeight - container.clientHeight <= container.scrollTop + 60);

        let html = '';
        let lastDate = '';

        messages.forEach(m => {
            const isOut = (m.direction === 'OUTBOUND');
            const isTpl = (m.type === 'template');
            const dateStr = m.date_str || 'Today';

            if (dateStr !== lastDate) {
                html += `<div class="date-divider"><span class="date-badge">${escapeHtml(dateStr)}</span></div>`;
                lastDate = dateStr;
            }

            const rowClass = isOut ? 'message-row outbound' : 'message-row inbound';
            let checkIcon = '';
            if (isOut) {
                if (m.status === 'read') {
                    checkIcon = '<span class="status-check read" title="Read">✓✓</span>';
                } else if (m.status === 'delivered') {
                    checkIcon = '<span class="status-check" title="Delivered">✓✓</span>';
                } else if (m.status === 'failed') {
                    checkIcon = '<span class="status-check" style="color: var(--danger)" title="Failed">⚠️</span>';
                } else {
                    checkIcon = '<span class="status-check" title="Sent">✓</span>';
                }
            }

            let bubbleContent = '';
            if (isTpl) {
                bubbleContent += `<div class="template-tag">📋 APPROVED TEMPLATE</div>`;
            }

            if (m.media_url) {
                bubbleContent += `<div style="margin-bottom: 6px;"><a href="${escapeHtml(m.media_url)}" target="_blank" rel="noopener noreferrer" style="color: #60a5fa; text-decoration: underline;">📎 View Attachment</a></div>`;
            }

            let textContent = formatMessageText(m.text);
            if (!textContent && m.raw_message) {
                const rm = m.raw_message;
                const candidate = (rm.message && (rm.message.text || rm.message.body || rm.message.caption || rm.message.content))
                    || rm.text || rm.body || rm.content || rm.caption
                    || (rm.templateId ? '📋 Template: ' + rm.templateId : null);
                if (candidate) {
                    textContent = formatMessageText(candidate);
                }
            }

            if (textContent) {
                bubbleContent += `<div style="white-space: pre-wrap;">${escapeHtml(textContent)}</div>`;
            } else if (m.media_url) {
                // Media only
            } else if (isTpl) {
                bubbleContent += `<div style="font-style: italic; opacity: 0.85;">📋 Template message</div>`;
            } else {
                bubbleContent += `<div style="font-style: italic; opacity: 0.75;">💬 [WhatsApp message]</div>`;
            }
            bubbleContent += `<div class="bubble-footer"><span title="Message ID: ${escapeHtml(m.id || '')}">${escapeHtml(m.time_str || '')}</span>${checkIcon}</div>`;

            html += `<div class="${rowClass}"><div class="bubble ${isTpl ? 'bubble-template' : ''}">${bubbleContent}</div></div>`;
        });

        container.innerHTML = html;
        lastRenderedCount = messages.length;

        if (isScrolledToBottom || forceScroll) {
            container.scrollTop = container.scrollHeight;
        }
    }

    function sendMessage() {
        const input = document.getElementById('message-input');
        const text = input.value.trim();
        if (!text) return;

        if (!isWindowOpen) {
            if (confirm('Meta 24-hour customer window is closed for this contact. Free-form text cannot be delivered.\n\nWould you like to open the Template Sender instead?')) {
                openTemplateModal();
                return;
            }
        }

        const btn = document.getElementById('btn-send');
        btn.disabled = true;

        // Optimistic UI insertion
        const container = document.getElementById('chat-messages');
        const tempId = 'temp_' + Date.now();
        const optimisticHtml = `
            <div class="message-row outbound" id="${tempId}">
                <div class="bubble">
                    <div style="white-space: pre-wrap;">${escapeHtml(text)}</div>
                    <div class="bubble-footer"><span>Just now</span> <span class="status-check" style="opacity: 0.5;">🕒</span></div>
                </div>
            </div>
        `;
        if (container.querySelector('.empty-chat')) {
            container.innerHTML = '';
        }
        container.insertAdjacentHTML('beforeend', optimisticHtml);
        container.scrollTop = container.scrollHeight;
        input.value = '';
        input.style.height = 'auto';

        const currentDomain = (window.keenDebugStore && window.keenDebugStore.crmInfo && window.keenDebugStore.crmInfo.domain) ? window.keenDebugStore.crmInfo.domain : '';
        const formData = new FormData();
        formData.append('action', 'send_direct_message');
        formData.append('phone', currentPhone);
        formData.append('text', text);
        formData.append('member_id', currentMemberId);
        formData.append('domain', currentDomain);

        window.keenDebugStore.logReq('send_direct_message', { phone: currentPhone, text: text, memberId: currentMemberId, domain: currentDomain });

        fetch('placement_tab.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                window.keenDebugStore.logRes('send_direct_message', data);

                if (data.success) {
                    loadChatHistory(true);
                } else {
                    const tempEl = document.getElementById(tempId);
                    if (tempEl) {
                        tempEl.querySelector('.status-check').innerHTML = '<span style="color: var(--danger);">⚠️</span>';
                    }
                    if (data.window_closed) {
                        isWindowOpen = false;
                        updateWindowBadge({ isOpen: false });
                        if (confirm('Message could not be delivered: 24-hour WhatsApp session has expired.\n\nOpen Template Sender to message this contact?')) {
                            openTemplateModal();
                        }
                    } else {
                        alert('Error sending WhatsApp message: ' + (data.error || 'Unknown error'));
                    }
                }
            })
            .catch(err => {
                btn.disabled = false;
                window.keenDebugStore.logErr('send_direct_message', err);
                alert('Network error while communicating with DoubleTick server.');
            });
    }

    function applyQuickReply(text) {
        const input = document.getElementById('message-input');
        input.value = text;
        input.focus();
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 120) + 'px';
    }

    // -------------------------------------------------------------
    // Template Modal Functions
    // -------------------------------------------------------------
    let crmTemplatesLoaded = false;
    function openTemplateModal() {
        if (!currentPhone) {
            alert('Please wait until contact phone number is loaded.');
            return;
        }
        document.getElementById('template-modal').style.display = 'block';
        document.getElementById('crm-template-status').style.display = 'none';

        if (!crmTemplatesLoaded) {
            const currentDomain = (window.keenDebugStore && window.keenDebugStore.crmInfo && window.keenDebugStore.crmInfo.domain) ? window.keenDebugStore.crmInfo.domain : '';
            const tplUrl = 'placement_tab.php?action=get_templates&member_id=' + encodeURIComponent(currentMemberId) + '&domain=' + encodeURIComponent(currentDomain);
            window.keenDebugStore.logReq('get_templates', { url: tplUrl, memberId: currentMemberId, domain: currentDomain });

            fetch(tplUrl)
                .then(res => res.json())
                .then(data => {
                    window.keenDebugStore.logRes('get_templates', data);
                    if (data.success && data.templates && Array.isArray(data.templates)) {
                        const sel = document.getElementById('crm-template-select');
                        sel.innerHTML = '<option value="">-- Choose from approved templates --</option>';
                        data.templates.forEach(tpl => {
                            const opt = document.createElement('option');
                            opt.value = tpl.name || '';
                            opt.setAttribute('data-lang', tpl.language || 'en');
                            opt.textContent = (tpl.name || '') + ' (' + (tpl.language || 'en') + ') - ' + (tpl.category || 'TEMPLATE');
                            sel.appendChild(opt);
                        });
                        crmTemplatesLoaded = true;
                    }
                })
                .catch(e => {
                    window.keenDebugStore.logErr('get_templates', e);
                });
        }
    }

    function closeTemplateModal() {
        document.getElementById('template-modal').style.display = 'none';
    }

    function onCrmTemplateChange(sel) {
        if (sel.value) {
            document.getElementById('crm-template-name').value = sel.value;
            const opt = sel.options[sel.selectedIndex];
            if (opt && opt.getAttribute('data-lang')) {
                document.getElementById('crm-template-lang').value = opt.getAttribute('data-lang');
            }
        }
    }

    function submitCrmTemplate() {
        const tplName = document.getElementById('crm-template-name').value.trim();
        const tplLang = document.getElementById('crm-template-lang').value.trim() || 'en';
        const p1 = document.getElementById('crm-param1').value.trim();
        const p2 = document.getElementById('crm-param2').value.trim();
        const p3 = document.getElementById('crm-param3').value.trim();
        const statusEl = document.getElementById('crm-template-status');
        const btn = document.getElementById('btn-send-crm-tpl');

        if (!tplName) {
            alert('Please select or specify a template name.');
            return;
        }

        btn.disabled = true;
        btn.innerText = 'Sending...';
        statusEl.style.display = 'block';
        statusEl.style.background = 'rgba(59, 130, 246, 0.2)';
        statusEl.style.color = '#93c5fd';
        statusEl.innerText = 'Sending WhatsApp template message...';

        const currentDomain = (window.keenDebugStore && window.keenDebugStore.crmInfo && window.keenDebugStore.crmInfo.domain) ? window.keenDebugStore.crmInfo.domain : '';
        const formData = new FormData();
        formData.append('action', 'send_template');
        formData.append('phone', currentPhone);
        formData.append('template_name', tplName);
        formData.append('language', tplLang);
        formData.append('param1', p1);
        formData.append('param2', p2);
        formData.append('param3', p3);
        formData.append('member_id', currentMemberId);
        formData.append('domain', currentDomain);

        window.keenDebugStore.logReq('send_template', {
            phone: currentPhone,
            templateName: tplName,
            language: tplLang,
            param1: p1,
            param2: p2,
            param3: p3,
            memberId: currentMemberId,
            domain: currentDomain
        });

        fetch('placement_tab.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerText = 'Send Template Message';
                window.keenDebugStore.logRes('send_template', data);

                if (data.success) {
                    statusEl.style.background = 'rgba(16, 185, 129, 0.2)';
                    statusEl.style.color = '#6ee7b7';
                    statusEl.innerText = '✓ Template sent successfully via DoubleTick!';
                    setTimeout(() => {
                        closeTemplateModal();
                        loadChatHistory(true);
                    }, 1200);
                } else {
                    statusEl.style.background = 'rgba(239, 68, 68, 0.2)';
                    statusEl.style.color = '#fca5a5';
                    statusEl.innerText = '✕ Error: ' + (data.error || 'Failed to send template');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerText = 'Send Template Message';
                window.keenDebugStore.logErr('send_template', err);
                statusEl.style.background = 'rgba(239, 68, 68, 0.2)';
                statusEl.style.color = '#fca5a5';
                statusEl.innerText = '✕ Network error while sending template.';
            });
    }

    // -------------------------------------------------------------
    // AI Summary Modal Functions
    // -------------------------------------------------------------
    function openAiSummary() {
        if (!currentPhone) {
            alert('Please wait until contact phone number is loaded.');
            return;
        }
        document.getElementById('ai-modal').style.display = 'block';
        document.getElementById('ai-content').innerHTML = '<em>Generating AI summary from WhatsApp history...</em>';

        const aiUrl = 'placement_tab.php?action=get_ai_summary&phone=' + encodeURIComponent(currentPhone) + '&member_id=' + encodeURIComponent(currentMemberId);
        window.keenDebugStore.logReq('get_ai_summary', { url: aiUrl, phone: currentPhone, memberId: currentMemberId });

        fetch(aiUrl)
            .then(res => res.json())
            .then(data => {
                window.keenDebugStore.logRes('get_ai_summary', data);
                const el = document.getElementById('ai-content');
                if (data.success && data.data) {
                    const d = data.data;
                    let out = '';
                    if (d.summary) out += '<p><strong>Summary:</strong><br>' + escapeHtml(d.summary) + '</p><br>';
                    if (d.actionItems && d.actionItems.length) {
                        out += '<p><strong>Action Items:</strong></p><ul style="padding-left: 20px;">';
                        d.actionItems.forEach(item => { out += '<li>' + escapeHtml(item) + '</li>'; });
                        out += '</ul><br>';
                    }
                    if (d.sentiment) out += '<p><strong>Customer Sentiment:</strong> ' + escapeHtml(d.sentiment) + '</p>';
                    el.innerHTML = out || '<pre>' + escapeHtml(JSON.stringify(d, null, 2)) + '</pre>';
                } else {
                    el.innerHTML = '<p style="color: var(--danger)">' + escapeHtml(data.error || 'No AI summary available for this conversation.') + '</p>';
                }
            })
            .catch(err => {
                window.keenDebugStore.logErr('get_ai_summary', err);
                document.getElementById('ai-content').innerText = 'Failed to fetch AI summary.';
            });
    }

    function closeAiModal() {
        document.getElementById('ai-modal').style.display = 'none';
    }

    // -------------------------------------------------------------
    // Diagnostics & Debug Modal Functions
    // -------------------------------------------------------------
    function openDebugModal() {
        const modal = document.getElementById('debug-modal');
        const textarea = document.getElementById('debug-textarea');
        const status = document.getElementById('debug-copy-status');
        if (status) status.innerText = '';
        if (textarea) textarea.value = window.keenDebugStore.exportJson();
        if (modal) modal.style.display = 'block';
    }

    function closeDebugModal() {
        const modal = document.getElementById('debug-modal');
        if (modal) modal.style.display = 'none';
    }

    function copyDebugJsonToClipboard() {
        const textarea = document.getElementById('debug-textarea');
        const status = document.getElementById('debug-copy-status');
        const content = textarea ? textarea.value : window.keenDebugStore.exportJson();

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(content).then(() => {
                if (status) status.innerText = '✓ Copied to clipboard! You can paste it into the chat.';
            }).catch(() => {
                fallbackCopy(textarea, status);
            });
        } else {
            fallbackCopy(textarea, status);
        }
    }

    function fallbackCopy(textarea, status) {
        if (!textarea) return;
        textarea.select();
        document.execCommand('copy');
        if (status) status.innerText = '✓ Copied to clipboard! You can paste it into the chat.';
    }

    function formatMessageText(val) {
        if (val === null || val === undefined) return '';
        if (typeof val === 'string') {
            const str = val.trim();
            if (str === 'Array' || str === '[object Object]') return '';
            return str;
        }
        if (typeof val === 'object') {
            if (val.text && typeof val.text === 'string') return val.text;
            if (val.text && typeof val.text === 'object') return val.text.body || val.text.text || val.text.content || JSON.stringify(val.text);
            if (val.body && typeof val.body === 'string') return val.body;
            if (val.caption && typeof val.caption === 'string') return val.caption;
            if (val.content && typeof val.content === 'string') return val.content;
            if (val.message && typeof val.message === 'string') return val.message;
            if (val.message && typeof val.message === 'object') return formatMessageText(val.message);
            return JSON.stringify(val);
        }
        return String(val);
    }

    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.innerText = str;
        return div.innerHTML;
    }
</script>
</body>
</html>
