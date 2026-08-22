<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Models\ActivationLog;

class LogController
{
    public function index(): void
    {
        Auth::require();
        $logs = ActivationLog::all(200);
        require __DIR__.'/../../views/admin/logs.php';
    }
}
