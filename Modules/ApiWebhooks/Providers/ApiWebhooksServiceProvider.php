<?php

namespace Modules\ApiWebhooks\Providers;

// Load API Doc.
// To avoid clattering PROD memory we are loading API Doc generator only in DEV.
if (\Helper::isDev()) {
    require_once __DIR__.'/../vendor/autoload.php';
} else {
    // To avoid Class "\Mpociot\ApiDoc\ApiDocGeneratorServiceProvider" not found.
    require_once __DIR__.'/../vendor/mpociot/laravel-apidoc-generator/src/ApiDocGeneratorServiceProvider.php';
}

use App\Attachment;
use App\Conversation;
use App\Customer;
use App\Email;
use App\Mailbox;
use App\Folder;
use App\Thread;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;

// Module alias
define('APIWEBHOOKS_MODULE', 'apiwebhooks');

class ApiWebhooksServiceProvider extends ServiceProvider
{
    public static $webhooks = [];

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
        $this->registerCommands();
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->hooks();
    }

    /**
     * Module hooks.
     */
    public function hooks()
    {
        // // Add module's CSS file to the application layout.
        // \Eventy::addFilter('stylesheets', function($styles) {
        //     $styles[] = \Module::getPublicPath(APIWEBHOOKS_MODULE).'/css/module.css';
        //     return $styles;
        // });

        // // Add module's JS file to the application layout.
        \Eventy::addFilter('javascripts', function($javascripts) {
            $javascripts[] = \Module::getPublicPath(APIWEBHOOKS_MODULE).'/js/laroute.js';
            $javascripts[] = \Module::getPublicPath(APIWEBHOOKS_MODULE).'/js/module.js';
            return $javascripts;
        });
        
        // Add item to settings sections.
        \Eventy::addFilter('settings.sections', function($sections) {
            $sections[APIWEBHOOKS_MODULE] = ['title' => __('API & Webhooks'), 'icon' => 'flash', 'order' => 600];

            return $sections;
        }, 40); 

        // Section settings
        \Eventy::addFilter('settings.section_settings', function($settings, $section) {
           
            if ($section != APIWEBHOOKS_MODULE) {
                return $settings;
            }
           
            $settings['apiwebhooks.api_key_salt'] = config('apiwebhooks.api_key_salt');
            $settings['apiwebhooks.cors_hosts'] = config('apiwebhooks.cors_hosts');

            return $settings;
        }, 20, 2);

        // Section parameters.
        \Eventy::addFilter('settings.section_params', function($params, $section) {
           
            if ($section != APIWEBHOOKS_MODULE) {
                return $params;
            }

            $webhooks = \Webhook::orderBy('id', 'desc')->get();

            $params = [
                'template_vars' => [
                    'webhooks' => $webhooks,
                ],
                'settings' => [
                    'apiwebhooks.api_key_salt' => [
                        'env' => 'APIWEBHOOKS_API_KEY_SALT',
                    ],
                    'apiwebhooks.cors_hosts' => [
                        'env' => 'APIWEBHOOKS_CORS_HOSTS',
                    ],
                ]
            ];

            return $params;
        }, 20, 2);

        // Settings view name
        \Eventy::addFilter('settings.view', function($view, $section) {
            if ($section != APIWEBHOOKS_MODULE) {
                return $view;
            } else {
                return 'apiwebhooks::settings';
            }
        }, 20, 2);

        // Before settings save.
        \Eventy::addFilter('settings.before_save', function($request, $section, $settings) {
            if ($section != APIWEBHOOKS_MODULE) {
                return $request;
            }

            if ($request->action == 'regenerate_api_key') {
                $new_settings = $request->settings;
                $new_settings['apiwebhooks.api_key_salt'] = \Str::random(10);
                $request->merge(['settings' => array_merge($request->settings ?? [], $new_settings)]);
            }

            return $request;
        }, 20, 3);

        // On settings save
        \Eventy::addFilter('settings.after_save', function($response, $request, $section, $settings) {
            if ($section != APIWEBHOOKS_MODULE) {
                return $response;
            }

            if ($request->action == 'add') {
                if (!empty($request->url) && !empty($request->events)) {
                    $webhook = new \Webhook();
                    $webhook->url = $request->url;
                    $webhook->events = $request->events;
                    $webhook->mailboxes = $request->mailboxes;
                    $webhook->save();

                    \ApiWebhooks::clearWebhooksCache();

                    \Session::forget('flash_success_floating');
                    \Session::flash('flash_success_floating', __('Webhook added'));
                }
            }

            if ($request->action == 'save_webhook') {
                if (!empty($request->id) && !empty($request->url) && !empty($request->events)) {
                    $webhook = \Webhook::find($request->id);
                    $webhook->url = $request->url;
                    $webhook->events = $request->events;
                    $webhook->mailboxes = $request->mailboxes;
                    $webhook->save();

                    \ApiWebhooks::clearWebhooksCache();

                    \Session::forget('flash_success_floating');
                    \Session::flash('flash_success_floating', __('Webhook updated'));
                }
            }

            return $response;
        }, 20, 4);

        // Schedule background processing.
        \Eventy::addFilter('schedule', function($schedule) {
            $schedule->command('freescout:webhooks-process')
                ->cron('* * * * *')
                ->withoutOverlapping($expires_at = 60 /* minutes */);
            $schedule->command('freescout:webhooks-clean-logs')
                //->cron('0 0 * * *');
                ->cron('* * * * *');

            return $schedule;
        });

        // Run a webhook.
        \Eventy::addAction('webhook.run', function($webhook, $event, $data) {
            // Check webhook mailbox.
            if (!empty($webhook->mailboxes) 
                && is_array($webhook->mailboxes)
                && get_class($data) == 'App\Conversation'
            ) {
                if (!in_array($data->mailbox_id, $webhook->mailboxes)) {
                    return;
                }
            }
            $webhook->run($event, $data);
        }, 20, 3);

        // Conversation Created.
        \Eventy::addAction('conversation.created_by_user', function($conversation, $thread) {
            self::maybeRunWebhook('convo.created', $conversation);
        }, 20, 2);

        // Conversation Created.
        \Eventy::addAction('conversation.created_by_customer', function($conversation, $thread) {
            self::maybeRunWebhook('convo.created', $conversation);
        }, 20, 2);

        // Conversation assigned.
        \Eventy::addAction('conversation.user_changed', function($conversation, $by_user) {
            if (!$conversation->user_id) {
                return;
            }
            self::maybeRunWebhook('convo.assigned', $conversation);
        }, 20, 2);

        // Conversation deleted (soft).
        \Eventy::addAction('conversation.deleted', function($conversation) {
            self::maybeRunWebhook('convo.deleted', $conversation);
        });

        // Conversation deleted forever.
        \Eventy::addAction('conversation.deleting', function($conversation) {
            self::maybeRunWebhook('convo.deleted_forever', $conversation);
        });

        // Conversation bulk deleted forever.
        \Eventy::addAction('conversations.before_delete_forever', function($conversation_ids) {
            $event_exists = false;

            $webhooks = self::getWebhooks();
            foreach ($webhooks as $webhook) {
                if (in_array('convo.deleted_forever', $webhook->events)) {
                    $event_exists = true;
                    break;
                }
            }

            if (!$event_exists) {
                return;
            }
            $conversations = Conversation::whereIn('id', $conversation_ids)->get();
            foreach ($conversations as $conversation) {
                self::maybeRunWebhook('convo.deleted_forever', $conversation);
            }
        });

        // Conversation deleted forever.
        \Eventy::addAction('conversation.state_changed', function($conversation, $user, $prev_state) {
            if ($prev_state == Conversation::STATE_DELETED && $conversation->state == Conversation::STATE_PUBLISHED) {
                self::maybeRunWebhook('convo.restored', $conversation);
            }
        }, 20, 3);

        // Conversation moved.
        \Eventy::addAction('conversation.moved', function($conversation) {
            self::maybeRunWebhook('convo.moved', $conversation);
        });

        // Conversation status changed.
        \Eventy::addAction('conversation.status_changed', function($conversation, $user, $changed_on_reply) {
            // if ($changed_on_reply) {
            //     return false;
            // }
            self::maybeRunWebhook('convo.status', $conversation);
        }, 20, 3);

        // Customer replied.
        \Eventy::addAction('conversation.customer_replied', function($conversation, $thread) {
            self::maybeRunWebhook('convo.customer.reply.created', $conversation);
        }, 20, 2);

        // User replied.
        \Eventy::addAction('conversation.user_replied', function($conversation, $thread) {
            self::maybeRunWebhook('convo.agent.reply.created', $conversation);
        }, 20, 2);

        // Note added.
        \Eventy::addAction('conversation.note_added', function($conversation, $thread) {
            self::maybeRunWebhook('convo.note.created', $conversation);
        }, 20, 2);

        // Customer created.
        \Eventy::addAction('customer.created', function($customer) {
            self::maybeRunWebhook('customer.created', $customer);
        });

        // Customer updated.
        \Eventy::addAction('customer.updated', function($customer) {
            self::maybeRunWebhook('customer.updated', $customer);
        });

        // Add "Trigger Webhook" action to Workflows.
        \Eventy::addFilter('webhooks.events', function($events) {
            // Get all active workflows containing "Trigger Webhook" action.
            if (\Module::isActive('workflows')) {
                $workflows = \Workflow::where('actions', \Helper::sqlLikeOperator(), '%"type":"webhook"%')->get();
                if (count($workflows)) {
                    foreach ($workflows as $workflow) {
                        foreach ($workflow->actions as $ands) {
                            foreach ($ands as $action) {
                                if ($action['type'] == 'webhook' && !empty($action['value'])) {
                                    $events[] = $action['value'];
                                }
                            }
                        }
                    }
                    $events = array_unique($events);
                }
            }
            return $events;
        });
        \Eventy::addFilter('workflows.actions_config', function($actions) {
            $actions['dummy']['items']['webhook'] = [
                'title' => __('Trigger Webhook'),
                'values_custom' => true
            ];
            return $actions;
        });
        \Eventy::addAction('workflows.values_custom', function($type, $value, $mode, $and_i, $row_i, $data) {
            if ($type != 'webhook') {
                return;
            }
            ?>
                <input class="form-control input-sized" name="<?php echo $mode ?>[<?php echo $and_i ?>][<?php echo $row_i ?>][value]" placeholder="custom.webhook.event" value="<?php echo htmlspecialchars($value); ?>" />
                <a href="<?php echo route('settings', ['section' => 'apiwebhooks']) ?>" class="btn btn-link btn-sm" target="_blank"><?php echo __('API & Webhooks') ?></a>
            <?php
            
        }, 20, 6);
        \Eventy::addFilter('workflow.perform_action', function($performed, $type, $operator, $value, $conversation, $workflow) {
            if ($type == 'webhook') {
                self::maybeRunWebhook($value, $conversation);
                return true;
            }

            return $performed;
        }, 20, 6);
    }

    public static function maybeRunWebhook($event, $data)
    {
        // There are no webhooks.
        if (self::$webhooks === null) {
            return;
        }
        $webhooks = self::getWebhooks();
        foreach ($webhooks as $webhook) {
            if (in_array($event, $webhook->events)) {
                \Helper::backgroundAction('webhook.run', [$webhook, $event, $data]);
            }
        }
    }

    public static function getWebhooks()
    {
        // Get all webhooks.
        if (empty(self::$webhooks)) {
            self::$webhooks = \Webhook::get();
        }

        return self::$webhooks;
    }

    public static function formatEntityList($list)
    {
        $formatted_list = [];

        if (!empty($list)) {
            foreach ($list as $item) {
                $formatted_list[] = \ApiWebhooks::formatEntity($item);
            }
        }

        return $formatted_list;
    }

    public static function formatEntity($entity, $full = true, $entity_type = '', $extra_data = [])
    {
        $result = null;

        if (!$entity) {
            return $result;
        }

        // When retrying failed webhooks the entiry is stored as array in DB.
        if (is_array($entity)) {
            return $entity;
        }

        // Conversation.
        if ($entity instanceof Conversation) {
            $threads = [];
        
            if (empty($extra_data['without_threads'])) {
                foreach ($entity->getThreads() as $thread) {
                    $threads[] = self::formatEntity($thread);
                }
            }

            $embedded = [
                'threads' => $threads
            ];

            if (!empty($extra_data['include_timelogs']) && \Module::isActive('timetracking')) {
                $timelogs = \Modules\TimeTracking\Entities\Timelog::where('conversation_id', $entity->id)
                    ->orderBy('id', 'desc')
                    ->get();

                $embedded['timelogs'] = [];
                foreach ($timelogs as $timelog) {
                    $embedded['timelogs'][] = \ApiWebhooks::formatEntity($timelog, false);
                }
            }

            if (!empty($extra_data['include_tags']) && \Module::isActive('tags')) {
                $tags = \Tag::conversationTags($entity);

                $embedded['tags'] = [];
                foreach ($tags as $tag) {
                    $embedded['tags'][] = \ApiWebhooks::formatEntity($tag, false);
                }
            }

            $result = [
                'id'     => $entity->id,
                'number' => $entity->number,
                'threadsCount' => (int)$entity->threads_count,
                'type' => Conversation::$types[$entity->type] ?? 'email',
                'folderId' => $entity->folder_id,
                'status' => Conversation::$statuses[$entity->status] ?? 'active',
                'state' => Conversation::$states[$entity->state] ?? 'published',
                'subject' => $entity->subject."",
                'preview' => $entity->preview,
                'mailboxId' => $entity->mailbox_id,
                'assignee' => ($entity->user_id && $entity->user ? self::formatEntity($entity->user, false) : null),
                'createdBy' => ($entity->source_via == Conversation::PERSON_USER ? self::formatEntity($entity->created_by_user, false) : self::formatEntity($entity->created_by_customer, false)),
                'createdAt' => self::formatEntity($entity->created_at),
                'updatedAt' => self::formatEntity($entity->updated_at),
                'closedBy' => ($entity->status == Conversation::STATUS_CLOSED ? $entity->closed_by_user_id : null),
                'closedByUser' =>  ($entity->status == Conversation::STATUS_CLOSED && $entity->closed_by_user_id ? self::formatEntity($entity->closed_by_user, false) : null),
                'closedAt' => self::formatEntity($entity->closed_at),
                'userUpdatedAt' => self::formatEntity($entity->user_updated_at),
                'customerWaitingSince' => [
                    'time' => self::formatEntity($entity->last_reply_at),
                    'friendly' => $entity->getWaitingSince(),
                    'latestReplyFrom' => Conversation::$persons[$entity->last_reply_from] ?? '',
                ],
                'source' => [
                    'type' => Conversation::$source_types[$entity->source_type] ?? '',
                    'via' => Conversation::$persons[$entity->source_via] ?? '',
                ],
                // 'tags' 
                'cc' => $entity->getCcArray(),
                'bcc' => $entity->getBccArray(),
                'customer' => ($entity->customer_id && $entity->customer ? self::formatEntity($entity->customer, false) : null),
                // 'customFields'
                '_embedded' => $embedded,
                // '_links' => [
                //     // todo when API is ready.
                // ]
            ];

            // Custom fields.
            if (\Module::isActive('customfields')) {
                $custom_fields_list = $extra_data['custom_fields'] ?? [];
                if (empty($custom_fields_list)) {
                    $custom_fields_list = \CustomField::getCustomFieldsWithValues($entity->mailbox_id, $entity->id);
                }
                if (!empty($custom_fields_list)) {
                    foreach ($custom_fields_list as $custom_field) {
                        $result['customFields'][] = \ApiWebhooks::formatEntity($custom_field);
                    }
                }
            }
        }

        // User.
        if ($entity instanceof User) {
            if ($full) {
                $result = [
                    'id' => $entity->id,
                    'firstName' => $entity->first_name,
                    'lastName' => $entity->last_name,
                    'email' => $entity->email,
                    'role' => User::$roles[$entity->role] ?? User::ROLE_USER,
                    'alternateEmails' => $entity->emails,
                    'jobTitle' => $entity->job_title,
                    'phone' => $entity->phone,
                    'timezone' => $entity->timezone,
                    'photoUrl' => $entity->getPhotoUrl(false),
                    'language' => $entity->locale,
                    'createdAt' => self::formatEntity($entity->created_at),
                    'updatedAt' => self::formatEntity($entity->updated_at),
                ];
            } else {
                $result = [
                    'id' => $entity->id,
                    'type' => 'user',
                    'firstName' => $entity->first_name,
                    'lastName' => $entity->last_name,
                    'photoUrl' => $entity->getPhotoUrl(false),
                    'email' => $entity->email,
                ];
            }
        }

        // Customer.
        if ($entity instanceof Customer) {
            if ($full) {

                $emails = [];
                foreach ($entity->emails as $email) {
                    $emails[] = self::formatEntity($email, false);
                }

                $phones = [];
                foreach ($entity->getPhones() as $phone) {
                    $phones[] = [
                        'id' => 0,
                        'value' => $phone['value'] ?? '',
                        'type' => Customer::$phone_types[$phone['type']] ?? 'other',
                    ];
                }

                $social_profiles = [];
                foreach ($entity->getSocialProfiles() as $social_profile) {
                    $social_profiles[] = [
                        'id' => 0,
                        'value' => $social_profile['value'] ?? '',
                        'type' => Customer::$social_types[$social_profile['type']] ?? 'other',
                    ];
                }

                $websites = [];
                foreach ($entity->getWebsites() as $website) {
                    $websites[] = [
                        'id' => 0,
                        'value' => $website,
                    ];
                }

                $result = [
                    'id' => $entity->id,
                    'firstName' => $entity->first_name,
                    'lastName' => $entity->last_name,
                    //'gender' => Customer::$genders[$entity->gender] ?? 'unknown',
                    'jobTitle' => $entity->job_title,
                    //'location' => $entity->address,
                    'company' => $entity->company,
                    'photoType' => Customer::$photo_types[$entity->photo_type ?? Customer::PHOTO_TYPE_UKNOWN] ?? 'unknown',
                    'photoUrl' => $entity->getPhotoUrl(false),
                    //'age' => $entity->age,
                    'createdAt' => self::formatEntity($entity->created_at),
                    'updatedAt' => self::formatEntity($entity->updated_at),
                    'notes' => $entity->notes,
                    '_embedded' => [
                        'emails' => $emails,
                        'phones' => $phones,
                        //'chats' => [],
                        'social_profiles' => $social_profiles,
                        'websites' => $websites,
                        //'properties' => $websites,
                        'address' => self::formatEntity($entity, false, 'address')
                    ],
                    // '_links' => [
                    //     // todo when API is ready.
                    // ]
                ];

                // Customer fields.
                if (\Module::isActive('crm')) {
                    $customer_fields_list = \CustomerField::getCustomerFieldsWithValues($entity->id);
                    
                    if (!empty($customer_fields_list)) {
                        foreach ($customer_fields_list as $customer_field) {
                            $result['customerFields'][] = \ApiWebhooks::formatEntity($customer_field);
                        }
                    }
                }
            } else {
                $result = [
                    'id' => $entity->id,
                    'type' => 'customer',
                    'firstName' => $entity->first_name,
                    'lastName' => $entity->last_name,
                    'photoUrl' => $entity->getPhotoUrl(false),
                    'email' => $entity->getMainEmail(),
                ];
            }
        }

        // Thread.
        if ($entity instanceof Thread) {
            $result = [
                'id' => $entity->id,
                'type' => $entity->getTypeName(),
                'status' => Thread::$statuses[$entity->status] ?? 'active',
                'state' => $entity->getStateName(),
                'action' => [
                    'type' => $entity->getActionTypeName(),
                    'text' => $entity->conversation ? strip_tags($entity->getActionDescription($entity->conversation->number, false)) : '',
                    'associatedEntities' => [
                        // todo
                    ],
                ],
                'body' => $entity->body,
                'source' => [
                    'type' => Thread::$source_types[$entity->source_type] ?? '',
                    'via' => Thread::$persons[$entity->source_via] ?? '',
                ],
                'customer' => ($entity->customer_id && $entity->customer ? self::formatEntity($entity->customer, false) : null),
                'createdBy' => ($entity->source_via == Thread::PERSON_USER ? self::formatEntity($entity->created_by_user, false) : self::formatEntity($entity->created_by_customer, false)),
                'assignedTo' => ($entity->user_id && $entity->user ? self::formatEntity($entity->user, false) : null),
                //'savedReplyId' => 
                'to' => $entity->getToArray(),
                'cc' => $entity->getCcArray(),
                'bcc' => $entity->getBccArray(),
                'createdAt' => self::formatEntity($entity->created_at),
                'openedAt' => self::formatEntity($entity->opened_at),
                '_embedded' => [
                    'attachments' => $entity->has_attachments ? \ApiWebhooks::formatEntityList($entity->attachments) : [],
                ],
                // '_links' => [
                //     // todo
                // ]
            ];
        }

        // Attachment.
        if ($entity instanceof Attachment) {
            $result = [
                'id' => $entity->id,
                'fileName' => $entity->file_name,
                'fileUrl' => $entity->url(),
                'mimeType' => $entity->mime_type,
                'size' => $entity->size,
            ];
        }

        // Mailbox.
        if ($entity instanceof Mailbox) {
            $result = [
                'id' => $entity->id,
                'name' => $entity->name,
                'email' => $entity->email,
                //'aliases' => $entity->aliases,
                'createdAt' => self::formatEntity($entity->created_at),
                'updatedAt' => self::formatEntity($entity->updated_at),
            ];
        }

        // Folder.
        if ($entity instanceof Folder) {
            $result = [
                'id' => $entity->id,
                'name' => $entity->getTypeName(),
                'type' => $entity->type,
                'userId' => $entity->user_id,
                'totalCount' => $entity->total_count,
                'activeCount' => $entity->active_count,
                'meta' => $entity->meta,
            ];
        }

        // Email.
        if ($entity instanceof Email) {
            $result = [
                'id' => $entity->id,
                'value' => $entity->email,
                'type' => Email::$types[$entity->type] ?? '',
            ];
        }

        // Webhook.
        if ($entity instanceof \Webhook) {
            $result = [
                'id' => $entity->id,
                'url' => $entity->url,
                'events' => $entity->events,
                'lastRunTime' => self::formatEntity($entity->last_run_time),
                'lastRunError' => (string)$entity->last_run_error,
            ];
        }

        // Date.
        if ($entity instanceof Carbon) {
            $result = $entity->setTimezone('UTC')->format('Y-m-d\TH:i:s\Z');
        }

        if ($entity_type == 'address') {
            $result = [
                'city' => $entity->city,
                'state' => $entity->state,
                'zip' => $entity->zip,
                'country' => $entity->country,
                'address' => $entity->address,
            ];
            // if ($full) {
            //     $result['_links'] = [];
            // }
        }

        // Custom fields.
        if (\Module::isActive('customfields')) {
            if ($entity instanceof \CustomField) {
                
                if (empty($extra_data['customfield_structure'])) {
                    // With values.
                    $text = '';
                    if ($entity->type == \CustomField::TYPE_DROPDOWN) {
                        if (!empty($entity->options) && !empty($entity->options[$entity->value])) {
                            $text = $entity->options[$entity->value];
                        }
                    }
                    $result = [
                        'id' => $entity->id,
                        'name' => $entity->name,
                        'value' => (string)$entity->value,
                        'text' => $text,
                    ];
                } else {
                    // Structure.
                    $result = [
                        'id' => $entity->id,
                        'name' => $entity->name,
                        'type' => str_replace(' ', '', strtolower(\CustomField::$types[$entity->type ?? \CustomField::TYPE_SINGLE_LINE])),
                        'options' => $entity->options,
                        'required' => (bool)$entity->required,
                        'sortOrder' => $entity->sort_order,
                    ];
                }
            }
        }

        // Customer fields.
        if (\Module::isActive('crm')) {
            if ($entity instanceof \CustomerField) {
                
                if (empty($extra_data['customfield_structure'])) {
                    // With values.
                    $text = '';
                    if ($entity->type == \CustomerField::TYPE_DROPDOWN) {
                        if (!empty($entity->options) && !empty($entity->options[$entity->value])) {
                            $text = $entity->options[$entity->value];
                        }
                    }
                    $result = [
                        'id' => $entity->id,
                        'name' => $entity->name,
                        'value' => (string)$entity->value,
                        'text' => $text,
                    ];
                } else {
                    // Structure.
                    $result = [
                        'id' => $entity->id,
                        'name' => $entity->name,
                        'type' => str_replace(' ', '', strtolower(\CustomerField::$types[$entity->type ?? \CustomerField::TYPE_SINGLE_LINE])),
                        'options' => $entity->options,
                        'required' => (bool)$entity->required,
                        'sortOrder' => $entity->sort_order,
                    ];
                }
            }
        }

        // Timelog entry.
        if (\Module::isActive('timetracking')) {
            if ($entity instanceof \Modules\TimeTracking\Entities\Timelog) {
                    $result = [
                        'id' => $entity->id,
                        'conversationStatus' => Conversation::$statuses[$entity->conversation_status] ?? 'active',
                        'userId' => $entity->user_id,
                        'timeSpent' => $entity->time_spent,
                        'paused' => (bool)$entity->paused,
                        'finished' => (bool)$entity->finished,
                        'createdAt' => self::formatEntity($entity->created_at),
                        'updatedAt' => self::formatEntity($entity->updated_at),
                    ];
                    if ($full) {
                        $result['conversationId'] = $entity->conversation_id;
                    }
            }
        }

        // Tag.
        if (\Module::isActive('tags')) {
            if ($entity instanceof \Tag) {
                $result = [
                    'id' => $entity->id,
                    'name' => $entity->name
                ];
                if ($full) {
                    $result['counter'] = $entity->counter;
                    $result['color'] = $entity->color;
                }
            }
        }

        return $result;
    }

    public static function getApiKey()
    {
        return md5(config('app.key').'api_key'.config('apiwebhooks.api_key_salt'));
    }

    public static function clearWebhooksCache()
    {
        self::$webhooks = [];
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerTranslations();
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('apiwebhooks.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'apiwebhooks'
        );
        if (\Helper::isDev()) {
            $this->mergeConfigFrom(
                __DIR__.'/../Config/apidoc.php', 'apidoc'
            );
        }
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/apiwebhooks');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ],'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/apiwebhooks';
        }, \Config::get('view.paths')), [$sourcePath]), 'apiwebhooks');

        // ApiDocs vendor.
        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/apidoc';
        }, \Config::get('view.paths')), [__DIR__.'/../Resources/views/vendor/apidoc']), 'apidoc');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $this->loadJsonTranslationsFrom(__DIR__ .'/../Resources/lang');
    }

    /**
     * Register an additional directory of factories.
     * @source https://github.com/sebastiaanluca/laravel-resource-flow/blob/develop/src/Modules/ModuleServiceProvider.php#L66
     */
    public function registerFactories()
    {
        if (! app()->environment('production')) {
            app(Factory::class)->load(__DIR__ . '/../Database/factories');
        }
    }

    public function registerCommands()
    {
        $this->commands([
            \Modules\ApiWebhooks\Console\WebhooksProcess::class,
            \Modules\ApiWebhooks\Console\WebhooksCleanLogs::class,
        ]);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
