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
use DoubleTickB24\Core\Logger;
use DoubleTickB24\DoubleTick\WebhookProcessor;

header('Content-Type: application/json');

$rawInput = file_get_contents('php://input');
if (!$rawInput) {
    echo json_encode(['success' => false, 'error' => 'Empty request body']);
    exit;
}

$payload = json_decode($rawInput, true);
if (!is_array($payload)) {
    echo json_encode(['success' => false, 'error' => 'Invalid JSON payload']);
    exit;
}

Logger::info("Incoming DoubleTick Webhook received", ['keys' => array_keys($payload)]);

// Identify portal
$b24 = BitrixClient::getFirstActive();
if (!$b24) {
    Logger::error("No active Bitrix24 portal found to process webhook");
    echo json_encode(['success' => false, 'error' => 'No active Bitrix24 portal configured']);
    exit;
}

try {
    $processor = new WebhookProcessor($b24);
    $result = $processor->process($payload);
    echo json_encode(['success' => true, 'result' => $result]);
} catch (\Throwable $e) {
    Logger::error("Exception in WebhookProcessor: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
