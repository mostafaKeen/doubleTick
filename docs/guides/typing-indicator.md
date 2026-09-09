Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Send WhatsApp Typing Indicator

Simulate real-time presence—let your customers know you’re typing before the message arrives.

## API Endpoint

Use the following endpoint to send a typing indicator:

```
POST https://public.doubletick.io/whatsapp/message/typing-indicator
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
  "wabaNumber": "waba_number",
  "customerNumber": "customer_number"
}
```

### Parameters

* `wabaNumber` (string, required): Your registered WABA phone number in <<glossary:international format>>.
* `customerNumber` (string, required): Customer’s phone number in <<glossary:international format>>.

***

## Responses

### Success Response (<<glossary:201>>)

```json
{
  "success": true
}
```

* `success: true` -> The typing indicator was successfully delivered to your customer.
* `success: false` -> The typing indicator could not be delivered to your customer.

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

### Unprocessable Entity (<<glossary:404>>)

```json
{
  "message": "error_message",
  "error": "Not Found",
  "statusCode": 404
}
```