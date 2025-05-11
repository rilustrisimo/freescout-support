@php
    if (!isset($data)) {
        $data = [];
    }
@endphp

<div class="form-group">
    <label for="url" class="col-sm-2 control-label">{{ __('URL') }}</label>
    <div class="col-sm-6">
        <input type="url" class="form-control apiwh-input-url" value="{{ $webhook->url ?? '' }}" name="url" required maxlength="255"/>
    </div>
</div>
<div class="form-group">
    <label for="events" class="col-sm-2 control-label">{{ __('Events') }}</label>
    <div class="col-sm-6">
        <select id="in_imap_folders" class="form-control apiwh-input-events" name="events[]" multiple required>
            @foreach (\Webhook::getAllEvents() as $event)
                <option value="{{ $event }}" @if (!empty($webhook->events) && in_array($event, $webhook->events)) selected="selected" @endif>{{ $event }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-group">
    <label for="mailboxes" class="col-sm-2 control-label">{{ __('Mailboxes') }}</label>
    <div class="col-sm-6">
        <select id="in_imap_folders" class="form-control apiwh-input-mailboxes" name="mailboxes[]" multiple>
            @foreach (auth()->user()->mailboxesCanView() as $wh_mailbox)
                <option value="{{ $wh_mailbox->id }}" @if (!empty($webhook->mailboxes) && in_array($wh_mailbox->id, $webhook->mailboxes)) selected="selected" @endif>{{ $wh_mailbox->name }}</option>
            @endforeach
        </select>
    </div>
</div>