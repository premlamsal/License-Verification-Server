<?php
http_response_code(404);
$admin = true;
$title = 'Not Found';
require __DIR__.'/../partials/header.php';
?>
<div class="text-center py-20">
    <h1 class="text-4xl font-bold text-gray-900 mb-4">404</h1>
    <p class="text-gray-600 mb-6">Page not found.</p>
    <a href="/dashboard" class="text-blue-600 hover:text-blue-800">Back to dashboard</a>
</div>
<?php require __DIR__.'/../partials/footer.php'; ?>
