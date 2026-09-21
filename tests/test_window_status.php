<?php
/**
 * Test 24-Hour WhatsApp Session Window Calculation & Message Content Extraction
 */

require_once __DIR__ . '/../config/config.php';
spl_autoload_register(function ($class) {
    $prefix = 'DoubleTickB24\\';
    $baseDir = __DIR__ . '/../src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) require_once $file;
});

use DoubleTickB24\Core\Database;
use DoubleTickB24\DoubleTick\DoubleTickClient;

echo "=== RUNNING 24-HOUR WINDOW & HISTORY TESTS ===\n\n";

$passCount = 0;
$failCount = 0;

function assertTest(string $desc, bool $condition) {
    global $passCount, $failCount;
    if ($condition) {
        echo "✅ PASS: {$desc}\n";
        $passCount++;
    } else {
        echo "❌ FAIL: {$desc}\n";
        $failCount++;
    }
}

$testPhone = '999999999999';
$db = Database::getInstance();

// Helper to query placement_tab via CLI sub-process
function queryPlacementHistory(string $phone): array {
    $cmd = 'php "' . __DIR__ . '/run_case.php" ' . escapeshellarg($phone);
    $out = shell_exec($cmd);
    $start = strpos($out, '{"success"');
    if ($start !== false) {
        $jsonStr = substr($out, $start);
        return json_decode($jsonStr, true) ?: [];
    }
    return [];
}

// Test 1: Active Inbound Message within 24 Hours
$twoHoursAgo = date('Y-m-d H:i:s', time() - 7200);
$db->prepare("DELETE FROM message_mappings WHERE customer_phone = :p")->execute(['p' => $testPhone]);
$db->prepare("
    INSERT INTO message_mappings (portal_id, b24_chat_id, b24_message_id, dt_message_id, customer_phone, direction, message_type, status, raw_data, created_at)
    VALUES (1, 0, 0, 'test_active_msg_1', :p, 'INBOUND', 'text', 'delivered', :rd, :ca)
")->execute([
    'p' => $testPhone,
    'rd' => json_encode(['text' => 'Hello from customer 2 hours ago']),
    'ca' => $twoHoursAgo,
]);

$res1 = queryPlacementHistory($testPhone);
assertTest("Inbound message within 2 hours results in isOpen = true", ($res1['window_status']['isOpen'] === true));
assertTest("Formatted remaining shows ~21h or 22h", (strpos($res1['window_status']['formattedRemaining'] ?? '', '21h') !== false || strpos($res1['window_status']['formattedRemaining'] ?? '', '22h') !== false));
assertTest("Messages array includes the inbound message", (count($res1['messages'] ?? []) >= 1 && $res1['messages'][0]['text'] === 'Hello from customer 2 hours ago'));

// Test 2: Inbound Message > 24 Hours Ago (Expired)
$twentyFiveHoursAgo = date('Y-m-d H:i:s', time() - 90000);
$db->prepare("UPDATE message_mappings SET created_at = :ca WHERE customer_phone = :p")->execute([
    'ca' => $twentyFiveHoursAgo,
    'p' => $testPhone,
]);

$res2 = queryPlacementHistory($testPhone);
assertTest("Inbound message > 24h ago results in isOpen = false", (($res2['window_status']['isOpen'] ?? null) === false));
assertTest("Expired session has formattedRemaining = null", (($res2['window_status']['formattedRemaining'] ?? null) === null));

// Cleanup test phone
$db->prepare("DELETE FROM message_mappings WHERE customer_phone = :p")->execute(['p' => $testPhone]);

// Test 3: DoubleTickClient::getChatWindowStatus returns null on error (does not falsely report closed)
$dt = new DoubleTickClient('test_api_key', '919000000000');
$winStatus = $dt->getChatWindowStatus('1234567890');
assertTest("DoubleTickClient::getChatWindowStatus returns isOpen === null on failure, not false", (($winStatus['isOpen'] ?? null) === null));

// Test 4: Lightbox HTML Data URI check
$tabHtml = file_get_contents(__DIR__ . '/../public/placement_tab.php');
assertTest("Lightbox image src is a valid data URI, not empty", (strpos($tabHtml, '<img id="lightbox-img" class="lightbox-img" src="data:image/svg+xml') !== false));
assertTest("Lightbox download href is safe anchor #", (strpos($tabHtml, '<a id="lightbox-download" class="btn btn-secondary" href="#"') !== false));

echo "\nSummary: {$passCount} Passed, {$failCount} Failed.\n";
if ($failCount > 0) exit(1);
