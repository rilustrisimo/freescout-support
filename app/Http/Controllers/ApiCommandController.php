<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\BufferedOutput;
use App\Misc\Helper;

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
     * Run schedule command via API
     */
    public function schedule(Request $request)
    {
        if (!$this->checkSecurity($request, 'schedule')) {
            abort(404);
        }
        
        $outputLog = new BufferedOutput();
        Artisan::call('schedule:run', [], $outputLog);
        $output = $outputLog->fetch();

        return response($output, 200)->header('Content-Type', 'text/plain');
    }

    /**
     * Run email fetching with debug output
     */
    public function fetchEmailsDebug(Request $request)
    {
        if (!$this->checkSecurity($request, 'fetch_debug')) {
            abort(404);
        }
        
        // Clear any existing mutex locks for fetching
        \Cache::forget('fetch_mutex_name');
        
        // Clear option that might be preventing fetch
        \App\Option::set('fetch_emails_last_run', time() - 3600); // Set to 1 hour ago
        
        $outputLog = new BufferedOutput();
        Artisan::call('freescout:fetch-emails', [
            '--days' => 3,
            '--unseen' => 1,
            '--debug' => 1
        ], $outputLog);
        $output = $outputLog->fetch();

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
        \App\Option::set('freescout_fetch_emails_last_run', time() - 3600); // Set to 1 hour ago
        
        $outputLog = new BufferedOutput();
        \Artisan::call('freescout:fetch-emails', [
            '--days' => 3,
            '--unseen' => 1
        ], $outputLog);
        $output = $outputLog->fetch();

        return response($output, 200)->header('Content-Type', 'text/plain');
    }
}