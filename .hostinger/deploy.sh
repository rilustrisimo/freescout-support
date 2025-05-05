#!/bin/bash

# Deployment hook script for Hostinger
# This script fixes the issue with laravel-log-viewer and the ClassMapGenerator

echo "Applying FreeScout deployment fixes..."

# Path to the ClassMapGenerator.php file
CLASS_MAP_GENERATOR_PATH="vendor/composer/ClassMapGenerator.php"

# Check if the file exists
if [ -f "$CLASS_MAP_GENERATOR_PATH" ]; then
    echo "Patching ClassMapGenerator.php to handle missing directories..."
    
    # Create a backup of the original file
    cp "$CLASS_MAP_GENERATOR_PATH" "${CLASS_MAP_GENERATOR_PATH}.bak"
    
    # Modify the file to handle missing directories
    sed -i 's/if (!is_dir($path)) {/if (!is_dir($path) \&\& !strpos($path, "\/rap2hpoutre\/laravel-log-viewer\/src\/controllers")) {/' "$CLASS_MAP_GENERATOR_PATH"
    
    echo "ClassMapGenerator.php patched successfully."
else
    echo "ClassMapGenerator.php not found. Skipping patch."
fi

echo "Deployment fixes applied successfully."