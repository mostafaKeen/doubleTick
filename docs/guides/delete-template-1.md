Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Delete a WhatsApp Template

Easily delete existing WhatsApp message templates using the DoubleTick API, ensuring efficient management of your templates. 🗑️✅

## API Endpoint

Use the following endpoint to delete a WhatsApp message template:

```
DELETE https://public.doubletick.io/template
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
  "name": "your_template_name",
  "wabaPhoneNumber": "sender_number"
}
```

### Parameters

* `name` (string, required): The name of the template to be deleted.
* `wabaPhoneNumber` (string, required): Your registered WhatsApp Business Account phone number in <<glossary:international format>>.

***

## Response

### Success Response (<<glossary:201>>)

```json
{
  "success": true
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

* Double-check the template name before sending a delete request.
* Ensure that the template is no longer required before deletion, as it cannot be restored.
* Use the **Manage Templates API** to list all available templates before deleting.