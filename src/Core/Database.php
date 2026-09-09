<?php

declare(strict_types=1);

namespace DoubleTickB24\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;
    private static array $config = [];

    public static function init(array $config): void
    {
        self::$config = $config;
    }

    public static function getInstance(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        if (empty(self::$config)) {
            $configFile = dirname(__DIR__, 2) . '/config/config.php';
            $loadedConfig = require $configFile;
            self::$config = $loadedConfig['database'];
        }

        $driver = self::$config['driver'] ?? 'sqlite';

        if ($driver === 'sqlite') {
            $dbPath = self::$config['sqlite_path'] ?? (dirname(__DIR__, 2) . '/storage/app.db');
            $dir = dirname($dbPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

            self::$instance = new PDO("sqlite:{$dbPath}", null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            self::$instance->exec('PRAGMA journal_mode = WAL;');
        } else {
            $host = self::$config['host'] ?? '127.0.0.1';
            $port = self::$config['port'] ?? 3306;
            $dbName = self::$config['database'] ?? 'doubletick_b24';
            $user = self::$config['username'] ?? 'root';
            $pass = self::$config['password'] ?? '';

            $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";
            self::$instance = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }

        self::runMigrations();

        return self::$instance;
    }

    public static function runMigrations(): void
    {
        $schemaFile = dirname(__DIR__, 2) . '/database/schema.sql';
        if (file_exists($schemaFile) && self::$instance !== null) {
            $sql = file_get_contents($schemaFile);
            if ($sql) {
                // Execute individual statements
                $statements = array_filter(array_map('trim', explode(';', $sql)));
                foreach ($statements as $stmt) {
                    if ($stmt !== '') {
                        try {
                            self::$instance->exec($stmt);
                        } catch (PDOException $e) {
                            // Suppress table exists or index exists errors
                        }
                    }
                }
            }
        }
    }
}
