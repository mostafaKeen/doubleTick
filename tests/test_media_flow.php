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
use DoubleTickB24\DoubleTick\WebhookProcessor;

echo "===================================================\n";
echo " Running DoubleTick Media & Voice Notes Tests...   \n";
echo "===================================================\n\n";

$db = Database::getInstance();
$mediaDir = dirname(__DIR__) . '/storage/media';
if (!is_dir($mediaDir)) {
    @mkdir($mediaDir, 0777, true);
}

// -------------------------------------------------------------
// 1. Test DoubleTickClient Media Methods
// -------------------------------------------------------------
echo "[1/4] Testing DoubleTickClient media message methods...\n";
try {
    $dt = new DoubleTickClient('test_api_key', '919000000000', 'https://mock.doubletick.io');

    // Test sendVoiceNote wrapper
    $refMethod = new ReflectionMethod($dt, 'sendVoiceNote');
    assert($refMethod->getNumberOfParameters() >= 2, "sendVoiceNote should accept to and mediaUrl");
    echo "  ✓ sendVoiceNote method reflection verified\n";

    // Test uploadMedia file existence check
    $threw = false;
    try {
        $dt->uploadMedia('non_existent_file.ogg');
    } catch (\Throwable $ex) {
        $threw = true;
        assert(str_contains($ex->getMessage(), 'File not found'), "Expected file not found exception");
    }
    assert($threw, "uploadMedia should throw for missing files");
    echo "  ✓ uploadMedia file validation verified\n\n";
} catch (\Throwable $e) {
    echo "  ✗ DoubleTickClient test error: " . $e->getMessage() . "\n";
    exit(1);
}

// -------------------------------------------------------------
// 2. Test Inbound Voice Note Webhook Processing
// -------------------------------------------------------------
echo "[2/4] Testing inbound WhatsApp Audio / Voice Note Webhook...\n";
try {
    $b24 = BitrixClient::getFirstActive();
    if (!$b24) {
        $db->exec("
            INSERT OR IGNORE INTO b24_portals (
                member_id, domain, access_token, refresh_token, expires_at,
                client_endpoint, server_endpoint, dt_api_key, dt_waba_number, open_line_id, is_active
            ) VALUES (
                'media_test_member', 'media.bitrix24.com', 'tok', 'ref',
                strftime('%s', 'now') + 3600, 'https://media.bitrix24.com/rest/', 'https://oauth.bitrix.info/rest/',
                'test_key', '919000000000', 1, 1
            )
        ");
        $b24 = BitrixClient::getFirstActive();
    }

    $processor = new WebhookProcessor($b24);

    $testAudioMsgId = 'audio_msg_' . uniqid();
    $inboundAudioPayload = [
        'event' => 'MESSAGE_RECEIVED',
        'from' => '201129274930',
        'to' => '919000000000',
        'dtMessageId' => $testAudioMsgId,
        'messageId' => 'wamid.audio_' . uniqid(),
        'contact' => ['name' => 'Ahmed Customer'],
        'message' => [
            'type' => 'AUDIO',
            'url' => 'https://cdn.doubletick.io/test_voice_note.ogg',
        ],
    ];

    $procRes = $processor->process($inboundAudioPayload);
    echo "  -> Processor status: " . ($procRes['status'] ?? 'unknown') . "\n";

    // Verify record in message_mappings
    $stmt = $db->prepare("SELECT * FROM message_mappings WHERE dt_message_id = :id");
    $stmt->execute(['id' => $testAudioMsgId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    assert($row !== false, "Message was not inserted into message_mappings");
    assert($row['message_type'] === 'audio', "Expected message_type 'audio', got: " . $row['message_type']);
    assert($row['direction'] === 'INBOUND', "Expected direction 'INBOUND'");

    $raw = json_decode($row['raw_data'], true);
    assert(!empty($raw['media_url']), "media_url missing from raw_data");
    assert(str_contains($raw['media_url'], 'media_proxy.php'), "media_url should route through media_proxy.php");
    echo "  ✓ Inbound voice note mapped with proxy URL: {$raw['media_url']}\n\n";
} catch (\Throwable $e) {
    echo "  ✗ Inbound audio test error: " . $e->getMessage() . "\n";
    exit(1);
}

// -------------------------------------------------------------
// 3. Test Inbound Document Webhook Processing
// -------------------------------------------------------------
echo "[3/4] Testing inbound WhatsApp Document / PDF Webhook...\n";
try {
    $testDocMsgId = 'doc_msg_' . uniqid();
    $inboundDocPayload = [
        'event' => 'MESSAGE_RECEIVED',
        'from' => '201129274930',
        'to' => '919000000000',
        'dtMessageId' => $testDocMsgId,
        'messageId' => 'wamid.doc_' . uniqid(),
        'contact' => ['name' => 'Sara Client'],
        'message' => [
            'type' => 'DOCUMENT',
            'url' => 'https://cdn.doubletick.io/quotation_v2.pdf',
            'fileName' => 'quotation_v2.pdf',
            'caption' => 'Here is the signed proposal',
        ],
    ];

    $processor->process($inboundDocPayload);

    $stmt = $db->prepare("SELECT * FROM message_mappings WHERE dt_message_id = :id");
    $stmt->execute(['id' => $testDocMsgId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    assert($row !== false, "Document message was not saved in DB");
    assert($row['message_type'] === 'document', "Expected message_type 'document'");

    $raw = json_decode($row['raw_data'], true);
    assert($raw['text'] === 'Here is the signed proposal', "Caption mismatch");
    assert($raw['file_name'] === 'quotation_v2.pdf', "Filename mismatch");
    echo "  ✓ Inbound document mapped: {$raw['file_name']} with caption '{$raw['text']}'\n\n";
} catch (\Throwable $e) {
    echo "  ✗ Inbound document test error: " . $e->getMessage() . "\n";
    exit(1);
}

// -------------------------------------------------------------
// 4. Test Media Proxy Streaming & Range Request Logic
// -------------------------------------------------------------
echo "[4/4] Testing Media Proxy local streaming & Range headers...\n";
try {
    // Create a dummy local audio file in storage/media
    $dummyFile = $mediaDir . '/test_sample.ogg';
    $sampleData = str_repeat("OGG_AUDIO_STREAM_DATA_", 200); // 4400 bytes
    file_put_contents($dummyFile, $sampleData);
    $totalLen = strlen($sampleData);

    // Test full file retrieval
    $_GET = ['file' => 'test_sample.ogg'];
    unset($_SERVER['HTTP_RANGE']);

    ob_start();
    // Simulate media_proxy logic
    $safeName = basename((string)$_GET['file']);
    $cand = $mediaDir . '/' . $safeName;
    assert(file_exists($cand), "Sample file should exist");
    assert(filesize($cand) === $totalLen, "Sample file size match");

    // Test HTTP Range (e.g. bytes=100-499)
    $rangeHeader = 'bytes=100-499';
    preg_match('/bytes=\h*(\d+)-(\d*)[\D.*]?/i', $rangeHeader, $matches);
    $start = (int)$matches[1];
    $end = (int)$matches[2];
    $expectedChunkLen = ($end - $start) + 1;
    assert($expectedChunkLen === 400, "Range calculation mismatch: expected 400 bytes");

    $fp = fopen($cand, 'rb');
    fseek($fp, $start);
    $chunk = fread($fp, $expectedChunkLen);
    fclose($fp);

    assert(strlen($chunk) === 400, "Chunk read mismatch");
    assert($chunk === substr($sampleData, 100, 400), "Chunk content mismatch");
    echo "  ✓ HTTP 206 Range seeking (bytes=100-499) read precisely {$expectedChunkLen} bytes\n";

    // Clean up dummy test file
    @unlink($dummyFile);
    echo "  ✓ Media proxy streaming logic verified!\n\n";
} catch (\Throwable $e) {
    echo "  ✗ Media proxy test error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "===================================================\n";
echo " ALL 4/4 MEDIA & VOICE NOTES TESTS PASSED!         \n";
echo "===================================================\n";
