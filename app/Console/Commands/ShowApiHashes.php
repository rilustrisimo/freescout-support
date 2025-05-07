<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Misc\Helper;

class ShowApiHashes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'show:api-hashes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display API command hashes';

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
        $storage_sync_hash = Helper::getApiCommandHash('storage_sync');
        $schedule_hash = Helper::getApiCommandHash('schedule');

        $this->info('Storage Sync Hash: ' . $storage_sync_hash);
        $this->info('Schedule Hash: ' . $schedule_hash);
        
        $app_url = config('app.url');
        $this->info("\nAPI URLs:");
        $this->info("Storage Sync URL: {$app_url}/api/command/storage-sync/{$storage_sync_hash}");
        $this->info("Schedule URL: {$app_url}/api/command/schedule/{$schedule_hash}");
    }
}