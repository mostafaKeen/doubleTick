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
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="style.css">
    <style>
        :root {
            --primary: #3F906D; --primary-hover: #2e6e52; --primary-glow: rgba(63, 144, 109, 0.25);
            --bg-dark: #0f172a; --card-bg: #1e293b; --card-border: #334155;
            --text-main: #f8fafc; --text-muted: #94a3b8; --success: #10b981; --warning: #f59e0b; --danger: #ef4444;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background-color: var(--bg-dark); color: var(--text-main); line-height: 1.6; padding: 24px; }
        .container { max-width: 1100px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 24px; border-bottom: 1px solid var(--card-border); margin-bottom: 24px; }
        .brand { display: flex; align-items: center; gap: 16px; }
        .brand-icon { width: 44px; height: 44px; background: var(--primary-glow); border: 1px solid var(--primary); border-radius: 12px; display: flex; align-items: center; justify-content: center; }
        .brand h1 { font-size: 20px; font-weight: 700; color: #fff; }
        .brand p { font-size: 13px; color: var(--text-muted); }
        .badge { padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; }
        .badge-success { background: rgba(16, 185, 129, 0.15); color: var(--success); border: 1px solid var(--success); }
        .badge-warning { background: rgba(245, 158, 11, 0.15); color: var(--warning); border: 1px solid var(--warning); }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin-bottom: 24px; }
        .card { background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 14px; padding: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.2); }
        .card h2 { font-size: 16px; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; color: var(--primary); }
        .stat-value { font-size: 28px; font-weight: 700; color: #fff; margin: 6px 0; }
        .stat-label { font-size: 13px; color: var(--text-muted); }
        .form-group { margin-bottom: 14px; }
        .form-group label { display: block; font-size: 12px; color: var(--text-muted); margin-bottom: 6px; font-weight: 500; }
        .form-control { width: 100%; padding: 10px 14px; background: #0f172a; border: 1px solid var(--card-border); border-radius: 8px; color: #fff; font-size: 14px; outline: none; }
        .form-control:focus { border-color: var(--primary); }
        .btn { display: inline-flex; align-items: center; justify-content: center; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; gap: 8px; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-hover); }
        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; text-align: left; }
        th, td { padding: 12px 14px; border-bottom: 1px solid var(--card-border); }
        th { color: var(--text-muted); font-weight: 600; background: rgba(15, 23, 42, 0.4); }
        .code-badge { background: #0f172a; padding: 4px 8px; border-radius: 6px; font-family: monospace; color: #38bdf8; font-size: 12px; }
    </style>
</head>
<body>

<div class="container">
    <header class="header">
        <div class="brand">
            <div class="brand-icon">
                <img src="assets/icon.svg" onerror="this.src='icon.svg'" width="32" height="32" alt="DoubleTick">
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
