<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Env;
use App\Models\ActivationLog;
use App\Models\LicenseKey;
use DateTime;
use RuntimeException;

class ApiController
{
    private function json(int $status, array $data): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    private function signatureValid(array $payload, ?string $signature): bool
    {
        if ($signature === null) {
            return false;
        }

        $secret = Env::get('LICENSE_SECRET', '');

        if ($secret === '') {
            return false;
        }

        $expected = hash_hmac('sha256', json_encode($payload), $secret);

        return hash_equals($expected, $signature);
    }

    private function clientIp(): ?string
    {
        return $_SERVER['REMOTE_ADDR'] ?? null;
    }

    private function log(string $licenseKey, ?string $domain, ?string $ip, string $status, string $message): void
    {
        ActivationLog::create($licenseKey, $domain, $ip, $status, $message);
    }

    public function verify(): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $headers = getallheaders();
        $signature = $headers['X-License-Signature'] ?? $headers['x-license-signature'] ?? null;

        if (! $this->signatureValid($input, $signature)) {
            $this->json(401, ['valid' => false, 'status' => 'inactive', 'message' => 'Invalid or missing signature.']);
        }

        $licenseKey = trim((string) ($input['license_key'] ?? ''));

        if ($licenseKey === '') {
            $this->json(400, ['valid' => false, 'status' => 'inactive', 'message' => 'license_key is required.']);
        }

        $key = LicenseKey::findByKey($licenseKey);

        if (! $key) {
            $this->log($licenseKey, $input['domain'] ?? null, $input['ip'] ?? $this->clientIp(), 'invalid', 'License key not found.');
            $this->json(200, ['valid' => false, 'status' => 'inactive', 'message' => 'License does not match. Please contact the application developer.']);
        }

        if ($key->status === 'revoked') {
            $this->log($licenseKey, $input['domain'] ?? null, $input['ip'] ?? $this->clientIp(), 'revoked', 'License has been revoked.');
            $this->json(200, ['valid' => false, 'status' => 'revoked', 'message' => 'License does not match. Please contact the application developer.']);
        }

        if ($key->status === 'inactive') {
            $this->log($licenseKey, $input['domain'] ?? null, $input['ip'] ?? $this->clientIp(), 'inactive', 'License is inactive.');
            $this->json(200, ['valid' => false, 'status' => 'inactive', 'message' => 'License does not match. Please contact the application developer.']);
        }

        if ($key->domain !== null && $key->domain !== ($input['domain'] ?? '')) {
            $this->log($licenseKey, $input['domain'] ?? null, $input['ip'] ?? $this->clientIp(), 'domain_mismatch', 'Domain mismatch.');
            $this->json(200, ['valid' => false, 'status' => 'inactive', 'message' => 'License does not match. Please contact the application developer.']);
        }

        if ($key->expires_at !== null) {
            $now = new DateTime();
            $expiresAt = new DateTime($key->expires_at);

            if ($expiresAt < $now) {
                LicenseKey::update($key->id, ['status' => 'expired']);
                $this->log($licenseKey, $input['domain'] ?? null, $input['ip'] ?? $this->clientIp(), 'expired', 'License expired.');
                $this->json(200, ['valid' => false, 'status' => 'expired', 'message' => 'License does not match. Please contact the application developer.']);
            }
        }

        LicenseKey::update($key->id, ['last_verified_at' => (new DateTime())->format('c')]);
        $this->log($licenseKey, $input['domain'] ?? null, $input['ip'] ?? $this->clientIp(), 'verified', 'License verified successfully.');

        $meta = $key->metaArray();

        $this->json(200, [
            'valid' => true,
            'status' => 'active',
            'meta' => $meta,
            'activated_at' => $key->activated_at,
            'expires_at' => $key->expires_at,
        ]);
    }

    public function activate(): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $headers = getallheaders();
        $signature = $headers['X-License-Signature'] ?? $headers['x-license-signature'] ?? null;

        if (! $this->signatureValid($input, $signature)) {
            $this->json(401, ['success' => false, 'message' => 'Invalid or missing signature.']);
        }

        $licenseKey = trim((string) ($input['license_key'] ?? ''));

        if ($licenseKey === '') {
            $this->json(400, ['success' => false, 'message' => 'license_key is required.']);
        }

        $key = LicenseKey::findByKey($licenseKey);

        if (! $key) {
            $this->log($licenseKey, $input['domain'] ?? null, $input['ip'] ?? $this->clientIp(), 'invalid', 'License key not found.');
            $this->json(200, ['success' => false, 'message' => 'License does not match. Please contact the application developer.']);
        }

        if ($key->status === 'revoked') {
            $this->log($licenseKey, $input['domain'] ?? null, $input['ip'] ?? $this->clientIp(), 'revoked', 'License revoked during activation.');
            $this->json(200, ['success' => false, 'message' => 'License does not match. Please contact the application developer.']);
        }

        if ($key->domain !== null && $key->domain !== ($input['domain'] ?? '')) {
            $this->log($licenseKey, $input['domain'] ?? null, $input['ip'] ?? $this->clientIp(), 'domain_mismatch', 'Domain mismatch during activation.');
            $this->json(200, ['success' => false, 'message' => 'License does not match. Please contact the application developer.']);
        }

        if ($key->expires_at !== null) {
            $now = new DateTime();
            $expiresAt = new DateTime($key->expires_at);

            if ($expiresAt < $now) {
                $this->log($licenseKey, $input['domain'] ?? null, $input['ip'] ?? $this->clientIp(), 'expired', 'License expired during activation.');
                $this->json(200, ['success' => false, 'message' => 'License does not match. Please contact the application developer.']);
            }
        }

        LicenseKey::update($key->id, [
            'status' => 'active',
            'domain' => $key->domain ?? ($input['domain'] ?? null),
            'activated_at' => (new DateTime())->format('c'),
        ]);

        $this->log($licenseKey, $input['domain'] ?? null, $input['ip'] ?? $this->clientIp(), 'activated', 'License activated successfully.');

        $this->json(200, [
            'success' => true,
            'message' => 'License activated successfully.',
            'meta' => $key->metaArray(),
            'expires_at' => $key->expires_at,
        ]);
    }
}
