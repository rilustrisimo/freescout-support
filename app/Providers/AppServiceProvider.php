<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // To avoid MySQL error in packages:
        // "SQLSTATE[42000]: Syntax error or access violation: 1071 Specified key was too long; max key length is 767 bytes"
        Schema::defaultStringLength(191);

        // Models observers
        \App\Mailbox::observe(\App\Observers\MailboxObserver::class);
        // Eloquent events for this table are not called automatically, so need to be called manually.
        //\App\MailboxUser::observe(\App\Observers\MailboxUserObserver::class);
        \App\Email::observe(\App\Observers\EmailObserver::class);
        \App\User::observe(\App\Observers\UserObserver::class);
        \App\Conversation::observe(\App\Observers\ConversationObserver::class);
        \App\Customer::observe(\App\Observers\CustomerObserver::class);
        \App\Thread::observe(\App\Observers\ThreadObserver::class);
        \App\Attachment::observe(\App\Observers\AttachmentObserver::class);
        \App\Follower::observe(\App\Observers\FollowerObserver::class);
        \Illuminate\Notifications\DatabaseNotification::observe(\App\Observers\DatabaseNotificationObserver::class);

        \Eventy::addFilter('webhook.payload', function($payload, $url) {
            // Check if the destination is a Slack webhook
            if (strpos($url, 'hooks.slack.com') !== false) {
                // Get relevant information from the payload to create a meaningful message
                $message = $this->formatSlackMessage($payload);
                
                // Return the Slack formatted payload
                return ['text' => $message];
            }
            
            return $payload;
        }, 20, 2);
    }

    /**
     * Format payload into a readable Slack message
     * 
     * @param array $payload The original webhook payload
     * @return string The formatted message for Slack
     */
    private function formatSlackMessage($payload)
    {
        $message = '';
        
        // Conversation-related events
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
            
            // Add most recent thread content if available
            if (isset($payload['_embedded']) && isset($payload['_embedded']['threads']) && !empty($payload['_embedded']['threads'])) {
                $latestThread = end($payload['_embedded']['threads']);
                if (!empty($latestThread['body'])) {
                    // Strip HTML tags and limit length
                    $body = strip_tags($latestThread['body']);
                    if (strlen($body) > 300) {
                        $body = substr($body, 0, 300) . '...';
                    }
                    $message .= "\n*Latest Message:*\n```{$body}```\n";
                }
            }
            
            // Add link to conversation (if you have a base URL for your helpdesk)
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

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Forse HTTPS if using CloudFlare "Flexible SSL"
        // https://support.cloudflare.com/hc/en-us/articles/200170416-What-do-the-SSL-options-mean-
        if (\Helper::isHttps()) {
            // $_SERVER['HTTPS'] = 'on';
            // $_SERVER['SERVER_PORT'] = '443';
            $this->app['url']->forceScheme('https');
        }

        // If APP_KEY is not set, redirect to /install.php
        if (!\Config::get('app.key') && !app()->runningInConsole() && !file_exists(storage_path('.installed'))) {
            // Not defined here yet
            //\Artisan::call("freescout:clear-cache");
            redirect(\Helper::getSubdirectory().'/install.php')->send();
        }

        // Process module registration error - disable module and show error to admin
        \Eventy::addFilter('modules.register_error', function ($exception, $module) {

            $msg = __('The :module_name module has been deactivated due to an error: :error_message', ['module_name' => $module->getName(), 'error_message' => $exception->getMessage()]);

            \Log::error($msg);

            // request() does is empty at this stage
            if (!empty($_POST['action']) && $_POST['action'] == 'activate') {

                // During module activation in case of any error we have to deactivate module.
                \App\Module::deactiveModule($module->getAlias());

                \Session::flash('flashes_floating', [[
                    'text' => $msg,
                    'type' => 'danger',
                    'role' => \App\User::ROLE_ADMIN,
                ]]);

                return;
            } elseif (empty($_POST)) {

                // failed to open stream: No such file or directory
                if (strstr($exception->getMessage(), 'No such file or directory')) {
                    \App\Module::deactiveModule($module->getAlias());

                    \Session::flash('flashes_floating', [[
                        'text' => $msg,
                        'type' => 'danger',
                        'role' => \App\User::ROLE_ADMIN,
                    ]]);
                }

                return;
            }

            return $exception;
        }, 10, 2);
    }
}
