Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Send WhatsApp Text Messages

Send instant and direct WhatsApp text messages via the DoubleTick API for seamless customer communication within the last 24-hour session window. 🚀

## API Endpoint

Use the following endpoint to send a text message:

```
POST https://public.doubletick.io/whatsapp/message/text
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
  "to": "customer_number",
  "from": "sender_number",
  "messageId": "uuid-v4xxx",
  "content": {
    "text": "Hello, this is a sample message from DoubleTick!"
  }
}
```

### Parameters

* `to` (string, required): Customer’s phone number in <<glossary:international format>>.
* `from` (string, required): Your registered sender phone number in <<glossary:international format>>.
* `messageId` (string, optional):
  * Message ID (UUID v4) to be used for the message.
  * If not provided, a random UUID v4 will be generated automatically.
  * Length to be exact 36 characters.
* `content` (object, required)
  * `text` (string, required): The text message content.

***

## Responses

### Success Response (<<glossary:201>>)

```json
{
  "status": "SENT",
  "recipient": "customer_number",
  "messageId": "unique_message_id"
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

* If messaging outside the **24-hour window**, use **template messages** instead.