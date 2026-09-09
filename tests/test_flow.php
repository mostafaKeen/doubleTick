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
use DoubleTickB24\Bitrix24\ImConnectorService;
use DoubleTickB24\Core\Database;
use DoubleTickB24\Core\Logger;
use DoubleTickB24\DoubleTick\DoubleTickClient;
use DoubleTickB24\DoubleTick\WebhookProcessor;

echo "===================================================\n";
echo " Running DoubleTick & Bitrix24 App Verification...\n";
echo "===================================================\n\n";

// 1. Test Database Initialization & Migrations
echo "[1/4] Testing Database & Migrations...\n";
try {
    $db = Database::getInstance();
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    echo "  -> Created tables: " . implode(', ', $tables) . "\n";
    assert(in_array('b24_portals', $tables), "Missing b24_portals table");
    assert(in_array('message_mappings', $tables), "Missing message_mappings table");
    assert(in_array('lead_attributions', $tables), "Missing lead_attributions table");
    assert(in_array('webhook_logs', $tables), "Missing webhook_logs table");
    echo "  ✓ Database initialization passed!\n\n";
} catch (\Throwable $e) {
    echo "  ✗ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

// 2. Test DoubleTick Client Payload Formatting
echo "[2/4] Testing DoubleTick Client payload construction...\n";
try {
    $dt = new DoubleTickClient('test_api_key_12345', '919000000000');
    assert($dt->normalizePhone('+91 (987) 654-3210') === '919876543210', "Phone normalization failed");
    echo "  ✓ Phone normalization (+91 (987) 654-3210 -> 919876543210) passed!\n";
    echo "  ✓ DoubleTick Client initialization passed!\n\n";
} catch (\Throwable $e) {
    echo "  ✗ DoubleTick Client error: " . $e->getMessage() . "\n";
    exit(1);
}

// 3. Test Mock Portal Creation & Webhook Handling
echo "[3/4] Testing Mock Bitrix24 Portal & Webhook Engine...\n";
try {
    $stmt = $db->prepare("
        INSERT INTO b24_portals (
            member_id, domain, access_token, refresh_token, expires_at,
            client_endpoint, server_endpoint, dt_api_key, dt_waba_number, open_line_id, is_active
        ) VALUES (
            'test_member_123', 'mock.bitrix24.com', 'test_access_token', 'test_refresh_token',
            :expires_at, 'https://mock.bitrix24.com/rest/', 'https://oauth.bitrix.info/rest/',
            'test_dt_key', '919000000000', 1, 1
        )
        ON CONFLICT(member_id) DO UPDATE SET is_active = 1
    ");
    $stmt->execute(['expires_at' => time() + 3600]);

    $b24 = BitrixClient::getByMemberId('test_member_123');
    assert($b24 !== null, "Could not load active portal from database");
    echo "  ✓ Loaded Portal: {$b24->getPortalData()['domain']}\n";

    // Test WebhookProcessor for Message Status Update
    $processor = new WebhookProcessor($b24);

    // Insert a dummy message mapping
    $db->exec("
        INSERT INTO message_mappings (
            portal_id, b24_chat_id, b24_message_id, dt_message_id, customer_phone, direction, status
        ) VALUES (
            {$b24->getPortalId()}, 10, 20, 'test_msg_abc123', '919876543210', 'OUTBOUND', 'sent'
        )
    ");

    // Process status update webhook
    $statusUpdatePayload = [
        'event' => 'MESSAGE_STATUS_UPDATE',
        'messageId' => 'test_msg_abc123',
        'status' => 'READ',
    ];
    $resStatus = $processor->process($statusUpdatePayload);
    echo "  ✓ Status webhook response: " . json_encode($resStatus) . "\n";

    $updatedStatus = $db->query("SELECT status FROM message_mappings WHERE dt_message_id = 'test_msg_abc123'")->fetchColumn();
    assert($updatedStatus === 'read', "Status in DB did not update to read");
    echo "  ✓ Verified message status updated to 'read' in DB!\n\n";

} catch (\Throwable $e) {
    echo "  ✗ Webhook processor error: " . $e->getMessage() . "\n";
    exit(1);
}

// 4. Test Lead Attribution Storage
echo "[4/4] Testing Meta CTWA Ad Lead Attribution Storage...\n";
try {
    $db->prepare("
        INSERT INTO lead_attributions (
            portal_id, lead_id, customer_phone, customer_name, is_ctwa,
            source_url, source_id, headline, ctwa_clid, created_at
        ) VALUES (
            {$b24->getPortalId()}, 55, '919876543210', 'Ad Prospect', 1,
            'https://fb.com/ad/999', 'camp_spring_sale', 'Get 50% Off Today', 'CTWA_XYZ_789', datetime('now')
        )
    ")->execute();

    $attr = $db->query("SELECT * FROM lead_attributions WHERE customer_phone = '919876543210'")->fetch();
    assert($attr['is_ctwa'] == 1, "is_ctwa flag incorrect");
    assert($attr['ctwa_clid'] === 'CTWA_XYZ_789', "CTWA Click ID incorrect");
    echo "  ✓ Verified CTWA Ad attribution stored: Ad ID={$attr['source_id']}, Headline='{$attr['headline']}', ClickID={$attr['ctwa_clid']}\n";
    echo "  ✓ Lead attribution passed!\n\n";
} catch (\Throwable $e) {
    echo "  ✗ Lead attribution error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "===================================================\n";
echo " ALL TESTS PASSED! Local PHP App is ready to run. \n";
echo "===================================================\n";
