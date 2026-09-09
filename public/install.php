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
use DoubleTickB24\Bitrix24\BizProcService;
use DoubleTickB24\Bitrix24\ImConnectorService;
use DoubleTickB24\Bitrix24\MessageServiceService;
use DoubleTickB24\Bitrix24\PlacementService;
use DoubleTickB24\Core\Database;
use DoubleTickB24\Core\Logger;

$config = require dirname(__DIR__) . '/config/config.php';
$appUrl = rtrim($config['app']['url'], '/');

// Capture install parameters from Bitrix24
$authId = (string)($_REQUEST['AUTH_ID'] ?? $_REQUEST['auth']['access_token'] ?? '');
$refreshId = (string)($_REQUEST['REFRESH_ID'] ?? $_REQUEST['auth']['refresh_token'] ?? '');
$domain = (string)($_REQUEST['DOMAIN'] ?? $_REQUEST['auth']['domain'] ?? '');
$memberId = (string)($_REQUEST['member_id'] ?? $_REQUEST['auth']['member_id'] ?? '');
$expiresIn = (int)($_REQUEST['AUTH_EXPIRES'] ?? $_REQUEST['auth']['expires_in'] ?? 3600);
$serverEndpoint = (string)($_REQUEST['auth']['server_endpoint'] ?? 'https://oauth.bitrix.info/rest/');

$status = 'idle';
$error = null;
$logs = [];

if ($authId && $domain && $memberId) {
    try {
        $db = Database::getInstance();
        $clientEndpoint = "https://{$domain}/rest/";

        // Upsert portal
        $stmt = $db->prepare("
            INSERT INTO b24_portals (
                member_id, domain, access_token, refresh_token, expires_at,
                client_endpoint, server_endpoint, is_active, installed_at, updated_at
            ) VALUES (
                :member_id, :domain, :access_token, :refresh_token, :expires_at,
                :client_endpoint, :server_endpoint, 1, datetime('now'), datetime('now')
            )
            ON CONFLICT(member_id) DO UPDATE SET
                domain = excluded.domain,
                access_token = excluded.access_token,
                refresh_token = excluded.refresh_token,
                expires_at = excluded.expires_at,
                client_endpoint = excluded.client_endpoint,
                server_endpoint = excluded.server_endpoint,
                is_active = 1,
                updated_at = datetime('now')
        ");

        $expiresAt = time() + $expiresIn;
        $stmt->execute([
            'member_id' => $memberId,
            'domain' => $domain,
            'access_token' => $authId,
            'refresh_token' => $refreshId,
            'expires_at' => $expiresAt,
            'client_endpoint' => $clientEndpoint,
            'server_endpoint' => $serverEndpoint,
        ]);

        $logs[] = "Portal credentials registered for {$domain}";

        $b24 = BitrixClient::getByMemberId($memberId);
        if ($b24) {
            // 1. Register Open Channel Connector
            $imConnector = new ImConnectorService($b24);
            $imRes = $imConnector->register(
                $appUrl . '/connector_settings.php',
                $appUrl . '/webhook_b24.php'
            );
            $logs[] = "Registered Open Channels Connector: " . ($imRes['connector']['result'] ? 'Success' : 'Check settings');

            // 2. Bind CRM Detail Placements
            $placement = new PlacementService($b24);
            $plRes = $placement->bindCrmTabs($appUrl . '/placement_tab.php');
            $logs[] = "Bound CRM Detail Tabs (Lead, Deal, Contact, Company)";

            // 3. Register Business Process Automation Robots
            $bizProc = new BizProcService($b24);
            $bpRes = $bizProc->registerActivities($appUrl . '/webhook_b24.php');
            $logs[] = "Registered CRM Automation Robots (Send Template, Send Text)";

            // 4. Register MessageService provider
            $msgService = new MessageServiceService($b24);
            $msRes = $msgService->register($appUrl . '/webhook_b24.php');
            $logs[] = "Registered CRM Timeline Messaging Provider";
        }

        $status = 'installed';
    } catch (\Throwable $e) {
        $status = 'error';
        $error = $e->getMessage();
        Logger::error("Installation failed: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installing DoubleTick WhatsApp for Bitrix24</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="style.css">
    <style>
        :root {
            --primary: #3F906D; --primary-hover: #2e6e52; --primary-glow: rgba(63, 144, 109, 0.25);
            --bg-dark: #0f172a; --card-bg: #1e293b; --card-border: #334155;
            --text-main: #f8fafc; --text-muted: #94a3b8; --success: #10b981; --danger: #ef4444;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background-color: var(--bg-dark); color: var(--text-main); line-height: 1.6; padding: 24px; }
        .container { max-width: 600px; margin: 60px auto 0; }
        .card { background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 14px; padding: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.2); }
        .btn { display: inline-flex; align-items: center; justify-content: center; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; }
        .btn-primary { background: var(--primary); color: #fff; }
    </style>
    <script src="//api.bitrix24.com/api/v1/"></script>
</head>
<body>
<div class="container" style="max-width: 600px; margin-top: 60px;">
    <div class="card" style="text-align: center;">
        <div style="margin-bottom: 20px;">
            <img src="assets/icon.svg" onerror="this.src='icon.svg'" width="64" height="64" alt="DoubleTick" style="border-radius: 16px;">
        </div>
        <h2>DoubleTick WhatsApp for Bitrix24</h2>
        
        <?php if ($status === 'installed'): ?>
            <p style="color: var(--success); margin: 15px 0; font-weight: 600;">
                ✓ Application installed successfully!
            </p>
            <div style="text-align: left; background: #0f172a; padding: 15px; border-radius: 8px; margin: 20px 0; font-size: 13px;">
                <?php foreach ($logs as $log): ?>
                    <div style="margin-bottom: 6px;">• <?= htmlspecialchars($log) ?></div>
                <?php endforeach; ?>
            </div>
            <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 20px;">
                Finalizing installation with Bitrix24...
            </p>
            <script>
                BX24.init(function() {
                    BX24.installFinish();
                    setTimeout(function() {
                        window.location.href = 'index.php';
                    }, 1500);
                });
            </script>
        <?php elseif ($status === 'error'): ?>
            <p style="color: var(--danger); margin: 15px 0; font-weight: 600;">
                Installation Error: <?= htmlspecialchars($error) ?>
            </p>
            <button class="btn btn-primary" onclick="window.location.reload();">Retry</button>
        <?php else: ?>
            <p style="margin: 15px 0; color: var(--text-muted);">
                Awaiting Bitrix24 authorization context...
            </p>
            <script>
                BX24.init(function() {
                    // If loaded inside Bitrix24 without initial POST data
                    var auth = BX24.getAuth();
                    if (auth && auth.access_token) {
                        var form = document.createElement('form');
                        form.method = 'POST';
                        form.action = 'install.php';
                        for (var key in auth) {
                            var input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = key;
                            input.value = auth[key];
                            form.appendChild(input);
                        }
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            </script>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
