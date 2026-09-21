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

// Strict anti-cache headers to guarantee fresh data every time the widget opens
header_remove('X-Frame-Options');
header('Content-Security-Policy: frame-ancestors *');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0');
header('Pragma: no-cache');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');


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
    if (empty($text) && !empty($msg['button_reply']) && is_array($msg['button_reply'])) {
        $text = "🔘 " . $strHelper($msg['button_reply']['title'] ?? ($msg['button_reply']['id'] ?? ''));
    }
    if (empty($text) && !empty($msg['list_reply']) && is_array($msg['list_reply'])) {
        $text = "📋 " . $strHelper($msg['list_reply']['title'] ?? '');
    }
    if (empty($text) && !empty($msg['button']) && is_array($msg['button'])) {
        $text = "🔘 " . $strHelper($msg['button']['text'] ?? ($msg['button']['payload'] ?? ''));
    }

    // 7. Template checks (all variations: templateName, template_name, templateId, template)
    if (empty($text)) {
        $tplName = $msg['templateName'] ?? $msg['template_name'] ?? $msg['templateId'] ?? $msg['template_id'] ?? null;
        if (!$tplName && !empty($msg['template'])) {
            if (is_string($msg['template'])) {
                $tplName = $msg['template'];
            } elseif (is_array($msg['template'])) {
                $tplName = $msg['template']['name'] ?? ($msg['template']['templateName'] ?? ($msg['template']['template_name'] ?? null));
            }
        }
        if ($tplName) {
            $text = "📋 Template: " . (is_string($tplName) ? $tplName : json_encode($tplName));
            $mediaType = 'template';
        }
    }

    // 8. Reactions
    if (empty($text) && !empty($msg['reaction']) && is_array($msg['reaction'])) {
        $emoji = $msg['reaction']['emoji'] ?? '';
        $text = $emoji ? "Reacted: {$emoji}" : "Reacted";
    }

    // 9. Location checks
    if (empty($text) && !empty($msg['location']) && is_array($msg['location'])) {
        $locName = $strHelper($msg['location']['name'] ?? ($msg['location']['address'] ?? 'Shared Location'));
        $text = "📍 " . $locName;
    }

    // 10. Contacts checks
    if (empty($text) && !empty($msg['contacts']) && is_array($msg['contacts'])) {
        $firstC = $msg['contacts'][0] ?? $msg['contacts'];
        $cName = $strHelper($firstC['name']['formatted_name'] ?? ($firstC['name'] ?? 'Shared Contact'));
        $text = "👤 Contact: " . $cName;
    }

    // 11. If still empty, scan any remaining string values (ignoring technical ID and metadata fields)
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

        // Merge items from cleanPhone if different from intlPhone
        if ($intlPhone !== $cleanPhone) {
            try {
                $fallbackRes = $dt->getChatMessages($cleanPhone, $waba);
                $fbItems = [];
                if (!empty($fallbackRes['messages']) && is_array($fallbackRes['messages'])) {
                    $fbItems = $fallbackRes['messages'];
                } elseif (!empty($fallbackRes['data']['messages']) && is_array($fallbackRes['data']['messages'])) {
                    $fbItems = $fallbackRes['data']['messages'];
                } elseif (!empty($fallbackRes['data']) && is_array($fallbackRes['data'])) {
                    $fbItems = $fallbackRes['data'];
                }
                $existingIds = [];
                foreach ($remoteItems as $ri) {
                    $riId = (string)($ri['id'] ?? $ri['messageId'] ?? '');
                    if ($riId) $existingIds[$riId] = true;
                }
                foreach ($fbItems as $fbi) {
                    $fbiId = (string)($fbi['id'] ?? $fbi['messageId'] ?? '');
                    if (!$fbiId || !isset($existingIds[$fbiId])) {
                        $remoteItems[] = $fbi;
                        if ($fbiId) $existingIds[$fbiId] = true;
                    }
                }
                if (empty($rawDtRes) || empty($rawDtRes['messages'])) {
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

            $isTemplate = !empty($m['templateId'])
                || !empty($m['templateName'])
                || !empty($m['template_name'])
                || !empty($m['template'])
                || ($content['media_type'] ?? '') === 'template'
                || strtolower((string)($m['messageType'] ?? '')) === 'template'
                || strtolower((string)($m['type'] ?? '')) === 'template';

            if ($isTemplate && $text === '') {
                $tName = $m['templateName'] ?? $m['template_name'] ?? $m['templateId'] ?? null;
                $text = $tName ? "📋 Template: {$tName}" : "📋 Template message";
            }

            $proxyMediaUrl = $mediaUrl;
            if ($mediaUrl && stripos($mediaUrl, 'doubletick.io') !== false) {
                $proxyMediaUrl = 'media_proxy.php?url=' . urlencode($mediaUrl) . '&member_id=' . urlencode($memberId) . '&domain=' . urlencode($domain);
            }

            $mType = 'text';
            $ext = '';
            if ($isTemplate) {
                $mType = 'template';
            } elseif ($mediaUrl) {
                $urlPath = parse_url($mediaUrl, PHP_URL_PATH) ?? '';
                $ext = strtolower(pathinfo($urlPath, PATHINFO_EXTENSION));
                $mType = match ($ext) {
                    'ogg', 'oga', 'opus', 'mp3', 'wav', 'webm', 'm4a' => 'audio',
                    'jpg', 'jpeg', 'png', 'webp', 'gif' => 'image',
                    'mp4', 'mov', 'avi' => 'video',
                    'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt' => 'document',
                    default => ($content['media_type'] ?? 'media')
                };
            }

            $isVoiceNote = ($mType === 'audio' && (str_contains($mediaUrl, 'voice') || in_array($ext, ['ogg', 'opus', 'webm']) || ($content['media_type'] ?? '') === 'voice'));
            $fileName = (string)($m['message']['fileName'] ?? $m['message']['filename'] ?? $m['fileName'] ?? $m['filename'] ?? '');
            if (!$fileName && $mediaUrl) {
                $fileName = basename(parse_url($mediaUrl, PHP_URL_PATH) ?? '') ?: ($mType . '_attachment');
            }

            $seenIds[$msgId] = true;
            $messages[] = [
                'id' => $msgId,
                'direction' => $isOutbound ? 'OUTBOUND' : 'INBOUND',
                'text' => $text,
                'media_url' => $proxyMediaUrl,
                'original_media_url' => $mediaUrl,
                'file_name' => $fileName,
                'timestamp' => $ts,
                'time_str' => date('h:i A', $ts),
                'date_str' => date('M j, Y', $ts),
                'status' => $status,
                'type' => $mType,
                'is_voice_note' => $isVoiceNote,
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
            $rawDecoded = !empty($row['raw_data']) ? json_decode((string)$row['raw_data'], true) : null;
            if (!empty($row['raw_data'])) {
                $content = extractDtMessageContent($row['raw_data']);
                $text = $content['text'];
                $mediaUrl = $content['media_url'];
            }
            if (!$text && !empty($row['template_name'])) {
                $text = "📋 Template: " . $row['template_name'];
            }
            if (!$text && is_array($rawDecoded)) {
                $tplName = $rawDecoded['template_name'] ?? ($rawDecoded['templateName'] ?? ($rawDecoded['templateId'] ?? null));
                if ($tplName) {
                    $text = "📋 Template: " . $tplName;
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
            $proxyMediaUrl = $mediaUrl;
            $fileName = null;
            $isVoiceNote = false;
            $mType = (string)($row['message_type'] ?? 'text');

            if (!empty($row['raw_data'])) {
                $rawDecoded = json_decode((string)$row['raw_data'], true);
                if (is_array($rawDecoded)) {
                    if (!empty($rawDecoded['media_url'])) {
                        $proxyMediaUrl = $rawDecoded['media_url'];
                    }
                    if (!empty($rawDecoded['file_name'])) {
                        $fileName = $rawDecoded['file_name'];
                    }
                    if (!empty($rawDecoded['media_type'])) {
                        $mType = $rawDecoded['media_type'];
                    }
                    if (!empty($rawDecoded['is_voice_note'])) {
                        $isVoiceNote = (bool)$rawDecoded['is_voice_note'];
                    }
                }
            }

            if ($proxyMediaUrl && stripos($proxyMediaUrl, 'doubletick.io') !== false) {
                $proxyMediaUrl = 'media_proxy.php?url=' . urlencode($proxyMediaUrl) . '&member_id=' . urlencode($memberId) . '&domain=' . urlencode($domain);
            }

            if ($proxyMediaUrl && ($mType === 'text' || $mType === 'media')) {
                $ext = strtolower(pathinfo(parse_url($proxyMediaUrl, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
                $mType = match ($ext) {
                    'ogg', 'oga', 'opus', 'mp3', 'wav', 'webm', 'm4a' => 'audio',
                    'jpg', 'jpeg', 'png', 'webp', 'gif' => 'image',
                    'mp4', 'mov', 'avi' => 'video',
                    'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt' => 'document',
                    default => 'media'
                };
            }
            if ($mType === 'audio') {
                $isVoiceNote = true;
            }

            $messages[] = [
                'id' => $dtId ?: ('local_' . $row['id']),
                'direction' => strtoupper((string)($row['direction'] ?? 'OUTBOUND')),
                'text' => $text,
                'media_url' => $proxyMediaUrl,
                'original_media_url' => $mediaUrl,
                'file_name' => $fileName,
                'timestamp' => $ts,
                'time_str' => date('h:i A', $ts),
                'date_str' => date('M j, Y', $ts),
                'status' => strtolower((string)($row['status'] ?? 'sent')),
                'type' => $mType,
                'is_voice_note' => $isVoiceNote,
                'raw_message' => $row,
            ];
        }

        // Also recover any unmapped inbound messages directly from webhook_logs for this phone
        try {
            $whDirectStmt = $db->prepare("
                SELECT id, event_type, payload, created_at FROM webhook_logs 
                WHERE (payload LIKE :phone OR payload LIKE :suffix)
                  AND (event_type IN ('MESSAGE_RECEIVED', 'INCOMING_MESSAGE', 'MESSAGE') OR payload LIKE '%\"MESSAGE_RECEIVED\"%')
                ORDER BY id ASC
            ");
            $whDirectStmt->execute([
                'phone' => '%' . $cleanPhone . '%',
                'suffix' => '%' . $phoneSuffix . '%',
            ]);
            $whDirectRows = $whDirectStmt->fetchAll();
            foreach ($whDirectRows as $wdr) {
                $pld = json_decode((string)$wdr['payload'], true);
                if (!is_array($pld)) continue;
                $pldMsgId = (string)($pld['dtMessageId'] ?? ($pld['messageId'] ?? ('wh_' . $wdr['id'])));
                if (isset($seenIds[$pldMsgId])) continue;

                $extracted = extractDtMessageContent($pld['message'] ?? $pld);
                $whText = $extracted['text'];
                $whMediaUrl = $extracted['media_url'];
                if ($whText === '' && !$whMediaUrl) continue;

                $whTs = !empty($wdr['created_at']) ? strtotime($wdr['created_at']) : time();
                $seenIds[$pldMsgId] = true;
                $messages[] = [
                    'id' => $pldMsgId,
                    'direction' => 'INBOUND',
                    'text' => $whText,
                    'media_url' => $whMediaUrl,
                    'original_media_url' => $whMediaUrl,
                    'file_name' => $pld['message']['fileName'] ?? null,
                    'timestamp' => $whTs,
                    'time_str' => date('h:i A', $whTs),
                    'date_str' => date('M j, Y', $whTs),
                    'status' => 'delivered',
                    'type' => $extracted['media_type'] ?: 'text',
                    'is_voice_note' => ($extracted['media_type'] === 'audio' || $extracted['media_type'] === 'voice'),
                    'raw_message' => $pld,
                ];
            }
        } catch (\Throwable $whdEx) {
            // Non-fatal
        }
    } catch (\Throwable $e) {
        Logger::warning("Could not fetch local chat history: " . $e->getMessage());
    }

    // Sort all messages chronologically
    usort($messages, function ($a, $b) {
        return $a['timestamp'] <=> $b['timestamp'];
    });

    // 3. Determine 24-hour customer service window status
    // WhatsApp/Meta rule: The 24-hour service window opens and resets on every inbound message from the customer.
    $latestInboundTs = 0;
    foreach ($messages as $msgItem) {
        if (($msgItem['direction'] ?? '') === 'INBOUND') {
            $msgTs = (int)($msgItem['timestamp'] ?? 0);
            if ($msgTs > $latestInboundTs) {
                $latestInboundTs = $msgTs;
            }
        }
    }

    // Also check local database directly in case some inbound messages were outside the current query
    try {
        $db = Database::getInstance();
        $inboundStmt = $db->prepare("
            SELECT MAX(created_at) as last_inbound 
            FROM message_mappings 
            WHERE direction = 'INBOUND' 
              AND (customer_phone LIKE :phone OR customer_phone LIKE :clean_phone OR customer_phone LIKE :intl_phone)
        ");
        $inboundStmt->execute([
            'phone' => '%' . $phoneSuffix,
            'clean_phone' => '%' . $cleanPhone . '%',
            'intl_phone' => '%' . $intlPhone . '%',
        ]);
        $lastInboundRow = $inboundStmt->fetch();
        if (!empty($lastInboundRow['last_inbound'])) {
            $dbInboundTs = strtotime($lastInboundRow['last_inbound']);
            if ($dbInboundTs > $latestInboundTs) {
                $latestInboundTs = $dbInboundTs;
            }
        }
    } catch (\Throwable $e) {
        // Non-fatal
    }

    $windowStatus = [
        'isOpen' => true,
        'expirationTimestamp' => null,
        'formattedRemaining' => null,
    ];

    $remoteWindowOpen = null;
    $remoteExpiration = null;
    try {
        $winRes = $dt->getChatWindowStatus($cleanPhone, $waba);
        if (isset($winRes['isOpen']) && $winRes['isOpen'] !== null) {
            $remoteWindowOpen = (bool)$winRes['isOpen'];
            $remoteExpiration = $winRes['expirationTimestamp'] ?? null;
        }
    } catch (\Throwable $e) {
        // Window check non-fatal
    }

    $now = time();
    if ($latestInboundTs > 0) {
        $elapsed = $now - $latestInboundTs;
        if ($elapsed < 86400) {
            // Customer replied within the last 24 hours -> 100% active WhatsApp session
            $remaining = 86400 - $elapsed;
            $hours = floor($remaining / 3600);
            $mins = floor(($remaining % 3600) / 60);
            $windowStatus['isOpen'] = true;
            $windowStatus['expirationTimestamp'] = $latestInboundTs + 86400;
            $windowStatus['formattedRemaining'] = "{$hours}h {$mins}m";
        } else {
            // Customer last inbound was over 24 hours ago -> Window closed unless remote DoubleTick reports open
            if ($remoteWindowOpen === true) {
                $windowStatus['isOpen'] = true;
                $windowStatus['expirationTimestamp'] = $remoteExpiration;
                if (!empty($remoteExpiration) && ($remoteExpiration - $now) > 0) {
                    $diff = $remoteExpiration - $now;
                    $windowStatus['formattedRemaining'] = floor($diff / 3600) . "h " . floor(($diff % 3600) / 60) . "m";
                }
            } else {
                $windowStatus['isOpen'] = false;
                $windowStatus['expirationTimestamp'] = $latestInboundTs + 86400;
                $windowStatus['formattedRemaining'] = null;
            }
        }
    } else {
        // No inbound messages recorded
        if ($remoteWindowOpen !== null) {
            $windowStatus['isOpen'] = $remoteWindowOpen;
            $windowStatus['expirationTimestamp'] = $remoteExpiration;
            if (!empty($remoteExpiration) && ($remoteExpiration - $now) > 0) {
                $diff = $remoteExpiration - $now;
                $windowStatus['formattedRemaining'] = floor($diff / 3600) . "h " . floor(($diff % 3600) / 60) . "m";
            }
        } else {
            $windowStatus['isOpen'] = true;
        }
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
// AJAX Action: Send WhatsApp Media Message (Image, Document, Video, Audio)
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_media') {
    header('Content-Type: application/json');
    $phone = trim((string)($_POST['phone'] ?? ''));
    $caption = trim((string)($_POST['caption'] ?? ''));
    $memberId = (string)($_POST['member_id'] ?? '');
    $domain = (string)($_POST['domain'] ?? '');

    $b24 = null;
    if ($memberId) $b24 = BitrixClient::getByMemberId($memberId);
    if (!$b24 && $domain) $b24 = BitrixClient::getByDomain($domain);
    if (!$b24) $b24 = BitrixClient::getFirstActive();
    $apiKey = $b24 ? $b24->getDoubleTickApiKey() : $config['doubletick']['api_key'];
    $waba = $b24 ? $b24->getDoubleTickWaba() : $config['doubletick']['default_waba'];

    if (!$apiKey || !$phone) {
        echo json_encode(['success' => false, 'error' => 'API Key and phone number are required.']);
        exit;
    }

    if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $errCode = $_FILES['file']['error'] ?? 'no_file';
        echo json_encode(['success' => false, 'error' => "File upload error ({$errCode})."]);
        exit;
    }

    $file = $_FILES['file'];
    $maxSize = 16 * 1024 * 1024; // 16MB DoubleTick limit
    if ($file['size'] > $maxSize) {
        echo json_encode(['success' => false, 'error' => 'File size exceeds maximum allowed limit of 16 MB.']);
        exit;
    }

    $origName = basename((string)$file['name']);
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    $mediaDir = dirname(__DIR__) . '/storage/media';
    if (!is_dir($mediaDir)) @mkdir($mediaDir, 0777, true);

    $savedName = 'out_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $origName);
    $targetPath = $mediaDir . '/' . $savedName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        echo json_encode(['success' => false, 'error' => 'Failed to store file on server.']);
        exit;
    }

    $mediaType = match ($ext) {
        'jpg', 'jpeg', 'png', 'webp', 'gif' => 'image',
        'mp4', 'mov', 'avi' => 'video',
        'ogg', 'opus', 'mp3', 'wav', 'webm', 'm4a' => 'audio',
        default => 'document'
    };

    try {
        $dt = new DoubleTickClient($apiKey, $waba, $config['doubletick']['api_url']);
        $dtMediaUrl = $dt->uploadMedia($targetPath, null, $origName);

        $res = $dt->sendMediaMessage($mediaType, $phone, $dtMediaUrl, $caption !== '' ? $caption : null, $origName, $waba);
        $msgId = $res['messageId'] ?? ($res['dtMessageId'] ?? ('out_' . uniqid()));

        $appUrl = rtrim($config['app']['url'], '/');
        $proxyUrl = $appUrl . '/media_proxy.php?file=' . urlencode($savedName) . '&name=' . urlencode($origName);

        // Save to database
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("
                INSERT INTO message_mappings (
                    portal_id, b24_chat_id, b24_message_id, dt_message_id, whatsapp_message_id,
                    customer_phone, direction, message_type, status, raw_data, created_at
                ) VALUES (
                    :portal_id, 0, 0, :dt_id, NULL,
                    :phone, 'OUTBOUND', :type, 'sent', :raw_data, datetime('now')
                )
            ");
            $stmt->execute([
                'portal_id' => $b24 ? $b24->getPortalId() : 1,
                'dt_id' => $msgId,
                'phone' => preg_replace('/[^0-9]/', '', $phone),
                'type' => $mediaType,
                'raw_data' => json_encode([
                    'text' => $caption,
                    'media_url' => $proxyUrl,
                    'dt_url' => $dtMediaUrl,
                    'media_type' => $mediaType,
                    'file_name' => $origName,
                    'file_size' => $file['size'],
                    'time' => date('Y-m-d H:i:s'),
                ]),
            ]);
        } catch (\Throwable $dbEx) {
            Logger::error("Failed to log sent media message: " . $dbEx->getMessage());
        }

        echo json_encode([
            'success' => true,
            'message_id' => $msgId,
            'media_url' => $proxyUrl,
            'media_type' => $mediaType,
            'file_name' => $origName,
            'caption' => $caption,
            'time_str' => date('h:i A'),
            'raw_response' => $res,
        ]);
    } catch (\Throwable $e) {
        @unlink($targetPath);
        $msg = $e->getMessage();
        $isClosed = (stripos($msg, 'closed window') !== false || stripos($msg, 'template message') !== false || stripos($msg, 'window is closed') !== false);
        echo json_encode([
            'success' => false,
            'error' => $msg,
            'window_closed' => $isClosed,
        ]);
    }
    exit;
}

// -------------------------------------------------------------
// AJAX Action: Send WhatsApp Voice Note (PTT)
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_voice_note') {
    header('Content-Type: application/json');
    $phone = trim((string)($_POST['phone'] ?? ''));
    $duration = (int)($_POST['duration'] ?? 0);
    $memberId = (string)($_POST['member_id'] ?? '');
    $domain = (string)($_POST['domain'] ?? '');

    $b24 = null;
    if ($memberId) $b24 = BitrixClient::getByMemberId($memberId);
    if (!$b24 && $domain) $b24 = BitrixClient::getByDomain($domain);
    if (!$b24) $b24 = BitrixClient::getFirstActive();
    $apiKey = $b24 ? $b24->getDoubleTickApiKey() : $config['doubletick']['api_key'];
    $waba = $b24 ? $b24->getDoubleTickWaba() : $config['doubletick']['default_waba'];

    if (!$apiKey || !$phone) {
        echo json_encode(['success' => false, 'error' => 'API Key and phone number are required.']);
        exit;
    }

    $mediaDir = dirname(__DIR__) . '/storage/media';
    if (!is_dir($mediaDir)) @mkdir($mediaDir, 0777, true);

    $savedName = 'voice_' . uniqid() . '.ogg';
    $targetPath = $mediaDir . '/' . $savedName;

    if (!empty($_FILES['audio_file']) && $_FILES['audio_file']['error'] === UPLOAD_ERR_OK) {
        move_uploaded_file($_FILES['audio_file']['tmp_name'], $targetPath);
    } elseif (!empty($_POST['audio_base64'])) {
        $base64 = preg_replace('/^data:audio\/\w+;base64,/', '', $_POST['audio_base64']);
        file_put_contents($targetPath, base64_decode($base64));
    } else {
        echo json_encode(['success' => false, 'error' => 'No voice recording audio received.']);
        exit;
    }

    if (!file_exists($targetPath) || filesize($targetPath) === 0) {
        echo json_encode(['success' => false, 'error' => 'Voice recording is empty.']);
        exit;
    }

    try {
        $dt = new DoubleTickClient($apiKey, $waba, $config['doubletick']['api_url']);

        // Upload recorded audio file to DoubleTick (uploadMedia normalizes ogg/webm/opus to audio/mp4)
        $dtMediaUrl = $dt->uploadMedia($targetPath, 'audio/mp4', 'voice_note.mp4');

        $durStr = $duration > 0 ? sprintf('%02d:%02d', floor($duration / 60), $duration % 60) : '';
        $caption = "🎙️ Voice Note" . ($durStr ? " ({$durStr})" : "");
        $res = $dt->sendMediaMessage('audio', $phone, $dtMediaUrl, null, null, $waba);
        $msgId = $res['messageId'] ?? ($res['dtMessageId'] ?? ('voice_' . uniqid()));

        $appUrl = rtrim($config['app']['url'], '/');
        $proxyUrl = $appUrl . '/media_proxy.php?file=' . urlencode($savedName) . '&name=voice_note.ogg';

        // Save to database
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("
                INSERT INTO message_mappings (
                    portal_id, b24_chat_id, b24_message_id, dt_message_id, whatsapp_message_id,
                    customer_phone, direction, message_type, status, raw_data, created_at
                ) VALUES (
                    :portal_id, 0, 0, :dt_id, NULL,
                    :phone, 'OUTBOUND', 'audio', 'sent', :raw_data, datetime('now')
                )
            ");
            $stmt->execute([
                'portal_id' => $b24 ? $b24->getPortalId() : 1,
                'dt_id' => $msgId,
                'phone' => preg_replace('/[^0-9]/', '', $phone),
                'raw_data' => json_encode([
                    'text' => $caption,
                    'media_url' => $proxyUrl,
                    'dt_url' => $dtMediaUrl,
                    'media_type' => 'audio',
                    'is_voice_note' => true,
                    'file_name' => 'voice_note.ogg',
                    'duration' => $duration,
                    'time' => date('Y-m-d H:i:s'),
                ]),
            ]);
        } catch (\Throwable $dbEx) {
            Logger::error("Failed to log sent voice note: " . $dbEx->getMessage());
        }

        echo json_encode([
            'success' => true,
            'message_id' => $msgId,
            'media_url' => $proxyUrl,
            'media_type' => 'document',
            'is_voice_note' => true,
            'duration' => $duration,
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

    $templateData = [];
    if (!empty($_POST['template_data'])) {
        $decoded = json_decode((string)$_POST['template_data'], true);
        if (is_array($decoded)) {
            $templateData = $decoded;
        }
    }

    $params = [];
    if (!empty($_POST['params'])) {
        $decodedParams = json_decode((string)$_POST['params'], true);
        if (is_array($decodedParams)) {
            $params = array_values(array_map('strval', $decodedParams));
        }
    }
    // Fallback for legacy param1, param2, param3...
    if (empty($params)) {
        for ($i = 1; $i <= 20; $i++) {
            if (isset($_POST["param{$i}"]) && trim((string)$_POST["param{$i}"]) !== '') {
                $params[] = trim((string)$_POST["param{$i}"]);
            }
        }
    }

    // If templateData wasn't explicitly structured, wrap body placeholders if params exist
    if (empty($templateData) && !empty($params)) {
        $templateData = [
            'body' => [
                'placeholders' => $params
            ]
        ];
    }

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
        $res = $dt->sendTemplateMessage($phone, $templateName, $language, $templateData, $waba);
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
                        'template_data' => $templateData,
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
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
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
            max-width: 580px;
            max-height: 88vh;
            overflow-y: auto;
            margin: 30px auto;
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

        /* Template Live Preview Card */
        .template-preview-bubble {
            background: #005c4b;
            color: #e9edef;
            padding: 12px 14px;
            border-radius: 10px 10px 2px 10px;
            font-size: 13px;
            line-height: 1.5;
            position: relative;
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            white-space: pre-wrap;
            word-break: break-word;
        }
        .template-preview-header {
            font-weight: 700;
            margin-bottom: 6px;
            color: #fff;
            font-size: 13.5px;
        }
        .template-preview-media {
            margin-bottom: 8px;
            border-radius: 6px;
            overflow: hidden;
            background: rgba(0, 0, 0, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px;
            color: #a7f3d0;
            font-size: 12px;
            border: 1px dashed rgba(255, 255, 255, 0.2);
            gap: 6px;
        }
        .template-preview-footer {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.6);
            margin-top: 8px;
        }
        .template-preview-buttons {
            display: flex;
            flex-direction: column;
            gap: 4px;
            margin-top: 8px;
            border-top: 1px solid rgba(255, 255, 255, 0.12);
            padding-top: 8px;
        }
        .template-preview-btn {
            background: rgba(255, 255, 255, 0.08);
            color: #38bdf8;
            border: none;
            border-radius: 6px;
            padding: 6px 12px;
            font-size: 12px;
            text-align: center;
            font-weight: 500;
        }
        .tpl-var-highlight {
            background: rgba(16, 185, 129, 0.35);
            color: #a7f3d0;
            padding: 1px 4px;
            border-radius: 4px;
            font-weight: 600;
        }
        .tpl-var-empty {
            background: rgba(245, 158, 11, 0.25);
            color: #fde68a;
            padding: 1px 4px;
            border-radius: 4px;
            font-style: italic;
        }

        /* WhatsApp-style Audio Player */
        .wa-audio-player {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 4px 2px;
            min-width: 250px;
            max-width: 320px;
        }
        .wa-audio-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            font-size: 16px;
            flex-shrink: 0;
        }
        .wa-audio-mic-badge {
            position: absolute;
            bottom: -2px;
            right: -2px;
            font-size: 10px;
            background: #10b981;
            border-radius: 50%;
            width: 14px;
            height: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .wa-audio-play-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.15);
            border: none;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            flex-shrink: 0;
        }
        .wa-audio-play-btn:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: scale(1.05);
        }
        .wa-audio-track-container {
            flex: 1;
            height: 24px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            cursor: pointer;
            position: relative;
        }
        .wa-audio-waveform {
            display: flex;
            align-items: center;
            gap: 2px;
            height: 16px;
            width: 100%;
        }
        .wa-audio-waveform span {
            flex: 1;
            background: rgba(255, 255, 255, 0.35);
            border-radius: 1px;
            min-height: 3px;
            transition: background 0.1s;
        }
        .wa-audio-progress-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            background: var(--primary);
            border-radius: 2px;
            width: 0%;
            transition: width 0.1s linear;
        }
        .wa-audio-meta {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 2px;
            flex-shrink: 0;
        }
        .wa-audio-time {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.7);
            font-family: monospace;
        }
        .wa-audio-speed-btn {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 10px;
            color: #e2e8f0;
            font-size: 10px;
            padding: 1px 5px;
            cursor: pointer;
            font-weight: 600;
        }
        .wa-audio-speed-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Image Message Styling */
        .wa-image-card {
            position: relative;
            cursor: pointer;
            overflow: hidden;
            border-radius: 8px;
            max-width: 280px;
            margin-bottom: 4px;
        }
        .wa-image-card img {
            display: block;
            width: 100%;
            max-height: 240px;
            object-fit: cover;
            border-radius: 8px;
            transition: transform 0.2s;
        }
        .wa-image-card:hover img {
            transform: scale(1.02);
        }
        .wa-image-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.25);
            opacity: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.2s;
            border-radius: 8px;
        }
        .wa-image-card:hover .wa-image-overlay {
            opacity: 1;
        }

        /* Document Message Styling */
        .wa-doc-card {
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 10px 14px;
            border-radius: 10px;
            max-width: 320px;
            margin-bottom: 4px;
        }
        .wa-doc-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: #e11d48;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: 700;
            flex-shrink: 0;
        }
        .wa-doc-icon.pdf { background: #e11d48; }
        .wa-doc-icon.doc { background: #2563eb; }
        .wa-doc-icon.xls { background: #16a34a; }
        .wa-doc-icon.zip { background: #d97706; }
        .wa-doc-icon.other { background: #475569; }
        .wa-doc-details {
            flex: 1;
            min-width: 0;
        }
        .wa-doc-name {
            font-size: 13px;
            font-weight: 600;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .wa-doc-meta {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.6);
            margin-top: 2px;
        }
        .wa-doc-dl-btn {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.2s;
            flex-shrink: 0;
        }
        .wa-doc-dl-btn:hover {
            background: var(--primary);
            transform: scale(1.08);
        }

        /* Attachment Preview Drawer */
        .attachment-preview-drawer {
            background: #1e2a30;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: 8px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
            animation: slideUp 0.2s ease;
        }
        @keyframes slideUp {
            from { transform: translateY(10px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .attachment-preview-thumb {
            width: 44px;
            height: 44px;
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            font-size: 20px;
            flex-shrink: 0;
        }
        .attachment-preview-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .attachment-preview-meta {
            flex: 1;
            min-width: 0;
        }
        .attachment-name {
            display: block;
            font-size: 13px;
            color: #fff;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .attachment-size {
            display: block;
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 2px;
        }
        .attachment-caption-input {
            flex: 2;
            background: #111b21;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 8px;
            color: #fff;
            padding: 6px 12px;
            font-size: 13px;
            outline: none;
        }
        .attachment-caption-input:focus {
            border-color: var(--primary);
        }
        .attachment-remove-btn {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 20px;
            cursor: pointer;
            padding: 4px;
            line-height: 1;
            border-radius: 4px;
        }
        .attachment-remove-btn:hover {
            color: #ef4444;
        }

        /* Composer Buttons */
        .composer-btn {
            height: 42px;
            width: 42px;
            padding: 0;
            justify-content: center;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .btn-mic {
            color: #94a3b8;
        }
        .btn-mic:hover {
            color: #10b981;
            background: rgba(16, 185, 129, 0.15);
            border-color: rgba(16, 185, 129, 0.3);
        }

        /* Active Voice Recording Bar */
        .recording-pulse-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #ef4444;
            animation: pulseRecord 1s infinite alternate;
        }
        @keyframes pulseRecord {
            from { opacity: 1; transform: scale(1); }
            to { opacity: 0.3; transform: scale(0.8); }
        }
        .recording-waveform {
            display: flex;
            align-items: center;
            gap: 3px;
            height: 20px;
        }
        .rec-bar {
            width: 3px;
            background: #ef4444;
            border-radius: 2px;
            animation: waveBounce 0.8s ease-in-out infinite alternate;
        }
        .rec-bar.b1 { height: 6px; animation-delay: 0.1s; }
        .rec-bar.b2 { height: 14px; animation-delay: 0.2s; }
        .rec-bar.b3 { height: 18px; animation-delay: 0.3s; }
        .rec-bar.b4 { height: 8px; animation-delay: 0.4s; }
        .rec-bar.b5 { height: 16px; animation-delay: 0.2s; }
        .rec-bar.b6 { height: 10px; animation-delay: 0.3s; }
        .rec-bar.b7 { height: 18px; animation-delay: 0.1s; }
        .rec-bar.b8 { height: 7px; animation-delay: 0.4s; }
        @keyframes waveBounce {
            0% { transform: scaleY(0.3); }
            100% { transform: scaleY(1.2); }
        }

        /* Fullscreen Lightbox */
        .lightbox-modal {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(8px);
            z-index: 200;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            animation: fadeInMsg 0.2s ease;
        }
        .lightbox-img {
            max-width: 90vw;
            max-height: 80vh;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.8);
            object-fit: contain;
        }
        .lightbox-close {
            position: absolute;
            top: 20px;
            right: 28px;
            color: #fff;
            font-size: 32px;
            cursor: pointer;
            transition: color 0.2s;
        }
        .lightbox-close:hover {
            color: #ef4444;
        }
        .lightbox-footer {
            margin-top: 16px;
            display: flex;
            align-items: center;
            gap: 16px;
            color: #fff;
        }
        .lightbox-caption {
            font-size: 14px;
            color: #e2e8f0;
        }
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

    <!-- Attachment Drawer (above composer) -->
    <div id="attachment-preview-drawer" class="attachment-preview-drawer" style="display: none;">
        <div class="attachment-preview-thumb" id="attachment-preview-thumb">📎</div>
        <div class="attachment-preview-meta">
            <span id="attachment-preview-name" class="attachment-name">filename.pdf</span>
            <span id="attachment-preview-size" class="attachment-size">1.2 MB</span>
        </div>
        <input type="text" id="attachment-caption-input" class="attachment-caption-input" placeholder="Add an optional caption...">
        <button class="attachment-remove-btn" onclick="clearAttachment()" title="Remove file">&times;</button>
    </div>

    <!-- Hidden file input for attachments -->
    <input type="file" id="chat-file-input" style="display: none;" onchange="handleFilePicked(this)" accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.txt">

    <!-- Message Composer -->
    <div class="chat-composer">
        <!-- Normal Composer Controls -->
        <div id="composer-normal-controls" style="display: flex; width: 100%; align-items: flex-end; gap: 8px;">
            <button class="btn btn-secondary composer-btn" onclick="openTemplateModal()" title="Send WhatsApp Template">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            </button>
            <button class="btn btn-secondary composer-btn" onclick="triggerFilePicker()" title="Attach File, Image or Document">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
            </button>
            <div class="composer-input-wrapper">
                <textarea id="message-input" class="composer-textarea" rows="1" placeholder="Type a WhatsApp message... (Enter to send, Shift+Enter for new line)"></textarea>
            </div>
            <button id="btn-send" class="btn-send" onclick="handleSendAction()" title="Send WhatsApp Message">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="transform: translateX(1px);"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            </button>
        </div>
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

        <!-- Dynamic Template Variables -->
        <div id="crm-template-dynamic-fields" style="margin-top: 6px;">
            <div id="crm-template-dynamic-hint" style="font-size: 12.5px; color: var(--text-muted); font-style: italic; margin-bottom: 12px; padding: 10px 12px; background: rgba(255,255,255,0.03); border-radius: 8px; border: 1px dashed rgba(255,255,255,0.12);">
                Select an approved template above to view and fill its variables.
            </div>
        </div>

        <!-- Live WhatsApp Message Preview -->
        <div id="crm-template-preview-wrapper" style="display: none; margin-top: 10px; margin-bottom: 16px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                <label style="font-size: 11px; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.6px; font-weight: 600;">
                    Live WhatsApp Preview
                </label>
                <span id="crm-template-var-count-badge" style="font-size: 11px; padding: 2px 8px; border-radius: 10px; background: rgba(63, 144, 109, 0.2); color: #6ee7b7;">
                    0 variables
                </span>
            </div>
            <div id="crm-template-preview" class="template-preview-bubble">
                <!-- Live preview generated here -->
            </div>
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

<!-- Full Image Lightbox Modal -->
<div id="lightbox-modal" class="lightbox-modal" onclick="closeLightbox(event)">
    <span class="lightbox-close" onclick="closeLightbox(event)">&times;</span>
    <img id="lightbox-img" class="lightbox-img" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg'/%3E" alt="Preview">
    <div class="lightbox-footer">
        <span id="lightbox-caption" class="lightbox-caption"></span>
        <a id="lightbox-download" class="btn btn-secondary" href="#" download="" target="_blank" rel="noopener">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Download
        </a>
    </div>
</div>

<script>
    // -------------------------------------------------------------
    // Global State Holder
    // -------------------------------------------------------------
    window.keenDebugStore = {
        crmInfo: {},
        requests: [],
        lastChatHistoryResponse: null,
        logReq: function() {},
        logRes: function() {},
        logErr: function() {},
        exportJson: function() { return ''; }
    };

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
        const p1 = document.getElementById('crm-param1');
        if (p1) p1.value = name;

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
                handleSendAction();
            }
        });

        // Initial fetch with forced cache bypass
        lastRenderedCount = 0;
        loadChatHistory(true);

        // Start background polling every 5 seconds
        if (pollInterval) clearInterval(pollInterval);
        pollInterval = setInterval(function() {
            loadChatHistory(false);
        }, 5000);

        // Auto-refresh when tab is refocused or becomes visible
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'visible') {
                lastRenderedCount = 0;
                loadChatHistory(true);
            }
        });
        window.addEventListener('focus', function() {
            lastRenderedCount = 0;
            loadChatHistory(true);
        });
    }

    function loadChatHistory(forceScroll) {
        if (!currentPhone) return;

        const currentDomain = (window.keenDebugStore && window.keenDebugStore.crmInfo && window.keenDebugStore.crmInfo.domain) ? window.keenDebugStore.crmInfo.domain : '';
        const nocache = Date.now();
        const url = 'placement_tab.php?action=get_chat_history&phone=' + encodeURIComponent(currentPhone) + '&member_id=' + encodeURIComponent(currentMemberId) + '&domain=' + encodeURIComponent(currentDomain) + '&_t=' + nocache;
        window.keenDebugStore.logReq('get_chat_history', { url, phone: currentPhone, memberId: currentMemberId, domain: currentDomain });

        fetch(url, {
            cache: 'no-store',
            headers: {
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache'
            }
        })
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

                const messages = data.messages || [];
                // Update 24h window badge (incorporating thread messages)
                updateWindowBadge(data.window_status, messages);
                renderMessages(messages, forceScroll);
            })
            .catch(err => {
                window.keenDebugStore.logErr('get_chat_history', err);
            });
    }

    function updateWindowBadge(win, messages) {
        let statusObj = win || { isOpen: false };

        // Client-side verification: if the thread contains an inbound message within 24h, the session is active!
        if (Array.isArray(messages) && messages.length > 0) {
            let maxInboundTs = 0;
            const nowSec = Math.floor(Date.now() / 1000);
            messages.forEach(m => {
                if (m.direction === 'INBOUND' && m.timestamp) {
                    const ts = parseInt(m.timestamp, 10);
                    if (ts > maxInboundTs) maxInboundTs = ts;
                }
            });
            if (maxInboundTs > 0 && (nowSec - maxInboundTs) < 86400) {
                const remainingSec = 86400 - (nowSec - maxInboundTs);
                const h = Math.floor(remainingSec / 3600);
                const m = Math.floor((remainingSec % 3600) / 60);
                statusObj = {
                    isOpen: true,
                    expirationTimestamp: maxInboundTs + 86400,
                    formattedRemaining: `${h}h ${m}m`
                };
            }
        }

        window.keenDebugStore.crmInfo.windowStatus = statusObj;
        const dot = document.getElementById('session-dot');
        const text = document.getElementById('session-text');
        const banner = document.getElementById('window-banner');

        if (statusObj && statusObj.isOpen) {
            isWindowOpen = true;
            dot.className = 'status-dot dot-active';
            text.innerText = '24h Session Active' + (statusObj.formattedRemaining ? ' (expires in ' + statusObj.formattedRemaining + ')' : '');
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
                bubbleContent += renderMediaAttachment(m, isOut);
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
                bubbleContent += `<div style="white-space: pre-wrap; margin-top: ${m.media_url ? '4px' : '0'};">${escapeHtml(textContent)}</div>`;
            } else if (m.media_url) {
                // Media only, cleanly rendered above
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

    // -------------------------------------------------------------
    // Rich Media Renderers (Audio, Image, Video, Document)
    // -------------------------------------------------------------
    function renderMediaAttachment(m, isOut) {
        if (!m.media_url) return '';
        const mUrl = m.media_url;
        const mType = m.type || 'media';
        const fileName = m.file_name || 'attachment';
        const isVoice = m.is_voice_note || mType === 'audio';

        if (mType === 'audio' || isVoice) {
            return `
                <div class="wa-audio-player" data-src="${escapeHtml(mUrl)}">
                    <div class="wa-audio-avatar">
                        ${isOut ? '👤' : '🎧'}
                        <span class="wa-audio-mic-badge" title="Voice note">🎙️</span>
                    </div>
                    <button class="wa-audio-play-btn" onclick="toggleAudio(this)" title="Play / Pause">
                        <svg class="icon-play" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                        <svg class="icon-pause" style="display:none;" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
                    </button>
                    <div class="wa-audio-track-container" onclick="seekAudio(event, this)" title="Click to seek">
                        <div class="wa-audio-waveform">
                            <span style="height: 35%;"></span><span style="height: 65%;"></span><span style="height: 90%;"></span>
                            <span style="height: 40%;"></span><span style="height: 75%;"></span><span style="height: 30%;"></span>
                            <span style="height: 85%;"></span><span style="height: 50%;"></span><span style="height: 100%;"></span>
                            <span style="height: 60%;"></span><span style="height: 45%;"></span><span style="height: 70%;"></span>
                            <span style="height: 55%;"></span><span style="height: 80%;"></span><span style="height: 40%;"></span>
                        </div>
                        <div class="wa-audio-progress-bar"></div>
                    </div>
                    <div class="wa-audio-meta">
                        <span class="wa-audio-time">0:00</span>
                        <button class="wa-audio-speed-btn" onclick="togglePlaybackSpeed(this)" title="Playback speed">1x</button>
                    </div>
                    <audio preload="metadata" src="${escapeHtml(mUrl)}" onended="onAudioEnded(this)" ontimeupdate="onAudioTimeUpdate(this)" onloadedmetadata="onAudioMetadataLoaded(this)"></audio>
                </div>
            `;
        }

        if (mType === 'image') {
            return `
                <div class="wa-image-card" onclick="openLightbox('${escapeHtml(mUrl)}', '${escapeHtml(fileName)}')">
                    <img src="${escapeHtml(mUrl)}" alt="${escapeHtml(fileName)}" loading="lazy">
                    <div class="wa-image-overlay">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                    </div>
                </div>
            `;
        }

        if (mType === 'video') {
            return `
                <div style="margin-bottom: 6px; max-width: 280px;">
                    <video controls preload="metadata" style="width: 100%; border-radius: 8px;">
                        <source src="${escapeHtml(mUrl)}">
                        Your browser does not support video.
                    </video>
                </div>
            `;
        }

        // Default: Document card
        let ext = (fileName.split('.').pop() || 'doc').toUpperCase();
        let iconClass = 'other';
        if (ext === 'PDF') iconClass = 'pdf';
        else if (['DOC', 'DOCX'].includes(ext)) iconClass = 'doc';
        else if (['XLS', 'XLSX', 'CSV'].includes(ext)) iconClass = 'xls';
        else if (['ZIP', 'RAR', '7Z', 'TAR'].includes(ext)) iconClass = 'zip';

        return `
            <div class="wa-doc-card">
                <div class="wa-doc-icon ${iconClass}">${escapeHtml(ext.substring(0, 4))}</div>
                <div class="wa-doc-details">
                    <div class="wa-doc-name" title="${escapeHtml(fileName)}">${escapeHtml(fileName)}</div>
                    <div class="wa-doc-meta">${escapeHtml(ext)} Document</div>
                </div>
                <a href="${escapeHtml(mUrl)}&download=1" download="${escapeHtml(fileName)}" class="wa-doc-dl-btn" title="Download ${escapeHtml(fileName)}" target="_blank" rel="noopener">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                </a>
            </div>
        `;
    }

    // -------------------------------------------------------------
    // Audio Player Event Handlers
    // -------------------------------------------------------------
    let currentlyPlayingAudio = null;

    function toggleAudio(btn) {
        const player = btn.closest('.wa-audio-player');
        const audio = player?.querySelector('audio');
        const iconPlay = btn.querySelector('.icon-play');
        const iconPause = btn.querySelector('.icon-pause');

        if (!audio) return;

        if (audio.paused) {
            if (currentlyPlayingAudio && currentlyPlayingAudio !== audio) {
                currentlyPlayingAudio.pause();
                const otherBtn = currentlyPlayingAudio.closest('.wa-audio-player')?.querySelector('.wa-audio-play-btn');
                if (otherBtn) {
                    otherBtn.querySelector('.icon-play').style.display = 'block';
                    otherBtn.querySelector('.icon-pause').style.display = 'none';
                }
            }
            audio.play().then(() => {
                currentlyPlayingAudio = audio;
                iconPlay.style.display = 'none';
                iconPause.style.display = 'block';
            }).catch(err => {
                console.warn('Audio playback error:', err);
            });
        } else {
            audio.pause();
            iconPlay.style.display = 'block';
            iconPause.style.display = 'none';
        }
    }

    function onAudioTimeUpdate(audio) {
        const player = audio.closest('.wa-audio-player');
        if (!player) return;
        const prog = player.querySelector('.wa-audio-progress-bar');
        const timeEl = player.querySelector('.wa-audio-time');
        if (audio.duration && !isNaN(audio.duration)) {
            const pct = (audio.currentTime / audio.duration) * 100;
            if (prog) prog.style.width = pct + '%';
            if (timeEl) timeEl.innerText = formatDuration(audio.currentTime);
        }
    }

    function onAudioMetadataLoaded(audio) {
        const player = audio.closest('.wa-audio-player');
        if (!player) return;
        const timeEl = player.querySelector('.wa-audio-time');
        if (timeEl && audio.duration && !isNaN(audio.duration)) {
            timeEl.innerText = formatDuration(audio.duration);
        }
    }

    function onAudioEnded(audio) {
        const player = audio.closest('.wa-audio-player');
        if (!player) return;
        const btn = player.querySelector('.wa-audio-play-btn');
        const prog = player.querySelector('.wa-audio-progress-bar');
        const timeEl = player.querySelector('.wa-audio-time');
        if (btn) {
            btn.querySelector('.icon-play').style.display = 'block';
            btn.querySelector('.icon-pause').style.display = 'none';
        }
        if (prog) prog.style.width = '0%';
        if (timeEl && audio.duration) timeEl.innerText = formatDuration(audio.duration);
        if (currentlyPlayingAudio === audio) currentlyPlayingAudio = null;
    }

    function seekAudio(e, track) {
        const player = track.closest('.wa-audio-player');
        const audio = player?.querySelector('audio');
        if (!audio || !audio.duration) return;
        const rect = track.getBoundingClientRect();
        const pos = (e.clientX - rect.left) / rect.width;
        audio.currentTime = Math.max(0, Math.min(pos * audio.duration, audio.duration));
    }

    function togglePlaybackSpeed(btn) {
        const player = btn.closest('.wa-audio-player');
        const audio = player?.querySelector('audio');
        if (!audio) return;
        let rate = audio.playbackRate || 1;
        if (rate === 1) rate = 1.5;
        else if (rate === 1.5) rate = 2;
        else rate = 1;
        audio.playbackRate = rate;
        btn.innerText = rate + 'x';
    }

    function formatDuration(sec) {
        if (!sec || isNaN(sec) || !isFinite(sec) || sec < 0) return '0:00';
        sec = Math.floor(sec);
        const m = Math.floor(sec / 60);
        const s = sec % 60;
        return m + ':' + (s < 10 ? '0' : '') + s;
    }

    // -------------------------------------------------------------
    // Fullscreen Image Lightbox
    // -------------------------------------------------------------
    function openLightbox(src, name) {
        const modal = document.getElementById('lightbox-modal');
        const img = document.getElementById('lightbox-img');
        const caption = document.getElementById('lightbox-caption');
        const dl = document.getElementById('lightbox-download');
        if (!modal || !img) return;

        img.src = src;
        caption.innerText = name || '';
        dl.href = src + (src.includes('?') ? '&' : '?') + 'download=1';
        dl.setAttribute('download', name || 'image.jpg');
        modal.style.display = 'flex';
    }

    function closeLightbox(e) {
        if (e && e.target && e.target.id === 'lightbox-img') return;
        const modal = document.getElementById('lightbox-modal');
        const img = document.getElementById('lightbox-img');
        if (img) img.src = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg'/%3E";
        if (modal) modal.style.display = 'none';
    }

    // -------------------------------------------------------------
    // Attachment File Picker & Drawer Handlers
    // -------------------------------------------------------------
    let selectedAttachmentFile = null;

    function triggerFilePicker() {
        const input = document.getElementById('chat-file-input');
        if (input) input.click();
    }

    function handleFilePicked(input) {
        if (!input.files || input.files.length === 0) return;
        const file = input.files[0];
        const maxSize = 16 * 1024 * 1024; // 16 MB DoubleTick limit
        if (file.size > maxSize) {
            alert('The selected file exceeds 16 MB. Please select a smaller file.');
            input.value = '';
            return;
        }

        selectedAttachmentFile = file;
        const drawer = document.getElementById('attachment-preview-drawer');
        const nameEl = document.getElementById('attachment-preview-name');
        const sizeEl = document.getElementById('attachment-preview-size');
        const thumbEl = document.getElementById('attachment-preview-thumb');
        const captionInput = document.getElementById('attachment-caption-input');

        nameEl.innerText = file.name;
        sizeEl.innerText = formatFileSize(file.size);
        captionInput.value = '';

        // Generate thumbnail preview for images
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                thumbEl.innerHTML = `<img src="${e.target.result}" alt="Thumb" style="width:100%;height:100%;object-fit:cover;border-radius:4px;">`;
            };
            reader.readAsDataURL(file);
        } else if (file.type.startsWith('audio/')) {
            thumbEl.innerHTML = '🎵';
        } else if (file.type.startsWith('video/')) {
            thumbEl.innerHTML = '🎬';
        } else if (file.name.endsWith('.pdf')) {
            thumbEl.innerHTML = '📄';
        } else {
            thumbEl.innerHTML = '📎';
        }

        drawer.style.display = 'flex';
        captionInput.focus();
    }

    function clearAttachment() {
        selectedAttachmentFile = null;
        const input = document.getElementById('chat-file-input');
        if (input) input.value = '';
        const drawer = document.getElementById('attachment-preview-drawer');
        if (drawer) drawer.style.display = 'none';
    }

    function formatFileSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    // -------------------------------------------------------------
    // Voice Note (PTT) Recorder Logic
    // -------------------------------------------------------------
    // -------------------------------------------------------------
    // Send Action Router (Text or Media)
    // -------------------------------------------------------------
    function handleSendAction() {
        if (selectedAttachmentFile) {
            sendAttachment();
        } else {
            sendMessage();
        }
    }

    function sendAttachment() {
        if (!selectedAttachmentFile || !currentPhone) return;

        const captionInput = document.getElementById('attachment-caption-input');
        const caption = captionInput ? captionInput.value.trim() : '';
        const file = selectedAttachmentFile;
        const currentDomain = (window.keenDebugStore && window.keenDebugStore.crmInfo && window.keenDebugStore.crmInfo.domain) ? window.keenDebugStore.crmInfo.domain : '';

        const btn = document.getElementById('btn-send');
        btn.disabled = true;

        const tempId = 'temp_att_' + Date.now();
        const container = document.getElementById('chat-messages');

        const optimisticHtml = `
            <div class="message-row outbound" id="${tempId}">
                <div class="bubble">
                    <div style="font-size: 12px; color: #a7f3d0; margin-bottom: 4px;">📎 Uploading ${escapeHtml(file.name)} (${formatFileSize(file.size)})...</div>
                    ${caption ? `<div style="white-space: pre-wrap;">${escapeHtml(caption)}</div>` : ''}
                    <div class="bubble-footer"><span>Uploading attachment...</span> <span class="status-check">🕒</span></div>
                </div>
            </div>
        `;
        if (container.querySelector('.empty-chat')) container.innerHTML = '';
        container.insertAdjacentHTML('beforeend', optimisticHtml);
        container.scrollTop = container.scrollHeight;

        const formData = new FormData();
        formData.append('action', 'send_media');
        formData.append('phone', currentPhone);
        formData.append('caption', caption);
        formData.append('file', file);
        formData.append('member_id', currentMemberId);
        formData.append('domain', currentDomain);

        clearAttachment();

        window.keenDebugStore.logReq('send_media', { phone: currentPhone, fileName: file.name, size: file.size, caption: caption });

        fetch('placement_tab.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                window.keenDebugStore.logRes('send_media', data);
                if (data.success) {
                    loadChatHistory(true);
                } else {
                    const tempEl = document.getElementById(tempId);
                    if (tempEl) {
                        tempEl.querySelector('.status-check').innerHTML = '<span style="color: var(--danger);">⚠️</span>';
                        tempEl.querySelector('.bubble-footer span:first-child').innerText = 'Upload failed';
                    }
                    alert('Failed to send attachment: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(err => {
                btn.disabled = false;
                window.keenDebugStore.logErr('send_media', err);
                alert('Error uploading attachment: ' + err.message);
            });
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
    window.crmTemplatesMap = {};
    window.currentSelectedTemplateParsed = null;
    window.currentSelectedTemplate = null;

    function openTemplateModal() {
        if (!currentPhone) {
            alert('Please wait until contact phone number is loaded.');
            return;
        }
        document.getElementById('template-modal').style.display = 'block';
        document.getElementById('crm-template-status').style.display = 'none';

        if (!crmTemplatesLoaded) {
            const currentDomain = (window.keenDebugStore && window.keenDebugStore.crmInfo && window.keenDebugStore.crmInfo.domain) ? window.keenDebugStore.crmInfo.domain : '';
            const tplUrl = 'placement_tab.php?action=get_templates&member_id=' + encodeURIComponent(currentMemberId) + '&domain=' + encodeURIComponent(currentDomain) + '&_t=' + Date.now();
            window.keenDebugStore.logReq('get_templates', { url: tplUrl, memberId: currentMemberId, domain: currentDomain });

            fetch(tplUrl, {
                cache: 'no-store',
                headers: {
                    'Cache-Control': 'no-cache, no-store, must-revalidate',
                    'Pragma': 'no-cache'
                }
            })
                .then(res => res.json())
                .then(data => {
                    window.keenDebugStore.logRes('get_templates', data);
                    if (data.success && data.templates && Array.isArray(data.templates)) {
                        const sel = document.getElementById('crm-template-select');
                        sel.innerHTML = '<option value="">-- Choose from approved templates --</option>';
                        window.crmTemplatesMap = {};
                        data.templates.forEach(tpl => {
                            const tName = tpl.name || '';
                            const tLang = tpl.language || 'en';
                            const key = tName + '__' + tLang;
                            window.crmTemplatesMap[key] = tpl;

                            const opt = document.createElement('option');
                            opt.value = key;
                            opt.setAttribute('data-name', tName);
                            opt.setAttribute('data-lang', tLang);
                            opt.textContent = tName + ' (' + tLang + ') - ' + (tpl.category || 'TEMPLATE');
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

    function extractTemplateVariables(tpl) {
        const result = {
            header: null,
            body: { text: '', variables: [] },
            footer: { text: '' },
            buttons: []
        };

        if (!tpl || !Array.isArray(tpl.components)) return result;

        tpl.components.forEach(comp => {
            const cType = (comp.type || '').toUpperCase();
            if (cType === 'HEADER') {
                const format = (comp.format || 'TEXT').toUpperCase();
                let defaultMediaUrl = '';
                let defaultFilename = '';

                if (Array.isArray(comp.variables) && comp.variables.length > 0) {
                    const firstVar = comp.variables[0];
                    if (typeof firstVar === 'object' && firstVar !== null) {
                        defaultMediaUrl = firstVar.mediaUrl || firstVar.url || '';
                        defaultFilename = firstVar.fileName || firstVar.filename || '';
                    }
                }
                if (!defaultMediaUrl && comp.mediaUrl) defaultMediaUrl = comp.mediaUrl;
                if (!defaultMediaUrl && comp.url) defaultMediaUrl = comp.url;
                if (!defaultMediaUrl && comp.example) {
                    if (Array.isArray(comp.example.header_url) && comp.example.header_url[0]) {
                        defaultMediaUrl = comp.example.header_url[0];
                    } else if (Array.isArray(comp.example.header_handle) && comp.example.header_handle[0]) {
                        defaultMediaUrl = comp.example.header_handle[0];
                    }
                }

                result.header = {
                    format: format,
                    text: comp.text || '',
                    defaultMediaUrl: defaultMediaUrl,
                    defaultFilename: defaultFilename,
                    variables: []
                };
                if (format === 'TEXT') {
                    const headerVars = [];
                    if (Array.isArray(comp.variables)) {
                        comp.variables.forEach((v, idx) => {
                            const vName = (typeof v === 'object' && v !== null) ? (v.name || String(idx + 1)) : String(v);
                            if (!headerVars.includes(vName)) headerVars.push(vName);
                        });
                    }
                    const textMatches = (comp.text || '').match(/\{\{([^{}]+)\}\}/g) || [];
                    textMatches.forEach(m => {
                        const raw = m.replace(/[\{\}]/g, '').trim();
                        if (!headerVars.includes(raw)) headerVars.push(raw);
                    });
                    result.header.variables = headerVars;
                }
            } else if (cType === 'BODY') {
                result.body.text = comp.text || '';
                const bodyVars = [];
                const textMatches = (comp.text || '').match(/\{\{([^{}]+)\}\}/g) || [];
                textMatches.forEach(m => {
                    const raw = m.replace(/[\{\}]/g, '').trim();
                    if (!bodyVars.includes(raw)) bodyVars.push(raw);
                });
                if (bodyVars.length === 0 && Array.isArray(comp.variables)) {
                    comp.variables.forEach((v, idx) => {
                        const vName = (typeof v === 'object' && v !== null) ? (v.name || String(idx + 1)) : String(v);
                        if (!bodyVars.includes(vName)) bodyVars.push(vName);
                    });
                }
                result.body.variables = bodyVars;
            } else if (cType === 'FOOTER') {
                result.footer.text = comp.text || '';
            } else if (cType === 'BUTTON' && Array.isArray(comp.buttons)) {
                comp.buttons.forEach((btn, bIdx) => {
                    const bType = (btn.type || '').toUpperCase();
                    const bUrl = btn.url || '';
                    const hasParam = bType === 'URL' && (bUrl.includes('{{') || (btn.urlType && btn.urlType.toUpperCase() === 'DYNAMIC'));
                    result.buttons.push({
                        index: bIdx,
                        type: bType,
                        text: btn.text || '',
                        url: bUrl,
                        hasParam: hasParam
                    });
                });
            }
        });

        return result;
    }

    function onCrmTemplateChange(sel) {
        const key = sel.value;
        const container = document.getElementById('crm-template-dynamic-fields');
        const previewWrapper = document.getElementById('crm-template-preview-wrapper');

        if (!key) {
            document.getElementById('crm-template-name').value = '';
            container.innerHTML = `
                <div id="crm-template-dynamic-hint" style="font-size: 12.5px; color: var(--text-muted); font-style: italic; margin-bottom: 12px; padding: 10px 12px; background: rgba(255,255,255,0.03); border-radius: 8px; border: 1px dashed rgba(255,255,255,0.12);">
                    Select an approved template above to view and fill its variables.
                </div>
            `;
            if (previewWrapper) previewWrapper.style.display = 'none';
            window.currentSelectedTemplateParsed = null;
            window.currentSelectedTemplate = null;
            return;
        }

        const tpl = (window.crmTemplatesMap && window.crmTemplatesMap[key]) ? window.crmTemplatesMap[key] : null;
        const opt = sel.options[sel.selectedIndex];
        const tplName = opt ? (opt.getAttribute('data-name') || (tpl && tpl.name) || '') : ((tpl && tpl.name) || '');
        const tplLang = opt ? (opt.getAttribute('data-lang') || (tpl && tpl.language) || 'en') : ((tpl && tpl.language) || 'en');

        document.getElementById('crm-template-name').value = tplName;
        document.getElementById('crm-template-lang').value = tplLang;

        if (!tpl) {
            renderManualFallbackFields();
            return;
        }

        const parsed = extractTemplateVariables(tpl);
        window.currentSelectedTemplateParsed = parsed;
        window.currentSelectedTemplate = tpl;

        renderTemplateVariableFields(parsed);
        updateTemplateLivePreview(parsed, tpl);
    }

    function renderTemplateVariableFields(parsed) {
        const container = document.getElementById('crm-template-dynamic-fields');
        container.innerHTML = '';

        let totalVars = 0;
        const bodyVars = parsed.body.variables || [];
        const header = parsed.header;
        const buttonsWithParam = (parsed.buttons || []).filter(b => b.hasParam);

        // 1. Header inputs / notices
        if (header) {
            if (header.format === 'IMAGE') {
                // Default approved image is used automatically - no required URL input needed
                const imgNotice = document.createElement('div');
                imgNotice.style.cssText = 'padding: 9px 12px; background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.25); border-radius: 8px; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between; gap: 8px;';
                imgNotice.innerHTML = `
                    <div style="display: flex; align-items: center; gap: 8px; font-size: 12.5px; color: #93c5fd;">
                        <span style="font-size: 15px;">🖼️</span>
                        <span><strong>Header Image:</strong> Using template's default approved image</span>
                    </div>
                    <span style="font-size: 11px; padding: 2px 7px; border-radius: 4px; background: rgba(16, 185, 129, 0.2); color: #6ee7b7; font-weight: 600;">Default</span>
                `;
                container.appendChild(imgNotice);
            } else if (header.format === 'VIDEO' || header.format === 'DOCUMENT') {
                totalVars++;
                const icon = header.format === 'VIDEO' ? '🎥' : '📄';
                const grp = document.createElement('div');
                grp.className = 'form-group';
                grp.innerHTML = `
                    <label style="display: flex; align-items: center; gap: 6px;">
                        <span>${icon}</span>
                        <span>Header ${header.format} URL</span>
                    </label>
                    <input type="url" id="crm-tpl-header-media" class="form-control" value="${escapeHtml(header.defaultMediaUrl || '')}" placeholder="https://example.com/file.${header.format === 'VIDEO' ? 'mp4' : 'pdf'}" oninput="onTplFieldInput()">
                `;
                container.appendChild(grp);

                if (header.format === 'DOCUMENT') {
                    const fnGrp = document.createElement('div');
                    fnGrp.className = 'form-group';
                    fnGrp.innerHTML = `
                        <label>Document File Name (Optional)</label>
                        <input type="text" id="crm-tpl-header-filename" class="form-control" value="${escapeHtml(header.defaultFilename || '')}" placeholder="e.g. statement.pdf" oninput="onTplFieldInput()">
                    `;
                    container.appendChild(fnGrp);
                }
            } else if (header.format === 'TEXT' && header.variables.length > 0) {
                header.variables.forEach((hVar, hIdx) => {
                    totalVars++;
                    const grp = document.createElement('div');
                    grp.className = 'form-group';
                    grp.innerHTML = `
                        <label>Header Parameter ({{${escapeHtml(hVar)}}})</label>
                        <input type="text" id="crm-tpl-header-var-${hIdx}" class="form-control" placeholder="Value for {{${escapeHtml(hVar)}}}" oninput="onTplFieldInput()">
                    `;
                    container.appendChild(grp);
                });
            }
        }

        // 2. Body inputs
        if (bodyVars.length > 0) {
            bodyVars.forEach((varName, idx) => {
                totalVars++;
                const grp = document.createElement('div');
                grp.className = 'form-group';

                let label = `Parameter ${idx + 1} ({{${escapeHtml(varName)}}})`;
                let defaultValue = '';
                let placeholder = `e.g. Value for {{${escapeHtml(varName)}}}`;

                const varLower = String(varName).toLowerCase();
                const bodyText = parsed.body.text || '';
                const isNameVar = varLower.includes('name') || (idx === 0 && /(?:hi|hello|dear|welcome)\s*\{\{/i.test(bodyText));
                const isOrderVar = varLower.includes('order') || varLower.includes('id') || varLower.includes('num');
                const isDateVar = varLower.includes('date') || varLower.includes('time');
                const isAmountVar = varLower.includes('price') || varLower.includes('amount') || varLower.includes('total');

                if (isNameVar) {
                    label = `Parameter ${idx + 1}: Customer Name ({{${escapeHtml(varName)}}})`;
                    defaultValue = currentContactName || '';
                    placeholder = 'e.g. ' + (currentContactName || 'Customer Name');
                } else if (isOrderVar) {
                    label = `Parameter ${idx + 1}: Order ID / Number ({{${escapeHtml(varName)}}})`;
                    placeholder = 'e.g. #10294';
                } else if (isDateVar) {
                    label = `Parameter ${idx + 1}: Date / Time ({{${escapeHtml(varName)}}})`;
                    placeholder = 'e.g. Tomorrow at 3:00 PM';
                } else if (isAmountVar) {
                    label = `Parameter ${idx + 1}: Amount / Price ({{${escapeHtml(varName)}}})`;
                    placeholder = 'e.g. $49.99';
                }

                grp.innerHTML = `
                    <label>${label}</label>
                    <input type="text" id="crm-tpl-body-var-${idx}" data-var-token="${escapeHtml(varName)}" class="form-control crm-tpl-body-input" value="${escapeHtml(defaultValue)}" placeholder="${placeholder}" oninput="onTplFieldInput()">
                `;
                container.appendChild(grp);
            });
        }

        // 3. Dynamic URL Button inputs
        if (buttonsWithParam.length > 0) {
            buttonsWithParam.forEach(btn => {
                totalVars++;
                const grp = document.createElement('div');
                grp.className = 'form-group';
                grp.innerHTML = `
                    <label>Button Link Parameter (${escapeHtml(btn.text || 'Action Button')})</label>
                    <input type="text" id="crm-tpl-btn-var-${btn.index}" class="form-control" placeholder="e.g. tracking-code-or-id" oninput="onTplFieldInput()">
                `;
                container.appendChild(grp);
            });
        }

        // Zero variables notice
        if (totalVars === 0) {
            const notice = document.createElement('div');
            notice.style.cssText = 'padding: 10px 14px; background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 8px; font-size: 13px; color: #6ee7b7; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;';
            notice.innerHTML = '<span>✓</span> <span><strong>No variables required.</strong> This message will be delivered exactly as approved by WhatsApp.</span>';
            container.appendChild(notice);
        }

        // Update count badge
        const badge = document.getElementById('crm-template-var-count-badge');
        if (badge) {
            badge.innerText = `${totalVars} variable${totalVars === 1 ? '' : 's'}`;
        }
    }

    function renderManualFallbackFields() {
        const container = document.getElementById('crm-template-dynamic-fields');
        container.innerHTML = `
            <div id="crm-manual-params-list">
                <div class="form-group">
                    <label>Parameter 1 (Customer Name)</label>
                    <input type="text" id="crm-tpl-body-var-0" class="form-control crm-tpl-body-input" value="${escapeHtml(currentContactName || '')}" placeholder="e.g. ${escapeHtml(currentContactName || 'Customer Name')}">
                </div>
                <div class="form-group">
                    <label>Parameter 2 (Optional)</label>
                    <input type="text" id="crm-tpl-body-var-1" class="form-control crm-tpl-body-input" placeholder="e.g. Order ID or shipment detail">
                </div>
            </div>
            <button type="button" class="btn btn-secondary" style="font-size: 11.5px; padding: 4px 10px; margin-bottom: 12px;" onclick="addManualParamField()">+ Add Another Parameter</button>
        `;
        const previewWrapper = document.getElementById('crm-template-preview-wrapper');
        if (previewWrapper) previewWrapper.style.display = 'none';
    }

    function addManualParamField() {
        const list = document.getElementById('crm-manual-params-list');
        if (!list) return;
        const currentCount = list.querySelectorAll('.crm-tpl-body-input').length;
        const nextIdx = currentCount;
        const grp = document.createElement('div');
        grp.className = 'form-group';
        grp.innerHTML = `
            <label>Parameter ${nextIdx + 1} (Optional)</label>
            <input type="text" id="crm-tpl-body-var-${nextIdx}" class="form-control crm-tpl-body-input" placeholder="e.g. Value for parameter ${nextIdx + 1}">
        `;
        list.appendChild(grp);
    }

    function onTplFieldInput() {
        if (window.currentSelectedTemplateParsed && window.currentSelectedTemplate) {
            updateTemplateLivePreview(window.currentSelectedTemplateParsed, window.currentSelectedTemplate);
        }
    }

    function updateTemplateLivePreview(parsed, tpl) {
        const previewWrapper = document.getElementById('crm-template-preview-wrapper');
        const previewEl = document.getElementById('crm-template-preview');
        if (!previewWrapper || !previewEl || !parsed) return;

        previewWrapper.style.display = 'block';

        let html = '';

        // Header preview
        if (parsed.header) {
            if (parsed.header.format === 'IMAGE') {
                const imgUrl = parsed.header.defaultMediaUrl;
                if (imgUrl && imgUrl.startsWith('http')) {
                    html += `<div class="template-preview-media" style="padding: 0; background: transparent; border: none;">
                        <img src="${escapeHtml(imgUrl)}" alt="Header Image" style="width: 100%; max-height: 160px; object-fit: cover; border-radius: 8px; display: block;">
                    </div>`;
                } else {
                    html += `<div class="template-preview-media">
                        <span>🖼️ Header Image: Using template's default approved image</span>
                    </div>`;
                }
            } else if (parsed.header.format === 'VIDEO' || parsed.header.format === 'DOCUMENT') {
                const mediaVal = document.getElementById('crm-tpl-header-media') ? document.getElementById('crm-tpl-header-media').value.trim() : (parsed.header.defaultMediaUrl || '');
                const icon = parsed.header.format === 'VIDEO' ? '🎥 Video Header' : '📄 Document Header';
                html += `<div class="template-preview-media">
                    <span>${icon}:</span>
                    <span>${mediaVal ? escapeHtml(mediaVal) : '<em style="opacity: 0.6;">(URL to be provided)</em>'}</span>
                </div>`;
            } else if (parsed.header.format === 'TEXT') {
                let hText = escapeHtml(parsed.header.text || '');
                (parsed.header.variables || []).forEach((hVar, hIdx) => {
                    const hInput = document.getElementById(`crm-tpl-header-var-${hIdx}`);
                    const val = hInput ? hInput.value.trim() : '';
                    const rep = val ? `<span class="tpl-var-highlight">${escapeHtml(val)}</span>` : `<span class="tpl-var-empty">{{${escapeHtml(hVar)}}}</span>`;
                    hText = hText.replace(new RegExp('\\{\\{' + hVar + '\\}\\}', 'g'), rep);
                });
                if (hText) {
                    html += `<div class="template-preview-header">${hText}</div>`;
                }
            }
        }

        // Body preview
        let bText = escapeHtml(parsed.body.text || '');
        (parsed.body.variables || []).forEach((bVar, bIdx) => {
            const inputEl = document.getElementById(`crm-tpl-body-var-${bIdx}`);
            const val = inputEl ? inputEl.value.trim() : '';
            const rep = val ? `<span class="tpl-var-highlight">${escapeHtml(val)}</span>` : `<span class="tpl-var-empty">{{${escapeHtml(bVar)}}}</span>`;
            bText = bText.replace(new RegExp('\\{\\{' + bVar + '\\}\\}', 'g'), rep);
        });
        html += `<div>${bText}</div>`;

        // Footer preview
        if (parsed.footer && parsed.footer.text) {
            html += `<div class="template-preview-footer">${escapeHtml(parsed.footer.text)}</div>`;
        }

        // Buttons preview
        if (parsed.buttons && parsed.buttons.length > 0) {
            html += '<div class="template-preview-buttons">';
            parsed.buttons.forEach(btn => {
                html += `<div class="template-preview-btn">🔗 ${escapeHtml(btn.text || 'Action Button')}</div>`;
            });
            html += '</div>';
        }

        previewEl.innerHTML = html;
    }

    function submitCrmTemplate() {
        const tplName = document.getElementById('crm-template-name').value.trim();
        const tplLang = document.getElementById('crm-template-lang').value.trim() || 'en';
        const statusEl = document.getElementById('crm-template-status');
        const btn = document.getElementById('btn-send-crm-tpl');

        if (!tplName) {
            alert('Please select or specify a template name.');
            return;
        }

        // Collect body placeholders
        const bodyInputs = document.querySelectorAll('.crm-tpl-body-input');
        const placeholders = [];
        bodyInputs.forEach(inp => {
            placeholders.push(inp.value.trim());
        });

        // Collect header data
        let headerData = null;
        if (window.currentSelectedTemplateParsed && window.currentSelectedTemplateParsed.header) {
            const header = window.currentSelectedTemplateParsed.header;
            const format = header.format;
            if (format === 'IMAGE') {
                // Auto-use default approved image from template
                if (header.defaultMediaUrl) {
                    headerData = {
                        type: 'IMAGE',
                        mediaUrl: header.defaultMediaUrl
                    };
                }
            } else if (format === 'VIDEO' || format === 'DOCUMENT') {
                const mediaInput = document.getElementById('crm-tpl-header-media');
                const mediaUrl = mediaInput ? mediaInput.value.trim() : (header.defaultMediaUrl || '');
                if (mediaUrl) {
                    headerData = {
                        type: format,
                        mediaUrl: mediaUrl
                    };
                    const docFn = document.getElementById('crm-tpl-header-filename') ? document.getElementById('crm-tpl-header-filename').value.trim() : (header.defaultFilename || '');
                    if (docFn) headerData.filename = docFn;
                }
            } else if (format === 'TEXT' && header.variables.length > 0) {
                const hVal = document.getElementById('crm-tpl-header-var-0') ? document.getElementById('crm-tpl-header-var-0').value.trim() : '';
                if (hVal) {
                    headerData = {
                        type: 'TEXT',
                        placeholder: hVal
                    };
                }
            }
        }

        // Collect button parameters
        const buttonParams = [];
        if (window.currentSelectedTemplateParsed && window.currentSelectedTemplateParsed.buttons) {
            window.currentSelectedTemplateParsed.buttons.forEach(b => {
                if (b.hasParam) {
                    const btnInput = document.getElementById(`crm-tpl-btn-var-${b.index}`);
                    const bVal = btnInput ? btnInput.value.trim() : '';
                    if (bVal) {
                        buttonParams.push({
                            type: 'URL',
                            parameter: bVal
                        });
                    }
                }
            });
        }

        // Build templateData object
        const templateData = {};
        if (headerData) templateData.header = headerData;
        if (placeholders.length > 0) templateData.body = { placeholders: placeholders };
        if (buttonParams.length > 0) templateData.buttons = buttonParams;

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
        formData.append('params', JSON.stringify(placeholders));
        formData.append('template_data', JSON.stringify(templateData));
        // Backward compatibility
        if (placeholders[0]) formData.append('param1', placeholders[0]);
        if (placeholders[1]) formData.append('param2', placeholders[1]);
        if (placeholders[2]) formData.append('param3', placeholders[2]);
        formData.append('member_id', currentMemberId);
        formData.append('domain', currentDomain);

        window.keenDebugStore.logReq('send_template', {
            phone: currentPhone,
            templateName: tplName,
            language: tplLang,
            placeholders: placeholders,
            templateData: templateData,
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

        const currentDomain = (window.keenDebugStore && window.keenDebugStore.crmInfo && window.keenDebugStore.crmInfo.domain) ? window.keenDebugStore.crmInfo.domain : '';
        const aiUrl = 'placement_tab.php?action=get_ai_summary&phone=' + encodeURIComponent(currentPhone) + '&member_id=' + encodeURIComponent(currentMemberId) + '&domain=' + encodeURIComponent(currentDomain) + '&_t=' + Date.now();
        window.keenDebugStore.logReq('get_ai_summary', { url: aiUrl, phone: currentPhone, memberId: currentMemberId, domain: currentDomain });

        fetch(aiUrl, {
            cache: 'no-store',
            headers: {
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache'
            }
        })
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
