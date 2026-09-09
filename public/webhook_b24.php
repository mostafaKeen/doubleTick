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
use DoubleTickB24\Core\Logger;
use DoubleTickB24\DoubleTick\DoubleTickClient;

header('Content-Type: application/json');

$config = require dirname(__DIR__) . '/config/config.php';

// Accept both POST form-urlencoded and JSON
$payload = $_REQUEST;
if (empty($payload)) {
    $raw = file_get_contents('php://input');
    if ($raw) {
        $payload = json_decode($raw, true) ?: [];
    }
}

$event = strtoupper((string)($payload['event'] ?? ''));
Logger::info("Incoming Bitrix24 Event received: {$event}", ['payload' => $payload]);

// Find portal
$memberId = (string)($payload['auth']['member_id'] ?? '');
$domain = (string)($payload['auth']['domain'] ?? '');

$b24 = null;
if ($memberId) {
    $b24 = BitrixClient::getByMemberId($memberId);
}
if (!$b24 && $domain) {
    $b24 = BitrixClient::getByDomain($domain);
}
if (!$b24) {
    $b24 = BitrixClient::getFirstActive();
}

if (!$b24) {
    Logger::error("No matching Bitrix24 portal found for event", ['member_id' => $memberId, 'domain' => $domain]);
    echo json_encode(['success' => false, 'error' => 'Portal not found']);
    exit;
}

$apiKey = $b24->getDoubleTickApiKey() ?: $config['doubletick']['api_key'];
$waba = $b24->getDoubleTickWaba() ?: $config['doubletick']['default_waba'];

if (!$apiKey) {
    Logger::error("DoubleTick API key missing in portal configuration");
    echo json_encode(['success' => false, 'error' => 'DoubleTick API key not configured']);
    exit;
}

$dtClient = new DoubleTickClient($apiKey, $waba, $config['doubletick']['api_url']);

try {
    // 1. Open Channel Outbound Message
    if ($event === 'ONIMCONNECTORMESSAGEADD' || isset($payload['data']['MESSAGES'])) {
        $imService = new ImConnectorService($b24);
        $imService->processOutboundEvent($payload, $dtClient);
        echo json_encode(['success' => true, 'action' => 'imconnector_outbound_processed']);
        exit;
    }

    // 2. Business Process / Automation Robot Action
    if (isset($payload['event_token']) || (isset($payload['code']) && str_starts_with($payload['code'], 'dt_'))) {
        $bizProc = new BizProcService($b24);
        $bizProc->handleExecution($payload, $dtClient);
        echo json_encode(['success' => true, 'action' => 'bizproc_executed']);
        exit;
    }

    // 3. MessageService Provider Send (from CRM Timeline)
    if (isset($payload['message_id']) && isset($payload['message_to'])) {
        $msgService = new MessageServiceService($b24);
        $msgService->handleSend($payload, $dtClient);
        echo json_encode(['success' => true, 'action' => 'messageservice_sent']);
        exit;
    }

    echo json_encode(['success' => true, 'status' => 'ignored']);
} catch (\Throwable $e) {
    Logger::error("Error handling Bitrix24 event: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
