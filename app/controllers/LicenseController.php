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
        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $licenseKey = trim((string) ($_POST['license_key'] ?? ''));
            $expiresAt = trim((string) ($_POST['expires_at'] ?? ''));
            $metaJson = trim((string) ($_POST['meta'] ?? '{}'));
            $status = trim((string) ($_POST['status'] ?? 'inactive'));

            if ($licenseKey === '') {
                $error = 'License key is required.';
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
                        'meta' => $metaJson === '' ? null : $metaJson,
                        'expires_at' => $expiresAt !== '' ? $expiresAt : null,
                    ]);
                    $success = 'License key created successfully.';
                }
            }
        }

        require __DIR__.'/../../views/admin/licenses.php';
    }

    public function edit(int $id): void
    {
        Auth::require();
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

            $meta = json_decode($metaJson, true);

            if ($metaJson !== '' && ! is_array($meta)) {
                $error = 'Meta must be valid JSON.';
            } else {
                LicenseKey::update($id, [
                    'status' => $status,
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
