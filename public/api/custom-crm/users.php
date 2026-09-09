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
    $memberId = (string)($_GET['member_id'] ?? '');
    $b24 = $memberId ? BitrixClient::getByMemberId($memberId) : BitrixClient::getFirstActive();

    if (!$b24) {
        http_response_code(400);
        echo json_encode([
            'users' => [],
            'error' => 'No active Bitrix24 portal connected.'
        ]);
        exit;
    }

    // Fetch active users from Bitrix24
    $usersRes = $b24->call('user.get', [
        'ACTIVE' => 'Y',
        'ADMIN' => 'N',
    ]);

    $rawUsers = $usersRes['result'] ?? [];
    $formattedUsers = [];

    foreach ($rawUsers as $u) {
        $name = trim(($u['NAME'] ?? '') . ' ' . ($u['LAST_NAME'] ?? ''));
        if (!$name) {
            $name = (string)($u['EMAIL'] ?? 'Bitrix24 Agent #' . $u['ID']);
        }

        $phone = (string)($u['PERSONAL_MOBILE'] ?? $u['WORK_PHONE'] ?? $u['PERSONAL_PHONE'] ?? '');
        if ($phone && !str_starts_with($phone, '+')) {
            $phone = '+' . preg_replace('/[^0-9]/', '', $phone);
        }

        $formattedUsers[] = [
            'id' => (string)$u['ID'],
            'name' => $name,
            'email' => (string)($u['EMAIL'] ?? ''),
            'phone' => $phone,
        ];
    }

    Logger::info("Custom CRM List Users API called", [
        'count' => count($formattedUsers),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ]);

    echo json_encode([
        'users' => $formattedUsers
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (\Throwable $e) {
    Logger::error("Custom CRM List Users API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'users' => [],
        'error' => $e->getMessage()
    ]);
}
