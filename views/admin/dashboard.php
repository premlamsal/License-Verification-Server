<?php $admin = true; $title = 'Dashboard'; require __DIR__.'/../partials/header.php'; ?>
<div class="space-y-6">
    <h2 class="text-2xl font-bold text-gray-900">Dashboard</h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="text-sm text-gray-500">Total Keys</div>
            <div class="text-2xl font-bold text-gray-900"><?php echo (int) ($stats['total'] ?? 0); ?></div>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="text-sm text-gray-500">Active</div>
            <div class="text-2xl font-bold text-green-700"><?php echo (int) ($stats['active'] ?? 0); ?></div>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="text-sm text-gray-500">Expired</div>
            <div class="text-2xl font-bold text-red-700"><?php echo (int) ($stats['expired'] ?? 0); ?></div>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="text-sm text-gray-500">Revoked</div>
            <div class="text-2xl font-bold text-gray-700"><?php echo (int) ($stats['revoked'] ?? 0); ?></div>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="text-sm text-gray-500">Today's Requests</div>
            <div class="text-2xl font-bold text-blue-700"><?php echo (int) ($stats['today'] ?? 0); ?></div>
        </div>
    </div>

    <div class="bg-white rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900">Recent Activation Logs</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-gray-500">License Key</th>
                        <th class="px-6 py-3 text-left text-gray-500">Domain</th>
                        <th class="px-6 py-3 text-left text-gray-500">Status</th>
                        <th class="px-6 py-3 text-left text-gray-500">Message</th>
                        <th class="px-6 py-3 text-left text-gray-500">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($recentLogs)): ?>
                        <tr><td colspan="5" class="px-6 py-6 text-center text-gray-500">No logs yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentLogs as $log): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 font-mono text-xs"><?php echo htmlspecialchars((string) $log->license_key); ?></td>
                                <td class="px-6 py-3"><?php echo htmlspecialchars((string) ($log->domain ?? '-')); ?></td>
                                <td class="px-6 py-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                                        <?php echo match($log->status) {
                                            'verified','activated' => 'bg-green-100 text-green-800',
                                            'invalid' => 'bg-red-100 text-red-800',
                                            'expired','revoked' => 'bg-orange-100 text-orange-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        }; ?>">
                                        <?php echo htmlspecialchars((string) $log->status); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-3"><?php echo htmlspecialchars((string) $log->message); ?></td>
                                <td class="px-6 py-3 whitespace-nowrap"><?php echo htmlspecialchars((string) $log->created_at); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require __DIR__.'/../partials/footer.php'; ?>
