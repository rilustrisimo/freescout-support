<?php

namespace Modules\ApiWebhooks\Http\Controllers;

use Modules\ApiWebhooks\Entities\Webhook;
use Modules\ApiWebhooks\Entities\WebhookLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class ApiWebhooksController extends Controller
{
    /**
     * Ajax controller.
     */
    public function ajaxHtml(Request $request)
    {
        $user = auth()->user();

        switch ($request->action) {
            case 'webhook_logs':

                $logs = WebhookLog::where('webhook_id', $request->param)
                    ->orderBy('id', 'desc')
                    ->limit(1000)
                    ->get();

                return view('apiwebhooks::partials/webhook_logs', [
                    'logs'   => $logs
                ]);
                break;

        }

        abort(404);
    }

    /**
     * Ajax controller.
     */
    public function ajax(Request $request)
    {
        $response = [
            'status' => 'error',
            'msg'    => '', // this is error message
        ];

        $user = auth()->user();

        switch ($request->action) {

            // Delete
            case 'delete':
               
                $webhook = Webhook::find($request->webhook_id);

                if (!$webhook) {
                    $response['msg'] = __('Webhook not found');
                }

                if (!$response['msg']) {
                    WebhookLog::where('webhook_id', $webhook->id)->delete();
                    $webhook->delete();

                    \ApiWebhooks::clearWebhooksCache();

                    $response['status'] = 'success';
                    \Session::flash('flash_success_floating', __('Webhook deleted'));
                }
                break;

            default:
                $response['msg'] = 'Unknown action';
                break;
        }

        if ($response['status'] == 'error' && empty($response['msg'])) {
            $response['msg'] = 'Unknown error occured';
        }

        return \Response::json($response);
    }
}
