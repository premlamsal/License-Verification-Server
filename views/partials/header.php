<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars((string) ($title ?? 'License Server')); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-50">
    <?php if (isset($admin) && $admin): ?>
    <div class="min-h-screen flex">
        <aside class="w-64 bg-gray-900 text-gray-100 flex flex-col">
            <div class="p-4 border-b border-gray-800">
                <h1 class="text-lg font-bold">License Server</h1>
                <p class="text-xs text-gray-400 mt-1">Super Admin Panel</p>
            </div>
            <nav class="flex-1 p-4 space-y-1">
                <a href="/dashboard" class="block px-3 py-2 rounded hover:bg-gray-800 <?php echo ($_SERVER['REQUEST_URI'] ?? '') === '/dashboard' ? 'bg-gray-800' : ''; ?>">Dashboard</a>
                <a href="/licenses" class="block px-3 py-2 rounded hover:bg-gray-800 <?php echo str_starts_with((string) ($_SERVER['REQUEST_URI'] ?? ''), '/licenses') ? 'bg-gray-800' : ''; ?>">License Keys</a>
                <a href="/logs" class="block px-3 py-2 rounded hover:bg-gray-800 <?php echo str_starts_with((string) ($_SERVER['REQUEST_URI'] ?? ''), '/logs') ? 'bg-gray-800' : ''; ?>">Activation Logs</a>
            </nav>
            <div class="p-4 border-t border-gray-800">
                <form method="POST" action="/logout" class="flex items-center justify-between">
                    <span class="text-sm text-gray-400"><?php echo htmlspecialchars((string) App\Core\Auth::username()); ?></span>
                    <button type="submit" class="text-sm text-red-400 hover:text-red-300">Logout</button>
                </form>
            </div>
        </aside>
        <main class="flex-1 overflow-auto">
    <?php endif; ?>

    <div class="max-w-6xl mx-auto p-6">