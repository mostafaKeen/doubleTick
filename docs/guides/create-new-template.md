Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Create New WhatsApp Template

Easily create WhatsApp message templates with the DoubleTick API, ensuring seamless and compliant customer communication. ✨📩

## API Endpoint

Use the following endpoint to create a new WhatsApp message template:

```
POST https://public.doubletick.io/template
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
  "language": "en",
  "components": {
    "header": {
      "format": "TEXT",
      "text": "Header text"
    },
    "body": {
      "text": "Body Text"
    },
    "footer": {
      "text": "Footer Text"
    },
    "buttons": [
      {
        "type": "URL",
        "text": "Create New Template",
        "url": "https://your-file-url/file.jpg"
      }
    ]
  },
  "category": "UTILITY",
  "allowCategoryUpdate": true,
  "name": "your_template_name",
  "wabaNumbers": [
    "sender_number"
  ]
}
```

### Parameters

* `language` (string, required): The language of the template.
* `components` (object, required): The structure of the template, including header, body, footer, and buttons.
  * `header` (object, optional): Can include text or media (image, video, document).
    * `format` (string, required): Header format (`TEXT`, `IMAGE`, `VIDEO`, `DOCUMENT`)
    * `text` (string, optional)
      * Length not exceed 60 characters.
  * `body` (object, required)
    * `text` (string, required)
      * Main text content with placeholders if needed.
      * Length not exceed 1024 characters.
  * `footer` (object, optional)
    * `text` (string, required)
      * Length not exceed 60 characters.
  * `buttons` (array of objects, optional)
    * `type` (string, required): List of interactive buttons (`QUICK_REPLY`, `URL`, `PHONE_NUMBER`).
    * `text` (string, required): Length to be in between 1 to 25 characters.
    * `url` (string, optional): Required if type selected as URL
    * `phoneNumber` (string, optional): Required if type selected as PHONE\_NUMBER
* `category` (string, required): Template category (`UTILITY`, `MARKETING`).
* `allowCategoryUpdate` (boolean, optional): Allows Meta to auto-assign a category (default: true).
* `name` (string, required):
  * Unique name for the template.
  * Length to be in between 1 to 512 characters.
* `wabaNumbers` (array of strings, required): List of WhatsApp Business API numbers registered for messaging.

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

* Choose a clear, descriptive template name.
* Use placeholders wisely for personalization.
* Follow WhatsApp's **Business Messaging Policy** to avoid rejection.