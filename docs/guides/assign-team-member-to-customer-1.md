Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Assign Team Member to Customer

Easily assign a team member to a customer using the DoubleTick API to streamline communication and enhance customer management. 🚀

## API Endpoint

Use the following endpoint to assign a team member to a customer:

```
POST https://public.doubletick.io/customer/assign
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
  "wabaNumber": "your_waba_number"
}
```

### Parameters

* `customerPhoneNumber` (string, required): The customer's phone number in <<glossary:international format>>.
* `assignedUserPhoneNumber` (string, required): The phone number of the team member to be assigned in <<glossary:international format>>.
* `wabaNumber` (string, required): The WhatsApp Business API number in <<glossary:international format>>.

***

## Response

### Success Response (<<glossary:201>>)

```json
{
  "name": "agent_name",
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

### Not Found (<<glossary:404>>)

```json
{
  "message": "error_message",
  "code": "message_token"
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

* Ensure **phone number** and **team member details** are valid before making the request.
* Assign team members based on their expertise and customer requirements.
* Regularly review team assignments to optimize customer management.