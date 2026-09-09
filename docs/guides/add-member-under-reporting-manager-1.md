Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Add Member Under Reporting Manager

Easily add members under a reporting manager using the DoubleTick API, ensuring accurate team hierarchy and streamlined access control. 👥✅

## API Endpoint

Use the following endpoint to add a member under a reporting manager:

```
POST https://public.doubletick.io/team/members
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
  "reportingManagerPhoneNumber": "manager_number",
  "members": [
    {
      "name": "new_agent_name",
      "phone": "new_agent_number",
      "orgRoleId": "role_assigned_to_new_agent",
      "wabaAccess": [
        {
          "wabaNumber": "waba_number",
          "wabaRoleId": "waba_number_access_role"
        },
        {
          "wabaNumber": "waba_number_2",
          "wabaRoleId": "waba_number_access_role"
        },
        {
          "wabaNumber": "waba_number_3",
          "wabaRoleId": "waba_number_access_role"
        }
      ],
      "addToTeamDirectly": true,
      "sendInviteMessage": true
    }
  ]
}
```

### Parameters

* `reportingManagerPhoneNumber` (string, required): Phone number of the reporting manager in <<glossary:international format>>.
* `members` (array of objects, required)
  * `name` (string, required): Member's full name.
  * `phone` (string, required): Member's phone number in <<glossary:international format>>.
  * `orgRoleId` (string, required): Organization role assigned to the member.
  * `wabaAccess` (array, required): Array of WhatsApp Business API (WABA) access objects:
    * `wabaNumber` (string, required): WABA number in <<glossary:international format>>.
    * `wabaRoleId` (string, required): Role Assigned for Waba Number.
  * `addToTeamDirectly` (boolean, required): Whether to add the member to the team directly.
  * `sendInviteMessage` (boolean, required): Whether to send an invite message to the member.

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

* Ensure all provided phone numbers and role IDs are valid and registered.
* Use the correct reporting manager and verify their phone number.
* Confirm WABA access and roles before assigning members.