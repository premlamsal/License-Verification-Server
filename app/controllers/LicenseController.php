<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Models\ActivationLog;
use App\Models\LicenseKey;

class LicenseController
{
    public function index(): void
    {
        Auth::require();
        $keys = LicenseKey::all();
        require __DIR__.'/../../views/admin/licenses.php';
    }

    public function create(): void
    {
        Auth::require();
        $keys = LicenseKey::all();
        $error = '';
        $success = '';

        $key = new \stdClass();
        $key->license_key = $this->generateUniqueKey();
        $key->domain = '';
        $key->secondary_domain = '';
        $key->status = 'inactive';
        $key->expires_at = date('Y-m-d', strtotime('+1 year'));
        $key->meta = '{"plan":"standard"}';
        $key->created_at = date('Y-m-d');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $licenseKey = trim((string) ($_POST['license_key'] ?? ''));
            $domain = trim((string) ($_POST['domain'] ?? ''));
            $secondaryDomain = trim((string) ($_POST['secondary_domain'] ?? ''));
            $expiresAt = trim((string) ($_POST['expires_at'] ?? ''));
            $metaJson = trim((string) ($_POST['meta'] ?? '{}'));
            $status = trim((string) ($_POST['status'] ?? 'inactive'));
            $createdAt = trim((string) ($_POST['created_at'] ?? date('Y-m-d')));

            if ($licenseKey === '') {
                $error = 'License key is required.';
            } elseif ($domain === '') {
                $error = 'Domain is required.';
            } elseif (LicenseKey::findByKey($licenseKey) !== null) {
                $error = 'This license key already exists.';
            } else {
                $meta = json_decode($metaJson, true);

                if ($metaJson !== '' && ! is_array($meta)) {
                    $error = 'Meta must be valid JSON.';
                } else {
                    LicenseKey::create([
                        'license_key' => $licenseKey,
                        'status' => $status,
                        'domain' => $domain,
                        'secondary_domain' => $secondaryDomain !== '' ? $secondaryDomain : null,
                        'meta' => $metaJson === '' ? null : $metaJson,
                        'expires_at' => $expiresAt !== '' ? $expiresAt : null,
                    ]);
                    $success = 'License key created successfully.';
                    $key = new \stdClass();
                    $key->license_key = $this->generateUniqueKey();
                    $key->domain = '';
                    $key->status = 'inactive';
                    $key->expires_at = date('Y-m-d', strtotime('+1 year'));
                    $key->meta = '{"plan":"standard"}';
                    $key->created_at = date('Y-m-d');
                }
            }
        }

        require __DIR__.'/../../views/admin/licenses.php';
    }

    private function generateUniqueKey(): string
    {
        do {
            $key = strtoupper(bin2hex(random_bytes(8)));
            $formatted = substr($key, 0, 4).'-'.substr($key, 4, 4).'-'.substr($key, 8, 4).'-'.substr($key, 12, 4);
        } while (LicenseKey::findByKey($formatted) !== null);

        return $formatted;
    }

    public function edit(int $id): void
    {
        Auth::require();
        $keys = LicenseKey::all();
        $key = LicenseKey::find($id);
        $error = '';
        $success = '';

        if (! $key) {
            $this->redirect('/licenses');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $expiresAt = trim((string) ($_POST['expires_at'] ?? ''));
            $metaJson = trim((string) ($_POST['meta'] ?? '{}'));
            $status = trim((string) ($_POST['status'] ?? $key->status));
            $secondaryDomain = trim((string) ($_POST['secondary_domain'] ?? ''));

            $meta = json_decode($metaJson, true);

            if ($metaJson !== '' && ! is_array($meta)) {
                $error = 'Meta must be valid JSON.';
            } else {
                LicenseKey::update($id, [
                    'status' => $status,
                    'secondary_domain' => $secondaryDomain !== '' ? $secondaryDomain : null,
                    'meta' => $metaJson === '' ? null : $metaJson,
                    'expires_at' => $expiresAt !== '' ? $expiresAt : null,
                ]);
                $success = 'License key updated successfully.';
                $key = LicenseKey::find($id);
            }
        }

        require __DIR__.'/../../views/admin/licenses.php';
    }

    public function delete(int $id): void
    {
        Auth::require();
        LicenseKey::delete($id);
        $this->redirect('/licenses');
    }

    public function show(int $id): void
    {
        Auth::require();
        $key = LicenseKey::find($id);
        if (! $key) {
            $this->redirect('/licenses');
        }
        $logs = ActivationLog::forKey($key->license_key, 100);
        require __DIR__.'/../../views/admin/licenses.php';
    }

    private function redirect(string $path): void
    {
        header('Location: '.$path);
        exit;
    }
}
