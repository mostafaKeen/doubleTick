<?php
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
use DoubleTickB24\Bitrix24\BitrixClient;
use DoubleTickB24\DoubleTick\DoubleTickClient;
use DoubleTickB24\Bitrix24\ImConnectorService;

echo "=== RUNNING OUTBOUND DELIVERY & SESSION RETRY TESTS ===\n\n";

$passCount = 0;
$failCount = 0;

function assertTest($cond, $name) {
    global $passCount, $failCount;
    if ($cond) {
        echo "✅ PASS: $name\n";
        $passCount++;
    } else {
        echo "❌ FAIL: $name\n";
        $failCount++;
    }
}

// 1. Test phone normalization with plus
$dt = new DoubleTickClient('dummy_key', '971501848828');
$clean = $dt->normalizePhone('+201129274930', false);
assertTest($clean === '201129274930', 'normalizePhone without plus strips +');
$withPlus = $dt->normalizePhone('201129274930', true);
assertTest($withPlus === '+201129274930', 'normalizePhone with plus adds +');

// 2. Test ImConnectorService failure handling with mock
$b24 = BitrixClient::getFirstActive();
if ($b24) {
    $imConnector = new ImConnectorService($b24);
    
    // Simulate DoubleTickClient returning 422 error
    $mockDt = new class('dummy', '971501848828') extends DoubleTickClient {
        public function sendTextMessage(string $to, string $text, ?string $from = null): array {
            return [
                'success' => false,
                'status_code' => 422,
                'error' => 'Chat window is closed. To send message to closed window please send template message',
            ];
        }
    };

    // Simulate OnImConnectorMessageAdd event
    $eventData = [
        'event' => 'ONIMCONNECTORMESSAGEADD',
        'data' => [
            'CONNECTOR' => 'doubletick_whatsapp',
            'LINE' => 1,
            'MESSAGES' => [
                [
                    'im' => [
                        'chat_id' => 9999,
                        'message_id' => 8888,
                    ],
                    'message' => [
                        'user_id' => 1,
                        'text' => 'Hello test fail',
                    ],
                    'chat' => [
                        'id' => '201129274930',
                    ],
                ]
            ]
        ]
    ];

    $imConnector->processOutboundEvent($eventData, $mockDt);

    // Verify database recorded status 'failed'
    $db = Database::getInstance();
    $stmt = $db->query("SELECT * FROM message_mappings WHERE customer_phone = '201129274930' AND direction = 'OUTBOUND' ORDER BY id DESC LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    assertTest(!empty($row), 'Message mapping was created for outbound attempt');
    assertTest(($row['status'] ?? '') === 'failed', 'Message mapping status is correctly marked as failed');
    assertTest(str_contains($row['raw_data'] ?? '', 'Chat window is closed'), 'Raw data includes window closed error detail');
}

echo "\nSummary: $passCount Passed, $failCount Failed.\n";
if ($failCount > 0) exit(1);
