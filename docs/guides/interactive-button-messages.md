Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Send WhatsApp Interactive Button Messages

Enhance customer engagement with interactive button messages using the DoubleTick API, allowing users to respond quickly with predefined options. 🎯🚀

## API Endpoint

Use the following endpoint to send an interactive button message:

```
POST https://public.doubletick.io/whatsapp/message/interactive
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
  "messageId": "uuid-v4",
  "content": {
    "header": "Sample Header",
    "body": "Sample Body",
    "footer": "Sample Footer",
    "buttons": [
      { "title": "Title 1", "id": "btn_1" },
      { "title": "Title 2", "id": "btn_2" }
    ]
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
  * `header` (string, optional):
    * Header text for the message.
    * Length not to be exceed 60 characters.
  * `body` (string, required):
    * The main text content of the message.
    * Length not to be exceed 1024 characters.
  * `footer` (string, optional):
    * Footer text for the message.
    * Length not to be exceed 20 characters.
  * `buttons` (array of objects, required): A list of button options.
    * `title` (string, required):
      * Text displayed on the button.
      * Length not to be exceed 20 characters.
    * `id` (string, required):
      * A unique identifier for the button.
      * Length not to be exceed 60 characters.

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

* Use clear and concise button labels for better user engagement.
* Ensure each button has a unique ID to track responses efficiently.
* Limit buttons to DoubleTick API's allowed maximum (up to 3 buttons per message).