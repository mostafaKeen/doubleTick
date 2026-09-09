Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Logout A Team Member

Securely log out team members from active sessions using the DoubleTick API, ensuring better access control and session management. 🔒✅

## API Endpoint

Use the following endpoint to log out a team member:

```
POST https://public.doubletick.io/team-member/logout
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
    "userPhoneNumber": "agent_number"
}
```

### Parameters

* `userPhoneNumber` (string, required): The phone number of the team member in <<glossary:international format>> to be logged out.

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

***

## Best Practices

* Ensure the phone number is correctly formatted before making the request.
* Use secure authentication to prevent unauthorized logouts.
* Regularly review active sessions for security compliance.