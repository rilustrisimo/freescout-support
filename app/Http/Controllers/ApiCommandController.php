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
     * Run schedule command via API with intelligent queue worker management
     */
    public function schedule(Request $request)
    {
        if (!$this->checkSecurity($request, 'schedule')) {
            abort(404);
        }
        
        $output_lines = [];
        $output_lines[] = "Running FreeScout scheduler via API endpoint";
        
        // Reset various mutex locks that might prevent scheduled tasks from running
        $fetch_mutex_name = \Cache::get('fetch_mutex_name');
        if ($fetch_mutex_name) {
            \Cache::forget($fetch_mutex_name);
            $output_lines[] = "Cleared fetch email mutex lock";
        }
        
        // Check for running queue workers before attempting to start new ones
        $worker_identifier = Helper::getWorkerIdentifier();
        $running_queue_workers = 0;
        $worker_pids = [];
        
        if (function_exists('shell_exec')) {
            try {
                $processes = preg_split("/[\r\n]/", Helper::shellExec("ps auxww | grep '{$worker_identifier}'"));
                foreach ($processes as $process) {
                    $process = trim($process);
                    preg_match("/^[\S]+\s+([\d]+)\s+/", $process, $m);
                    if (empty($m)) {
                        preg_match("/^([\d]+)\s+[\S]+\s+/", $process, $m);
                    }
                    if (!preg_match("/(sh \-c|grep )/", $process) && !empty($m[1])) {
                        $running_queue_workers++;
                        $worker_pids[] = $m[1];
                    }
                }
            } catch (\Exception $e) {
                // Continue anyway
            }
            
            if ($running_queue_workers > 1) {
                $output_lines[] = "Detected {$running_queue_workers} queue workers - restarting to prevent conflicts";
                Helper::queueWorkerRestart();
                sleep(1);
                
                // Check if processes are still running after sending restart signal
                $remaining_pids = Helper::getRunningProcesses($worker_identifier);
                if (count($remaining_pids) > 0) {
                    $output_lines[] = "Terminating remaining queue workers: " . implode(', ', $remaining_pids);
                    shell_exec('kill ' . implode(' ', $remaining_pids) . ' 2>/dev/null');
                    sleep(1);
                    $running_queue_workers = 0;
                }
            } elseif ($running_queue_workers == 1) {
                $output_lines[] = "Queue worker already running (PID: " . implode(', ', $worker_pids) . ")";
                
                // Update timestamp so it doesn't appear stalled in system status
                Option::set('queue_work_last_run', time());
                Option::set('queue_work_last_successful_run', time());
            }
        }
        
        // Now run the scheduler
        $outputLog = new BufferedOutput();
        $_SERVER['argv'] = ['artisan', 'schedule:run']; // Ensure isScheduleRun() detects correctly
        Artisan::call('schedule:run', [], $outputLog);
        $output = $outputLog->fetch();
        
        // If we have output from the scheduler, add it to our output lines
        if ($output && trim($output) != 'No scheduled commands are ready to run.') {
            $output_lines[] = "\nScheduler output:";
            $output_lines[] = $output;
        } else {
            $output_lines[] = "No scheduled commands were ready to run";
            
            // Only start a queue worker if none is running and scheduler didn't start one
            if ($running_queue_workers == 0 && function_exists('shell_exec')) {
                $queue_work_params = config('app.queue_work_params');
                if (!is_array($queue_work_params)) {
                    $queue_work_params = ['--queue' => 'emails,default', '--sleep' => 5, '--tries' => 1, '--timeout' => 1800];
                }
                
                // Add identifier to avoid conflicts 
                $queue_work_params['--queue'] .= ','.$worker_identifier;
                
                // Format command for output
                $display_command = "Starting queue worker manually: 'php artisan queue:work";
                foreach ($queue_work_params as $key => $value) {
                    $display_command .= ' '.$key.'='.$value;
                }
                $output_lines[] = $display_command . "'";
                
                // Build the background command
                $command = 'php '.base_path().'/artisan queue:work';
                foreach ($queue_work_params as $key => $value) {
                    $command .= ' '.$key.'='.$value;
                }
                $command .= ' > '.storage_path().'/logs/queue-jobs.log 2>&1 &';
                
                shell_exec($command);
                
                // Update timestamps
                Option::set('queue_work_last_run', time());
                Option::set('queue_work_last_successful_run', time());
                $output_lines[] = "Queue worker started successfully";
            }
        }

        return response(implode("\n", $output_lines), 200)->header('Content-Type', 'text/plain');
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
     * Run queue worker directly via API - Prevents multiple instances
     */
    public function queueWork(Request $request)
    {
        if (!$this->checkSecurity($request, 'queue_work')) {
            abort(404);
        }
        
        $output = [];
        
        // Check if command is already running before starting a new one
        if (function_exists('shell_exec')) {
            $worker_identifier = Helper::getWorkerIdentifier();
            $running_commands = 0;
            $pids = [];
            
            try {
                $processes = preg_split("/[\r\n]/", Helper::shellExec("ps auxww | grep '{$worker_identifier}'"));
                foreach ($processes as $process) {
                    $process = trim($process);
                    preg_match("/^[\S]+\s+([\d]+)\s+/", $process, $m);
                    if (empty($m)) {
                        // Another format (used in Docker image)
                        preg_match("/^([\d]+)\s+[\S]+\s+/", $process, $m);
                    }
                    if (!preg_match("/(sh \-c|grep )/", $process) && !empty($m[1])) {
                        $running_commands++;
                        $pids[] = $m[1];
                    }
                }
            } catch (\Exception $e) {
                // Do nothing
            }
            
            if ($running_commands > 1) {
                $output[] = "Warning: {$running_commands} queue:work commands are running simultaneously";
                $output[] = "Restarting queue workers to prevent resource contention...";
                
                // Use the same logic as SystemController to restart queue workers
                Helper::queueWorkerRestart();
                
                // Kill existing processes to ensure we start fresh
                sleep(1); // Give processes time to detect the restart signal
                
                // Check if processes are still running after sending restart signal
                $remaining_pids = Helper::getRunningProcesses($worker_identifier);
                if (count($remaining_pids) > 0) {
                    $output[] = "Terminating remaining processes: " . implode(', ', $remaining_pids);
                    shell_exec('kill ' . implode(' ', $remaining_pids) . ' 2>/dev/null');
                    sleep(1); // Wait for processes to terminate
                }
            } elseif ($running_commands == 1) {
                $output[] = "One queue:work process already running (PID: " . implode(', ', $pids) . ")";
                $output[] = "Updating last run timestamps without starting a new process";
                
                // Just update timestamps and exit
                Option::set('queue_work_last_run', time());
                Option::set('queue_work_last_successful_run', time());
                
                return response(implode("\n", $output), 200)->header('Content-Type', 'text/plain');
            }
        }

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
            $output[] = "Queue worker started successfully";
        } else {
            $output[] = "Error: shell_exec function is disabled, cannot start queue worker";
        }
        
        return response(implode("\n", $output), 200)->header('Content-Type', 'text/plain');
    }
}