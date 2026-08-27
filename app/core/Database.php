<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $dbPath = __DIR__.'/../../storage/licenses.db';
        $dir = dirname($dbPath);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        try {
            self::$pdo = new PDO('sqlite:'.$dbPath);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$pdo->exec('PRAGMA journal_mode=WAL');
            self::migrate();
        } catch (PDOException $e) {
            throw new RuntimeException('Database connection failed: '.$e->getMessage());
        }

        return self::$pdo;
    }

    private static function migrate(): void
    {
        $pdo = self::$pdo;

        $pdo->exec('CREATE TABLE IF NOT EXISTS license_keys (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            license_key TEXT NOT NULL UNIQUE,
            status TEXT NOT NULL DEFAULT "inactive",
            meta TEXT,
            domain TEXT,
            activated_at TEXT,
            expires_at TEXT,
            last_verified_at TEXT,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )');

        $pdo->exec('CREATE TABLE IF NOT EXISTS activation_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            license_key TEXT NOT NULL,
            domain TEXT,
            ip TEXT,
            status TEXT NOT NULL,
            message TEXT,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )');

        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_license_keys_key ON license_keys(license_key)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_license_keys_status ON license_keys(status)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_activation_logs_license ON activation_logs(license_key)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_activation_logs_created ON activation_logs(created_at)');

        $stmt = $pdo->query("PRAGMA table_info(license_keys)");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 1);
        if (!in_array('secondary_domain', $columns, true)) {
            $pdo->exec('ALTER TABLE license_keys ADD COLUMN secondary_domain TEXT');
        }
    }
}
