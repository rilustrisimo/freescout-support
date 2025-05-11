```bash
curl -X {{$route['methods'][0]}} {{$route['methods'][0] == 'GET' ? '-G ' : ''}}"{{ rtrim($baseUrl, '/')}}/{{ ltrim($route['boundUri'], '/') }}@if(count($route['cleanQueryParameters']))?@foreach($route['cleanQueryParameters'] as $parameter => $value)
{{ urlencode($parameter) }}={{ urlencode($value) }}@if(!$loop->last)&@endif
@endforeach
@endif" @if(count($route['headers']))\
@foreach($route['headers'] as $header => $value)
    -H "{{$header}}: {{$value}}"@if(! ($loop->last) || ($loop->last && count($route['bodyParameters']))) \
@endif
@endforeach
@endif
@php
    foreach($route['cleanBodyParameters'] as $param_name => $param_value) {
        if (!empty($param_value[0]) && ($param_value[0] == '{' || $param_value[0] == '[')) {
            $route['cleanBodyParameters'][$param_name] = json_decode($param_value);
        }
    }
@endphp
@if(count($route['cleanBodyParameters']))
    -d '{!! json_encode($route['cleanBodyParameters']) !!}'
@endif

```
