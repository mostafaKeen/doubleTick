Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Upload Media

Easily upload media files using the DoubleTick API, supporting images, videos, and documents for seamless WhatsApp messaging. 📤✅

## API Endpoint

Use the following endpoint to upload media:

```
POST https://public.doubletick.io/media/upload
```

***

## Request Headers

```json
{
  "Authorization": "YOUR_API_KEY"
}
```

## Request Body Parameters

The request must include a **form-data** body with the media file:

| Parameter | Type | Required | Description              |
| --------- | ---- | -------- | ------------------------ |
| `file`    | file | Yes      | The media file to upload |

### Example Request (Form-Data)

```form-data
file: my-pic.jpg
```

***

## Response

### Success Response (<<glossary:201>>)

```json
{
    "mediaUrl": "https://your-media-url.com/my-pic.jpg",
    "expiresIn": 86400
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

* Ensure uploaded files comply with supported formats.
* Store the returned `mediaUrl` securely for future use.
* Handle expiration time (`expiresIn`) to avoid using expired media.