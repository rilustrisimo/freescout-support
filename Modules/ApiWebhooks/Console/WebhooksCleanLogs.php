<?php

namespace Modules\ApiWebhooks\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\Model;
use Modules\ApiWebhooks\Entities\WebhookLog;

class WebhooksCleanLogs extends Command
{
    /**
     * The signature of the console command.
     *
     * @var string
     */
    protected $signature = 'freescout:webhooks-clean-logs';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Clean webhooks retry logs.';

    /**
     * Execute the console command.
     *
     * @return void
     *
     * @throws \Adldap\Models\ModelNotFoundException
     */
    public function handle()
    {
        $webhook_logs = WebhookLog::where('finished', true)
            ->where('updated_at', '<', \Carbon\Carbon::now()->subDays(3))
            ->delete();
    }
}
