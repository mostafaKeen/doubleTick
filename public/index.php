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
            // If WABA is blank or placeholder, auto-detect using API key
            if ($key && (empty($waba) || $waba === '919999999999')) {
                try {
                    $dtTemp = new DoubleTickClient($key, null, $config['doubletick']['api_url']);
                    $detected = $dtTemp->getPrimaryWabaNumber();
                    if ($detected) {
                        $waba = $detected;
                    }
                } catch (\Throwable $e) {
                    // Ignore detection error
                }
            }

            $stmt = $db->prepare("UPDATE b24_portals SET dt_api_key = :k, dt_waba_number = :w WHERE id = :id");
            $stmt->execute(['k' => $key, 'w' => $waba, 'id' => $b24->getPortalId()]);
            $notice = 'DoubleTick credentials updated successfully!' . ($waba ? " Connected WABA: {$waba}" : '');
            $b24 = BitrixClient::getFirstActive();
            $portalData = $b24 ? $b24->getPortalData() : [];
        } else {
            $error = 'No active Bitrix24 portal installed yet. Please install the app in Bitrix24 first.';
        }
    } elseif ($action === 'detect_waba') {
        $apiKey = $b24 ? ($b24->getDoubleTickApiKey() ?: $config['doubletick']['api_key']) : $config['doubletick']['api_key'];
        if (!$apiKey) {
            $error = 'Please enter and save your DoubleTick Public API key first.';
        } else {
            try {
                $dt = new DoubleTickClient($apiKey, null, $config['doubletick']['api_url']);
                $channels = $dt->listChannels();
                if (!empty($channels['channels']) && is_array($channels['channels'])) {
                    $detected = $channels['channels'][0]['wabaNumber'] ?? null;
                    $wabaName = $channels['channels'][0]['displayName'] ?? 'Default';
                    if ($detected && $b24) {
                        $db->prepare("UPDATE b24_portals SET dt_waba_number = :w WHERE id = :id")
                           ->execute(['w' => $detected, 'id' => $b24->getPortalId()]);
                        $b24 = BitrixClient::getFirstActive();
                        $portalData = $b24 ? $b24->getPortalData() : [];
                        $notice = "Successfully detected and connected channel: '{$wabaName}' ({$detected})";
                    } else {
                        $notice = "Found channels: " . json_encode($channels['channels']);
                    }
                } else {
                    $error = "No connected WhatsApp channels found on your DoubleTick account.";
                }
            } catch (\Throwable $e) {
                $error = "Channel detection error: " . $e->getMessage();
            }
        }
    } elseif ($action === 'send_test_message') {
        $to = trim($_POST['test_phone'] ?? '');
        $text = trim($_POST['test_text'] ?? 'Hello! This is a test message from Bitrix24 DoubleTick App.');
        $apiKey = $b24 ? ($b24->getDoubleTickApiKey() ?: $config['doubletick']['api_key']) : $config['doubletick']['api_key'];
        $waba = $b24 ? ($b24->getDoubleTickWaba() ?: $config['doubletick']['default_waba']) : $config['doubletick']['default_waba'];

        if (!$apiKey) {
            $error = 'DoubleTick API key is required. Please save credentials first.';
        } elseif (!$to) {
            $error = 'Recipient phone number is required.';
        } else {
            try {
                $dt = new DoubleTickClient($apiKey, $waba, $config['doubletick']['api_url']);
                $res = $dt->sendTextMessage($to, $text, $waba);

                if (!empty($res['error']) || (!empty($res['status_code']) && $res['status_code'] >= 400)) {
                    $errDetail = is_array($res['error']) ? json_encode($res['error']) : (string)$res['error'];
                    if (!empty($res['raw']['message'])) {
                        $msgDetail = is_array($res['raw']['message']) ? implode(', ', $res['raw']['message']) : (string)$res['raw']['message'];
                        $errDetail .= " ({$msgDetail})";
                    }
                    $error = "DoubleTick Error: {$errDetail}";
                } else {
                    $msgId = $res['messageId'] ?? $res['dtMessageId'] ?? 'OK';
                    $notice = "WhatsApp message sent successfully! DoubleTick Message ID: {$msgId}";
                }
            } catch (\Throwable $e) {
                $error = "Failed to send message: " . $e->getMessage();
            }
        }
    } elseif ($action === 'send_template_message') {
        $to = trim($_POST['template_phone'] ?? '');
        $templateName = trim($_POST['template_name'] ?? '');
        $language = trim($_POST['template_language'] ?? 'en');
        $apiKey = $b24 ? ($b24->getDoubleTickApiKey() ?: $config['doubletick']['api_key']) : $config['doubletick']['api_key'];
        $waba = $b24 ? ($b24->getDoubleTickWaba() ?: $config['doubletick']['default_waba']) : $config['doubletick']['default_waba'];

        $params = [];
        for ($i = 1; $i <= 5; $i++) {
            $val = trim($_POST["template_param_{$i}"] ?? '');
            if ($val !== '') {
                $params[] = $val;
            }
        }

        if (!$apiKey) {
            $error = 'DoubleTick API key is required.';
        } elseif (!$to) {
            $error = 'Recipient phone number is required.';
        } elseif (!$templateName) {
            $error = 'Template name is required.';
        } else {
            try {
                $dt = new DoubleTickClient($apiKey, $waba, $config['doubletick']['api_url']);
                $res = $dt->sendTemplateMessage($to, $templateName, $language, $params, $waba);

                if (!empty($res['error']) || (!empty($res['status_code']) && $res['status_code'] >= 400)) {
                    $errDetail = is_array($res['error']) ? json_encode($res['error']) : (string)$res['error'];
                    if (!empty($res['raw']['message'])) {
                        $msgDetail = is_array($res['raw']['message']) ? implode(', ', $res['raw']['message']) : (string)$res['raw']['message'];
                        $errDetail .= " ({$msgDetail})";
                    }
                    $error = "Template Error: {$errDetail}";
                } else {
                    $msgId = $res['messageId'] ?? ($res['messages'][0]['messageId'] ?? 'OK');
                    $status = $res['status'] ?? ($res['messages'][0]['status'] ?? 'SENT');
                    $notice = "WhatsApp Template '{$templateName}' sent successfully to {$to}! Status: {$status} (Message ID: {$msgId})";
                }
            } catch (\Throwable $e) {
                $error = "Failed to send template: " . $e->getMessage();
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

// Fetch approved templates for selection if API key available
$approvedTemplates = [];
$activeApiKey = $b24 ? ($b24->getDoubleTickApiKey() ?: $config['doubletick']['api_key']) : $config['doubletick']['api_key'];
if ($activeApiKey) {
    try {
        $activeWaba = $b24 ? ($b24->getDoubleTickWaba() ?: $config['doubletick']['default_waba']) : $config['doubletick']['default_waba'];
        $dtTpl = new DoubleTickClient($activeApiKey, $activeWaba, $config['doubletick']['api_url']);
        $tplRes = $dtTpl->getTemplates('APPROVED');
        if (!empty($tplRes) && is_array($tplRes)) {
            // Can be array of items or { templates: [...] }
            $approvedTemplates = isset($tplRes['templates']) && is_array($tplRes['templates']) ? $tplRes['templates'] : $tplRes;
        }
    } catch (\Throwable $e) {
        // Silently continue
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
    <style>
        :root {
            --primary: #3F906D;
            --primary-hover: #2e6e52;
            --primary-glow: rgba(63, 144, 109, 0.25);
            --secondary: #00a2e8;
            --bg-dark: #0f172a;
            --card-bg: #1e293b;
            --card-border: #334155;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: var(--font-family); background-color: var(--bg-dark); color: var(--text-main); line-height: 1.6; padding: 24px; }
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
        .form-control { width: 100%; padding: 10px 14px; background: #0f172a; border: 1px solid var(--card-border); border-radius: 8px; color: #fff; font-size: 14px; outline: none; transition: border-color 0.2s; }
        .form-control:focus { border-color: var(--primary); }
        .btn { display: inline-flex; align-items: center; justify-content: center; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; transition: all 0.2s; text-decoration: none; gap: 8px; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-hover); box-shadow: 0 0 12px var(--primary-glow); }
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
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="28" height="28">
                    <rect width="48" height="48" rx="10" fill="#3F906D"/>
                    <path d="M24 8C15.16 8 8 15.16 8 24C8 26.83 8.74 29.58 10.14 32L7.9 40.2L16.32 38C18.66 39.28 21.3 40 24 40C32.84 40 40 32.84 40 24C40 15.16 32.84 8 24 8ZM24 37.3C21.6 37.3 19.26 36.66 17.22 35.46L16.74 35.18L11.72 36.5L13.06 31.62L12.74 31.12C11.42 29 10.72 26.54 10.72 24C10.72 16.68 16.68 10.72 24 10.72C31.32 10.72 37.28 16.68 37.28 24C37.28 31.32 31.32 37.3 24 37.3Z" fill="#FFFFFF"/>
                    <path d="M29.5 25.5C29 25.25 26.5 24.02 26.04 23.85C25.58 23.68 25.24 23.6 24.9 24.1C24.56 24.6 23.6 25.75 23.3 26.1C23.02 26.45 22.72 26.49 22.22 26.24C21.72 25.99 20.12 25.46 18.22 23.77C16.74 22.45 15.74 20.82 15.46 20.32C15.18 19.82 15.42 19.55 15.68 19.3C15.9 19.08 16.18 18.72 16.42 18.44C16.68 18.16 16.76 17.96 16.92 17.62C17.08 17.28 17 16.98 16.88 16.74C16.76 16.5 15.74 14 15.32 12.98C14.9 12 14.5 12.14 14.2 12.12H13.24C12.9 12.12 12.36 12.24 11.9 12.74C11.44 13.24 10.16 14.44 10.16 16.88C10.16 19.32 11.94 21.68 12.18 22C12.44 22.34 15.68 27.3 20.62 29.42C21.8 29.92 22.72 30.22 23.42 30.46C24.6 30.82 25.68 30.78 26.54 30.64C27.5 30.5 29.5 29.42 29.92 28.24C30.34 27.06 30.34 26.04 30.22 25.82C30.08 25.62 29.98 25.74 29.5 25.5Z" fill="#FFFFFF"/>
                </svg>
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
                <div style="display: flex; gap: 10px; margin-top: 10px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Save Credentials</button>
                </div>
            </form>
            <form method="POST" style="margin-top: 10px;">
                <input type="hidden" name="action" value="detect_waba">
                <button type="submit" class="btn" style="width: 100%; background: #334155; color: #fff;">
                    🔍 Auto-Detect Connected WABA Number
                </button>
            </form>
        </div>

        <div class="card">
            <h2>🧪 Direct Text Message (Active 24h Window)</h2>
            <form method="POST" style="margin-bottom: 16px;">
                <input type="hidden" name="action" value="send_test_message">
                <div class="form-group">
                    <label>Test WhatsApp Recipient</label>
                    <input type="text" name="test_phone" class="form-control" placeholder="Phone with country code (e.g. 201129274930)">
                </div>
                <div class="form-group">
                    <label>Message Text</label>
                    <input type="text" name="test_text" class="form-control" value="Hello! This is a test message from Bitrix24 DoubleTick App.">
                </div>
                <p style="font-size: 11px; color: var(--text-muted); margin-bottom: 10px;">
                    Note: WhatsApp Cloud API only allows direct text messages if the contact messaged you in the last 24 hours.
                </p>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Send Direct Message</button>
            </form>

            <form method="POST">
                <input type="hidden" name="action" value="simulate_ad_lead">
                <button type="submit" class="btn" style="width: 100%; background: #334155; color: #fff;">
                    🎯 Simulate Meta CTWA Ad Lead
                </button>
            </form>
        </div>

        <div class="card" style="border: 1px solid var(--primary);">
            <h2>📢 Send WhatsApp Template (Closed Window)</h2>
            <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 12px;">
                Use approved WhatsApp templates to initiate conversations or message contacts outside the 24-hour service window.
            </p>
            <form method="POST">
                <input type="hidden" name="action" value="send_template_message">
                <div class="form-group">
                    <label>Recipient Phone Number</label>
                    <input type="text" name="template_phone" class="form-control" required placeholder="e.g. 201129274930" value="201129274930">
                </div>

                <?php if (!empty($approvedTemplates)): ?>
                    <div class="form-group">
                        <label>Select Approved Template (<?= count($approvedTemplates) ?> available)</label>
                        <select id="template_select" class="form-control" onchange="onTemplateSelected(this)">
                            <option value="">-- Choose from approved templates --</option>
                            <?php foreach ($approvedTemplates as $tpl): ?>
                                <option value="<?= htmlspecialchars($tpl['name'] ?? '') ?>" 
                                        data-lang="<?= htmlspecialchars($tpl['language'] ?? 'en') ?>">
                                    <?= htmlspecialchars($tpl['name'] ?? '') ?> (<?= htmlspecialchars($tpl['language'] ?? 'en') ?>) - <?= htmlspecialchars($tpl['category'] ?? 'TEMPLATE') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label>Template Name</label>
                    <input type="text" id="template_name_input" name="template_name" class="form-control" required placeholder="e.g. hello_world or welcome_message">
                </div>
                <div class="form-group">
                    <label>Language Code</label>
                    <input type="text" id="template_lang_input" name="template_language" class="form-control" value="en" placeholder="e.g. en, ar, es">
                </div>
                <div class="form-group">
                    <label>Placeholder 1 ({{1}} - optional)</label>
                    <input type="text" name="template_param_1" class="form-control" placeholder="Value for {{1}}">
                </div>
                <div class="form-group">
                    <label>Placeholder 2 ({{2}} - optional)</label>
                    <input type="text" name="template_param_2" class="form-control" placeholder="Value for {{2}}">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; background: #2563eb;">
                    🚀 Send WhatsApp Template
                </button>
            </form>
            <script>
                function onTemplateSelected(sel) {
                    if (sel.value) {
                        document.getElementById('template_name_input').value = sel.value;
                        var opt = sel.options[sel.selectedIndex];
                        if (opt && opt.getAttribute('data-lang')) {
                            document.getElementById('template_lang_input').value = opt.getAttribute('data-lang');
                        }
                    }
                }
            </script>
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
