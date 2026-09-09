Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Assign Team Member to Chat

Easily assign team members to customer chats using the DoubleTick API, ensuring efficient support and seamless communication. 👥✅

## API Endpoint

Use the following endpoint to assign a team member to a chat:

```
POST https://public.doubletick.io/team-member/assign
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
    "customerPhoneNumber": "customer_number",
    "assignedUserPhoneNumber": "agent_number",
    "reassign": true,
    "wabaNumber": "your_waba_number"
}
```

### Parameters

* `customerPhoneNumber` (string, required): The customer’s phone number in <<glossary:international format>>.
* `assignedUserPhoneNumber` (string, required): The phone number of the team member to be assigned in <<glossary:international format>>.
* `reassign` (boolean, required): Whether to reassign the chat if it is already assigned.
* `wabaNumber` (string, required): The registered WhatsApp Business API number in <<glossary:international format>>.

***

## Response

### Success Response (<<glossary:201>>)

```json
{
    "name": "assigned_agent_name",
    "email": "agent_email_address",
    "phone": "agent_number"
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

* Ensure the team member is available before assigning.
* Use reassignment wisely to avoid disrupting ongoing conversations.