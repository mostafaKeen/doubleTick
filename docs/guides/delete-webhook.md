Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Delete Webhook

Remove a registered webhook using the DoubleTick API to stop receiving unnecessary event notifications and keep your system optimized. 🚀

## API Endpoint

Use the following endpoint to delete a webhook:

```
DELETE https://public.doubletick.io/v2/webhook/deregister
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
  "webhookId": "webhook_id"
}
```

### Parameters

* `webhookId` (string, required): The unique ID of the webhook to be deleted.

***

## Response

### Success Response (<<glossary:201>>)

```json
{}
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

* Verify that the **webhook ID** exists before making a delete request.
* Ensure that no critical dependencies rely on the webhook before removing it.
* Use this API to clean up outdated webhooks for optimized performance.