# Overview

## Prerequisites

To enable API in the FreeScout you need to install <a href="https://freescout.net/module/api-webhooks/" target="_blank">API &amp; Webhooks</a> module.

All API requests should be made using UTF-8 encoding.

## Authentication

API Key can be retrieved in "Manage » API &amp; Webhoks".

There are three ways to authenticate using API Key to make API calls:

1) Pass API Key in "api_key" GET-parameter:

<code>http://demo.freescout.net/api/conversations?api_key=c2ba609c687a3425402b9d881e5075db</code>

2) Pass API Key as a username with a random password via HTTP Basic authentication:

<code>Authorization: <strong>c2ba609c687a3425402b9d881e5075db</strong> randompassword</code>

3) Pass API Key as "X-FreeScout-API-Key" HTTP header:

<code>X-FreeScout-API-Key: <strong>c2ba609c687a3425402b9d881e5075db</strong></code>

## HTTP Methods

<table>
  <thead>
    <tr>
      <th>Method</th>
      <th>Meaning</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><code>GET</code></td>
      <td>Read one or more entities.</td>
    </tr>
    <tr>
      <td><code>POST</code></td>
      <td>Create new entity.</td>
    </tr>
    {{--<tr>
      <td><code>PUT</code></td>
      <td>Update entity, overwriting all fields. When a field is not set in the request, it’s the same as if it was set with <code>null</code> value.</td>
    </tr>
    <tr>
      <td><code>PATCH</code></td>
      <td>Partially update entity using the <a href="http://jsonpatch.com/" target="_blank" rel="nofollow">JSON Patch</a> format.</td>
    </tr>--}}
    <tr>
      <td><code>DELETE</code></td>
      <td>Delete single entity.</td>
    </tr>
    {{--<tr>
      <td><code>HEAD</code></td>
      <td>Same as <code>GET</code>, but the API returns an empty response.</td>
    </tr>--}}
    <tr>
      <td><code>OPTIONS</code></td>
      <td>Returns allowed HTTP methods and <code>Access-Control-*</code> headers for <code>CORS</code> preflight request.</td>
    </tr>
  </tbody>
</table>

## Status Codes

<table>
  <thead>
    <tr>
      <th>Status Code</th>
      <th>Name</th>
      <th>Description</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><code>200</code></td>
      <td>OK</td>
      <td>Request was successful and response contains data.</td>
    </tr>
    <tr>
      <td><code>201</code></td>
      <td>Created</td>
      <td>Resource was created. Response will contain either <code>Location</code> header or <code>Resource-ID</code> header. Usually for <code>POST</code> requests.</td>
    </tr>
    <tr>
      <td><code>204</code></td>
      <td>No Content</td>
      <td>Request was successful and response is empty. Typical response for <code>PUT</code>, <code>PATCH</code> and <code>DELETE</code> requests.</td>
    </tr>
    <tr>
      <td><code>301</code></td>
      <td>Moved permanently</td>
      <td>Requested entity existed under a different ID or path. Check the <code>Location</code> header.</td>
    </tr>
    <tr>
      <td><code>400</code></td>
      <td>Bad Request</td>
      <td>Client error - the request doesn’t meet all requirements, see <a href="#errors">error messages</a>.</td>
    </tr>
    <tr>
      <td><code>401</code></td>
      <td>Not Authorized</td>
      <td>API Key is either not provided or not valid.</td>
    </tr>
    <tr>
      <td><code>403</code></td>
      <td>Access denied</td>
      <td>Your API Key is valid, but you are denied access - the response should contain details.</td>
    </tr>
    <tr>
      <td><code>404</code></td>
      <td>Not Found</td>
      <td>Resource was not found - it doesn’t exist or it was deleted.</td>
    </tr>
    <tr>
      <td><code>405</code></td>
      <td>Method Not Allowed</td>
      <td>This error indicates that you are using incorrect HTTP method or incorrect API endpoint URL (allowed HTTP methods are URLs are described in this documentation for each API method) or you need to update API & Webhooks Module to the latest version (in older versions not all API methods were present).</td>
    </tr>
    <tr>
      <td><code>409</code></td>
      <td>Conflict</td>
      <td>Resource cannot be created because conflicting entity already exists.</td>
    </tr>
    <tr>
      <td><code>412</code></td>
      <td>Precondition failed</td>
      <td>The request was well formed and valid, but some other conditions were not met.</td>
    </tr>
    <tr>
      <td><code>413</code></td>
      <td>Request Entity Too Large</td>
      <td>The payload of the request is larger than limit of the <a href="https://www.cyberciti.biz/faq/linux-unix-bsd-nginx-413-request-entity-too-large/" target="_blank" rel="nofollow">web server or PHP</a>.</td>
    </tr>
    <tr>
      <td><code>415</code></td>
      <td>Unsupported Media Type</td>
      <td>The API is unable to work with the provided payload.</td>
    </tr>
    {{--<tr>
      <td><code>429</code></td>
      <td>Too Many Requests</td>
      <td>You reached the <a href="">rate limit</a></td>
    </tr>--}}
    <tr>
      <td><code>500</code></td>
      <td>Internal Server Error</td>
      <td>Whoops, looks like something went wrong on our end.</td>
    </tr>
    {{--<tr>
      <td><code>503</code></td>
      <td>Service Unavailable</td>
      <td>The API cannot process the request at the moment</td>
    </tr>--}}
    <tr>
      <td><code>504</code></td>
      <td>Gateway Timeout</td>
      <td>The API call timed-out. It is safe to retry the request after a short delay.</td>
    </tr>
  </tbody>
</table>

## Date Format

All dates in the API are displayed and expected to be in <a href="https://en.wikipedia.org/wiki/ISO_8601" target="_blank" rel="nofollow">ISO 8601</a> format in the UTC timezone:<br/>
<code>YYYY-MM-DDThh:mm:ssZ</code> ("Z" is the designator for the zero UTC offset).

Example: <code>2020-01-02T23:00:00Z</code>

## Errors

Example of the error response is presented on the right side (or below).

<pre>
	<code class="language-bash hljs">
{
    "message": "Bad request",
    "errorCode": "BAD REQUEST",
    "_embedded": {
        "errors": [
            {
                "path": "subject",
                "message": "may not be empty",
                "source": "JSON"
            },
            {
                "path": "status",
                "message": "Expected one of these: 'active', 'spam', 'open', 'closed', 'pending'",
                "rejectedValue": "Wrong",
                "source": "JSON"
            }
        ]
    }
}
</code>
</pre>

<pre>
	<code class="language-javascript hljs">
{
    "message": "Bad request",
    "errorCode": "BAD REQUEST",
    "_embedded": {
        "errors": [
            {
                "path": "subject",
                "message": "may not be empty",
                "source": "JSON"
            },
            {
                "path": "status",
                "message": "Expected one of these: 'active', 'spam', 'open', 'closed', 'pending'",
                "rejectedValue": "Wrong",
                "source": "JSON"
            }
        ]
    }
}
</code>
</pre>

<pre>
	<code class="language-php hljs">
{
    "message": "Bad request",
    "_embedded": {
        "errors": [
            {
                "path": "subject",
                "message": "may not be empty",
                "source": "JSON"
            },
            {
                "path": "status",
                "message": "Expected one of these: 'active', 'spam', 'open', 'closed', 'pending'",
                "rejectedValue": "Wrong",
                "source": "JSON"
            }
        ]
    }
}
</code>
</pre>

{{--<h3>Error Codes</h3>

<dl>
  {{--<dt>EMPTY VALUE</dt>
  <dd>Field cannot be empty.</dd>

  <dt>VALUE NOT SUPPORTED</dt>
  <dd>Provided value was not among predefined set of values.</dd>

  <dt>SIZE</dt>
  <dd>Value length must be in a predefined range. Please check documentation for the endpoint you were calling.</dd>

  <dt>NOT WELL-FORMED EMAIL ADDRESS</dt>
  <dd>Value is not a well formed email address.</dd>

  <dt>NULL VALUE</dt>
  <dd>Field cannot be null.</dd>

  <dt>MALFORMED DATE</dt>
  <dd>The value must be a valid ISO 8601 date with the format yyyy-MM-dd'T'HH:mm:ss'Z', for example 2017-03-31T23:30:01Z.</dd>

  <dt>INVALID NUMBER</dt>
  <dd>Value must be a number of predefined quality, for example must be greater than or equal to 5.</dd>

  <dt>INVALID IDENTIFIER</dt>
  <dd>Value is not a valid entity identifier. For example you may encounter this error if a negative number is used.</dd>-- }}

  <dt>BAD REQUEST</dt>
  <dd>Invalid input data.</dd>

  <dt>ACCESS DENIED</dt>
  <dd>You are not allowed to access the requested resource. There can be multiple reasons for this error - for example, you don’t have access to features like Reports.</dd>

  <dt>CANNOT DELETE ATTACHMENT FROM PUBLISHED CONVERSATION</dt>
  <dd>Attachments can only be deleted from drafts, not published conversations.</dd>

  {{--<dt>INVALID URI</dt>
  <dd>The endpoint URI is not well formed. While the API always tries to explain the specific error and pinpoint wrong query parameters it is not always possible. Please check documentation for the endpoint you were calling.</dd>-- }}

  <dt>INVALID JSON</dt>
  <dd>The request body either isn’t well formed JSON or JSON structure doesn’t conform to the endpoint’s specification - number is used instead of object or the other way around.</dd>

  <dt>CONFLICT</dt>
  <dd>Entity cannot be created because conflicting entity already exists. Typical example is when you try to add a customer email that is already being used by another customer.</dd>
  
  {{--<dt>INVALID NUMBER</dt>
  <dd>The provided value is not a non-negative integer (i.e. five instead of 5321).</dd>-- }}

  <dt>UNSUPPORTED MEDIA TYPE</dt>
  <dd>API expected JSON Content-Type header but received something else.</dd>

  <dt>UNSUPPORTED HTTP METHOD</dt>
  <dd>The given endpoint does not support the HTTP method used, see the error message for list of supported HTTP methods and/or consult the documentation for the particular endpoint.</dd>

  <dt>ALLOWED PATCH OPERATIONS</dt>
  <dd>You either used invalid JSONPatch operation or the operation you used is not permitted for the update path. For example you can only remove field that is nullable.</dd>

</dl>--}}


{{--

CONVERSATION LOCKED - TOO MANY THREADS
The maximum number of threads in a single conversation was reached. If you want to add a new thread, create a new conversation. If you wanted to create a conversation, please ensure the number of threads is below 100.


CONVERSATION LOCKED - CONVERSATION IS TOO OLD
Company policy prevents updating old conversations. If you want to add a new thread, create a new conversation.

VALID CONVERSATION OWNER
The user ID is not a valid conversation owner - it has to be ID of a user from your account.

VALID IMPORTED THREAD
When creating imported threads it is possible to set a createdAt value, but only if imported field is set to true.

ENUM VALUE
The provided value has to be from a predefined list of values, the error message provides list of the allowed values.

LIST OF ENUM VALUES
The provided list of values has to be from a predefined list of values, separated by comma. The error message provides a list of the allowed values.

REJECTED REQUEST
The request was rejected because the URI did not conform to the rules. Please consult the documentation for the particular endpoint.
--}}