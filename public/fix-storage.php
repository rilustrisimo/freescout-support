<?php
// Script to fix missing vars.js issue on hosts where symlinks are disabled
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

// Skip symlink creation since it's disabled
echo "Step 1: Manually creating storage directory structure...\n";

$source_dir = $laravel_path . '/storage/app/public';
$target_dir = __DIR__ . '/storage';

// Create the target directory if it doesn't exist
if (!file_exists($target_dir)) {
    if (mkdir($target_dir, 0755, true)) {
        echo "Created directory: {$target_dir}\n";
    } else {
        echo "Failed to create directory: {$target_dir}\n";
    }
}

// Function to recursively copy files from one directory to another
function copyDirectory($source, $target) {
    if (!is_dir($source)) {
        echo "Source is not a directory: {$source}\n";
        return false;
    }

    if (!file_exists($target)) {
        if (!mkdir($target, 0755, true)) {
            echo "Failed to create directory: {$target}\n";
            return false;
        }
    }

    $dir = opendir($source);
    while (($file = readdir($dir)) !== false) {
        if ($file == '.' || $file == '..') {
            continue;
        }

        $sourcePath = $source . '/' . $file;
        $targetPath = $target . '/' . $file;

        if (is_dir($sourcePath)) {
            copyDirectory($sourcePath, $targetPath);
        } else {
            if (copy($sourcePath, $targetPath)) {
                echo "Copied: {$sourcePath} → {$targetPath}\n";
            } else {
                echo "Failed to copy: {$sourcePath} → {$targetPath}\n";
            }
        }
    }
    closedir($dir);
    return true;
}

echo "Copying files from {$source_dir} to {$target_dir}...\n";
copyDirectory($source_dir, $target_dir);

echo "\nStep 2: Generate vars.js file...\n";
$output = [];
exec('php artisan freescout:generate-vars 2>&1', $output, $return_var);
echo implode("\n", $output) . "\n";
echo "Return code: $return_var\n\n";

echo "Step 3: Clearing cache...\n";
$output = [];
exec('php artisan freescout:clear-cache 2>&1', $output, $return_var);
echo implode("\n", $output) . "\n";
echo "Return code: $return_var\n\n";

// Force copy the vars.js file specifically
$source_vars = $laravel_path . '/storage/app/public/js/vars.js';
$target_vars_dir = __DIR__ . '/storage/js';
$target_vars = $target_vars_dir . '/vars.js';

// Create js directory if needed
if (!file_exists($target_vars_dir)) {
    if (mkdir($target_vars_dir, 0755, true)) {
        echo "Created directory: {$target_vars_dir}\n";
    } else {
        echo "Failed to create directory: {$target_vars_dir}\n";
    }
}

// Copy vars.js specifically
if (file_exists($source_vars)) {
    if (copy($source_vars, $target_vars)) {
        echo "Copied vars.js: {$source_vars} → {$target_vars}\n";
    } else {
        echo "Failed to copy vars.js: {$source_vars} → {$target_vars}\n";
    }
} else {
    echo "Source vars.js not found at: {$source_vars}\n";
}

echo "\nChecking if vars.js exists...\n";
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

echo "\nSetting up cron job to keep storage in sync...\n";

// Create a script that will be used by cron to keep storage in sync
$sync_script = <<<'EOT'
<?php
// This script syncs the storage/app/public directory to public/storage
// It should be run periodically via cron

$laravel_path = __DIR__;
$source_dir = $laravel_path . '/storage/app/public';
$target_dir = $laravel_path . '/public/storage';

// Function to recursively copy files
function copyDirectory($source, $target) {
    if (!is_dir($source)) return false;
    if (!file_exists($target)) mkdir($target, 0755, true);

    $dir = opendir($source);
    while (($file = readdir($dir)) !== false) {
        if ($file == '.' || $file == '..') continue;

        $sourcePath = $source . '/' . $file;
        $targetPath = $target . '/' . $file;

        if (is_dir($sourcePath)) {
            copyDirectory($sourcePath, $targetPath);
        } else {
            // Only copy if file doesn't exist or is newer
            if (!file_exists($targetPath) || filemtime($sourcePath) > filemtime($targetPath)) {
                copy($sourcePath, $targetPath);
            }
        }
    }
    closedir($dir);
    return true;
}

// Sync the directories
copyDirectory($source_dir, $target_dir);
EOT;

// Save the sync script
$sync_path = $laravel_path . '/storage_sync.php';
if (file_put_contents($sync_path, $sync_script)) {
    echo "Created storage sync script: {$sync_path}\n";
    
    // Make the script executable
    chmod($sync_path, 0755);
    
    echo "You need to set up a cron job to run this script periodically.\n";
    echo "Add the following cron job in your Hostinger control panel:\n\n";
    echo "*/15 * * * * php " . $sync_path . " > /dev/null 2>&1\n\n";
    echo "This will sync your storage directories every 15 minutes.\n";
} else {
    echo "Failed to create storage sync script\n";
}

// Create a test file in the public directory
$test_content = "This file helps test if file writing works.";
$test_file = __DIR__ . '/storage_test_' . time() . '.txt';
if (file_put_contents($test_file, $test_content)) {
    echo "\nTest file created successfully at: {$test_file}\n";
    echo "This confirms we can write to the public directory.\n";
} else {
    echo "\nFailed to create test file. Check permissions.\n";
}

echo "\nCompleted at: " . date('Y-m-d H:i:s');
echo "\n\nIMPORTANT: Delete this file now that you've used it!";