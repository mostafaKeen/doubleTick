Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Send WhatsApp Interactive List Messages

Allow customers to effortlessly choose from a structured list of options using the DoubleTick API, making interactions smoother and more intuitive. 📋✅

## API Endpoint

Use the following endpoint to send an interactive list message:

```
POST https://public.doubletick.io/whatsapp/message/interactive-list
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
    "button": "Button Text",
    "sections": [
      {
        "title": "Category 1",
        "rows": [
          { "id": "option_1", "title": "Option 1", "description": "Description for option 1" },
          { "id": "option_2", "title": "Option 2", "description": "Description for option 2" }
        ]
      }
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
    * Main message body text.
    * Length not to be exceed 1024 characters.
  * `footer` (string, optional):
    * Footer text for additional information.
    * Length not to be exceed 20 characters.
  * `button` (string, required): Button label to open the list.
  * `sections` (array of objects, required): List sections with selectable options.
    * `title` (string, required):
      * Title of the section.
      * Length not to be exceed 24 characters.
    * `rows` (array of objects, required): List of selectable options.
      * `id` (string, required):
        * Unique identifier for the option.
        * Length not to be exceed 60 characters.
      * `title` (string, required):
        * Display name of the option.
        * Length not to be exceed 24 characters.
      * `description` (string, optional):
        * Additional details about the option.
        * Length not to be exceed 72 characters.

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

* Use clear and structured options to improve user experience.
* Ensure each option has a unique ID for better response tracking.
* Limit button options to DoubleTick API's allowed maximum (up to 10 list button options per message).