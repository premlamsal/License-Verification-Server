<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Models\ActivationLog;
use App\Models\LicenseKey;

class DashboardController
{
    public function index(): void
    {
        Auth::require();
        $stats = LicenseKey::stats();
        $recentLogs = ActivationLog::all(10);
        require __DIR__.'/../../views/admin/dashboard.php';
    }
}
