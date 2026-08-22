<?php $admin = true; $title = 'License Keys'; require __DIR__.'/../partials/header.php'; ?>
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-900">License Keys</h2>
        <?php if (! isset($key)): ?>
            <a href="/licenses/create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                Create Key
            </a>
        <?php endif; ?>
    </div>

    <?php if (isset($success) && $success !== ''): ?>
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
            <?php echo htmlspecialchars((string) $success); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error) && $error !== ''): ?>
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
            <?php echo htmlspecialchars((string) $error); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($key) && ! isset($logs)): ?>
        <?php $action = isset($_GET['action']) && $_GET['action'] === 'create' ? '/licenses/create' : '/licenses/edit?id=' . (int) $key->id; ?>
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h3 class="text-lg font-semibold mb-4"><?php echo isset($_GET['action']) && $_GET['action'] === 'create' ? 'Create License Key' : 'Edit License Key'; ?></h3>
            <form method="POST" class="space-y-4 max-w-2xl">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">License Key</label>
                    <input type="text" name="license_key" value="<?php echo htmlspecialchars((string) ($key->license_key ?? '')); ?>" <?php echo isset($_GET['action']) && $_GET['action'] === 'edit' ? 'readonly' : ''; ?>
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all"
                           placeholder="XXXXX-XXXXX-XXXXX-XXXXX">
                    <?php if (isset($_GET['action']) && $_GET['action'] === 'edit'): ?>
                        <p class="text-xs text-gray-500 mt-1">License key cannot be changed after creation.</p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all">
                        <?php foreach (['inactive', 'active', 'expired', 'revoked'] as $status): ?>
                            <option value="<?php echo $status; ?>" <?php echo ($key->status ?? '') === $status ? 'selected' : ''; ?>>
                                <?php echo ucfirst($status); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Expires At</label>
                    <input type="datetime-local" name="expires_at" value="<?php echo $key->expires_at ? date('Y-m-d\TH:i', strtotime((string) $key->expires_at)) : ''; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all">
                    <p class="text-xs text-gray-500 mt-1">Leave empty for no expiration.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Meta (JSON)</label>
                    <textarea name="meta" rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all font-mono text-sm"
                              placeholder='{"plan": "pro", "features": ["all"]}'><?php echo htmlspecialchars((string) ($key->meta ?? '{}')); ?></textarea>
                    <p class="text-xs text-gray-500 mt-1">Optional metadata such as plan or features.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <?php echo isset($_GET['action']) && $_GET['action'] === 'create' ? 'Create' : 'Update'; ?>
                    </button>
                    <a href="/licenses" class="text-gray-600 hover:text-gray-800 px-4 py-2">Cancel</a>
                </div>
            </form>
        </div>
    <?php elseif (isset($key) && isset($logs)): ?>
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">License Details</h3>
                    <p class="text-sm text-gray-600 mt-1">Key: <span class="font-mono"><?php echo htmlspecialchars((string) $key->license_key); ?></span></p>
                </div>
                <a href="/licenses/edit?id=<?php echo (int) $key->id; ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">Edit</a>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div><span class="text-gray-500">Status:</span> <span class="font-medium"><?php echo htmlspecialchars((string) $key->status); ?></span></div>
                <div><span class="text-gray-500">Domain:</span> <span class="font-medium"><?php echo htmlspecialchars((string) ($key->domain ?? '-')); ?></span></div>
                <div><span class="text-gray-500">Activated At:</span> <span class="font-medium"><?php echo $key->activated_at ? htmlspecialchars((string) $key->activated_at) : '-'; ?></span></div>
                <div><span class="text-gray-500">Expires At:</span> <span class="font-medium"><?php echo $key->expires_at ? htmlspecialchars((string) $key->expires_at) : '-'; ?></span></div>
                <div class="sm:col-span-2"><span class="text-gray-500">Meta:</span> <pre class="mt-1 bg-gray-50 border border-gray-200 rounded p-3 text-xs overflow-x-auto"><?php echo htmlspecialchars((string) ($key->meta ?? '{}')); ?></pre></div>
            </div>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 mt-6">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900">Activation Logs</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-gray-500">Status</th>
                            <th class="px-6 py-3 text-left text-gray-500">Domain</th>
                            <th class="px-6 py-3 text-left text-gray-500">IP</th>
                            <th class="px-6 py-3 text-left text-gray-500">Message</th>
                            <th class="px-6 py-3 text-left text-gray-500">Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($logs)): ?>
                            <tr><td colspan="5" class="px-6 py-6 text-center text-gray-500">No logs found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <tr class="hover:bg-gray-50">
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
                                    <td class="px-6 py-3"><?php echo htmlspecialchars((string) ($log->domain ?? '-')); ?></td>
                                    <td class="px-6 py-3"><?php echo htmlspecialchars((string) ($log->ip ?? '-')); ?></td>
                                    <td class="px-6 py-3"><?php echo htmlspecialchars((string) $log->message); ?></td>
                                    <td class="px-6 py-3 whitespace-nowrap"><?php echo htmlspecialchars((string) $log->created_at); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-gray-500">ID</th>
                            <th class="px-6 py-3 text-left text-gray-500">License Key</th>
                            <th class="px-6 py-3 text-left text-gray-500">Status</th>
                            <th class="px-6 py-3 text-left text-gray-500">Domain</th>
                            <th class="px-6 py-3 text-left text-gray-500">Expires</th>
                            <th class="px-6 py-3 text-left text-gray-500">Created</th>
                            <th class="px-6 py-3 text-left text-gray-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($keys)): ?>
                            <tr><td colspan="7" class="px-6 py-8 text-center text-gray-500">No license keys found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($keys as $key): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3"><?php echo (int) $key->id; ?></td>
                                    <td class="px-6 py-3 font-mono text-xs"><?php echo htmlspecialchars((string) $key->license_key); ?></td>
                                    <td class="px-6 py-3">
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                                            <?php echo match($key->status) {
                                                'active' => 'bg-green-100 text-green-800',
                                                'inactive' => 'bg-gray-100 text-gray-800',
                                                'expired' => 'bg-red-100 text-red-800',
                                                'revoked' => 'bg-orange-100 text-orange-800',
                                                default => 'bg-gray-100 text-gray-800',
                                            }; ?>">
                                            <?php echo htmlspecialchars((string) $key->status); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-3"><?php echo htmlspecialchars((string) ($key->domain ?? '-')); ?></td>
                                    <td class="px-6 py-3"><?php echo $key->expires_at ? htmlspecialchars((string) $key->expires_at) : '-'; ?></td>
                                    <td class="px-6 py-3 whitespace-nowrap"><?php echo htmlspecialchars((string) $key->created_at); ?></td>
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        <a href="/licenses/show?id=<?php echo (int) $key->id; ?>" class="text-blue-600 hover:text-blue-800 mr-3">View</a>
                                        <a href="/licenses/edit?id=<?php echo (int) $key->id; ?>" class="text-emerald-600 hover:text-emerald-800 mr-3">Edit</a>
                                        <a href="/licenses/delete?id=<?php echo (int) $key->id; ?>" class="text-red-600 hover:text-red-800" onclick="return confirm('Delete this key?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php require __DIR__.'/../partials/footer.php'; ?>
