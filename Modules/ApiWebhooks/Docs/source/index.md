---
title: FreeScout API Docs

language_tabs:
- javascript
- bash
- php

includes:

search: false

toc_footers:
- <a href="https://freescout.net" target="_blank">&copy; FreeScout</a>
---
<!-- START_INFO -->
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
    
    <tr>
      <td><code>DELETE</code></td>
      <td>Delete single entity.</td>
    </tr>
    
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
    
    <tr>
      <td><code>500</code></td>
      <td>Internal Server Error</td>
      <td>Whoops, looks like something went wrong on our end.</td>
    </tr>
    
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





<!-- END_INFO -->

#Conversations


<!-- START_1d6ac3c69bc5f2271b33806815418dc6 -->
## Create Conversation

This method creates a conversation in a mailbox with at least one thread.

> Example request:

```javascript
const url = new URL("https://demo.freescout.net/api/conversations");

let headers = {
    "X-FreeScout-API-Key": "c2ba609c687a3425402b9d881e5075db",
    "Content-Type": "application/json",
    "Accept": "application/json",
}
let body = {
    "type": "email",
    "mailboxId": 1,
    "subject": "Hi there",
    "customer": {
        "email": "mark@example.org"
    },
    "threads": [
        {
            "text": "This is the message from a user",
            "type": "message",
            "user": 7
        },
        {
            "text": "This is the note from a user",
            "type": "note",
            "user": 7
        },
        {
            "text": "This is the message from a customer",
            "type": "customer",
            "cc": [
                "antony@example.org"
            ],
            "customer": {
                "email": "mark@example.org",
                "firstName": "Mark"
            }
        }
    ],
    "imported": false,
    "assignTo": 15,
    "status": "active",
    "customFields": [
        {
            "id": 37,
            "value": "Some text"
        },
        {
            "id": 18,
            "value": 3
        }
    ],
    "createdAt": "2020-03-16T14:07:23Z",
    "closedAt": "2020-03-16T14:07:23Z"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```

```bash
curl -X POST "https://demo.freescout.net/api/conversations" \
    -H "X-FreeScout-API-Key: c2ba609c687a3425402b9d881e5075db" \
    -H "Content-Type: application/json" \
    -d '{"type":"email","mailboxId":1,"subject":"Hi there","customer":{"email":"mark@example.org"},"threads":[{"text":"This is the message from a user","type":"message","user":7},{"text":"This is the note from a user","type":"note","user":7},{"text":"This is the message from a customer","type":"customer","cc":["antony@example.org"],"customer":{"email":"mark@example.org","firstName":"Mark"}}],"imported":false,"assignTo":15,"status":"active","customFields":[{"id":37,"value":"Some text"},{"id":18,"value":3}],"createdAt":"2020-03-16T14:07:23Z","closedAt":"2020-03-16T14:07:23Z"}'

```

```php

$client = new \GuzzleHttp\Client();
$response = $client->post("https://demo.freescout.net/api/conversations", [
    'headers' => [
            "X-FreeScout-API-Key" => "c2ba609c687a3425402b9d881e5075db",
            "Content-Type" => "application/json",
        ],
    'json' => [
                "type" => "email",
                    "mailboxId" => "1",
                    "subject" => "Hi there",
                    "customer" => array(
   'email' => 'mark@example.org',
),
                    "threads" => array (
  0 => 
  array(
     'text' => 'This is the message from a user',
     'type' => 'message',
     'user' => 7,
  ),
  1 => 
  array(
     'text' => 'This is the note from a user',
     'type' => 'note',
     'user' => 7,
  ),
  2 => 
  array(
     'text' => 'This is the message from a customer',
     'type' => 'customer',
     'cc' => 
    array (
      0 => 'antony@example.org',
    ),
     'customer' => 
    array(
       'email' => 'mark@example.org',
       'firstName' => 'Mark',
    ),
  ),
),
                    "imported" => "",
                    "assignTo" => "15",
                    "status" => "active",
                    "customFields" => array (
  0 => 
  array(
     'id' => 37,
     'value' => 'Some text',
  ),
  1 => 
  array(
     'id' => 18,
     'value' => 3,
  ),
),
                    "createdAt" => "2020-03-16T14:07:23Z",
                    "closedAt" => "2020-03-16T14:07:23Z",
            ],
]);
$body = $response->getBody();
print_r(json_decode((string) $body));
```


> Example response (201):

```json
HTTP/1.1 201 Created
Resource-ID: 35
```

### HTTP Request
`POST api/conversations`

#### Body Parameters

Parameter | Type | Required | Description
--------- | ------- | ------- | ------- | -----------
    type | string |  (required)  | Conversation type: email, phone, chat (after importing "chat" conversation, support agent replies will not reach customer linked to the conversation, as connection to customer's messenger can't be imported).
    mailboxId | number |  (required)  | ID of a mailbox where the conversation is being created.
    subject | string |  (required)  | Conversation’s subject.
    customer | object |  (required)  | Customer associated with the conversation. Customer object must contain a valid customer id or an email address: { "id": 123 } or { "email": "mark@example.org" }. If the id field is defined, the email will be ignored. If the id is not defined, email is used to look up a customer. If a customer does not exist, a new one will be created. If a customer is matched either via id or email field, the rest of the optional fields is ignored. When creating a phone conversation "firstName" or "phone" can be passed instead of "email".
    threads | object |  (required)  | Conversation threads. There has to be least one thread in a conversation. Newest threads go first.
    imported | boolean |  | When imported is set to true (boolean value without quotes), no outgoing emails or notifications will be generated, auto reply will not be sent to the customer.
    assignTo | number |  | User ID to assign new conversation to.
    status | string |  | Conversation status: active, pending, closed.
    customFields | object |  | Conversation custom fields.
    createdAt | string |  | Conversation date (ISO 8601 date time format).
    closedAt | string |  | When the conversation was closed, only applicable for imported conversations (ISO 8601 date time format).

<!-- END_1d6ac3c69bc5f2271b33806815418dc6 -->

<!-- START_7eb9046b1022373c1e9cab182bfc71a9 -->
## Get Conversation

> Example request:

```javascript
const url = new URL("https://demo.freescout.net/api/conversations/1");

    let params = {
            "embed": "threads",
        };
    Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "X-FreeScout-API-Key": "c2ba609c687a3425402b9d881e5075db",
    "Accept": "application/json",
    "Content-Type": "application/json; charset=UTF-8",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```

```bash
curl -X GET -G "https://demo.freescout.net/api/conversations/1?embed=threads" \
    -H "X-FreeScout-API-Key: c2ba609c687a3425402b9d881e5075db"
```

```php

$client = new \GuzzleHttp\Client();
$response = $client->get("https://demo.freescout.net/api/conversations/1", [
    'headers' => [
            "X-FreeScout-API-Key" => "c2ba609c687a3425402b9d881e5075db",
        ],
    'query' => [
            "embed" => "threads",
        ],
]);
$body = $response->getBody();
print_r(json_decode((string) $body));
```


> Example response (200):

```json
{
    "id": 1,
    "number": 3,
    "threadsCount": 2,
    "type": "email",
    "folderId": 11,
    "status": "closed",
    "state": "published",
    "subject": "Refund",
    "preview": "Could you please refund my recent payment...",
    "mailboxId": 15,
    "assignee": {
        "id": 9,
        "type": "user",
        "firstName": "John",
        "lastName": "Doe",
        "email": "johndoe@example.org"
    },
    "createdBy": {
        "id": 11,
        "type": "customer",
        "email": "customer@example.org"
    },
    "createdAt": "2020-03-15T22:46:22Z",
    "updatedAt": "2020-03-15T22:46:22Z",
    "closedBy": 14,
    "closedByUser": {
        "id": 14,
        "type": "user",
        "firstName": "John",
        "lastName": "Doe",
        "photoUrl": "https:\/\/support.example.org\/storage\/users\/5a10629fd2bae86563892b191f6677e7.jpg",
        "email": "johndoe@example.org"
    },
    "closedAt": "2020-03-16T14:07:23Z",
    "userUpdatedAt": "2020-03-16T14:07:23Z",
    "customerWaitingSince": {
        "time": "2020-07-24T20:18:33Z",
        "friendly": "10 hours ago",
        "latestReplyFrom": "customer"
    },
    "source": {
        "type": "email",
        "via": "customer"
    },
    "cc": [
        "fox@example.org"
    ],
    "bcc": [
        "fox@example.org"
    ],
    "customer": {
        "id": 91,
        "type": "customer",
        "firstName": "Rodney",
        "lastName": "Robertson",
        "photoUrl": "https:\/\/support.example.org\/storage\/customers\/7a10629fd2bae86563892b191f6677e7.jpg",
        "email": "rodney@example.org"
    },
    "customFields": [
        {
            "id": 22,
            "name": "Amount",
            "value": "7",
            "text": ""
        },
        {
            "id": 23,
            "name": "Currency",
            "value": "1",
            "text": "USD"
        }
    ],
    "_embedded": {
        "threads": [
            {
                "id": 17,
                "type": "customer",
                "status": "active",
                "state": "published",
                "action": {
                    "type": "changed-ticket-assignee",
                    "text": "John Doe assigned conversation to Mark",
                    "associatedEntities": []
                },
                "body": "Thank you very much!",
                "source": {
                    "type": "email",
                    "via": "customer"
                },
                "customer": {
                    "id": 91,
                    "type": "customer",
                    "firstName": "Rodney",
                    "lastName": "Robertson",
                    "photoUrl": "https:\/\/support.example.org\/storage\/customers\/7a10629fd2bae86563892b191f6677e7.jpg",
                    "email": "rodney@example.org"
                },
                "createdBy": {
                    "id": 91,
                    "type": "customer",
                    "firstName": "Rodney",
                    "lastName": "Robertson",
                    "photoUrl": "https:\/\/support.example.org\/storage\/customers\/7a10629fd2bae86563892b191f6677e7.jpg",
                    "email": "rodney@example.org"
                },
                "assignedTo": {
                    "id": 14,
                    "type": "user",
                    "firstName": "John",
                    "lastName": "Doe",
                    "photoUrl": "https:\/\/support.example.org\/storage\/users\/5a10629fd2bae86563892b191f6677e7.jpg",
                    "email": "johndoe@example.org"
                },
                "to": [
                    "test@example.org"
                ],
                "cc": [
                    "fox@example.org"
                ],
                "bcc": [
                    "fox@example.org"
                ],
                "createdAt": "2020-06-05T20:18:33Z",
                "openedAt": "2020-06-07T10:01:25Z",
                "_embedded": {
                    "attachments": [
                        {
                            "id": 71,
                            "fileName": "example.pdf",
                            "fileUrl": "https:\/\/support.example.org\/storage\/attachment\/7\/3\/1\/example.pdf?id=71&token=c5135450a05cc47d7aa3333d8a3e7831",
                            "mimeType": "application\/pdf",
                            "size": 2331
                        }
                    ]
                }
            }
        ],
        "timelogs": [
            {
                "id": 498,
                "conversationStatus": "pending",
                "userId": 1,
                "timeSpent": 219,
                "paused": false,
                "finished": true,
                "createdAt": "2021-04-21T13-24-01Z",
                "updatedAt": "2021-04-21T13-43-10Z"
            },
            {
                "id": 497,
                "conversationId": 1984,
                "conversationStatus": "active",
                "userId": 1,
                "timeSpent": 711,
                "paused": false,
                "finished": true,
                "createdAt": "2021-04-21T13-22-09Z",
                "updatedAt": "2021-04-21T13-43-10Z"
            }
        ],
        "tags": [
            {
                "id": 57,
                "name": "overdue"
            },
            {
                "id": 39,
                "name": "refund"
            }
        ]
    }
}
```

### HTTP Request
`GET api/conversations/{conversationId}`

#### Query Parameters

Parameter | Required | Description
--------- | ------- | ------- | -----------
    embed |  | Pass comma separated values to include extra data: threads, timelogs, tags. Default: threads.

<!-- END_7eb9046b1022373c1e9cab182bfc71a9 -->

<!-- START_50f5969ffa4376ab4d09a74616534468 -->
## List Conversations

Request parameters can be used to filter conversations. By default conversations are sorted by createdAt (from newest to oldest): ?sortField=createdAt&amp;sortOrder=desc

> Example request:

```javascript
const url = new URL("https://demo.freescout.net/api/conversations");

    let params = {
            "embed": "threads",
            "mailboxId": "123",
            "folderId": "57",
            "status": "active",
            "state": "deleted",
            "type": "email",
            "assignedTo": "35",
            "customerEmail": "john@example.org",
            "customerPhone": "777-777-777",
            "customerId": "17",
            "number": "359",
            "subject": "test",
            "tag": "overdue",
            "createdByUserId": "9",
            "createdByCustomerId": "10",
            "createdSince": "2021-01-07T12:00:03Z",
            "updatedSince": "2021-01-07T12:00:03Z",
            "sortField": "updatedAt",
            "sortOrder": "asc",
            "page": "1",
            "pageSize": "100",
        };
    Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "X-FreeScout-API-Key": "c2ba609c687a3425402b9d881e5075db",
    "Accept": "application/json",
    "Content-Type": "application/json; charset=UTF-8",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```

```bash
curl -X GET -G "https://demo.freescout.net/api/conversations?embed=threads&mailboxId=123&folderId=57&status=active&state=deleted&type=email&assignedTo=35&customerEmail=john%40example.org&customerPhone=777-777-777&customerId=17&number=359&subject=test&tag=overdue&createdByUserId=9&createdByCustomerId=10&createdSince=2021-01-07T12%3A00%3A03Z&updatedSince=2021-01-07T12%3A00%3A03Z&sortField=updatedAt&sortOrder=asc&page=1&pageSize=100" \
    -H "X-FreeScout-API-Key: c2ba609c687a3425402b9d881e5075db"
```

```php

$client = new \GuzzleHttp\Client();
$response = $client->get("https://demo.freescout.net/api/conversations", [
    'headers' => [
            "X-FreeScout-API-Key" => "c2ba609c687a3425402b9d881e5075db",
        ],
    'query' => [
            "embed" => "threads",
            "mailboxId" => "123",
            "folderId" => "57",
            "status" => "active",
            "state" => "deleted",
            "type" => "email",
            "assignedTo" => "35",
            "customerEmail" => "john@example.org",
            "customerPhone" => "777-777-777",
            "customerId" => "17",
            "number" => "359",
            "subject" => "test",
            "tag" => "overdue",
            "createdByUserId" => "9",
            "createdByCustomerId" => "10",
            "createdSince" => "2021-01-07T12:00:03Z",
            "updatedSince" => "2021-01-07T12:00:03Z",
            "sortField" => "updatedAt",
            "sortOrder" => "asc",
            "page" => "1",
            "pageSize" => "100",
        ],
]);
$body = $response->getBody();
print_r(json_decode((string) $body));
```


> Example response (201):

```json
{
    "_embedded": {
        "conversations": [
            {
                "id": 1,
                "number": 3,
                "threads": 2,
                "type": "email",
                "folderId": 11,
                "status": "closed",
                "state": "published",
                "subject": "Refund",
                "preview": "Could you please refund my recent payment...",
                "mailboxId": 15,
                "assignee": {
                    "id": 9,
                    "type": "user",
                    "firstName": "John",
                    "lastName": "Doe",
                    "email": "johndoe@example.org"
                },
                "createdBy": {
                    "id": 11,
                    "type": "customer",
                    "email": "customer@example.org"
                },
                "createdAt": "2020-03-15T22:46:22Z",
                "updatedAt": "2020-03-15T22:46:22Z",
                "closedBy": 14,
                "closedByUser": {
                    "id": 14,
                    "type": "user",
                    "firstName": "John",
                    "lastName": "Doe",
                    "photoUrl": "https:\/\/support.example.org\/storage\/users\/5a10629fd2bae86563892b191f6677e7.jpg",
                    "email": "johndoe@example.org"
                },
                "closedAt": "2020-03-16T14:07:23Z",
                "userUpdatedAt": "2020-03-16T14:07:23Z",
                "customerWaitingSince": {
                    "time": "2020-07-24T20:18:33Z",
                    "friendly": "10 hours ago",
                    "latestReplyFrom": "customer"
                },
                "source": {
                    "type": "email",
                    "via": "customer"
                },
                "cc": [
                    "fox@example.org"
                ],
                "bcc": [
                    "fox@example.org"
                ],
                "customer": {
                    "id": 91,
                    "type": "customer",
                    "firstName": "Rodney",
                    "lastName": "Robertson",
                    "photoUrl": "https:\/\/support.example.org\/storage\/customers\/7a10629fd2bae86563892b191f6677e7.jpg",
                    "email": "rodney@example.org"
                },
                "customFields": [],
                "_embedded": {
                    "threads": []
                }
            }
        ]
    },
    "page": {
        "size": 50,
        "totalElements": 1,
        "totalPages": 1,
        "number": 1
    }
}
```

### HTTP Request
`GET api/conversations`

#### Query Parameters

Parameter | Required | Description
--------- | ------- | ------- | -----------
    embed |  | Pass comma separated values to include extra data: threads, timelogs, tags.
    mailboxId |  | Filter conversations from a specific mailbox. Can contain multiple comma separated IDs.
    folderId |  | Filter conversations from a specific folder ID (filtering by Custom Folder ID provided by Custom Folders Module is not possible).
    status |  | Filter conversation by status (defaults to active): active, pending, closed, spam. Can contain multiple comma separated values.
    state |  | Filter conversation by state: draft, published, deleted.
    type |  | Filter conversation by type: email, phone, chat.
    assignedTo |  | Filter conversations by assignee id. Pass an empty value in order to get unassigned conversations.
    customerEmail |  | Filter conversations by customer email.
    customerPhone |  | Filter conversations by customer phone number.
    customerId |  | Filter conversations by customer ID.
    number |  | Look up conversation by conversation number.
    subject |  | Look up conversations containing a text in the subject.
    tag |  | Look up conversations by tag.
    createdByUserId |  | Filter conversations by a user who created it.
    createdByCustomerId |  | Filter conversations by a customer who created it.
    createdSince |  | Return only conversations created after the specified date.
    updatedSince |  | Return only conversations modified after the specified date.
    sortField |  | Sort the result by specified field: createdAt, mailboxId, number, subject, updatedAt, waitingSince.
    sortOrder |  | Sort order: desc (default), asc.
    page |  | Page number.
    pageSize |  | Page size (defaults to 50).

<!-- END_50f5969ffa4376ab4d09a74616534468 -->

<!-- START_9af4092fc65bbb7be5b27a76d35ddaa8 -->
## Update Conversation

Order of passed parameters (status, assignTo, etc.) determines the order in which changes are made.

> Example request:

```javascript
const url = new URL("https://demo.freescout.net/api/conversations/1");

let headers = {
    "X-FreeScout-API-Key": "c2ba609c687a3425402b9d881e5075db",
    "Content-Type": "application/json",
    "Accept": "application/json",
}
let body = {
    "byUser": 33,
    "status": "active",
    "assignTo": 15,
    "mailboxId": 1,
    "customerId": 7,
    "subject": 0
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```

```bash
curl -X PUT "https://demo.freescout.net/api/conversations/1" \
    -H "X-FreeScout-API-Key: c2ba609c687a3425402b9d881e5075db" \
    -H "Content-Type: application/json" \
    -d '{"byUser":33,"status":"active","assignTo":15,"mailboxId":1,"customerId":7,"subject":0}'

```

```php

$client = new \GuzzleHttp\Client();
$response = $client->put("https://demo.freescout.net/api/conversations/1", [
    'headers' => [
            "X-FreeScout-API-Key" => "c2ba609c687a3425402b9d881e5075db",
            "Content-Type" => "application/json",
        ],
    'json' => [
                "byUser" => "33",
                    "status" => "active",
                    "assignTo" => "15",
                    "mailboxId" => "1",
                    "customerId" => "7",
                    "subject" => "0",
            ],
]);
$body = $response->getBody();
print_r(json_decode((string) $body));
```


> Example response (204):

```json
HTTP/1.1 204 No Content
```

### HTTP Request
`PUT api/conversations/{conversationId}`

#### Body Parameters

Parameter | Type | Required | Description
--------- | ------- | ------- | ------- | -----------
    byUser | number |  | ID of the user updating the conversation. Required when changing: "status", "assignTo" or "mailboxId".
    status | string |  | Change conversation status: active, pending, closed, spam.
    assignTo | number |  | Change conversation assignee to the user with the specified ID.
    mailboxId | number |  | Move conversation to the mailbox with the specified ID.
    customerId | number |  | Change conversation customer to the customer with the specified ID.
    subject | number |  | Change conversation subject.

<!-- END_9af4092fc65bbb7be5b27a76d35ddaa8 -->

<!-- START_4707936ff528253a44af8ffa61aac295 -->
## Delete Conversation

This method deletes a conversation forever.

> Example request:

```javascript
const url = new URL("https://demo.freescout.net/api/conversations/1");

let headers = {
    "X-FreeScout-API-Key": "c2ba609c687a3425402b9d881e5075db",
    "Accept": "application/json",
    "Content-Type": "application/json; charset=UTF-8",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```

```bash
curl -X DELETE "https://demo.freescout.net/api/conversations/1" \
    -H "X-FreeScout-API-Key: c2ba609c687a3425402b9d881e5075db"
```

```php

$client = new \GuzzleHttp\Client();
$response = $client->delete("https://demo.freescout.net/api/conversations/1", [
    'headers' => [
            "X-FreeScout-API-Key" => "c2ba609c687a3425402b9d881e5075db",
        ],
]);
$body = $response->getBody();
print_r(json_decode((string) $body));
```


> Example response (204):

```json
HTTP/1.1 204 No Content
```

### HTTP Request
`DELETE api/conversations/{conversationId}`


<!-- END_4707936ff528253a44af8ffa61aac295 -->

#Custom Fields


<!-- START_e8e93ef443c404f9ef8d0aa18adf6ac3 -->
## Update Custom Fields

> Example request:

```javascript
const url = new URL("https://demo.freescout.net/api/conversations/1/custom_fields");

let headers = {
    "X-FreeScout-API-Key": "c2ba609c687a3425402b9d881e5075db",
    "Content-Type": "application/json",
    "Accept": "application/json",
}
let body = {
    "customFields": [
        {
            "id": 37,
            "value": "Test value"
        }
    ]
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```

```bash
curl -X PUT "https://demo.freescout.net/api/conversations/1/custom_fields" \
    -H "X-FreeScout-API-Key: c2ba609c687a3425402b9d881e5075db" \
    -H "Content-Type: application/json" \
    -d '{"customFields":[{"id":37,"value":"Test value"}]}'

```

```php

$client = new \GuzzleHttp\Client();
$response = $client->put("https://demo.freescout.net/api/conversations/1/custom_fields", [
    'headers' => [
            "X-FreeScout-API-Key" => "c2ba609c687a3425402b9d881e5075db",
            "Content-Type" => "application/json",
        ],
    'json' => [
                "customFields" => array (
  0 => 
  array(
     'id' => 37,
     'value' => 'Test value',
  ),
),
            ],
]);
$body = $response->getBody();
print_r(json_decode((string) $body));
```


> Example response (204):

```json
HTTP/1.1 204 No Content
```

### HTTP Request
`PUT api/conversations/{conversationId}/custom_fields`

#### Body Parameters

Parameter | Type | Required | Description
--------- | ------- | ------- | ------- | -----------
    customFields | array |  (required)  | List of custom fields to be applied to the conversation. When updating a custom filed with type  "Dropdown", in the value field you must provide a number (ID), not a textual value.

<!-- END_e8e93ef443c404f9ef8d0aa18adf6ac3 -->

#Customers


<!-- START_089467e7ea475fb2aca445b2d23f6e7d -->
## Create Customer

This method does not update existing customers. Method makes sure that the email address is unique and does not check uniqueness of other parameters. If the request contains email(s) and customers with all these emails already exist, no customer will be created.

If want to avoid creating duplicate customers with same &quot;firstName&quot;, &quot;lastName&quot; and &quot;phone&quot;, before executing this method use &quot;List Customers&quot; method to check if the customer already exists.

> Example request:

```javascript
const url = new URL("https://demo.freescout.net/api/customers");

let headers = {
    "X-FreeScout-API-Key": "c2ba609c687a3425402b9d881e5075db",
    "Content-Type": "application/json",
    "Accept": "application/json",
}
let body = {
    "firstName": "Mark",
    "lastName": "Morrison",
    "phone": "777-777-777",
    "photoUrl": "https:\/\/example.org\/upload\/customer.jpg",
    "jobTitle": "Secretary",
    "photoType": "unknown",
    "address": {
        "city": "LA",
        "state": "California",
        "zip": "123123",
        "country": "US",
        "address": "1419 Westwood Blvd"
    },
    "notes": "Nothing special to say",
    "company": "Example, Inc",
    "emails": [
        {
            "value": "mark@example.org",
            "type": "home"
        }
    ],
    "phones": [
        {
            "value": "777-777-777",
            "type": "home"
        }
    ],
    "socialProfiles": [
        {
            "value": "@markexample",
            "type": "twitter"
        }
    ],
    "websites": [
        {
            "value": "https:\/\/example.org"
        }
    ]
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```

```bash
curl -X POST "https://demo.freescout.net/api/customers" \
    -H "X-FreeScout-API-Key: c2ba609c687a3425402b9d881e5075db" \
    -H "Content-Type: application/json" \
    -d '{"firstName":"Mark","lastName":"Morrison","phone":"777-777-777","photoUrl":"https:\/\/example.org\/upload\/customer.jpg","jobTitle":"Secretary","photoType":"unknown","address":{"city":"LA","state":"California","zip":"123123","country":"US","address":"1419 Westwood Blvd"},"notes":"Nothing special to say","company":"Example, Inc","emails":[{"value":"mark@example.org","type":"home"}],"phones":[{"value":"777-777-777","type":"home"}],"socialProfiles":[{"value":"@markexample","type":"twitter"}],"websites":[{"value":"https:\/\/example.org"}]}'

```

```php

$client = new \GuzzleHttp\Client();
$response = $client->post("https://demo.freescout.net/api/customers", [
    'headers' => [
            "X-FreeScout-API-Key" => "c2ba609c687a3425402b9d881e5075db",
            "Content-Type" => "application/json",
        ],
    'json' => [
                "firstName" => "Mark",
                    "lastName" => "Morrison",
                    "phone" => "777-777-777",
                    "photoUrl" => "https://example.org/upload/customer.jpg",
                    "jobTitle" => "Secretary",
                    "photoType" => "unknown",
                    "address" => array(
   'city' => 'LA',
   'state' => 'California',
   'zip' => '123123',
   'country' => 'US',
   'address' => '1419 Westwood Blvd',
),
                    "notes" => "Nothing special to say",
                    "company" => "Example, Inc",
                    "emails" => array (
  0 => 
  array(
     'value' => 'mark@example.org',
     'type' => 'home',
  ),
),
                    "phones" => array (
  0 => 
  array(
     'value' => '777-777-777',
     'type' => 'home',
  ),
),
                    "socialProfiles" => array (
  0 => 
  array(
     'value' => '@markexample',
     'type' => 'twitter',
  ),
),
                    "websites" => array (
  0 => 
  array(
     'value' => 'https://example.org',
  ),
),
            ],
]);
$body = $response->getBody();
print_r(json_decode((string) $body));
```


> Example response (201):

```json
HTTP/1.1 201 Created
Resource-ID: 17
```

### HTTP Request
`POST api/customers`

#### Body Parameters

Parameter | Type | Required | Description
--------- | ------- | ------- | ------- | -----------
    firstName | string |  | First name of the customer (max 40 characters).
    lastName | string |  | Last name of the customer (max 40 characters).
    phone | string |  | Phone number.
    photoUrl | string |  | URL of the customer’s photo (max 200 characters).
    jobTitle | string |  | Job title (max 60 characters).
    photoType | string |  | Type of photo: unknown, gravatar, twitter, facebook, googleprofile, googleplus, linkedin.
    address | object |  | Customer's address (country contains <a href="https://en.wikipedia.org/wiki/ISO_3166-1#Current_codes" target="_blank" rel="nofollow">two-letter country code</a>): { "city": "Los Angeles", "state": "California", "zip": "123123", "country": "US", "address": "1419 Westwood Blvd" }.
    notes | string |  | Notes.
    company | string |  | Company (max 60 characters).
    emails | object |  | List of email entries: [ { "value": "mark@example.org", "type": "home" } ].
    phones | object |  | List of phones entries: [ { "value": "777-777-777", "type": "home" } ].
    socialProfiles | object |  | List of social profile entries: [ { "value": "@markexample", "type": "twitter" } ].
    websites | object |  | List of website entries: [ { "value": "https:\/\/example.org" } ].

<!-- END_089467e7ea475fb2aca445b2d23f6e7d -->

<!-- START_f5555906b7b3e8fc19c9d95b2ae1bb4d -->
## Get Customer

> Example request:

```javascript
const url = new URL("https://demo.freescout.net/api/customers/1");

let headers = {
    "X-FreeScout-API-Key": "c2ba609c687a3425402b9d881e5075db",
    "Accept": "application/json",
    "Content-Type": "application/json; charset=UTF-8",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```

```bash
curl -X GET -G "https://demo.freescout.net/api/customers/1" \
    -H "X-FreeScout-API-Key: c2ba609c687a3425402b9d881e5075db"
```

```php

$client = new \GuzzleHttp\Client();
$response = $client->get("https://demo.freescout.net/api/customers/1", [
    'headers' => [
            "X-FreeScout-API-Key" => "c2ba609c687a3425402b9d881e5075db",
        ],
]);
$body = $response->getBody();
print_r(json_decode((string) $body));
```


> Example response (200):

```json
{
    "id": 75,
    "firstName": "Mark",
    "lastName": "Morrison",
    "company": "Example, Inc",
    "jobTitle": "Secretary",
    "photoType": "unknown",
    "photoUrl": "https:\/\/support.example.org\/storage\/customers\/7a10629fd2bae86563892b191f6677e7.jpg",
    "createdAt": "2020-07-23T12:34:12Z",
    "updatedAt": "2020-07-24T20:18:33Z",
    "notes": "Nothing special to say.",
    "customerFields": [
        {
            "id": 11,
            "name": "Age",
            "value": "25",
            "text": ""
        },
        {
            "id": 2,
            "name": "Gender",
            "value": "1",
            "text": "Male"
        }
    ],
    "_embedded": {
        "emails": [
            {
                "id": 1,
                "value": "mark@example.org",
                "type": "home"
            }
        ],
        "phones": [
            {
                "id": 0,
                "value": "777-777-777",
                "type": "home"
            }
        ],
        "social_profiles": [
            {
                "id": 0,
                "value": "@markexample",
                "type": "twitter"
            }
        ],
        "websites": [
            {
                "id": 0,
                "value": "https:\/\/example.org"
            }
        ],
        "address": {
            "city": "Los Angeles",
            "state": "California",
            "zip": "123123",
            "country": "US",
            "address": "1419 Westwood Blvd"
        }
    }
}
```

### HTTP Request
`GET api/customers/{customerId}`


<!-- END_f5555906b7b3e8fc19c9d95b2ae1bb4d -->

<!-- START_96ed66d9e6531df9b49e02d84ca5a619 -->
## List Customers

Request parameters can be used to filter customers. By default customers are sorted by createdAt (from newest to oldest): ?sortField=createdAt&amp;sortOrder=desc

> Example request:

```javascript
const url = new URL("https://demo.freescout.net/api/customers");

    let params = {
            "firstName": "John",
            "lastName": "Doe",
            "phone": "777-777-777",
            "email": "john@example.org",
            "updatedSince": "2021-01-07T12:00:03Z",
            "sortField": "firstName",
            "sortOrder": "asc",
            "page": "1",
            "pageSize": "100",
        };
    Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "X-FreeScout-API-Key": "c2ba609c687a3425402b9d881e5075db",
    "Accept": "application/json",
    "Content-Type": "application/json; charset=UTF-8",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```

```bash
curl -X GET -G "https://demo.freescout.net/api/customers?firstName=John&lastName=Doe&phone=777-777-777&email=john%40example.org&updatedSince=2021-01-07T12%3A00%3A03Z&sortField=firstName&sortOrder=asc&page=1&pageSize=100" \
    -H "X-FreeScout-API-Key: c2ba609c687a3425402b9d881e5075db"
```

```php

$client = new \GuzzleHttp\Client();
$response = $client->get("https://demo.freescout.net/api/customers", [
    'headers' => [
            "X-FreeScout-API-Key" => "c2ba609c687a3425402b9d881e5075db",
        ],
    'query' => [
            "firstName" => "John",
            "lastName" => "Doe",
            "phone" => "777-777-777",
            "email" => "john@example.org",
            "updatedSince" => "2021-01-07T12:00:03Z",
            "sortField" => "firstName",
            "sortOrder" => "asc",
            "page" => "1",
            "pageSize" => "100",
        ],
]);
$body = $response->getBody();
print_r(json_decode((string) $body));
```


> Example response (201):

```json
{
    "_embedded": {
        "customers": [
            {
                "id": 75,
                "firstName": "Mark",
                "lastName": "Morrison",
                "company": "Example, Inc",
                "jobTitle": "Secretary",
                "photoType": "unknown",
                "photoUrl": "https:\/\/support.example.org\/storage\/customers\/7a10629fd2bae86563892b191f6677e7.jpg",
                "createdAt": "2020-07-23T12:34:12Z",
                "updatedAt": "2020-07-24T20:18:33Z",
                "notes": "Nothing special to say.",
                "_embedded": {
                    "emails": [],
                    "phones": [],
                    "social_profiles": [],
                    "websites": [],
                    "address": {
                        "city": null,
                        "state": null,
                        "zip": null,
                        "country": null,
                        "address": null
                    }
                }
            }
        ]
    },
    "page": {
        "size": 50,
        "totalElements": 1,
        "totalPages": 1,
        "number": 1
    }
}
```

### HTTP Request
`GET api/customers`

#### Query Parameters

Parameter | Required | Description
--------- | ------- | ------- | -----------
    firstName |  | Filter customers by first name.
    lastName |  | Filter customers by last name.
    phone |  | Filter customers by phone number.
    email |  | Filter customers by email.
    updatedSince |  | Return only customers modified after the specified date.
    sortField |  | Sort the result by specified field: createdAt (default), firstName, lastName, updatedAt.
    sortOrder |  | Sort order: desc (default), asc.
    page |  | Page number.
    pageSize |  | Page size (defaults to 50).

<!-- END_96ed66d9e6531df9b49e02d84ca5a619 -->

<!-- START_cc9faa85d22f6ac8472d46eebdabafe6 -->
## Update Customer

> Example request:

```javascript
const url = new URL("https://demo.freescout.net/api/customers/1");

let headers = {
    "X-FreeScout-API-Key": "c2ba609c687a3425402b9d881e5075db",
    "Content-Type": "application/json",
    "Accept": "application/json",
}
let body = {
    "firstName": "Mark",
    "lastName": "Morrison",
    "phone": "777-777-777",
    "photoUrl": "https:\/\/example.org\/upload\/customer.jpg",
    "jobTitle": "Secretary",
    "photoType": "unknown",
    "address": {
        "city": "LA",
        "state": "California",
        "zip": "123123",
        "country": "US",
        "address": "1419 Westwood Blvd"
    },
    "notes": "Nothing special to say",
    "company": "Example, Inc",
    "emails": [
        "mark@example.org",
        "admin@example.org"
    ],
    "emails_add": [
        "mark.new1@example.org",
        "mark.new2@example.org"
    ],
    "phones": [
        {
            "value": "777-777-777",
            "type": "home"
        }
    ],
    "socialProfiles": [
        {
            "value": "@markexample",
            "type": "twitter"
        }
    ],
    "websites": [
        {
            "value": "https:\/\/example.org"
        }
    ]
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```

```bash
curl -X PUT "https://demo.freescout.net/api/customers/1" \
    -H "X-FreeScout-API-Key: c2ba609c687a3425402b9d881e5075db" \
    -H "Content-Type: application/json" \
    -d '{"firstName":"Mark","lastName":"Morrison","phone":"777-777-777","photoUrl":"https:\/\/example.org\/upload\/customer.jpg","jobTitle":"Secretary","photoType":"unknown","address":{"city":"LA","state":"California","zip":"123123","country":"US","address":"1419 Westwood Blvd"},"notes":"Nothing special to say","company":"Example, Inc","emails":["mark@example.org","admin@example.org"],"emails_add":["mark.new1@example.org","mark.new2@example.org"],"phones":[{"value":"777-777-777","type":"home"}],"socialProfiles":[{"value":"@markexample","type":"twitter"}],"websites":[{"value":"https:\/\/example.org"}]}'

```

```php

$client = new \GuzzleHttp\Client();
$response = $client->put("https://demo.freescout.net/api/customers/1", [
    'headers' => [
            "X-FreeScout-API-Key" => "c2ba609c687a3425402b9d881e5075db",
            "Content-Type" => "application/json",
        ],
    'json' => [
                "firstName" => "Mark",
                    "lastName" => "Morrison",
                    "phone" => "777-777-777",
                    "photoUrl" => "https://example.org/upload/customer.jpg",
                    "jobTitle" => "Secretary",
                    "photoType" => "unknown",
                    "address" => array(
   'city' => 'LA',
   'state' => 'California',
   'zip' => '123123',
   'country' => 'US',
   'address' => '1419 Westwood Blvd',
),
                    "notes" => "Nothing special to say",
                    "company" => "Example, Inc",
                    "emails" => array (
  0 => 'mark@example.org',
  1 => 'admin@example.org',
),
                    "emails_add" => array (
  0 => 'mark.new1@example.org',
  1 => 'mark.new2@example.org',
),
                    "phones" => array (
  0 => 
  array(
     'value' => '777-777-777',
     'type' => 'home',
  ),
),
                    "socialProfiles" => array (
  0 => 
  array(
     'value' => '@markexample',
     'type' => 'twitter',
  ),
),
                    "websites" => array (
  0 => 
  array(
     'value' => 'https://example.org',
  ),
),
            ],
]);
$body = $response->getBody();
print_r(json_decode((string) $body));
```


> Example response (204):

```json
HTTP/1.1 204 No Content
```

### HTTP Request
`PUT api/customers/{customerId}`

#### Body Parameters

Parameter | Type | Required | Description
--------- | ------- | ------- | ------- | -----------
    firstName | string |  | First name of the customer (max 40 characters).
    lastName | string |  | Last name of the customer (max 40 characters).
    phone | string |  | Phone number.
    photoUrl | string |  | URL of the customer’s photo (max 200 characters).
    jobTitle | string |  | Job title (max 60 characters).
    photoType | string |  | Type of photo: unknown, gravatar, twitter, facebook, googleprofile, googleplus, linkedin.
    address | object |  | Customer's address (country contains <a href="https://en.wikipedia.org/wiki/ISO_3166-1#Current_codes" target="_blank" rel="nofollow">two-letter country code</a>): { "city": "Los Angeles", "state": "California", "zip": "123123", "country": "US", "address": "1419 Westwood Blvd" }.
    notes | string |  | Notes.
    company | string |  | Company (max 60 characters).
    emails | object |  | List of emails (when this parameter is set the 'emails_add' parameter has no effect): [ "mark@example.org", "admin@example.org" ].
    emails_add | object |  | Add emails to the customer: [ "mark.new1@example.org", "mark.new2@example.org" ].
    phones | object |  | List of phones entries: [ { "value": "777-777-777", "type": "home" } ].
    socialProfiles | object |  | List of social profile entries: [ { "value": "@markexample", "type": "twitter" } ].
    websites | object |  | List of website entries: [ { "value": "https:\/\/example.org" } ].

<!-- END_cc9faa85d22f6ac8472d46eebdabafe6 -->

<!-- START_5751a8eff0a093ec8e17624f96494d8c -->
## Update Customer Fields

> Example request:

```javascript
const url = new URL("https://demo.freescout.net/api/customers/1/customer_fields");

let headers = {
    "X-FreeScout-API-Key": "c2ba609c687a3425402b9d881e5075db",
    "Content-Type": "application/json",
    "Accept": "application/json",
}
let body = {
    "customerFields": [
        {
            "id": 37,
            "value": "Test value"
        }
    ]
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```

```bash
curl -X PUT "https://demo.freescout.net/api/customers/1/customer_fields" \
    -H "X-FreeScout-API-Key: c2ba609c687a3425402b9d881e5075db" \
    -H "Content-Type: application/json" \
    -d '{"customerFields":[{"id":37,"value":"Test value"}]}'

```

```php

$client = new \GuzzleHttp\Client();
$response = $client->put("https://demo.freescout.net/api/customers/1/customer_fields", [
    'headers' => [
            "X-FreeScout-API-Key" => "c2ba609c687a3425402b9d881e5075db",
            "Content-Type" => "application/json",
        ],
    'json' => [
                "customerFields" => array (
  0 => 
  array(
     'id' => 37,
     'value' => 'Test value',
  ),
),
            ],
]);
$body = $response->getBody();
print_r(json_decode((string) $body));
```


> Example response (204):

```json
HTTP/1.1 204 No Content
```

### HTTP Request
`PUT api/customers/{customerId}/customer_fields`

#### Body Parameters

Parameter | Type | Required | Description
--------- | ------- | ------- | ------- | -----------
    customerFields | array |  (required)  | List of customer fields to be updated.

<!-- END_5751a8eff0a093ec8e17624f96494d8c -->

#Mailboxes


<!-- START_f6d887f2268494913dd607a56d24b613 -->
## List Mailboxes

Method returns mailboxes sorted by id. Request parameters can be used to filter mailboxes.

> Example request:

```javascript
const url = new URL("https://demo.freescout.net/api/mailboxes");

    let params = {
            "userId": "7",
            "page": "1",
            "pageSize": "100",
        };
    Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "X-FreeScout-API-Key": "c2ba609c687a3425402b9d881e5075db",
    "Accept": "application/json",
    "Content-Type": "application/json; charset=UTF-8",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```

```bash
curl -X GET -G "https://demo.freescout.net/api/mailboxes?userId=7&page=1&pageSize=100" \
    -H "X-FreeScout-API-Key: c2ba609c687a3425402b9d881e5075db"
```

```php

$client = new \GuzzleHttp\Client();
$response = $client->get("https://demo.freescout.net/api/mailboxes", [
    'headers' => [
            "X-FreeScout-API-Key" => "c2ba609c687a3425402b9d881e5075db",
        ],
    'query' => [
            "userId" => "7",
            "page" => "1",
            "pageSize" => "100",
        ],
]);
$body = $response->getBody();
print_r(json_decode((string) $body));
```


> Example response (201):

```json
{
    "_embedded": {
        "mailboxes": [
            {
                "id": 1,
                "name": "Demo Mailbox",
                "email": "support@support.example.org",
                "createdAt": "2020-08-09T10-09-00Z",
                "updatedAt": "2021-01-16T12-38-46Z"
            }
        ]
    },
    "page": {
        "size": 50,
        "totalElements": 1,
        "totalPages": 1,
        "number": 1
    }
}
```

### HTTP Request
`GET api/mailboxes`

#### Query Parameters

Parameter | Required | Description
--------- | ------- | ------- | -----------
    userId |  | Get maiboxes to which specified user has an access.
    page |  | Page number.
    pageSize |  | Page size (defaults to 50).

<!-- END_f6d887f2268494913dd607a56d24b613 -->

<!-- START_71bf01927d52f8824bfdaa37b0fdcd25 -->
## List Mailbox Custom Fields

#### Response Fields

Field | Type | Description
--------- | ------- | -----------
id | number | Custom field ID.
name | string | Name of the custom field.
type | string | Type of the custom field: singleline, multiline, dropdown, date, number
options | object | Contains options for dropdown custom fields.
required | boolean | Specifies if the custom field has to be filled.
sortOrder | number | Order of the custom field when displayed in the app.

> Example request:

```javascript
const url = new URL("https://demo.freescout.net/api/mailboxes/1/custom_fields");

let headers = {
    "X-FreeScout-API-Key": "c2ba609c687a3425402b9d881e5075db",
    "Accept": "application/json",
    "Content-Type": "application/json; charset=UTF-8",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```

```bash
curl -X GET -G "https://demo.freescout.net/api/mailboxes/1/custom_fields" \
    -H "X-FreeScout-API-Key: c2ba609c687a3425402b9d881e5075db"
```

```php

$client = new \GuzzleHttp\Client();
$response = $client->get("https://demo.freescout.net/api/mailboxes/1/custom_fields", [
    'headers' => [
            "X-FreeScout-API-Key" => "c2ba609c687a3425402b9d881e5075db",
        ],
]);
$body = $response->getBody();
print_r(json_decode((string) $body));
```


> Example response (200):

```json
{
    "_embedded": {
        "custom_fields": [
            {
                "id": 18,
                "name": "Priority",
                "type": "dropdown",
                "options": {
                    "1": "Low",
                    "2": "Medium",
                    "3": "High"
                },
                "required": false,
                "sortOrder": 1
            },
            {
                "id": 19,
                "name": "Purchase Date",
                "type": "date",
                "options": null,
                "required": false,
                "sortOrder": 3
            },
            {
                "id": 37,
                "name": "Vendor",
                "type": "singleline",
                "options": "",
                "required": false,
                "sortOrder": 6
            },
            {
                "id": 38,
                "name": "Comments",
                "type": "multiline",
                "options": "",
                "required": false,
                "sortOrder": 7
            },
            {
                "id": 39,
                "name": "Amount",
                "type": "number",
                "options": "",
                "required": false,
                "sortOrder": 8
            }
        ]
    },
    "page": {
        "size": 50,
        "totalElements": 5,
        "totalPages": 1,
        "number": 1
    }
}
```

### HTTP Request
`GET api/mailboxes/{mailboxId}/custom_fields`


<!-- END_71bf01927d52f8824bfdaa37b0fdcd25 -->

<!-- START_475f9126fcd69e6d0993f7ef95d50559 -->
## List Mailbox Folders

> Example request:

```javascript
const url = new URL("https://demo.freescout.net/api/mailboxes/1/folders");

    let params = {
            "userId": "7",
            "folderId": "3",
            "pageSize": "100",
        };
    Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "X-FreeScout-API-Key": "c2ba609c687a3425402b9d881e5075db",
    "Accept": "application/json",
    "Content-Type": "application/json; charset=UTF-8",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```

```bash
curl -X GET -G "https://demo.freescout.net/api/mailboxes/1/folders?userId=7&folderId=3&pageSize=100" \
    -H "X-FreeScout-API-Key: c2ba609c687a3425402b9d881e5075db"
```

```php

$client = new \GuzzleHttp\Client();
$response = $client->get("https://demo.freescout.net/api/mailboxes/1/folders", [
    'headers' => [
            "X-FreeScout-API-Key" => "c2ba609c687a3425402b9d881e5075db",
        ],
    'query' => [
            "userId" => "7",
            "folderId" => "3",
            "pageSize" => "100",
        ],
]);
$body = $response->getBody();
print_r(json_decode((string) $body));
```


> Example response (200):

```json
{
    "_embedded": {
        "folders": [
            {
                "id": 1681,
                "name": "Unassigned",
                "type": 1,
                "userId": null,
                "totalCount": 0,
                "activeCount": 0,
                "meta": null
            },
            {
                "id": 1958,
                "name": "Mine",
                "type": 20,
                "userId": 145,
                "totalCount": 0,
                "activeCount": 0,
                "meta": null
            },
            {
                "id": 1959,
                "name": "Starred",
                "type": 25,
                "userId": 145,
                "totalCount": 0,
                "activeCount": 0,
                "meta": null
            },
            {
                "id": 1682,
                "name": "Drafts",
                "type": 30,
                "userId": null,
                "totalCount": 0,
                "activeCount": 0,
                "meta": null
            },
            {
                "id": 1683,
                "name": "Assigned",
                "type": 40,
                "userId": null,
                "totalCount": 0,
                "activeCount": 0,
                "meta": null
            },
            {
                "id": 1684,
                "name": "Closed",
                "type": 60,
                "userId": null,
                "totalCount": 0,
                "activeCount": 0,
                "meta": null
            },
            {
                "id": 1685,
                "name": "Spam",
                "type": 80,
                "userId": null,
                "totalCount": 0,
                "activeCount": 0,
                "meta": null
            },
            {
                "id": 1686,
                "name": "Deleted",
                "type": 110,
                "userId": null,
                "totalCount": 0,
                "activeCount": 0,
                "meta": null
            }
        ]
    },
    "page": {
        "size": 50,
        "totalElements": 24,
        "totalPages": 1,
        "number": 1
    }
}
```

### HTTP Request
`GET api/mailboxes/{mailboxId}/folders`

#### Query Parameters

Parameter | Required | Description
--------- | ------- | ------- | -----------
    userId |  | Get folders belonging to the specified user.
    folderId |  | Get specific folder.
    pageSize |  | Page size (defaults to 50).

<!-- END_475f9126fcd69e6d0993f7ef95d50559 -->

#Tags


<!-- START_dde6989ab5551d4fb09439f7cb2554c5 -->
## List Tags

Method returns tags sorted by id.

> Example request:

```javascript
const url = new URL("https://demo.freescout.net/api/tags");

    let params = {
            "conversationId": "7",
            "page": "1",
            "pageSize": "100",
        };
    Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "X-FreeScout-API-Key": "c2ba609c687a3425402b9d881e5075db",
    "Accept": "application/json",
    "Content-Type": "application/json; charset=UTF-8",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```

```bash
curl -X GET -G "https://demo.freescout.net/api/tags?conversationId=7&page=1&pageSize=100" \
    -H "X-FreeScout-API-Key: c2ba609c687a3425402b9d881e5075db"
```

```php

$client = new \GuzzleHttp\Client();
$response = $client->get("https://demo.freescout.net/api/tags", [
    'headers' => [
            "X-FreeScout-API-Key" => "c2ba609c687a3425402b9d881e5075db",
        ],
    'query' => [
            "conversationId" => "7",
            "page" => "1",
            "pageSize" => "100",
        ],
]);
$body = $response->getBody();
print_r(json_decode((string) $body));
```


> Example response (201):

```json
{
    "_embedded": {
        "tags": [
            {
                "id": 1,
                "name": "overdue",
                "counter": 5,
                "color": 1
            }
        ]
    },
    "page": {
        "size": 50,
        "totalElements": 1,
        "totalPages": 1,
        "number": 1
    }
}
```

### HTTP Request
`GET api/tags`

#### Query Parameters

Parameter | Required | Description
--------- | ------- | ------- | -----------
    conversationId |  | Conversation ID.
    page |  | Page number.
    pageSize |  | Page size (defaults to 50).

<!-- END_dde6989ab5551d4fb09439f7cb2554c5 -->

<!-- START_794a859956f6c8a5ea83a64d7e2a85aa -->
## Update Conversation Tags

This method allows to update tags for a conversation. The full list of tags must be sent in the request. If some tag specified does not exist it will be first created and then applied to the conversation. Any conversation tags which are not listed in the request will be removed. Send an empty list of tags to remove all tags.

> Example request:

```javascript
const url = new URL("https://demo.freescout.net/api/conversations/1/tags");

let headers = {
    "X-FreeScout-API-Key": "c2ba609c687a3425402b9d881e5075db",
    "Content-Type": "application/json",
    "Accept": "application/json",
}
let body = {
    "tags": [
        "overdue",
        "refund"
    ]
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```

```bash
curl -X PUT "https://demo.freescout.net/api/conversations/1/tags" \
    -H "X-FreeScout-API-Key: c2ba609c687a3425402b9d881e5075db" \
    -H "Content-Type: application/json" \
    -d '{"tags":["overdue","refund"]}'

```

```php

$client = new \GuzzleHttp\Client();
$response = $client->put("https://demo.freescout.net/api/conversations/1/tags", [
    'headers' => [
            "X-FreeScout-API-Key" => "c2ba609c687a3425402b9d881e5075db",
            "Content-Type" => "application/json",
        ],
    'json' => [
                "tags" => array (
  0 => 'overdue',
  1 => 'refund',
),
            ],
]);
$body = $response->getBody();
print_r(json_decode((string) $body));
```


> Example response (204):

```json
HTTP/1.1 204 No Content
```

### HTTP Request
`PUT api/conversations/{conversationId}/tags`

#### Body Parameters

Parameter | Type | Required | Description
--------- | ------- | ------- | ------- | -----------
    tags | array |  (required)  | List of tags (tag names) to be applied to the conversation.

<!-- END_794a859956f6c8a5ea83a64d7e2a85aa -->

#Threads


<!-- START_2c8f7daedab7955b7ef939aaacd9da94 -->
## Create Thread

This method adds a new customer reply, user reply or user note to a conversation.

> Example request:

```javascript
const url = new URL("https://demo.freescout.net/api/conversations/1/threads");

let headers = {
    "X-FreeScout-API-Key": "c2ba609c687a3425402b9d881e5075db",
    "Content-Type": "application/json",
    "Accept": "application/json",
}
let body = {
    "type": "message",
    "text": "Plese let us know if you have any other questions.",
    "customer": {
        "email": "mark@example.org"
    },
    "user": 33,
    "imported": false,
    "status": "closed",
    "state": "published",
    "cc": [
        "anna@example.org",
        "bill@example.org"
    ],
    "bcc": [
        "bob@example.org",
        "andrea@example.org"
    ],
    "createdAt": "2020-03-16T14:07:23Z",
    "attachments": [
        {
            "fileName": "file.txt",
            "mimeType": "plain\/text",
            "data": "ZmlsZQ=="
        },
        {
            "fileName": "file2.txt",
            "mimeType": "plain\/text",
            "fileUrl": "https:\/\/example.org\/uploads\/file2.txt"
        }
    ]
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```

```bash
curl -X POST "https://demo.freescout.net/api/conversations/1/threads" \
    -H "X-FreeScout-API-Key: c2ba609c687a3425402b9d881e5075db" \
    -H "Content-Type: application/json" \
    -d '{"type":"message","text":"Plese let us know if you have any other questions.","customer":{"email":"mark@example.org"},"user":33,"imported":false,"status":"closed","state":"published","cc":["anna@example.org","bill@example.org"],"bcc":["bob@example.org","andrea@example.org"],"createdAt":"2020-03-16T14:07:23Z","attachments":[{"fileName":"file.txt","mimeType":"plain\/text","data":"ZmlsZQ=="},{"fileName":"file2.txt","mimeType":"plain\/text","fileUrl":"https:\/\/example.org\/uploads\/file2.txt"}]}'

```

```php

$client = new \GuzzleHttp\Client();
$response = $client->post("https://demo.freescout.net/api/conversations/1/threads", [
    'headers' => [
            "X-FreeScout-API-Key" => "c2ba609c687a3425402b9d881e5075db",
            "Content-Type" => "application/json",
        ],
    'json' => [
                "type" => "message",
                    "text" => "Plese let us know if you have any other questions.",
                    "customer" => array(
   'email' => 'mark@example.org',
),
                    "user" => "33",
                    "imported" => "",
                    "status" => "closed",
                    "state" => "published",
                    "cc" => array (
  0 => 'anna@example.org',
  1 => 'bill@example.org',
),
                    "bcc" => array (
  0 => 'bob@example.org',
  1 => 'andrea@example.org',
),
                    "createdAt" => "2020-03-16T14:07:23Z",
                    "attachments" => array (
  0 => 
  array(
     'fileName' => 'file.txt',
     'mimeType' => 'plain/text',
     'data' => 'ZmlsZQ==',
  ),
  1 => 
  array(
     'fileName' => 'file2.txt',
     'mimeType' => 'plain/text',
     'fileUrl' => 'https://example.org/uploads/file2.txt',
  ),
),
            ],
]);
$body = $response->getBody();
print_r(json_decode((string) $body));
```


> Example response (201):

```json
HTTP/1.1 201 Created
Resource-ID: 25
```

### HTTP Request
`POST api/conversations/{conversationId}/threads`

#### Body Parameters

Parameter | Type | Required | Description
--------- | ------- | ------- | ------- | -----------
    type | string |  (required)  | Thread type: customer (customer reply), message (user reply), note (user note).
    text | string |  (required)  | The message text.
    customer | object |  | Customer adding the thread (required if thread 'type' is 'customer'). Customer object must contain a valid customer id or an email address: { "id": 123 } or { "email": "mark@example.org" }. If the id field is defined, the email will be ignored. If the id is not defined, email is used to look up a customer. If a customer does not exist, a new one will be created. If a customer is matched either via id or email field, the rest of the optional fields is ignored.
    user | number |  | ID of the user who is adding the thread (required if thread 'type' is 'message' or 'note').
    imported | boolean |  | When imported is set to 'true', no outgoing emails or notifications will be generated.
    status | string |  | Conversation status: active, pending, closed. Use this field to change conversation status when adding a thread. If not explicitly set, a customer reply will reactivate the conversation and support agent reply will make it pending.
    state | string |  | Thread state: draft, published (default).
    cc | array |  | List of CC email addresses.
    bcc | array |  | List of BCC email addresses.
    createdAt | string |  | Creation date to be used when importing conversations and threads in ISO 8601 date time format (can be used only when 'imported' field is set to true).
    attachments | array |  | List of attachments to be attached to the thread. Attachment content can be passed in "data" parameter as BASE64 encoded string or as URL in "fileUrl" parameter. 

<!-- END_2c8f7daedab7955b7ef939aaacd9da94 -->

#Timelogs


<!-- START_abf1c40fcabb2bc92586516518f6ed42 -->
## List Conversation Timelogs

Get Time Tracking Module timelogs for a conversation. Timelogs are sorted from newest to oldest.

> Example request:

```javascript
const url = new URL("https://demo.freescout.net/api/conversations/1/timelogs");

    let params = {
            "page": "1",
            "pageSize": "100",
        };
    Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "X-FreeScout-API-Key": "c2ba609c687a3425402b9d881e5075db",
    "Accept": "application/json",
    "Content-Type": "application/json; charset=UTF-8",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```

```bash
curl -X GET -G "https://demo.freescout.net/api/conversations/1/timelogs?page=1&pageSize=100" \
    -H "X-FreeScout-API-Key: c2ba609c687a3425402b9d881e5075db"
```

```php

$client = new \GuzzleHttp\Client();
$response = $client->get("https://demo.freescout.net/api/conversations/1/timelogs", [
    'headers' => [
            "X-FreeScout-API-Key" => "c2ba609c687a3425402b9d881e5075db",
        ],
    'query' => [
            "page" => "1",
            "pageSize" => "100",
        ],
]);
$body = $response->getBody();
print_r(json_decode((string) $body));
```


> Example response (201):

```json
{
    "_embedded": {
        "timelogs": [
            {
                "id": 498,
                "conversationStatus": "pending",
                "userId": 1,
                "timeSpent": 219,
                "paused": false,
                "finished": true,
                "createdAt": "2021-04-21T13-24-01Z",
                "updatedAt": "2021-04-21T13-43-10Z"
            },
            {
                "id": 497,
                "conversationStatus": "active",
                "userId": 1,
                "timeSpent": 711,
                "paused": false,
                "finished": true,
                "createdAt": "2021-04-21T13-22-09Z",
                "updatedAt": "2021-04-21T13-43-10Z"
            }
        ]
    },
    "page": {
        "size": 50,
        "totalElements": 1,
        "totalPages": 1,
        "number": 1
    }
}
```

### HTTP Request
`GET api/conversations/{conversationId}/timelogs`

#### Query Parameters

Parameter | Required | Description
--------- | ------- | ------- | -----------
    page |  | Page number.
    pageSize |  | Page size (defaults to 50).

<!-- END_abf1c40fcabb2bc92586516518f6ed42 -->

#Users


<!-- START_12e37982cc5398c7100e59625ebb5514 -->
## Create User

This method does not update existing users. Method makes sure that the email address is unique and does not check uniqueness of other parameters. Method creates only regular Users and does not allow to create Administrators. No invitation email is being sent upon user creation. Created user does not have permissions to access any mailboxes by default.

> Example request:

```javascript
const url = new URL("https://demo.freescout.net/api/users");

let headers = {
    "X-FreeScout-API-Key": "c2ba609c687a3425402b9d881e5075db",
    "Content-Type": "application/json",
    "Accept": "application/json",
}
let body = {
    "firstName": "John",
    "lastName": "Doe",
    "email": "johndoe@example.org",
    "password": "123456789",
    "alternateEmails": "johndoe777@example.org",
    "jobTitle": "Support agent",
    "phone": "777-777-777",
    "timezone": "Europe\/Paris",
    "photoUrl": "https:\/\/example.org\/upload\/customer.jpg"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```

```bash
curl -X POST "https://demo.freescout.net/api/users" \
    -H "X-FreeScout-API-Key: c2ba609c687a3425402b9d881e5075db" \
    -H "Content-Type: application/json" \
    -d '{"firstName":"John","lastName":"Doe","email":"johndoe@example.org","password":"123456789","alternateEmails":"johndoe777@example.org","jobTitle":"Support agent","phone":"777-777-777","timezone":"Europe\/Paris","photoUrl":"https:\/\/example.org\/upload\/customer.jpg"}'

```

```php

$client = new \GuzzleHttp\Client();
$response = $client->post("https://demo.freescout.net/api/users", [
    'headers' => [
            "X-FreeScout-API-Key" => "c2ba609c687a3425402b9d881e5075db",
            "Content-Type" => "application/json",
        ],
    'json' => [
                "firstName" => "John",
                    "lastName" => "Doe",
                    "email" => "johndoe@example.org",
                    "password" => "123456789",
                    "alternateEmails" => "johndoe777@example.org",
                    "jobTitle" => "Support agent",
                    "phone" => "777-777-777",
                    "timezone" => "Europe/Paris",
                    "photoUrl" => "https://example.org/upload/customer.jpg",
            ],
]);
$body = $response->getBody();
print_r(json_decode((string) $body));
```


> Example response (201):

```json
HTTP/1.1 201 Created
Resource-ID: 17
```

### HTTP Request
`POST api/users`

#### Body Parameters

Parameter | Type | Required | Description
--------- | ------- | ------- | ------- | -----------
    firstName | string |  (required)  | First name of the user.
    lastName | string |  (required)  | Last name of the user.
    email | string |  (required)  | Email address.
    password | string |  | User password.
    alternateEmails | string |  | User alternate emails (comma separated).
    jobTitle | string |  | Job title.
    phone | string |  | Phone number.
    timezone | string |  | User timezone. List of timezones: https://www.php.net/manual/en/timezones.php.
    photoUrl | string |  | URL of the user's photo.

<!-- END_12e37982cc5398c7100e59625ebb5514 -->

<!-- START_d75d1239520346b5c625a2a483b653dc -->
## Get User

> Example request:

```javascript
const url = new URL("https://demo.freescout.net/api/users/1");

let headers = {
    "X-FreeScout-API-Key": "c2ba609c687a3425402b9d881e5075db",
    "Accept": "application/json",
    "Content-Type": "application/json; charset=UTF-8",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```

```bash
curl -X GET -G "https://demo.freescout.net/api/users/1" \
    -H "X-FreeScout-API-Key: c2ba609c687a3425402b9d881e5075db"
```

```php

$client = new \GuzzleHttp\Client();
$response = $client->get("https://demo.freescout.net/api/users/1", [
    'headers' => [
            "X-FreeScout-API-Key" => "c2ba609c687a3425402b9d881e5075db",
        ],
]);
$body = $response->getBody();
print_r(json_decode((string) $body));
```


> Example response (200):

```json
{
    "id": 1,
    "firstName": "John",
    "lastName": "Doe",
    "email": "johndoe@example.org",
    "role": "admin",
    "alternateEmails": "johndoe777@example.org",
    "jobTitle": "Support agent",
    "phone": "+1867342345",
    "timezone": "Etc\/GMT-3",
    "photoUrl": "https:\/\/example.org\/upload\/customer.jpg",
    "language": "en",
    "createdAt": "2018-08-09T10-08-53Z",
    "updatedAt": "2020-12-22T14-54-35Z"
}
```

### HTTP Request
`GET api/users/{userId}`


<!-- END_d75d1239520346b5c625a2a483b653dc -->

<!-- START_fc1e4f6a697e3c48257de845299b71d5 -->
## List Users

Request parameters can be used to filter users.

> Example request:

```javascript
const url = new URL("https://demo.freescout.net/api/users");

    let params = {
            "email": "johndoe@example.org",
            "page": "1",
            "pageSize": "100",
        };
    Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "X-FreeScout-API-Key": "c2ba609c687a3425402b9d881e5075db",
    "Accept": "application/json",
    "Content-Type": "application/json; charset=UTF-8",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```

```bash
curl -X GET -G "https://demo.freescout.net/api/users?email=johndoe%40example.org&page=1&pageSize=100" \
    -H "X-FreeScout-API-Key: c2ba609c687a3425402b9d881e5075db"
```

```php

$client = new \GuzzleHttp\Client();
$response = $client->get("https://demo.freescout.net/api/users", [
    'headers' => [
            "X-FreeScout-API-Key" => "c2ba609c687a3425402b9d881e5075db",
        ],
    'query' => [
            "email" => "johndoe@example.org",
            "page" => "1",
            "pageSize" => "100",
        ],
]);
$body = $response->getBody();
print_r(json_decode((string) $body));
```


> Example response (201):

```json
{
    "_embedded": {
        "users": [
            {
                "id": 1,
                "firstName": "John",
                "lastName": "Doe",
                "email": "johndoe@example.org",
                "role": "admin",
                "alternateEmails": "johndoe777@example.org",
                "jobTitle": "Support agent",
                "phone": "+1867342345",
                "timezone": "Etc\/GMT-3",
                "photoUrl": "https:\/\/example.org\/upload\/customer.jpg",
                "language": "en",
                "createdAt": "2018-08-09T10-08-53Z",
                "updatedAt": "2020-12-22T14-54-35Z"
            }
        ]
    },
    "page": {
        "size": 50,
        "totalElements": 1,
        "totalPages": 1,
        "number": 1
    }
}
```

### HTTP Request
`GET api/users`

#### Query Parameters

Parameter | Required | Description
--------- | ------- | ------- | -----------
    email |  | Look up user by email.
    page |  | Page number.
    pageSize |  | Page size (defaults to 50).

<!-- END_fc1e4f6a697e3c48257de845299b71d5 -->

<!-- START_a812ad7216b5b2d96aef13e7b49604f7 -->
## Delete User

This method deletes a user.

> Example request:

```javascript
const url = new URL("https://demo.freescout.net/api/users/1");

    let params = {
            "byUserId": "1",
            "assignTo": "assignTo[2]=7",
        };
    Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "X-FreeScout-API-Key": "c2ba609c687a3425402b9d881e5075db",
    "Accept": "application/json",
    "Content-Type": "application/json; charset=UTF-8",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```

```bash
curl -X DELETE "https://demo.freescout.net/api/users/1?byUserId=1&assignTo=assignTo%5B2%5D%3D7" \
    -H "X-FreeScout-API-Key: c2ba609c687a3425402b9d881e5075db"
```

```php

$client = new \GuzzleHttp\Client();
$response = $client->delete("https://demo.freescout.net/api/users/1", [
    'headers' => [
            "X-FreeScout-API-Key" => "c2ba609c687a3425402b9d881e5075db",
        ],
    'query' => [
            "byUserId" => "1",
            "assignTo" => "assignTo[2]=7",
        ],
]);
$body = $response->getBody();
print_r(json_decode((string) $body));
```


> Example response (204):

```json
HTTP/1.1 204 No Content
```

### HTTP Request
`DELETE api/users/{userId}`

#### Query Parameters

Parameter | Required | Description
--------- | ------- | ------- | -----------
    byUserId |  (required)  | ID of the user performing the deletion of the user.
    assignTo |  | Optional mapping array determining new assignee for conversations assigned to the user which is being deleted. Format: assignTo[MAILBOX_ID]=NEW_ASSIGNEE_ID.

<!-- END_a812ad7216b5b2d96aef13e7b49604f7 -->

#Webhooks


<!-- START_bc21adbacd4d04a672f21b2db9813c44 -->
## Create Webhook

> Example request:

```javascript
const url = new URL("https://demo.freescout.net/api/webhooks");

let headers = {
    "X-FreeScout-API-Key": "c2ba609c687a3425402b9d881e5075db",
    "Content-Type": "application/json",
    "Accept": "application/json",
}
let body = {
    "url": "https:\/\/example.org\/freescout",
    "events": [
        "convo.created"
    ]
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```

```bash
curl -X POST "https://demo.freescout.net/api/webhooks" \
    -H "X-FreeScout-API-Key: c2ba609c687a3425402b9d881e5075db" \
    -H "Content-Type: application/json" \
    -d '{"url":"https:\/\/example.org\/freescout","events":["convo.created"]}'

```

```php

$client = new \GuzzleHttp\Client();
$response = $client->post("https://demo.freescout.net/api/webhooks", [
    'headers' => [
            "X-FreeScout-API-Key" => "c2ba609c687a3425402b9d881e5075db",
            "Content-Type" => "application/json",
        ],
    'json' => [
                "url" => "https://example.org/freescout",
                    "events" => array (
  0 => 'convo.created',
),
            ],
]);
$body = $response->getBody();
print_r(json_decode((string) $body));
```


> Example response (201):

```json
HTTP/1.1 201 Created
Resource-ID: 17
```

### HTTP Request
`POST api/webhooks`

#### Body Parameters

Parameter | Type | Required | Description
--------- | ------- | ------- | ------- | -----------
    url | string |  (required)  | URL that will be called when any of the events occur.
    events | array |  (required)  | List of events to track: convo.assigned, convo.created, convo.deleted, convo.moved, convo.status, convo.customer.reply.created, convo.agent.reply.created, convo.note.created, customer.created, customer.updated.

<!-- END_bc21adbacd4d04a672f21b2db9813c44 -->

<!-- START_051884c3f3f0766eb3c7cd7b49434380 -->
## Delete Webhook

> Example request:

```javascript
const url = new URL("https://demo.freescout.net/api/webhooks/1");

let headers = {
    "X-FreeScout-API-Key": "c2ba609c687a3425402b9d881e5075db",
    "Accept": "application/json",
    "Content-Type": "application/json; charset=UTF-8",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```

```bash
curl -X DELETE "https://demo.freescout.net/api/webhooks/1" \
    -H "X-FreeScout-API-Key: c2ba609c687a3425402b9d881e5075db"
```

```php

$client = new \GuzzleHttp\Client();
$response = $client->delete("https://demo.freescout.net/api/webhooks/1", [
    'headers' => [
            "X-FreeScout-API-Key" => "c2ba609c687a3425402b9d881e5075db",
        ],
]);
$body = $response->getBody();
print_r(json_decode((string) $body));
```


> Example response (204):

```json
HTTP/1.1 204 No Content
```

### HTTP Request
`DELETE api/webhooks/{webhookId}`


<!-- END_051884c3f3f0766eb3c7cd7b49434380 -->


