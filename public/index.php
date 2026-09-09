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
use DoubleTickB24\Bitrix24\CrmLeadService;
use DoubleTickB24\Core\Database;
use DoubleTickB24\DoubleTick\DoubleTickClient;

$config = require dirname(__DIR__) . '/config/config.php';
$db = Database::getInstance();

$b24 = BitrixClient::getFirstActive();
$portalData = $b24 ? $b24->getPortalData() : [];

$notice = '';
$error = '';

// Handle quick actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_credentials') {
        $key = trim($_POST['dt_api_key'] ?? '');
        $waba = trim($_POST['dt_waba'] ?? '');

        if ($b24) {
            $stmt = $db->prepare("UPDATE b24_portals SET dt_api_key = :k, dt_waba_number = :w WHERE id = :id");
            $stmt->execute(['k' => $key, 'w' => $waba, 'id' => $b24->getPortalId()]);
            $notice = 'DoubleTick credentials updated successfully!';
            $b24 = BitrixClient::getFirstActive();
            $portalData = $b24 ? $b24->getPortalData() : [];
        } else {
            $error = 'No active Bitrix24 portal installed yet. Please install the app in Bitrix24 first.';
        }
    } elseif ($action === 'send_test_message') {
        $to = trim($_POST['test_phone'] ?? '');
        $text = trim($_POST['test_text'] ?? 'Test message from DoubleTick Bitrix24 App!');
        $apiKey = $b24 ? ($b24->getDoubleTickApiKey() ?: $config['doubletick']['api_key']) : $config['doubletick']['api_key'];
        $waba = $b24 ? ($b24->getDoubleTickWaba() ?: $config['doubletick']['default_waba']) : $config['doubletick']['default_waba'];

        if (!$apiKey) {
            $error = 'DoubleTick API key is required.';
        } elseif (!$to) {
            $error = 'Recipient phone number is required.';
        } else {
            try {
                $dt = new DoubleTickClient($apiKey, $waba, $config['doubletick']['api_url']);
                $res = $dt->sendTextMessage($to, $text);
                $notice = "Test message sent! DoubleTick Response ID: " . ($res['messageId'] ?? $res['dtMessageId'] ?? 'OK');
            } catch (\Throwable $e) {
                $error = "Failed to send message: " . $e->getMessage();
            }
        }
    } elseif ($action === 'simulate_ad_lead') {
        if ($b24) {
            $crmService = new CrmLeadService($b24);
            $mockPhone = '1555' . rand(1000000, 9999999);
            $mockReferral = [
                'source_url' => 'https://facebook.com/ads/123456789',
                'source_id' => 'camp_' . rand(100, 999),
                'headline' => 'Special 30% Promo Campaign',
                'ctwa_clid' => 'CTWA_' . bin2hex(random_bytes(8)),
            ];
            $leadRes = $crmService->createOrUpdateFromWhatsApp($mockPhone, 'Test Ad Lead', true, $mockReferral, 'Hi, I saw your Facebook ad and want to learn more!');
            $notice = "Simulated Meta Ad Lead created in Bitrix24! Lead ID: #" . $leadRes['lead_id'];
        } else {
            $error = 'No active Bitrix24 portal found.';
        }
    }
}

// Fetch stats
$totalMessages = (int)$db->query("SELECT COUNT(*) FROM message_mappings")->fetchColumn();
$inboundMessages = (int)$db->query("SELECT COUNT(*) FROM message_mappings WHERE direction = 'INBOUND'")->fetchColumn();
$outboundMessages = (int)$db->query("SELECT COUNT(*) FROM message_mappings WHERE direction = 'OUTBOUND'")->fetchColumn();
$totalLeads = (int)$db->query("SELECT COUNT(*) FROM lead_attributions")->fetchColumn();
$ctwaLeads = (int)$db->query("SELECT COUNT(*) FROM lead_attributions WHERE is_ctwa = 1")->fetchColumn();

// Recent messages
$recentMessages = $db->query("SELECT * FROM message_mappings ORDER BY id DESC LIMIT 8")->fetchAll();

// Recent webhooks
$recentWebhooks = $db->query("SELECT * FROM webhook_logs ORDER BY id DESC LIMIT 8")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DoubleTick WhatsApp Dashboard for Bitrix24</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="container">
    <header class="header">
        <div class="brand">
            <div class="brand-icon">
                <img src="assets/icon.svg" width="32" height="32" alt="DoubleTick">
            </div>
            <div>
                <h1>DoubleTick WhatsApp</h1>
                <p>Enterprise Bitrix24 CRM Integration</p>
            </div>
        </div>
        <div>
            <?php if ($b24): ?>
                <span class="badge badge-success">● Connected: <?= htmlspecialchars($portalData['domain']) ?></span>
            <?php else: ?>
                <span class="badge badge-warning">● Awaiting Installation</span>
            <?php endif; ?>
        </div>
    </header>

    <?php if ($notice): ?>
        <div class="badge badge-success" style="width: 100%; margin-bottom: 20px; padding: 12px; font-size: 14px;">
            ✓ <?= htmlspecialchars($notice) ?>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="badge badge-warning" style="width: 100%; margin-bottom: 20px; padding: 12px; font-size: 14px; background: rgba(239, 68, 68, 0.15); color: #ef4444; border-color: #ef4444;">
            ✕ <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- Metrics Row -->
    <div class="grid">
        <div class="card">
            <h2>💬 Synced Messages</h2>
            <div class="stat-value"><?= number_format($totalMessages) ?></div>
            <div class="stat-label">
                <?= $inboundMessages ?> Inbound • <?= $outboundMessages ?> Outbound
            </div>
        </div>
        <div class="card">
            <h2>🎯 Meta Ad Attributions</h2>
            <div class="stat-value"><?= number_format($ctwaLeads) ?></div>
            <div class="stat-label">
                <?= $totalLeads ?> Total WhatsApp Leads Created
            </div>
        </div>
        <div class="card">
            <h2>⚡ Open Line Status</h2>
            <div class="stat-value">
                <?= !empty($portalData['open_line_id']) ? ('Line #' . $portalData['open_line_id']) : 'Active' ?>
            </div>
            <div class="stat-label">
                WABA: <?= htmlspecialchars($portalData['dt_waba_number'] ?? 'Not set') ?>
            </div>
        </div>
    </div>

    <!-- Configuration & Testing Grid -->
    <div class="grid">
        <div class="card">
            <h2>⚙️ DoubleTick Credentials</h2>
            <form method="POST">
                <input type="hidden" name="action" value="save_credentials">
                <div class="form-group">
                    <label>Public API Key</label>
                    <input type="password" name="dt_api_key" class="form-control" 
                           value="<?= htmlspecialchars($portalData['dt_api_key'] ?? '') ?>" 
                           placeholder="Enter DoubleTick API key">
                </div>
                <div class="form-group">
                    <label>Default WABA Number</label>
                    <input type="text" name="dt_waba" class="form-control" 
                           value="<?= htmlspecialchars($portalData['dt_waba_number'] ?? '') ?>" 
                           placeholder="e.g. 919876543210 (without +)">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Save Credentials</button>
            </form>
        </div>

        <div class="card">
            <h2>🧪 Diagnostic Quick Tests</h2>
            <form method="POST" style="margin-bottom: 16px;">
                <input type="hidden" name="action" value="send_test_message">
                <div class="form-group">
                    <label>Test WhatsApp Recipient</label>
                    <input type="text" name="test_phone" class="form-control" placeholder="Phone with country code (e.g. 919876543210)">
                </div>
                <div class="form-group">
                    <label>Message</label>
                    <input type="text" name="test_text" class="form-control" value="Hello! This is a test message from Bitrix24 DoubleTick App.">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Send Test WhatsApp</button>
            </form>

            <form method="POST">
                <input type="hidden" name="action" value="simulate_ad_lead">
                <button type="submit" class="btn" style="width: 100%; background: #334155; color: #fff;">
                    🎯 Simulate Meta CTWA Ad Lead
                </button>
            </form>
        </div>
    </div>

    <!-- Live Sync Tables -->
    <div class="card" style="margin-bottom: 24px;">
        <h2>📋 Recent Message Sync Log</h2>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Direction</th>
                        <th>Phone</th>
                        <th>DoubleTick ID</th>
                        <th>B24 Chat / Msg</th>
                        <th>Status</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentMessages)): ?>
                        <tr><td colspan="6" style="text-align: center; color: var(--text-muted);">No messages synced yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentMessages as $msg): ?>
                            <tr>
                                <td>
                                    <span class="badge <?= $msg['direction'] === 'INBOUND' ? 'badge-success' : 'badge-warning' ?>">
                                        <?= $msg['direction'] ?>
                                    </span>
                                </td>
                                <td>+<?= htmlspecialchars($msg['customer_phone']) ?></td>
                                <td><span class="code-badge"><?= htmlspecialchars(substr($msg['dt_message_id'], 0, 18)) ?>...</span></td>
                                <td>Chat #<?= $msg['b24_chat_id'] ?> (Msg #<?= $msg['b24_message_id'] ?>)</td>
                                <td><?= htmlspecialchars(strtoupper($msg['status'])) ?></td>
                                <td><?= htmlspecialchars($msg['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Webhook Health -->
    <div class="card">
        <h2>⚡ Incoming Webhook Logs</h2>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Source</th>
                        <th>Event</th>
                        <th>Status</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentWebhooks)): ?>
                        <tr><td colspan="4" style="text-align: center; color: var(--text-muted);">No webhooks received yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentWebhooks as $wh): ?>
                            <tr>
                                <td><span class="code-badge"><?= htmlspecialchars($wh['source']) ?></span></td>
                                <td><strong><?= htmlspecialchars($wh['event_type']) ?></strong></td>
                                <td><span class="badge badge-success"><?= $wh['status_code'] ?> OK</span></td>
                                <td><?= htmlspecialchars($wh['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

</body>
</html>
