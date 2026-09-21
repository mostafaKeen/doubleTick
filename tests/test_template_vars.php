<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';
spl_autoload_register(function ($class) {
    $prefix = 'DoubleTickB24\\';
    $baseDir = dirname(__DIR__) . '/src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) require_once $file;
});

use DoubleTickB24\DoubleTick\DoubleTickClient;
use DoubleTickB24\Bitrix24\BitrixClient;
use DoubleTickB24\Core\Database;

echo "===================================================\n";
echo " Testing WhatsApp Template Variables & Payload...\n";
echo "===================================================\n\n";

// Mock DoubleTick Client extending DoubleTickClient to inspect generated POST payloads
class TestInspectableDoubleTickClient extends DoubleTickClient {
    public ?string $lastEndpoint = null;
    public ?array $lastPayload = null;

    public function post(string $endpoint, array $body = []): array {
        $this->lastEndpoint = $endpoint;
        $this->lastPayload = $body;
        return [
            'status' => 'SUCCESS',
            'messageId' => 'mid_mock_test_' . uniqid(),
            'messages' => [['messageId' => 'mid_mock_test_123', 'status' => 'SENT']]
        ];
    }
}

$dt = new TestInspectableDoubleTickClient('mock_key', '919000000000');

// Test Case 1: Template with 0 variables
echo "[1/5] Testing Template with 0 variables...\n";
$dt->sendTemplateMessage('201129274930', 'no_var_announcement', 'en', []);
$sentMsg = $dt->lastPayload['messages'][0];
assert($sentMsg['content']['templateName'] === 'no_var_announcement', "Template name mismatch");
assert(!isset($sentMsg['content']['templateData']), "0-var template should not have templateData");
echo "  ✓ 0-variable template payload is clean (no dummy params)!\n\n";

// Test Case 2: Template with 1 variable
echo "[2/5] Testing Template with 1 variable...\n";
$dt->sendTemplateMessage('201129274930', 'single_var_greeting', 'en', ['Monica Ganwani']);
$sentMsg = $dt->lastPayload['messages'][0];
assert(isset($sentMsg['content']['templateData']['body']['placeholders']), "Missing body placeholders");
assert(count($sentMsg['content']['templateData']['body']['placeholders']) === 1, "Expected exactly 1 placeholder");
assert($sentMsg['content']['templateData']['body']['placeholders'][0] === 'Monica Ganwani', "Placeholder value mismatch");
echo "  ✓ 1-variable template correctly formatted with single placeholder!\n\n";

// Test Case 3: Template with 4 variables (order update)
echo "[3/5] Testing Template with 4 variables...\n";
$params = ['Monica Ganwani', '#ORD-8823', 'Tomorrow at 4 PM', 'Delivered'];
$dt->sendTemplateMessage('201129274930', 'order_status_v4', 'en', $params);
$sentMsg = $dt->lastPayload['messages'][0];
assert(count($sentMsg['content']['templateData']['body']['placeholders']) === 4, "Expected exactly 4 placeholders");
assert($sentMsg['content']['templateData']['body']['placeholders'] === $params, "4 parameters mismatch");
echo "  ✓ 4-variable template correctly formatted without truncating to 2 or 3!\n\n";

// Test Case 4: Template with Image Header and Dynamic URL Button
echo "[4/5] Testing Template with Header Media & Dynamic Button...\n";
$structuredTemplateData = [
    'header' => [
        'type' => 'IMAGE',
        'mediaUrl' => 'https://example.com/promo.png'
    ],
    'body' => [
        'placeholders' => ['Monica Ganwani', '50% Summer Discount']
    ],
    'buttons' => [
        [
            'type' => 'URL',
            'parameter' => 'summer-promo-2026'
        ]
    ]
];
$dt->sendTemplateMessage('201129274930', 'promo_with_header_and_btn', 'en', $structuredTemplateData);
$sentMsg = $dt->lastPayload['messages'][0];
assert($sentMsg['content']['templateData']['header']['type'] === 'IMAGE', "Header type mismatch");
assert($sentMsg['content']['templateData']['header']['mediaUrl'] === 'https://example.com/promo.png', "Media URL mismatch");
assert(count($sentMsg['content']['templateData']['body']['placeholders']) === 2, "Body placeholders mismatch");
assert($sentMsg['content']['templateData']['buttons'][0]['parameter'] === 'summer-promo-2026', "Button param mismatch");
echo "  ✓ Complex template with Image Header, Body Placeholders, and Button parameter verified!\n\n";

// Test Case 5: Placement tab backend endpoint simulated POST handling
echo "[5/5] Testing placement_tab backend endpoint simulation...\n";
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'action' => 'send_template',
    'phone' => '201129274930',
    'template_name' => 'order_delivery_notice',
    'language' => 'en',
    'params' => json_encode(['Customer A', 'Item 101', 'Arrived']),
    'template_data' => json_encode([
        'body' => ['placeholders' => ['Customer A', 'Item 101', 'Arrived']]
    ]),
    'member_id' => 'test_member_123',
    'domain' => 'mock.bitrix24.com'
];

$phone = trim((string)($_POST['phone'] ?? ''));
$templateName = trim((string)($_POST['template_name'] ?? ''));
$language = trim((string)($_POST['language'] ?? 'en'));

$templateData = [];
if (!empty($_POST['template_data'])) {
    $decoded = json_decode((string)$_POST['template_data'], true);
    if (is_array($decoded)) $templateData = $decoded;
}
$paramsParsed = [];
if (!empty($_POST['params'])) {
    $decodedParams = json_decode((string)$_POST['params'], true);
    if (is_array($decodedParams)) $paramsParsed = array_values(array_map('strval', $decodedParams));
}
assert(count($paramsParsed) === 3, "Failed to parse params array");
assert($templateData['body']['placeholders'] === ['Customer A', 'Item 101', 'Arrived'], "Failed to parse template_data");

$dt->sendTemplateMessage($phone, $templateName, $language, $templateData);
$sentMsg = $dt->lastPayload['messages'][0];
assert(count($sentMsg['content']['templateData']['body']['placeholders']) === 3, "Backend simulation failed to format 3 params");

echo "  ✓ Simulated placement_tab backend POST correctly received and passed dynamic variables!\n\n";

echo "===================================================\n";
echo " ALL TEMPLATE VARIABLE TESTS PASSED!\n";
echo "===================================================\n";
