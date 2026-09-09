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
    <script src="//api.bitrix24.com/api/v1/"></script>
</head>
<body style="padding: 16px;">

<div class="container" style="max-width: 650px;">
    <div class="header" style="margin-bottom: 16px; padding-bottom: 16px;">
        <div class="brand">
            <img src="assets/icon.svg" width="36" height="36" alt="DoubleTick">
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
