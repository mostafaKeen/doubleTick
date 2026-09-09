Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Unassign Team Member to Chat

Easily unassign team members from customer chats using the DoubleTick API, ensuring flexible and efficient support management. 🔄✅

## API Endpoint

Use the following endpoint to unassign a team member from a chat:

```
POST https://public.doubletick.io/team-member/unassign
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
  "wabaNumber": "your_waba_number"
}
```

### Parameters

* `customerPhoneNumber` (string, required): The customer’s phone number in <<glossary:international format>> from which the team member should be unassigned.
* `wabaNumber` (string, required): The registered WhatsApp Business API number in <<glossary:international format>>.

***

## Response

### Success Response (201)

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

* Verify the customer’s phone number is correct before making the request.