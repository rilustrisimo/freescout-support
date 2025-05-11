@if (!empty($logs))
    <table class="table table-striped">
    	<thead>
    		<tr>
    			<th>{{ __('Date & Event') }}</th>
    			<th>{{ __('Status') }}</th>
    			<th>{{ __('Attempts') }}</th>
    		</tr>
    	</thead>
    	<tbody>
    		@foreach($logs as $log)
    		<tr>
    			<td class="small">{{ App\User::dateFormat($log->created_at) }}<br/><i>{{ $log->event }}</i></td>
    			<td>
    				@if ($log->isOk())
    					<div class="label label-success">{{ $log->status_code }}</div>
    				@else
    					<div class="label label-danger">{{ $log->status_code }}</div>
    				@endif 
                    @if ($log->error) &nbsp;<code>{{ $log->error }}</code>@endif
    			</td>
    			<td><strong>{{ $log->attempts }}</strong> @if (!$log->finished)/ {{ \Webhook::MAX_ATTEMPTS }}@endif<br/><small>{{ App\User::dateFormat($log->updated_at) }}</small></td>
    		</tr>
    		@endforeach
    	</tbody>
    </table>
@else
    <div class="alert alert-info">{{ __('No logs found.') }}</div>
@endif