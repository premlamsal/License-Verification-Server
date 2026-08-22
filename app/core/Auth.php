<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\LicenseKey;
use App\Models\ActivationLog;

class Auth
{
    public static function username(): string
    {
        return Env::required('ADMIN_USERNAME');
    }

    public static function checkPassword(string $password): bool
    {
        return hash_equals(Env::required('ADMIN_PASSWORD'), $password);
    }

    public static function sessionStarted(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    public static function login(string $username, string $password): bool
    {
        if ($username !== self::username() || ! self::checkPassword($password)) {
            return false;
        }

        if (! self::sessionStarted()) {
            session_name(Env::get('ADMIN_SESSION_NAME', 'license_admin_session'));
            session_start();
        }

        session_regenerate_id(true);

        $_SESSION['admin_authenticated'] = true;
        $_SESSION['admin_login_time'] = time();

        return true;
    }

    public static function logout(): void
    {
        if (self::sessionStarted()) {
            $_SESSION = [];
            session_destroy();
        }
    }

    public static function check(): bool
    {
        if (! self::sessionStarted()) {
            session_name(Env::get('ADMIN_SESSION_NAME', 'license_admin_session'));
            session_start();
        }

        if (empty($_SESSION['admin_authenticated'])) {
            return false;
        }

        if (isset($_SESSION['admin_login_time']) && (time() - $_SESSION['admin_login_time']) > 86400) {
            self::logout();

            return false;
        }

        return true;
    }

    public static function require(): void
    {
        if (! self::check()) {
            self::logout();
            header('Location: /login');
            exit;
        }
    }
}
