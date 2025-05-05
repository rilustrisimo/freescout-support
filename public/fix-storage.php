<?php
// Script to fix missing vars.js issue
// ⚠️ IMPORTANT: Delete this file after using it!

// Basic security - use a random token to prevent unauthorized access
$token = 'gpm_fix_' . md5(date('Y-m-d'));

// Check if the token is correct
if (empty($_GET['token']) || $_GET['token'] !== $token) {
    echo "<p>Access denied. Please use the correct token.</p>";
    echo "<p>Use URL: fix-storage.php?token={$token}</p>";
    exit;
}

// Show any errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Output as plain text
header('Content-Type: text/plain');

echo "Starting storage fix...\n\n";

// Define the Laravel base path
$laravel_path = realpath(__DIR__ . '/..');

// Change to the Laravel base directory
chdir($laravel_path);

echo "Working directory: " . getcwd() . "\n\n";

echo "Step 1: Creating storage symlink...\n";
$output = [];
exec('php artisan storage:link 2>&1', $output, $return_var);
echo implode("\n", $output) . "\n";
echo "Return code: $return_var\n\n";

echo "Step 2: Generate vars.js file...\n";
$output = [];
exec('php artisan freescout:generate-vars 2>&1', $output, $return_var);
echo implode("\n", $output) . "\n";
echo "Return code: $return_var\n\n";

echo "Step 3: Clearing cache...\n";
$output = [];
exec('php artisan freescout:clear-cache 2>&1', $output, $return_var);
echo implode("\n", $output) . "\n";
echo "Return code: $return_var\n\n";

echo "Checking if vars.js exists...\n";
$vars_js_path = $laravel_path . '/storage/app/public/js/vars.js';
$public_vars_js_path = __DIR__ . '/storage/js/vars.js';

if (file_exists($vars_js_path)) {
    echo "✓ vars.js exists in storage/app/public/js/\n";
} else {
    echo "✗ vars.js missing from storage/app/public/js/\n";
}

if (file_exists($public_vars_js_path)) {
    echo "✓ vars.js exists in public/storage/js/\n";
} else {
    echo "✗ vars.js missing from public/storage/js/\n";
}

echo "\nCompleted at: " . date('Y-m-d H:i:s');
echo "\n\nIMPORTANT: Delete this file now that you've used it!";