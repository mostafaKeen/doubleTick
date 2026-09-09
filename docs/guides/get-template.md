Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Get WhatsApp Templates

Retrieve your WhatsApp templates effortlessly with the DoubleTick API, filtering by status, category, language, and more for seamless template management. 📄✅

## API Endpoint

Use the following endpoint to fetch WhatsApp templates:

```
GET https://public.doubletick.io/v2/templates
```

***

## Request Headers

```json
{
  "Authorization": "YOUR_API_KEY",
  "Content-Type": "application/json"
}
```

## Query Parameters

| Parameter             | Type    | Description                                                                              |
| --------------------- | ------- | ---------------------------------------------------------------------------------------- |
| `status`              | string  | The status of the templates (e.g., `APPROVED`, `REJECTED`, `PENDING`, `PAUSED` or `ALL`) |
| `name`                | string  | The name of the template                                                                 |
| `language`            | string  | The language of the template. Defaults to be `en` - English                              |
| `category`            | string  | The category of the template (`MARKETING`, `UTILITY`)                                    |
| `wabaPhoneNumbers`    | string  | The WhatsApp Business API phone numbers in <<glossary:international format>>s.           |
| `allWabaPhoneNumbers` | boolean | If set to true, returns templates for all WABA Phone Numbers.                            |

***

## Response

### Success Response (<<glossary:201>>)

```json
[
  {
    "id": "template_id",
    "name": "your_template_name",
    "language": "en",
    "category": "MARKETING",
    "components": [
      {
        "type": "HEADER",
        "format": "TEXT",
        "text": "Header Content"
      },
      {
        "type": "BODY",
        "text": "Body Content with placeholders"
      },
      {
        "type": "FOOTER",
        "text": "Footer Content"
      }
    ],
    "buttons": [
      {
        "type": "QUICK_REPLY",
        "text": "Button 1"
      }
    ],
    "status": "APPROVED",
    "createdBy": "creator_name",
    "wabaPhoneNumber": "your_waba_number"
  }
]
```

* `id` (string): Unique identifier of the template.

* `name` (string): Name of the template.

* `language` (string): Language code of the template (e.g., `"en"` for English).

* `category` (string): Category of the template, e.g., `"MARKETING"`.

* `components` (array): Defines different parts of the template.
  * `type` (string): Type of component, e.g., `"HEADER"`, `"BODY"`, or `"FOOTER"`.
  * `format` (string, optional): Specifies the format for the `HEADER` component (e.g., `"TEXT"`).
  * `text` (string): Content of the component, with placeholders.

* `buttons` (array): Contains interactive buttons for the template.
  * `type` (string): Type of button, e.g., `"QUICK_REPLY"`.
  * `text` (string): Label of the button.

* `status` (string): Current approval status of the template, e.g., `"APPROVED"`.

* `createdBy` (string): Name of the user who created the template.

* `wabaPhoneNumber` (string): The registered WhatsApp Business API phone number associated with the template.

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

* Use specific filters (e.g., `status=APPROVED`, `category=MARKETING`) for optimized results.
* Ensure that your API key is valid and has the necessary permissions.
* Retrieve templates periodically to keep track of changes and approvals.