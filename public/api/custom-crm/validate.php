<?php

declare(strict_types=1);

namespace DoubleTickB24;

require_once dirname(__DIR__, 2) . '/config/config.php';
spl_autoload_register(function ($class) {
    $prefix = 'DoubleTickB24\\';
    $baseDir = dirname(__DIR__, 2) . '/src/';
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

header('Content-Type: application/json; charset=utf-8');

try {
    // 1. Extract session token from JSON body, POST, GET, or Headers
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $token = (string)($input['sessionToken'] ?? $input['token'] ?? $_POST['sessionToken'] ?? $_GET['sessionToken'] ?? $_GET['token'] ?? '');

    if (!$token) {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/Bearer\s+(\S+)/i', $authHeader, $matches)) {
            $token = $matches[1];
        }
    }

    if (!$token) {
        Logger::warning("Custom CRM Validate User: missing session token", ['input' => $input, 'get' => $_GET]);
        http_response_code(401);
        echo json_encode(['error' => 'Missing sessionToken']);
        exit;
    }

    // 2. Resolve User ID and Member ID from the session token
    // Supported formats:
    // Format A: Signed JSON token: base64(json({userId, memberId, ts}))
    // Format B: Direct composite "userId:memberId"
    // Format C: Plain Bitrix24 User ID "1"
    $userId = null;
    $memberId = null;

    if (str_contains($token, '.')) {
        [$b64Payload] = explode('.', $token, 2);
        $payload = json_decode(base64_decode($b64Payload), true);
        if (is_array($payload)) {
            $userId = (string)($payload['user_id'] ?? $payload['userId'] ?? $payload['id'] ?? '');
            $memberId = (string)($payload['member_id'] ?? $payload['memberId'] ?? '');
        }
    } elseif (str_contains($token, ':')) {
        [$uId, $mId] = explode(':', $token, 2);
        $userId = $uId;
        $memberId = $mId;
    } else {
        $userId = $token;
    }

    $b24 = $memberId ? BitrixClient::getByMemberId($memberId) : BitrixClient::getFirstActive();
    if (!$b24) {
        http_response_code(400);
        echo json_encode(['error' => 'No active Bitrix24 portal found.']);
        exit;
    }

    // 3. Fetch user details from Bitrix24
    $params = [];
    if (is_numeric($userId)) {
        $params['ID'] = (int)$userId;
    }

    $userRes = $b24->call('user.get', $params);
    $user = $userRes['result'][0] ?? null;

    if (!$user) {
        Logger::warning("Custom CRM Validate User: user not found in Bitrix24", ['userId' => $userId]);
        http_response_code(404);
        echo json_encode(['error' => 'User not found in Bitrix24 CRM']);
        exit;
    }

    $name = trim(($user['NAME'] ?? '') . ' ' . ($user['LAST_NAME'] ?? ''));
    if (!$name) {
        $name = (string)($user['EMAIL'] ?? 'Bitrix24 Agent #' . $user['ID']);
    }

    $phone = (string)($user['PERSONAL_MOBILE'] ?? $user['WORK_PHONE'] ?? $user['PERSONAL_PHONE'] ?? '');
    if ($phone && !str_starts_with($phone, '+')) {
        $phone = '+' . preg_replace('/[^0-9]/', '', $phone);
    }

    $response = [
        'id' => (string)$user['ID'],
        'name' => $name,
        'email' => (string)($user['EMAIL'] ?? ''),
        'phone' => $phone,
    ];

    Logger::info("Custom CRM Validate User success", ['userId' => $user['ID'], 'name' => $name]);

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (\Throwable $e) {
    Logger::error("Custom CRM Validate User error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
