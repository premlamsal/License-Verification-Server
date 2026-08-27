<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use DateTime;
use PDO;

class LicenseKey
{
    public int $id;
    public string $license_key;
    public string $status;
    public ?string $meta;
    public ?string $domain;
    public ?string $secondary_domain;
    public ?string $activated_at;
    public ?string $expires_at;
    public ?string $last_verified_at;
    public string $created_at;

    public static function all(): array
    {
        $stmt = Database::connection()->query('SELECT * FROM license_keys ORDER BY created_at DESC');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return array_map([self::class, 'fromArray'], $rows);
    }

    public static function find(int $id): ?LicenseKey
    {
        $stmt = Database::connection()->prepare('SELECT * FROM license_keys WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (! $row) {
            return null;
        }

        return self::fromArray($row);
    }

    public static function findByKey(string $licenseKey): ?LicenseKey
    {
        $stmt = Database::connection()->prepare('SELECT * FROM license_keys WHERE license_key = :key LIMIT 1');
        $stmt->execute(['key' => $licenseKey]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (! $row) {
            return null;
        }

        return self::fromArray($row);
    }

    public static function create(array $data): LicenseKey
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('INSERT INTO license_keys (license_key, status, domain, secondary_domain, meta, expires_at) VALUES (:license_key, :status, :domain, :secondary_domain, :meta, :expires_at)');

        $stmt->execute([
            'license_key' => $data['license_key'],
            'status' => $data['status'] ?? 'inactive',
            'domain' => $data['domain'] ?? null,
            'secondary_domain' => $data['secondary_domain'] ?? null,
            'meta' => $data['meta'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
        ]);

        return self::find((int) $pdo->lastInsertId());
    }

    public static function update(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];

        $allowed = ['status', 'meta', 'domain', 'secondary_domain', 'activated_at', 'expires_at', 'last_verified_at'];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = :{$field}";
                $params[$field] = $data[$field];
            }
        }

        if ($fields === []) {
            return false;
        }

        $sql = 'UPDATE license_keys SET '.implode(', ', $fields).' WHERE id = :id';

        return Database::connection()->prepare($sql)->execute($params);
    }

    public static function delete(int $id): bool
    {
        return Database::connection()->prepare('DELETE FROM license_keys WHERE id = :id')->execute(['id' => $id]);
    }

    public static function stats(): array
    {
        $pdo = Database::connection();

        $total = (int) $pdo->query('SELECT COUNT(*) FROM license_keys')->fetchColumn();
        $active = (int) $pdo->query('SELECT COUNT(*) FROM license_keys WHERE status = "active"')->fetchColumn();
        $expired = (int) $pdo->query('SELECT COUNT(*) FROM license_keys WHERE status = "expired"')->fetchColumn();
        $revoked = (int) $pdo->query('SELECT COUNT(*) FROM license_keys WHERE status = "revoked"')->fetchColumn();
        $today = (int) $pdo->query('SELECT COUNT(*) FROM activation_logs WHERE date(created_at) = date("now")')->fetchColumn();

        return compact('total', 'active', 'expired', 'revoked', 'today');
    }

    public static function fromArray(array $row): LicenseKey
    {
        $model = new self();
        $model->id = (int) $row['id'];
        $model->license_key = (string) $row['license_key'];
        $model->status = (string) $row['status'];
        $model->meta = $row['meta'] ?? null;
        $model->domain = $row['domain'] ?? null;
        $model->secondary_domain = $row['secondary_domain'] ?? null;
        $model->activated_at = $row['activated_at'] ?? null;
        $model->expires_at = $row['expires_at'] ?? null;
        $model->last_verified_at = $row['last_verified_at'] ?? null;
        $model->created_at = (string) $row['created_at'];

        return $model;
    }

    public function metaArray(): array
    {
        $decoded = json_decode((string) $this->meta, true);

        return is_array($decoded) ? $decoded : [];
    }
}
