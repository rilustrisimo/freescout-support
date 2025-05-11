<?php

namespace App;

namespace Modules\ApiWebhooks\Entities;

use Modules\ApiWebhooks\Entities\WebhookLog;
use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    const MAX_ATTEMPTS = 10;

    public static $events = [
        'convo.assigned',
        'convo.created',
        'convo.deleted',
        'convo.deleted_forever',
        'convo.restored',
        //'convo.merged',
        'convo.moved',
        'convo.status',
        //'convo.tags',
        'convo.customer.reply.created',
        'convo.agent.reply.created',
        'convo.note.created',
        'customer.created',
        'customer.updated',
    ];

    public $timestamps = false;

    protected $casts = [
        'events' => 'array',
        'mailboxes' => 'array',
    ];

    public static function getAllEvents()
    {
        return \Eventy::filter('webhooks.events', self::$events);
    }

    public static function getSecretKey()
    {
        return md5(config('app.key').'webhook_key');
    }

    public function run($event, $data, $webhook_log_id = null)
    {
        $options = [
            'timeout' => 30, // seconds
        ];

        $options = \Helper::setGuzzleDefaultOptions($options);

        // Format entity for webhook
        $params = \ApiWebhooks::formatEntity($data);

        $this->last_run_time = date('Y-m-d H:i:s');
        
        // Special handling for Slack webhooks
        $is_slack = strpos($this->url, 'hooks.slack.com') !== false;
        
        try {
            // Configure headers and content
            $options['headers'] = [
                'Content-Type' => 'application/json',
                'X-FreeScout-Event' => $event,
            ];
            
            // For Slack webhooks, format the payload specifically
            if ($is_slack) {
                // Create a simple text message for Slack
                $slack_message = $this->createSlackMessage($params, $data);
                $options['json'] = ['text' => $slack_message];
                
                // Don't include signature for Slack
            } else {
                // For all other webhooks, use the standard format with signature
                $options['json'] = $params;
                $options['headers']['X-FreeScout-Signature'] = self::sign(json_encode($params));
            }
            
            // Send the webhook request
            $response = (new \GuzzleHttp\Client())->request('POST', $this->url, $options);
        } catch (\Exception $e) {
            $this->last_run_error = $e->getMessage();
            $this->save();

            WebhookLog::add($this, $event, 0, $params, $e->getMessage(), $webhook_log_id);
            return false;
        }

        // Check for success
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() <= 299) {
            $this->last_run_error = '';
            $this->save();
            
            return true;
        } else {
            $error = 'Response status code: '.$response->getStatusCode();
            $this->last_run_error = $error;
            $this->save();

            WebhookLog::add($this, $event, $response->getStatusCode(), $params, $error, $webhook_log_id);
            return false;
        }
    }

    /**
     * Create a formatted message for Slack webhooks
     * 
     * @param array $payload The formatted entity data
     * @param object $original The original data object
     * @return string The message text for Slack
     */
    protected function createSlackMessage($payload, $original = null)
    {
        $message = '';
        
        // Handle conversation related events
        if (isset($payload['subject'])) {
            $message .= "*Conversation:* #{$payload['number']} - {$payload['subject']}\n";
            
            // Status info
            if (isset($payload['status'])) {
                $message .= "*Status:* {$payload['status']}\n";
            }
            
            // Add assignee info if available
            if (isset($payload['assignee']) && !empty($payload['assignee'])) {
                $assignee = $payload['assignee'];
                $message .= "*Assigned to:* {$assignee['firstName']} {$assignee['lastName']}\n";
            } else {
                $message .= "*Assigned to:* Unassigned\n";
            }
            
            // Customer info if available
            if (isset($payload['customer']) && !empty($payload['customer'])) {
                $customer = $payload['customer'];
                $message .= "*Customer:* {$customer['firstName']} {$customer['lastName']} ({$customer['email']})\n";
            }
            
            // Add thread content
            if (isset($payload['_embedded']) && isset($payload['_embedded']['threads']) && !empty($payload['_embedded']['threads'])) {
                // Get the last thread that's not a lineitem
                $threads = $payload['_embedded']['threads'];
                $latestThread = null;
                
                // Find the latest non-lineitem thread (actual message)
                foreach (array_reverse($threads) as $thread) {
                    if ($thread['type'] !== 'lineitem' && !empty($thread['body'])) {
                        $latestThread = $thread;
                        break;
                    }
                }
                
                if ($latestThread) {
                    // Strip HTML tags and limit length
                    $body = strip_tags($latestThread['body']);
                    if (strlen($body) > 300) {
                        $body = substr($body, 0, 300) . '...';
                    }
                    
                    // Add sender information if available
                    $sender = '';
                    if (isset($latestThread['createdBy']) && !empty($latestThread['createdBy'])) {
                        $sender = $latestThread['createdBy']['firstName'] . ' ' . $latestThread['createdBy']['lastName'];
                        if ($latestThread['createdBy']['type'] === 'user') {
                            $sender .= ' (Agent)';
                        } else {
                            $sender .= ' (Customer)';
                        }
                        $message .= "\n*From:* {$sender}\n";
                    }
                    
                    $message .= "*Latest Message:*\n```{$body}```\n";
                }
            } else if ($original instanceof \App\Conversation) {
                // Try to get threads directly from the conversation object if payload doesn't have them
                $threads = $original->threads()->orderBy('created_at', 'desc')->take(5)->get();
                foreach ($threads as $thread) {
                    if ($thread->type != \App\Thread::TYPE_LINEITEM && !empty($thread->body)) {
                        // Strip HTML tags and limit length
                        $body = strip_tags($thread->body);
                        if (strlen($body) > 300) {
                            $body = substr($body, 0, 300) . '...';
                        }
                        
                        $message .= "\n*Latest Message:*\n```{$body}```\n";
                        break;
                    }
                }
            }
            
            // Add link to conversation
            $baseUrl = config('app.url');
            if ($baseUrl) {
                $message .= "\n<{$baseUrl}/conversation/{$payload['number']}|View Conversation>";
            }
        } 
        // Customer-related events
        else if (isset($payload['firstName']) && isset($payload['_embedded']) && isset($payload['_embedded']['emails'])) {
            $message .= "*Customer:* {$payload['firstName']} {$payload['lastName']}\n";
            
            if (!empty($payload['_embedded']['emails'])) {
                $email = $payload['_embedded']['emails'][0]['value'] ?? 'No email';
                $message .= "*Email:* {$email}\n";
            }
            
            if (!empty($payload['company'])) {
                $message .= "*Company:* {$payload['company']}\n";
            }
            
            $baseUrl = config('app.url');
            if ($baseUrl && isset($payload['id'])) {
                $message .= "\n<{$baseUrl}/customer/{$payload['id']}|View Customer>";
            }
        } 
        // Generic fallback
        else {
            $message = "New event from FreeScout";
            
            // Try to add details about what triggered the event
            if (isset($payload['id'])) {
                $message .= " (ID: {$payload['id']})";
            }
        }
        
        return $message;
    }

    public static function create($data)
    {
        $webhook = null;

        if (!empty($data['url']) && !empty($data['events'])) {

            $events = $data['events'];    
            if (!is_array($events)) {
                if (is_string($events)) {
                    $events = explode(',', $events);
                } else {
                    return null;
                }
            }

            // Remove non-existing events.
            foreach ($events as $i => $event) {
                if (!in_array($event, self::$events)) {
                    unset($events[$i]);
                }
            }
            if (!$events) {
                return null;
            }

            $webhook = new \Webhook();
            $webhook->url = $data['url'];
            $webhook->events = $events;
            $webhook->save();

            \ApiWebhooks::clearWebhooksCache();
        }

        return $webhook;
    }
}
