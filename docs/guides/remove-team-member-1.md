Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Remove Team Member

Efficiently remove a team member from your organization using the DoubleTick API to maintain an updated and structured team hierarchy. 🚀

## API Endpoint

Use the following endpoint to remove a team member:

```
DELETE https://public.doubletick.io/team-member
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
  "memberPhoneNumber": "agent_number"
}
```

### Parameters

* `memberPhoneNumber` (string, required): The phone number of the team member in <<glossary:international format>> to be removed.

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

* Double-check the **phone number** before making the request to avoid errors.
* Use this endpoint when a team member leaves or their role is no longer required.
* Maintain an updated team list for better team management.