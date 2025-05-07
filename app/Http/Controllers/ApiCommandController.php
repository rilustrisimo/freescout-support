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
}