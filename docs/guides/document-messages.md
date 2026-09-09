Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Send WhatsApp Document Messages

Easily share PDFs, spreadsheets, and other documents through WhatsApp using the DoubleTick API for seamless file transfer. 🚀

## API Endpoint

Use the following endpoint to send a document message:

```
POST https://public.doubletick.io/whatsapp/message/document
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
    "mediaUrl": "https://your-document-url.com/file.pdf",
    "caption": "Sample file",
    "filename": "Sample Document"
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
  * `mediaUrl` (string, required):
    * URL of the document file to be sent.
    * Length to be in between 1 to 2048 characters.
  * `caption` (string, optional):
    * Caption of the document file to be sent.
    * Length to be in between 1 to 3000 characters.
  * `filename` (string, optional):
    * The display name of the document file.
    * Length to be in between 1 to 240 characters.

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

* Ensure the document file is in a supported format (PDF, DOCX, XLSX, TXT).
* The media URL must be publicly accessible or hosted on a compatible server.
* If you don’t have a shareable media URL, [upload your media](https://docs.doubletick.io/reference/upload-media) to our cloud and use the generated URL to send it.
* Maintain an optimal file size for faster message delivery.