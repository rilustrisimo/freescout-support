<?php

namespace App;

namespace Modules\ApiWebhooks\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\ApiWebhooks\Entities\Webhook;

class WebhookLog extends Model
{
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Automatically converted into Carbon dates.
     */
    protected $dates = ['created_at', 'updated_at'];

    public function webhook()
    {
        return $this->belongsTo('Modules\ApiWebhooks\Entities\Webhook');
    }

    public static function add($webhook, $event, $status_code, $data, $error = '', $webhook_log_id = null)
    {
        if (!$webhook_log_id) {
            $webhook_log = new WebhookLog();
            $webhook_log->webhook_id = $webhook->id;
            $webhook_log->status_code = $status_code;
            $webhook_log->event = $event;
            $webhook_log->error = $error;
            $webhook_log->data = $data;
            $webhook_log->finished = $webhook_log->isOk();

            \Log::error('Webhook: '.$webhook->url.' ('.$status_code.') | Event: '.$event.' | Error: '.$error);
        } else {
            $webhook_log = WebhookLog::find($webhook_log_id);
            $webhook_log->status_code = $status_code;
            $webhook_log->event = $event;
            $webhook_log->error = $error;
            $webhook_log->finished = $webhook_log->isOk();
            $webhook_log->attempts++;

            if ($webhook_log->attempts >= Webhook::MAX_ATTEMPTS) {
                $webhook_log->finished = true;
            }
        }
        try {
            if ($webhook_log) {
                $webhook_log->save();
            }
        } catch (\Exception $e) {
            \Helper::logException($e);
        }
    }

    public function isOk()
    {
        return ($this->status_code >= 200 && $this->status_code <= 299);
    }
}
