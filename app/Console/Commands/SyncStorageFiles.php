<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SyncStorageFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'freescout:sync-storage-files {--force : Force copy all files even if they already exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Syncs files from storage/app/public to public/storage for environments where symlinks are disabled';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Starting storage sync process...');
        
        $sourceDir = storage_path('app/public');
        $targetDir = public_path('storage');
        
        if (!File::exists($sourceDir)) {
            $this->error("Source directory does not exist: {$sourceDir}");
            return 1;
        }
        
        if (!File::exists($targetDir)) {
            $this->info("Target directory does not exist, creating: {$targetDir}");
            File::makeDirectory($targetDir, 0755, true);
        }
        
        $force = $this->option('force');
        
        try {
            $this->syncDirectory($sourceDir, $targetDir, $force);
            $this->info('Storage sync completed successfully');
            
            // Generate vars.js file specifically
            $this->call('freescout:generate-vars');
            
            // Copy vars.js specifically to ensure it's there
            $sourceVars = storage_path('app/public/js/vars.js');
            $targetVarsDir = public_path('storage/js');
            $targetVars = $targetVarsDir . '/vars.js';
            
            if (!File::exists($targetVarsDir)) {
                File::makeDirectory($targetVarsDir, 0755, true);
            }
            
            if (File::exists($sourceVars)) {
                File::copy($sourceVars, $targetVars, true);
                $this->info("Copied vars.js to {$targetVars}");
            } else {
                $this->error("Source vars.js not found at: {$sourceVars}");
            }
            
            return 0;
        } catch (\Exception $e) {
            $this->error("Error syncing storage files: " . $e->getMessage());
            Log::error("Error syncing storage files: " . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * Recursively sync files from source to target directory
     */
    protected function syncDirectory($source, $target, $force = false)
    {
        $files = File::files($source);
        $directories = File::directories($source);
        
        // Create target directory if it doesn't exist
        if (!File::exists($target)) {
            File::makeDirectory($target, 0755, true);
        }
        
        // Copy files
        foreach ($files as $file) {
            $sourceFile = $file->getPathname();
            $targetFile = $target . '/' . $file->getFilename();
            
            if ($force || !File::exists($targetFile) || File::lastModified($sourceFile) > File::lastModified($targetFile)) {
                File::copy($sourceFile, $targetFile);
                $this->line("Copied: {$file->getFilename()}");
            }
        }
        
        // Process subdirectories
        foreach ($directories as $directory) {
            $dirName = basename($directory);
            $this->syncDirectory(
                $source . '/' . $dirName,
                $target . '/' . $dirName,
                $force
            );
        }
    }
}