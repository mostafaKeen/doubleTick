<?php

declare(strict_types=1);

namespace DoubleTickB24\Core;

class Logger
{
    private static string $logDir = '';

    public static function init(string $dir): void
    {
        self::$logDir = $dir;
        if (!is_dir(self::$logDir)) {
            mkdir(self::$logDir, 0777, true);
        }
    }

    public static function log(string $level, string $message, array $context = []): void
    {
        if (empty(self::$logDir)) {
            self::$logDir = dirname(__DIR__, 2) . '/storage/logs';
            if (!is_dir(self::$logDir)) {
                mkdir(self::$logDir, 0777, true);
            }
        }

        $date = date('Y-m-d');
        $timestamp = date('Y-m-d H:i:s');
        $logFile = self::$logDir . "/app-{$date}.log";

        $contextStr = empty($context) ? '' : ' ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $entry = "[{$timestamp}] [{$level}] {$message}{$contextStr}" . PHP_EOL;

        file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
    }

    public static function info(string $message, array $context = []): void
    {
        self::log('INFO', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::log('ERROR', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::log('WARNING', $message, $context);
    }

    public static function debug(string $message, array $context = []): void
    {
        self::log('DEBUG', $message, $context);
    }
}
