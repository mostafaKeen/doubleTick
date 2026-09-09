Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Export Chats For a Customer For Given WabaNumber

Easily export WhatsApp chat history, including media, using the DoubleTick API for compliance, audits, and customer insights. 🚀

## API Endpoint

Use the following endpoint to export chat history:

```
POST https://public.doubletick.io/export-chats
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
  "wabaNumber": "your_waba_number",
  "customerPhoneNumber": "customer_number",
  "startDate": "DD-MM-YYYY",
  "endDate": "DD-MM-YYYY",
  "includeMedia": true
}
```

### Parameters

* `wabaNumber` (string, required): Your registered WhatsApp Business Account number in <<glossary:international format>>.
* `customerPhoneNumber` (string, required): Customer’s phone number in <<glossary:international format>>.
* `startDate` (string, required): Start date for chat history retrieval (DD-MM-YYYY format).
* `endDate` (string, required): End date for chat history retrieval (DD-MM-YYYY format).
* `includeMedia` (boolean, required): Whether to include media files in the export.

***

## Responses

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

***

## Best Practices

* Use appropriate date filters to optimize retrieval performance.
* Store exported chat data securely to meet compliance and audit requirements.
* Limit the date range to avoid large exports that may impact API performance.