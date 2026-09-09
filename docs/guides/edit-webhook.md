Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Edit Webhook

Modify an existing webhook’s configuration using the DoubleTick API, allowing updates to event types, headers, URLs, and retry settings for seamless event tracking. 🚀

## API Endpoint

Use the following endpoint to edit a webhook:

```
POST https://public.doubletick.io/v2/webhook/:webhookId
```

***

## Request Headers

```json
{
  "Authorization": "YOUR_API_KEY",
  "Content-Type": "application/json"
}
```

## Request Body Parameters

```json
{
  "method": "GET",
  "headers": {
    "newKey": "New Value",
    "newKey-1": "New Value 1"
  },
  "body": {
    "newKey": "New Value",
    "newKey-1": "New Value 1"
  },
  "query": {
    "newKey": "New Value",
    "newKey-1": "New Value 1"
  },
  "authorization": {
    "type": "BEARER",
    "payload": "some-secure-token"
  },
  "url": "https://webhook-url.com/",
  "bodyFormat": "JSON",
  "webhookEvents": [
    "RAW_CLOUD_API_WEBHOOK",
    "REMOVE_TAG"
  ],
  "retryOnTimeout": true,
  "name": "webhook_edit_req",
  "wabaNumbers": [
    "919999999999",
    "919999999998"
  ]
}
```

### Parameters:

* `method` (string, required): HTTP method for the webhook (`GET`, `POST`, `PUT`, `DELETE`, `PATCH`, `HEAD`).
* `headers` (object, optional): Custom headers for the webhook.
* `body` (object, optional): Additional request body parameters.
* `query` (object, optional): Query parameters sent with the request.
* `authorization` (object, required):
  * `type` (string, required): `BASIC` or `BEARER`.
  * `payload` (string, required): The authorization payload, such as a token or credentials.
* `url` (string, required): Fully qualified webhook URL.
* `bodyFormat` (string, required): JSON or FORM\_DATA.
* `webhookEvents` (array of strings, required): List of events to be captured.
* `retryOnTimeout` (boolean, required): Whether to retry the webhook call on timeout.
* `name` (string, required): Webhook name.
* `wabaNumbers` (array of strings, required): List of WhatsApp Business Account numbers.

***

## Response

### Success Response (<<glossary:201>>)

```json
{
  "validWebhooks": [
    {
      "wabaNumber": "919999999999",
      "webhookEventType": "RAW_CLOUD_API_WEBHOOK"
    },
    {
      "wabaNumber": "919999999999",
      "webhookEventType": "REMOVE_TAG"
    },
    {
      "wabaNumber": "919999999998",
      "webhookEventType": "RAW_CLOUD_API_WEBHOOK"
    },
    {
      "wabaNumber": "919999999998",
      "webhookEventType": "REMOVE_TAG"
    }
  ],
  "invalidWebhooks": [],
  "invalidWabaNumbers": []
}
```

### Bad Request (<<glossary:400>>)

```json
{
    "statusCode": 400,
    "message": "Invalid request format",
    "error": "Bad Request"
}
```

### Unauthorized (<<glossary:401>>)

```json
{
    "statusCode": 401,
    "message": "Invalid API key",
    "error": "Unauthorized"
}
```

### Not Found (<<glossary:404>>)

```json
{
    "message": "Cannot PATCH /v2/p/v2/webhook/:webhookId",
    "error": "Not Found",
    "statusCode": 404
}
```

***

## Best Practices

* Validate the **webhook ID** before making an update request.
* Ensure the **updated URL and headers** are correctly formatted.
* Modify only necessary fields to prevent unintended changes.