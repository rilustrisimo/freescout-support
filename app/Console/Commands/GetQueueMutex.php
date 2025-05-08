<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use App\Misc\Helper;

class GetQueueMutex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'freescout:get-queue-mutex';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the queue:work mutex name';

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
        $schedule = app(Schedule::class);
        
        // Get queue work params
        $queue_work_params = config('app.queue_work_params');
        if (!is_array($queue_work_params)) {
            $queue_work_params = ['--queue' => 'default', '--sleep' => 3, '--tries' => 3];
        }
        
        // Add identifier to avoid conflicts
        $queue_work_params['--queue'] .= ','.Helper::getWorkerIdentifier();
        
        // Get the mutex name the same way Kernel.php does
        $mutex_name = $schedule->command('queue:work', $queue_work_params)
            ->skip(function () {
                return true;
            })
            ->mutexName();
            
        // Return mutex name directly to stdout without line break
        // This makes it easier to capture in API controller
        $this->output->write($mutex_name);
        
        return 0;
    }
}