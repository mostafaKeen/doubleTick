Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Change Reporting Manager

Update a team member’s reporting manager using the DoubleTick API to maintain an accurate and organized team hierarchy. 🚀

## API Endpoint

Use the following endpoint to change a team member’s reporting manager:

```
PATCH https://public.doubletick.io/team-member/reporting-manager
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
  "reportingManagerPhoneNumber": "new_manager_number",
  "memberPhoneNumber": "agent_number"
}
```

### Parameters

* `reportingManagerPhoneNumber` (string, required): The phone number of the new reporting manager in <<glossary:international format>>.
* `memberPhoneNumber` (string, required): The phone number of the team member whose reporting manager needs to be changed in <<glossary:international format>>.

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

* Ensure the **team member** and **new reporting manager** exist in your organization before making the request.
* Keep your team hierarchy updated for better role management.
* Validate phone numbers to prevent incorrect assignments.