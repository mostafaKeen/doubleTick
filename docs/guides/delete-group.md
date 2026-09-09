Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Delete a Broadcast Group

Easily remove one or more WhatsApp broadcast groups using the DoubleTick API to keep your messaging lists organized and up-to-date. 🚀

## API Endpoint

Use the following endpoint to delete a broadcast group:

```
DELETE https://public.doubletick.io/groups
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
  "groupIds": [
    "unique_group_id_1",
    "unique_group_id_2"
  ]
}
```

### Parameter

* `groupIds` (array of strings, required): A list of broadcast group IDs to be deleted.

***

## Responses

### Success Response (<<glossary:201>>)

```json
{}
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

* Double-check the **group ID** before sending the delete request.
* Ensure that the group is no longer needed before deletion.