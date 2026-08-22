<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use DateTime;
use PDO;

class ActivationLog
{
    public int $id;
    public string $license_key;
    public ?string $domain;
    public ?string $ip;
    public string $status;
    public string $message;
    public string $created_at;

    public static function all(?int $limit = 100, ?int $offset = 0): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT * FROM activation_logs ORDER BY created_at DESC LIMIT :limit OFFSET :offset');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return array_map([self::class, 'fromArray'], $rows);
    }

    public static function forKey(string $licenseKey, int $limit = 50): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT * FROM activation_logs WHERE license_key = :key ORDER BY created_at DESC LIMIT :limit');
        $stmt->execute(['key' => $licenseKey, 'limit' => $limit]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return array_map([self::class, 'fromArray'], $rows);
    }

    public static function create(string $licenseKey, ?string $domain, ?string $ip, string $status, string $message): ActivationLog
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('INSERT INTO activation_logs (license_key, domain, ip, status, message) VALUES (:license_key, :domain, :ip, :status, :message)');

        $stmt->execute([
            'license_key' => $licenseKey,
            'domain' => $domain,
            'ip' => $ip,
            'status' => $status,
            'message' => $message,
        ]);

        $id = (int) $pdo->lastInsertId();

        return self::find($id);
    }

    public static function find(int $id): ?ActivationLog
    {
        $stmt = Database::connection()->prepare('SELECT * FROM activation_logs WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (! $row) {
            return null;
        }

        return self::fromArray($row);
    }

    public static function fromArray(array $row): ActivationLog
    {
        $model = new self();
        $model->id = (int) $row['id'];
        $model->license_key = (string) $row['license_key'];
        $model->domain = $row['domain'] ?? null;
        $model->ip = $row['ip'] ?? null;
        $model->status = (string) $row['status'];
        $model->message = (string) $row['message'];
        $model->created_at = (string) $row['created_at'];

        return $model;
    }
}
