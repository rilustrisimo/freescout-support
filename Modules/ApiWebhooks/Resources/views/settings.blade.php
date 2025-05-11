<form class="form-horizontal margin-top margin-bottom" method="POST" action="">
    {{ csrf_field() }}

    <input type="hidden" name="settings[dummy]" value="1" />


    <h3 class="subheader">API</h3>

    <div class="form-group">
        <label for="url" class="col-sm-2 control-label">{{ __('API Key') }}</label>
        <div class="col-sm-6">
            <div class="input-group input-sized-lg">
                <input type="text" class="form-control disabled" readonly value="{{ \ApiWebhooks::getApiKey() }}">
                <span class="input-group-btn">
                    <button class="btn btn-xs btn-default" name="action" value="regenerate_api_key">{{ __('Regenerate') }}</button>
                </span>
            </div>
            <input type="hidden" name="settings[apiwebhooks.api_key_salt]" value="{{ $settings['apiwebhooks.api_key_salt'] }}" />
            <a href="https://api-docs.freescout.net/" target="_blank">{{ __('API Documentation') }}</a>
        </div>
    </div>

    <div class="form-group">
        <label for="url" class="col-sm-2 control-label">{{ __('Allowed CORS Hosts') }}</label>
        <div class="col-sm-6">
            
            <input type="text" name="settings[apiwebhooks.cors_hosts]" value="{{ $settings['apiwebhooks.cors_hosts'] }}" class="form-control input-sized-lg"/>

            <p class="form-help">
                {{ __("Specify 'Access-Control-Allow-Origin' header value to allow access to the API from JavaScript.") }}<br/>
                <a href="#apiwh-cors-example" data-toggle="collapse">{{ __('Examples') }} <span class="caret"></span></a><div id="apiwh-cors-example" class="collapse text-help">
                ● * – {{ __('any host') }}<br/>
                ● https://example.org<br/>
                ● http://localhost:3000</div>
            </p>
        </div>
    </div>

    <div class="form-group margin-top-0 margin-bottom-0">
        <div class="col-sm-6 col-sm-offset-2">
            <button type="submit" class="btn btn-primary" name="action" value="api_save">
                {{ __('Save') }}
            </button>
        </div>
    </div>

</form>

<form class="form-horizontal margin-top margin-bottom" method="POST" action="">
    {{ csrf_field() }}

    <input type="hidden" name="settings[apiwebhooks.api_key_salt]" value="{{ $settings['apiwebhooks.api_key_salt'] }}" />
    <input type="hidden" name="settings[apiwebhooks.cors_hosts]" value="{{ $settings['apiwebhooks.cors_hosts'] }}" />

    <h3 class="subheader">{{ __('Webhooks') }}</h3>

    <div class="form-group">
        <label for="url" class="col-sm-2 control-label">{{ __('Secret Key') }}</label>
        <div class="col-sm-6">
            <label class="control-label text-help">
                <strong>{{ \Webhook::getSecretKey() }}</strong>
            </label>
        </div>
    </div>

    <hr/>

    @include('apiwebhooks::partials/form')

    <div class="form-group margin-top margin-bottom">
        <div class="col-sm-6 col-sm-offset-2">
            <button type="submit" class="btn btn-primary" name="action" value="add">
                {{ __('Add Webhook') }}
            </button>
        </div>
    </div>
</form>

@if (!empty($webhooks))
    @foreach ($webhooks as $webhook)
        <div class="col-lg-8">
            <form class="form-horizontal margin-top panel panel-shaded panel-padded" method="POST" action="">
                {{ csrf_field() }}

                <input type="hidden" name="settings[dummy]" value="1" />
                <input type="hidden" name="id" value="{{ $webhook->id }}" />

                <input type="hidden" name="settings[apiwebhooks.api_key_salt]" value="{{ $settings['apiwebhooks.api_key_salt'] }}" />
                <input type="hidden" name="settings[apiwebhooks.cors_hosts]" value="{{ $settings['apiwebhooks.cors_hosts'] }}" />
                
                @include('apiwebhooks::partials/form', ['webhook' => $webhook])
                @if ($webhook->last_run_time)
                    <div class="form-group">
                        <label for="url" class="col-sm-2 control-label">{{ __('Status') }}</label>
                        <div class="col-sm-6">
                            <label class="control-label text-help">
                                <div class="text-left">
                                @if (!$webhook->last_run_error)
                                    <strong class="label label-success">OK</strong>&nbsp; 
                                    <small class="help-text">{{ App\User::dateFormat($webhook->last_run_time) }}</small>
                                @else
                                    <div class="alert alert-danger alert-narrow margin-bottom-0 text-left">{{ $webhook->last_run_error ?? __('Error') }}</div>
                                    <small class="help-text">{{ App\User::dateFormat($webhook->last_run_time) }}</small>
                                @endif
                                &nbsp; (<a class="small" href="{{ route('apiwebhooks.ajax_html', ['action' => 'webhook_logs', 'param' => $webhook->id]) }}" data-trigger="modal" data-modal-title="{{ __('Webhook Error Log') }}" data-modal-no-footer="true" data-modal-size="lg">{{ __('Error Log') }}</a>)
                                </div>
                            </label>
                        </div>
                    </div>
                @endif
                <div class="form-group margin-top margin-bottom-0">
                    <div class="col-sm-6 col-sm-offset-2">
                        <button type="submit" class="btn btn-primary" name="action" value="save_webhook">
                            {{ __('Save') }}
                        </button> 
                        <a href="#" class="btn btn-link text-danger apiwh-delete" data-webhook_id="{{ $webhook->id }}" data-loading-text="{{ 'Deleting' }}…">{{ __('Delete') }}</a>
                    </div>
                </div>
            </form>
        </div>
    @endforeach
@endif

@section('javascript')
    @parent
    initApiWebhooksSettings('{{ __('Delete this webhook?') }}', '{{ __('All (default)') }}');
@endsection