<?php

namespace Modules\ApiWebhooks\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\Model;
use Modules\ApiWebhooks\Entities\WebhookLog;

class WebhooksProcess extends Command
{
    /**
     * The signature of the console command.
     *
     * @var string
     */
    protected $signature = 'freescout:webhooks-process';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Retry failed webhooks.';

    /**
     * Execute the console command.
     *
     * @return void
     *
     * @throws \Adldap\Models\ModelNotFoundException
     */
    public function handle()
    {
        $webhook_logs = WebhookLog::where('finished', false)->get();

        foreach ($webhook_logs as $webhook_log) {

            $check_date = \Carbon\Carbon::now()->subMinutes(($webhook_log->attempts-1) * 2);
            
            if ($webhook_log->updated_at->lt($check_date) && $webhook_log->webhook) {
                $result = $webhook_log->webhook->run($webhook_log->event, $webhook_log->data, $webhook_log->id);

                if ($result) {
                    $webhook_log->finished = true;
                    $webhook_log->save();
                }
            }
        }
    }
}
