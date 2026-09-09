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

$config = require dirname(__DIR__) . '/config/config.php';

$memberId = (string)($_REQUEST['member_id'] ?? $_REQUEST['auth']['member_id'] ?? '');
$b24 = $memberId ? BitrixClient::getByMemberId($memberId) : BitrixClient::getFirstActive();

$lineId = isset($_REQUEST['LINE']) ? (int)$_REQUEST['LINE'] : null;
$message = '';

if ($b24 && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'save_settings') {
        $apiKey = trim($_POST['dt_api_key'] ?? '');
        $waba = trim($_POST['dt_waba'] ?? '');
        $openLine = !empty($_POST['open_line_id']) ? (int)$_POST['open_line_id'] : null;

        $db = Database::getInstance();
        $stmt = $db->prepare("
            UPDATE b24_portals 
            SET dt_api_key = :key, dt_waba_number = :waba, open_line_id = :line, updated_at = datetime('now')
            WHERE id = :id
        ");
        $stmt->execute([
            'key' => $apiKey,
            'waba' => $waba,
            'line' => $openLine,
            'id' => $b24->getPortalId(),
        ]);

        if ($openLine) {
            $im = new ImConnectorService($b24);
            $im->activate($openLine, true);
        }

        $message = "Settings updated and connector activated on Open Line #{$openLine}!";
        // Reload client
        $b24 = BitrixClient::getByMemberId($memberId ?: $b24->getPortalData()['member_id']);
    }
}

// Fetch open lines list from Bitrix24
$openLines = [];
if ($b24) {
    $linesRes = $b24->call('imopenlines.config.list.get');
    $openLines = $linesRes['result'] ?? [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DoubleTick WhatsApp Connector Settings</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="style.css">
    <style>
        :root {
            --primary: #3F906D; --primary-hover: #2e6e52; --primary-glow: rgba(63, 144, 109, 0.25);
            --bg-dark: #0f172a; --card-bg: #1e293b; --card-border: #334155;
            --text-main: #f8fafc; --text-muted: #94a3b8; --success: #10b981; --warning: #f59e0b;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background-color: var(--bg-dark); color: var(--text-main); line-height: 1.6; padding: 16px; }
        .container { max-width: 650px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 16px; border-bottom: 1px solid var(--card-border); margin-bottom: 16px; }
        .brand { display: flex; align-items: center; gap: 12px; }
        .brand h1 { font-size: 18px; font-weight: 700; color: #fff; }
        .brand p { font-size: 12px; color: var(--text-muted); }
        .card { background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 14px; padding: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.2); }
        .card h2 { font-size: 15px; margin-bottom: 14px; color: var(--primary); }
        .form-group { margin-bottom: 14px; }
        .form-group label { display: block; font-size: 12px; color: var(--text-muted); margin-bottom: 6px; font-weight: 500; }
        .form-control { width: 100%; padding: 10px 14px; background: #0f172a; border: 1px solid var(--card-border); border-radius: 8px; color: #fff; font-size: 14px; outline: none; }
        .btn { display: inline-flex; align-items: center; justify-content: center; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; }
        .btn-primary { background: var(--primary); color: #fff; }
    </style>
    <script src="//api.bitrix24.com/api/v1/"></script>
</head>
<body style="padding: 16px;">

<div class="container" style="max-width: 650px;">
    <div class="header" style="margin-bottom: 16px; padding-bottom: 16px;">
        <div class="brand">
            <img src="assets/icon.svg" onerror="this.src='icon.svg'" width="36" height="36" alt="DoubleTick">
            <div>
                <h1>DoubleTick WhatsApp Connector</h1>
                <p>Configure Open Channels two-way WhatsApp routing</p>
            </div>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="badge badge-success" style="width: 100%; margin-bottom: 16px; padding: 10px 14px; font-size: 13px;">
            ✓ <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <h2>Connection Settings</h2>
        <form method="POST">
            <input type="hidden" name="action" value="save_settings">
            <input type="hidden" name="member_id" value="<?= htmlspecialchars($memberId) ?>">

            <div class="form-group">
                <label>DoubleTick Public API Key</label>
                <input type="password" name="dt_api_key" class="form-control" 
                       value="<?= htmlspecialchars($b24 ? ($b24->getDoubleTickApiKey() ?? '') : '') ?>" 
                       placeholder="key_xxxxxxxxxxxxxxxx">
            </div>

            <div class="form-group">
                <label>Connected WABA WhatsApp Number</label>
                <input type="text" name="dt_waba" class="form-control" 
                       value="<?= htmlspecialchars($b24 ? ($b24->getDoubleTickWaba() ?? '') : '') ?>" 
                       placeholder="e.g. 919999999999 (without +)">
            </div>

            <div class="form-group">
                <label>Route Messages to Bitrix24 Open Line</label>
                <select name="open_line_id" class="form-control">
                    <option value="">-- Select Open Line --</option>
                    <?php foreach ($openLines as $line): ?>
                        <?php 
                            $lId = (int)$line['ID'];
                            $selected = ($b24 && $b24->getOpenLineId() === $lId) ? 'selected' : '';
                        ?>
                        <option value="<?= $lId ?>" <?= $selected ?>>
                            #<?= $lId ?>: <?= htmlspecialchars($line['LINE_NAME']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="stat-label">Inbound customer messages will route to this Open Line queue.</span>
            </div>

            <div style="margin-top: 20px; display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary">Save & Connect</button>
            </div>
        </form>
    </div>
</div>

<script>
    BX24.init(function() {
        BX24.resizeWindow(680, 520);
    });
</script>

</body>
</html>
