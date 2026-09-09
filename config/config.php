<?php

declare(strict_types=1);

namespace DoubleTickB24;

// Basic .env file loader if present
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (str_contains($line, '=')) {
            [$name, $value] = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            if (!isset($_ENV[$name]) && !isset($_SERVER[$name])) {
                putenv("{$name}={$value}");
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

$defaultUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'off' ? 'http' : 'https') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
if (isset($_SERVER['SCRIPT_NAME'])) {
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    if ($scriptDir && $scriptDir !== '/' && $scriptDir !== '\\' && $scriptDir !== '.') {
        $defaultUrl .= str_replace('\\', '/', $scriptDir);
    }
}

return [
    'app' => [
        'name' => 'KEEN DoubleTick',
        'url' => getenv('APP_URL') ?: $defaultUrl,
        'env' => getenv('APP_ENV') ?: 'production',
        'debug' => (bool)(getenv('APP_DEBUG') ?: false),
    ],
    'doubletick' => [
        'api_url' => getenv('DOUBLETICK_API_URL') ?: 'https://public.doubletick.io',
        'api_key' => getenv('DOUBLETICK_API_KEY') ?: '',
        'default_waba' => getenv('DOUBLETICK_DEFAULT_WABA') ?: '',
        'webhook_secret' => getenv('DOUBLETICK_WEBHOOK_SECRET') ?: '',
    ],
    'bitrix24' => [
        'client_id' => getenv('B24_CLIENT_ID') ?: '',
        'client_secret' => getenv('B24_CLIENT_SECRET') ?: '',
        'connector_id' => 'doubletick_whatsapp',
        'connector_name' => 'KEEN DoubleTick',
        'messageservice_id' => 'doubletick_wa',
        'messageservice_name' => 'KEEN DoubleTick',
    ],
    'database' => [
        'driver' => getenv('DB_DRIVER') ?: 'sqlite',
        // For SQLite:
        'sqlite_path' => dirname(__DIR__) . '/storage/app.db',
        // For MySQL:
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => getenv('DB_PORT') ?: '3306',
        'database' => getenv('DB_NAME') ?: 'doubletick_b24',
        'username' => getenv('DB_USER') ?: 'root',
        'password' => getenv('DB_PASS') ?: '',
    ],
    'storage' => [
        'logs' => dirname(__DIR__) . '/storage/logs',
        'media' => dirname(__DIR__) . '/storage/media',
    ]
];
