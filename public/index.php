<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use App\Core\Auth;
use App\Core\Env;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\LicenseController;
use App\Controllers\LogController;
use App\Controllers\ApiController;

Env::load(__DIR__.'/../.env');

if (php_sapi_name() !== 'cli') {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$path = rtrim($path, '/');
if ($path === '') {
    $path = '/';
}

$isApi = str_starts_with((string) $path, '/api');

if ($isApi) {
    $controller = new ApiController();

    match ($path) {
        '/api/verify' => $controller->verify(),
        '/api/activate' => $controller->activate(),
        default => $controller->json(404, ['error' => 'Not found.']),
    };

    exit;
}

if ($path === '/login') {
    $controller = new AuthController();

    if ($method === 'POST') {
        $controller->loginPost();
    } else {
        $controller->loginPage();
    }

    exit;
}

if ($path === '/logout') {
    (new AuthController())->logout();
    exit;
}

Auth::require();

$controller = match ($path) {
    '/' => new DashboardController(),
    '/dashboard' => new DashboardController(),
    '/licenses' => new LicenseController(),
    '/licenses/create' => new LicenseController(),
    '/logs' => new LogController(),
    default => null,
};

if ($controller === null) {
    http_response_code(404);
    require __DIR__.'/../views/partials/header.php';
    require __DIR__.'/../views/errors/404.php';
    require __DIR__.'/../views/partials/footer.php';
    exit;
}

$action = match ($path) {
    '/licenses/create' => 'create',
    default => 'index',
};

if (isset($_GET['action'])) {
    $action = $_GET['action'];
}

match ($action) {
    'create' => $controller->create(),
    'edit' => $controller->edit((int) ($_GET['id'] ?? 0)),
    'delete' => $controller->delete((int) ($_GET['id'] ?? 0)),
    'show' => $controller->show((int) ($_GET['id'] ?? 0)),
    default => $controller->index(),
};
