Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Register New Webhook

Set up real-time event notifications using the DoubleTick API by registering a webhook to capture message status updates, customer interactions, and system events. 🚀

## API Endpoint

Use the following endpoint to register a webhook:

```
POST https://public.doubletick.io/v2/webhook/register
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
  "headers": { "newKeyHeader": "New Value Header" },
  "body": { "newKeyBody": "New Value Body" },
  "query": { "newKeyQuery": "New Value Query" },
  "authorization": {
      "type": "BASIC",
      "payload": "some-secure-token"
  },
  "url": "https://your-callback-url.com",
  "bodyFormat": "JSON",
  "webhookEvents": ["ADD_TAG"],
  "retryOnTimeout": true,
  "name": "Test Webhook",
  "wabaNumbers": ["waba_number1", "waba_number2", "waba_number3"]
}
```

### Parameters

* `method` (string, required): HTTP method for the webhook (GET, POST, PUT, DELETE, PATCH, HEAD).
* `headers` (object, optional): Custom headers for the webhook.
* `body` (object, optional): Additional request body parameters.
* `query` (object, optional): Query parameters sent with the request.
* `authorization` (object, required):
  * `type` (string, optional): `BASIC` or `BEARER`.
  * `payload` (string, optional): Secure token for authentication.
* `url` (string, required, optional): Fully qualified webhook URL.
* `bodyFormat` (string, required): JSON or FORM\_DATA.
* `webhookEvents` (array of strings, required): List of events to be captured.
  * All Events are listed below:
    * ADD\_TAG
    * MESSAGE\_RECEIVED
    * MESSAGE\_STATUS\_UPDATE
    * CHAT\_ASSIGNED\_TO\_AGENT
    * CHAT\_UNASSIGNED
    * UPDATE\_CUSTOMER\_CUSTOM\_FIELD
    * WIDGET\_LEAD\_RECEIVED
    * VERIFIED\_WIDGET\_LEAD\_RECEIVED
    * NEW\_LEAD
    * RAW\_CLOUD\_API\_WEBHOOK
    * CLOSE\_CONVERSATION
    * TEMPLATE\_UPDATE
    * ADD\_TAG
    * REMOVE\_TAG
    * CALL\_TO\_WHATSAPP\_MESSAGE\_RECEIVED
    * COVERSATION\_OPENED
    * CUSTOMER\_BUSINESS\_CHAT\_OPEN
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
            "wabaNumber": "waba_number_assigned_to_webhook",
            "webhookEventType": "ADD_TAG"
        }
    ],
    "invalidWebhooks": [],
    "invalidWabaNumbers": []
}
```

### Bad Request (<<glossary:400>>)

```json JSON
{
  "message": "error_message",
  "error": "Bad Request",
  "statusCode": 400
}
```

### Unauthorized (<<glossary:401>>)

```json
{
  "message": "Invalid public api key",
  "error": "Unauthorized",
  "statusCode": 401
}
```

### Unprocessable Entity (<<glossary:422>>)

```json
{
  "message": "invalid file type for audio: text/html; charset=utf-8",
  "error": "Unprocessable Entity",
  "statusCode": 422
}
```

***

## Best Practices

* Ensure the **webhook URL** is accessible and properly formatted.
* Use secure authentication methods to protect data.
* Select only the necessary **webhook events** to optimize performance.