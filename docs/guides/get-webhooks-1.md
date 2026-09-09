Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Get Webhooks

Retrieve a list of registered webhooks, including their event types and associated WABA numbers, using the DoubleTick API for seamless event tracking. 🚀

## API Endpoint

Use the following endpoint to retrieve all registered webhooks:

```
GET https://public.doubletick.io/v2/webhooks?sortBy=name&sortDirection=ASC
```

***

## Request Headers

```json
{
  "Authorization": "YOUR_API_KEY"
}
```

## Query Parameters

| Parameter       | Type   | Description                                                                            |
| --------------- | ------ | -------------------------------------------------------------------------------------- |
| `sortBy`        | string | Sort webhooks by `name`, `url`, `createdBy`, or `integrationCount`. Default is `name`. |
| `sortDirection` | string | Sorting order: `ASC` (ascending) or `DESC` (descending). Default is `ASC`.             |

***

## Response

### Success Response (<<glossary:201>>)

```json
[
    {
        "webhookId": "webhook_id",
        "url": "webhook_url",
        "wabaNumbers": [
            "waba_number1",
            "waba_number2",
            "waba_number3"
        ],
        "eventTypes": [
            "MESSAGE_RECEIVED",
            "MESSAGE_STATUS_UPDATE"
        ],
        "integrationCount": "3",
        "createdBy": "creator_name",
        "name": "webhook_name"
    }
]
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

* Use the **sortBy** and **sortDirection** parameters to filter and organize webhook data efficiently.
* Validate webhook details to maintain accurate event tracking.
* Keep track of webhook registrations and remove inactive ones.