<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\BufferedOutput;
use App\Misc\Helper;
use App\Option;

class ApiCommandController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // No middleware - we use hash for security
    }

    /**
     * Security check for all API endpoints
     * 
     * @param Request $request
     * @param string $action
     * @return bool
     */
    protected function checkSecurity(Request $request, $action = '')
    {
        if (empty($request->hash) || $request->hash != Helper::getApiCommandHash($action)) {
            return false;
        }
        return true;
    }

    /**
     * Run storage sync command via API
     */
    public function storageSync(Request $request)
    {
        if (!$this->checkSecurity($request, 'storage_sync')) {
            abort(404);
        }
        
        $outputLog = new BufferedOutput();
        Artisan::call('freescout:sync-storage-files', [], $outputLog);
        $output = $outputLog->fetch();

        return response($output, 200)->header('Content-Type', 'text/plain');
    }

    /**
     * Run schedule command via API with enhanced logging and force execution
     */
    public function schedule(Request $request)
    {
        if (!$this->checkSecurity($request, 'schedule')) {
            abort(404);
        }
        
        // Reset various mutex locks that might prevent scheduled tasks from running
        $fetch_mutex_name = \Cache::get('fetch_mutex_name');
        if ($fetch_mutex_name) {
            \Cache::forget($fetch_mutex_name);
        }
        
        // Fix queue worker mutex issues by clearing any stuck locks
        if (function_exists('shell_exec')) {
            $queue_mutex_name = '';
            try {
                // Get queue mutex name the same way the kernel does
                $outputLog = new BufferedOutput();
                Artisan::call('freescout:get-queue-mutex', [], $outputLog);
                $queue_mutex_name = trim($outputLog->fetch());
                
                if ($queue_mutex_name && \Cache::has($queue_mutex_name)) {
                    \Cache::forget($queue_mutex_name);
                }
            } catch (\Exception $e) {
                // Command may not exist, continue anyway
            }
            
            // Restart queue worker
            Helper::queueWorkerRestart();
        }
        
        // Set the last run time to 1 hour ago to force all scheduled commands to check if they need to run
        Option::set('queue_work_last_run', time() - 3600);
        Option::set('freescout_fetch_emails_last_run', time() - 3600);
        
        // Now run the scheduler
        $outputLog = new BufferedOutput();
        $_SERVER['argv'] = ['artisan', 'schedule:run']; // Ensure isScheduleRun() detects correctly
        Artisan::call('schedule:run', [], $outputLog);
        $output = $outputLog->fetch();
        
        // Fix queue workers if needed
        if (strpos($output, 'No scheduled commands are ready to run') !== false) {
            // If no commands ran, try to start the queue worker directly
            try {
                $queue_work_params = config('app.queue_work_params');
                if (!is_array($queue_work_params)) {
                    $queue_work_params = ['--queue' => 'default', '--sleep' => 3, '--tries' => 3];
                }
                
                // Add identifier to avoid conflicts 
                $queue_work_params['--queue'] .= ','.Helper::getWorkerIdentifier();
                
                // Start queue worker separately
                if (function_exists('shell_exec') && !count(Helper::getRunningProcesses())) {
                    $command = 'php '.base_path().'/artisan queue:work';
                    foreach ($queue_work_params as $key => $value) {
                        $command .= ' '.$key.'='.$value;
                    }
                    $command .= ' > '.storage_path().'/logs/queue-jobs.log 2>&1 &';
                    shell_exec($command);
                    $output .= "\nStarted queue worker in background.";
                }
            } catch (\Exception $e) {
                $output .= "\nError starting queue worker: ".$e->getMessage();
            }
        }

        return response($output, 200)->header('Content-Type', 'text/plain');
    }

    /**
     * Run fetch emails command directly via API
     * This bypasses the scheduler timing and forces email fetching
     */
    public function fetchEmails(Request $request)
    {
        if (!$this->checkSecurity($request, 'fetch_emails')) {
            abort(404);
        }
        
        // Clear any existing mutex locks to ensure command runs
        $mutex_name = \Cache::get('fetch_mutex_name');
        if ($mutex_name) {
            \Cache::forget($mutex_name);
        }
        
        // Reset the last run time to force execution
        Option::set('freescout_fetch_emails_last_run', time() - 3600); // Set to 1 hour ago
        
        $outputLog = new BufferedOutput();
        \Artisan::call('freescout:fetch-emails', [
            '--days' => 3,
            '--unseen' => 1
        ], $outputLog);
        $output = $outputLog->fetch();

        return response($output, 200)->header('Content-Type', 'text/plain');
    }
    
    /**
     * Run queue worker directly via API - Matched to system implementation
     */
    public function queueWork(Request $request)
    {
        if (!$this->checkSecurity($request, 'queue_work')) {
            abort(404);
        }
        
        $output = [];
        $output[] = "Running scheduled commands:";
        
        // Get queue parameters from config
        $queue_work_params = config('app.queue_work_params');
        if (!is_array($queue_work_params)) {
            $queue_work_params = ['--queue' => 'emails,default', '--sleep' => 5, '--tries' => 1, '--timeout' => 1800];
        }
        
        // Add identifier to avoid conflicts
        $queue_work_params['--queue'] .= ','.Helper::getWorkerIdentifier();
        
        // Format the command as displayed in the reference
        $display_command = "Running scheduled command: '/opt/alt/php81/usr/bin/php' 'artisan' queue:work";
        foreach ($queue_work_params as $key => $value) {
            $display_command .= ' '.$key.'='.$value;
        }
        $display_command .= " > '".storage_path()."/logs/queue-jobs.log' 2>&1";
        $output[] = $display_command;
        
        // Restart queue worker to clear any locks
        Helper::queueWorkerRestart();
        
        // Build the background command
        $command = 'php '.base_path().'/artisan queue:work';
        foreach ($queue_work_params as $key => $value) {
            $command .= ' '.$key.'='.$value;
        }
        $command .= ' > '.storage_path().'/logs/queue-jobs.log 2>&1 &';
        
        // Execute command
        if (function_exists('shell_exec')) {
            shell_exec($command);
            
            // Set option to record last run time
            Option::set('queue_work_last_run', time());
            Option::set('queue_work_last_successful_run', time());
        }
        
        return response(implode("\n", $output), 200)->header('Content-Type', 'text/plain');
    }
}