<?php

/**
 * Web and Webhooks
 */
Route::group(['middleware' => 'web', 'prefix' => \Helper::getSubdirectory(), 'namespace' => 'Modules\ApiWebhooks\Http\Controllers'], function()
{
    Route::get('/', 'ApiWebhooksController@index');

    Route::post('/api-webhooks/ajax', ['uses' => 'ApiWebhooksController@ajax', 'middleware' => ['auth', 'roles'], 'roles' => ['admin'], 'laroute' => true])->name('apiwebhooks.ajax');
    Route::get('/api-webhooks/ajax-html/{action}/{param}', ['uses' => 'ApiWebhooksController@ajaxHtml', 'middleware' => ['auth', 'roles'], 'roles' => ['admin']])->name('apiwebhooks.ajax_html');
});

/**
 * API.
 */
Route::group(['middleware' => ['bindings', \Modules\ApiWebhooks\Http\Middleware\ApiAuth::class], 'prefix' => \Helper::getSubdirectory(true).'api/', 'namespace' => 'Modules\ApiWebhooks\Http\Controllers'], function()
{
	// This route returns CORS headers for OPTIONS requests.
	Route::options('{all}', 'ApiController@all')->where('all', '.*');

	// Conversations
	Route::post('/conversations', 'ApiController@createConversation');
	Route::get('/conversations/{conversationId}', 'ApiController@getConversation');
	Route::get('/conversations', 'ApiController@listConversations');
	Route::put('/conversations/{conversationId}', 'ApiController@updateConversation');
	Route::delete('/conversations/{conversationId}', 'ApiController@deleteConversation');

	// Threads
	Route::post('/conversations/{conversationId}/threads', 'ApiController@createThread');

	// Customers
    Route::post('/customers', 'ApiController@createCustomer');
    Route::get('/customers/{customerId}', 'ApiController@getCustomer');
    Route::get('/customers', 'ApiController@listCustomers');
    Route::put('/customers/{customerId}', 'ApiController@updateCustomer');
    Route::put('/customers/{customerId}/customer_fields', 'ApiController@updateCustomerFields');

	// Users
    Route::post('/users', 'ApiController@createUser');
    Route::get('/users/{userId}', 'ApiController@getUser');
    Route::get('/users', 'ApiController@listUsers');
    Route::delete('/users/{userId}', 'ApiController@deleteUser');

	// Mailboxes
    Route::get('/mailboxes', 'ApiController@listMailboxes');
    Route::get('/mailboxes/{mailboxId}/custom_fields', 'ApiController@mailboxCustomFields');
    Route::get('/mailboxes/{mailboxId}/folders', 'ApiController@mailboxFolders');

	// Webhooks
	Route::post('/webhooks', 'ApiController@createWebhook');
    Route::delete('/webhooks/{webhookId}', 'ApiController@deleteWebhook');

	// Custom Fields
	Route::put('/conversations/{conversationId}/custom_fields', 'ApiController@updateCustomFields');

	// Time Tracking
	Route::get('/conversations/{conversationId}/timelogs', 'ApiController@listTimelogs');

	// Tags
	Route::get('/tags', 'ApiController@listTags');
	Route::put('/conversations/{conversationId}/tags', 'ApiController@updateTags');
});
