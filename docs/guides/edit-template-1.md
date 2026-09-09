Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Edit a WhatsApp Template

Easily update your existing WhatsApp templates with the DoubleTick API to keep your messages relevant and compliant with business needs. ✏️✅

## API Endpoint

Use the following endpoint to edit a WhatsApp message template:

```
PATCH https://public.doubletick.io/template/:templateId
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
      "text": "Updated Header Text"
    },
    "body": { "text": "Updated Body Text" },
    "footer": { "text": "Updated Footer Text" },
    "buttons": [
      {
        "type": "QUICK_REPLY",
        "text": "Updated Button Text",
        "url": "https://example.com/sample-url",
        "phoneNumber": "example_phone_number"
      }
    ]
  },
  "category": "MARKETING",
  "allowCategoryUpdate": true,
  "name": "your_template_name",
  "wabaNumbers": ["sender_waba_number"]
}
```

### Parameters

#### PATH PARAMS:

* **`templateId`** (string, required): The unique ID of the template to be updated.

#### BODY PARAMS:

* `language` (string, required): The language of the template. (eg., Defaults to be `en` English)
* `components` (object, optional): Updated components of the template.
  * `header` (object, optional)
    * `format` (string, required): Available options are `TEXT`, `IMAGE`, `VIDEO` & `DOCUMENT`.
    * `text` (string, optional): Length not exceed 60 characters.
  * `body` (object, optional)
    * `text` (string, required): Length not exceed 1024 characters.
  * `footer` (object, optional)
    * `text` (string, required): Length not exceed 60 characters.
  * `buttons` (array, optional)
    * `type` (string, required): Button type (`QUICK_REPLY`, `URL`, `PHONE_NUMBER`).
    * `text` (string, required): Button text. Length to be in between 1 to 25 characters.
    * `url` (string, optional): URL for `URL` button type.
    * `phoneNumber` (string, optional): Phone number for `PHONE_NUMBER` button type.
* `category` (string, required): Template category (`MARKETING`, `UTILITY`).
* `allowCategoryUpdate` (boolean, required): Allows Meta to auto-assign a category (default: true).
* `name` (string, required)
  * Unique name for the template.
  * Length to be in between 1 to 512 characters.
* `wabaNumbers` (array of strings, required): List of WhatsApp Business API numbers associated with the template.

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

### Not Found (<<glossary:404>>)

```json
{
  "message": "error_message",
  "statusCode": 404
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

* Double-check the template ID before sending an edit request.
* Use **Get Templates API** to list all available templates before updating.