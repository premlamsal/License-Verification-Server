<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Env;

class AuthController
{
    public function loginPage(): void
    {
        if (Auth::check()) {
            $this->redirect('/');
        }

        require __DIR__.'/../../views/auth/login.php';
    }

    public function loginPost(): void
    {
        if (Auth::check()) {
            $this->redirect('/');
        }

        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($username === '' || $password === '') {
                $error = 'Please enter both username and password.';
            } elseif (Auth::login($username, $password)) {
                $this->redirect('/');
            } else {
                $error = 'Invalid username or password.';
            }
        }

        require __DIR__.'/../../views/auth/login.php';
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('/login');
    }

    private function redirect(string $path): void
    {
        header('Location: '.$path);
        exit;
    }
}
