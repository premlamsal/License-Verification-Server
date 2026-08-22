<?php $admin = true; $title = 'License Keys'; require __DIR__.'/../partials/header.php'; ?>
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-900">License Keys</h2>
        <a href="/licenses/create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
            + New License
        </a>
    </div>

    <?php if (isset($success) && $success !== ''): ?>
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded"><?php echo htmlspecialchars((string) $success); ?></div>
    <?php endif; ?>

    <?php if (isset($error) && $error !== ''): ?>
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded"><?php echo htmlspecialchars((string) $error); ?></div>
    <?php endif; ?>

    <?php if (isset($key) && isset($logs)): ?>
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">License Details</h3>
                <div class="space-x-3">
                    <a href="/licenses/edit?id=<?php echo (int) $key->id; ?>" class="text-emerald-600 hover:text-emerald-800">Edit</a>
                    <a href="/licenses" class="text-gray-600 hover:text-gray-800">Back to List</a>
                </div>
            </div>
            <div class="p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm text-gray-500">License Key</dt>
                        <dd class="mt-1 font-mono text-sm bg-gray-50 p-2 rounded flex items-center justify-between">
                            <span><?php echo htmlspecialchars((string) $key->license_key); ?></span>
                            <button onclick="navigator.clipboard.writeText('<?php echo htmlspecialchars((string) $key->license_key); ?>'); this.textContent='Copied!'; setTimeout(()=>this.textContent='Copy',1500)" class="text-xs bg-gray-200 hover:bg-gray-300 px-2 py-1 rounded ml-2">Copy</button>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Status</dt>
                        <dd class="mt-1">
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
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Active Domain</dt>
                        <dd class="mt-1 text-sm"><?php echo htmlspecialchars((string) ($key->domain ?? 'Not activated')); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Created At</dt>
                        <dd class="mt-1 text-sm"><?php echo htmlspecialchars((string) ($key->created_at ?? '-')); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Expires At</dt>
                        <dd class="mt-1 text-sm"><?php echo htmlspecialchars((string) ($key->expires_at ?? 'Never')); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Plan</dt>
                        <dd class="mt-1 text-sm"><?php echo htmlspecialchars((string) ($key->metaArray()['plan'] ?? 'standard')); ?></dd>
                    </div>
                </dl>

                <div class="mt-6 pt-6 border-t border-gray-100">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Copy to Gangotri App</h4>
                    <button onclick="copyConfig(<?php echo (int) $key->id; ?>, '<?php echo htmlspecialchars((string) $key->license_key); ?>')" class="bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                        Copy .env Config
                    </button>
                    <p class="text-xs text-gray-500 mt-1">Copies LICENSE_KEY and server URL to clipboard. Paste into gangotri/.env</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900">Activation Logs</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-gray-500">Domain</th>
                            <th class="px-6 py-3 text-left text-gray-500">IP</th>
                            <th class="px-6 py-3 text-left text-gray-500">Status</th>
                            <th class="px-6 py-3 text-left text-gray-500">Message</th>
                            <th class="px-6 py-3 text-left text-gray-500">Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($logs)): ?>
                            <tr><td colspan="5" class="px-6 py-6 text-center text-gray-500">No logs yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3"><?php echo htmlspecialchars((string) ($log->domain ?? '-')); ?></td>
                                    <td class="px-6 py-3 font-mono text-xs"><?php echo htmlspecialchars((string) ($log->ip ?? '-')); ?></td>
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

        <script>
        function copyConfig(id, key) {
            const serverUrl = window.location.origin;
            const text = 'LICENSE_KEY=' + key + '\nLICENSE_SERVER_URL=' + serverUrl + '\nLICENSE_SECRET=your_shared_secret_here';
            navigator.clipboard.writeText(text).then(() => {
                const btn = event.target;
                btn.textContent = 'Copied!';
                setTimeout(() => btn.textContent = 'Copy .env Config', 1500);
            });
        }
        </script>

    <?php elseif (isset($key)): ?>
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900"><?php echo isset($key->id) ? 'Edit License Key' : 'Create License Key'; ?></h3>
            </div>
            <div class="p-6">
                <form method="POST" action="<?php echo htmlspecialchars(isset($key->id) ? '/licenses/edit?id='.(int) $key->id : '/licenses/create'); ?>">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">License Key</label>
                            <input type="text" name="license_key" value="<?php echo htmlspecialchars((string) ($key->license_key ?? '')); ?>" <?php echo isset($key->id) ? 'readonly' : ''; ?>
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm <?php echo isset($key->id) ? 'bg-gray-100' : ''; ?>"
                                placeholder="Auto-generated" <?php echo isset($key->id) ? '' : 'required'; ?>>
                            <?php if (!isset($key->id)): ?>
                                <p class="mt-1 text-xs text-gray-500">A unique key is auto-generated. Change it if you want.</p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Domain <span class="text-red-500">*</span></label>
                            <input type="text" name="domain" value="<?php echo htmlspecialchars((string) ($key->domain ?? '')); ?>" <?php echo isset($key->id) ? 'readonly' : ''; ?>
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm <?php echo isset($key->id) ? 'bg-gray-100' : ''; ?>"
                                placeholder="example.com" required>
                            <p class="mt-1 text-xs text-gray-500">This license will only work on this domain. It cannot be changed later.</p>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Created At</label>
                                <input type="date" name="created_at" value="<?php echo htmlspecialchars((string) ($key->created_at ?? date('Y-m-d'))); ?>"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Expires At</label>
                                <input type="date" name="expires_at" value="<?php echo htmlspecialchars((string) ($key->expires_at ?? '')); ?>"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <p class="mt-1 text-xs text-gray-500">Leave empty for no expiration.</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="inactive" <?php echo ($key->status ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                <option value="active" <?php echo ($key->status ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="expired" <?php echo ($key->status ?? '') === 'expired' ? 'selected' : ''; ?>>Expired</option>
                                <option value="revoked" <?php echo ($key->status ?? '') === 'revoked' ? 'selected' : ''; ?>>Revoked</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Meta (JSON)</label>
                            <textarea name="meta" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm font-mono"><?php echo htmlspecialchars((string) ($key->meta ?? '{}')); ?></textarea>
                        </div>
                        <div class="flex items-center space-x-3">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                                <?php echo isset($key->id) ? 'Update' : 'Create License'; ?>
                            </button>
                            <a href="/licenses" class="text-gray-600 hover:text-gray-800">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php elseif (isset($keys)): ?>
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-gray-500">Key</th>
                            <th class="px-6 py-3 text-left text-gray-500">Status</th>
                            <th class="px-6 py-3 text-left text-gray-500">Domain</th>
                            <th class="px-6 py-3 text-left text-gray-500">Expires</th>
                            <th class="px-6 py-3 text-left text-gray-500">Created</th>
                            <th class="px-6 py-3 text-left text-gray-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($keys)): ?>
                            <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">No license keys found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($keys as $k): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3 font-mono text-xs"><?php echo htmlspecialchars((string) $k->license_key); ?></td>
                                    <td class="px-6 py-3">
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                                            <?php echo match($k->status) {
                                                'active' => 'bg-green-100 text-green-800',
                                                'inactive' => 'bg-gray-100 text-gray-800',
                                                'expired' => 'bg-red-100 text-red-800',
                                                'revoked' => 'bg-orange-100 text-orange-800',
                                                default => 'bg-gray-100 text-gray-800',
                                            }; ?>">
                                            <?php echo htmlspecialchars((string) $k->status); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-3"><?php echo htmlspecialchars((string) ($k->domain ?? '-')); ?></td>
                                    <td class="px-6 py-3"><?php echo $k->expires_at ? htmlspecialchars((string) $k->expires_at) : '-'; ?></td>
                                    <td class="px-6 py-3 whitespace-nowrap"><?php echo htmlspecialchars((string) ($k->created_at ?? '-')); ?></td>
                                    <td class="px-6 py-3 whitespace-nowrap space-x-3">
                                        <a href="/licenses/show?id=<?php echo (int) $k->id; ?>" class="text-blue-600 hover:text-blue-800">View</a>
                                        <a href="/licenses/edit?id=<?php echo (int) $k->id; ?>" class="text-emerald-600 hover:text-emerald-800">Edit</a>
                                        <a href="/licenses/delete?id=<?php echo (int) $k->id; ?>" class="text-red-600 hover:text-red-800" onclick="return confirm('Delete this key?')">Delete</a>
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