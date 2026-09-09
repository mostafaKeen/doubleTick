Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Get Chat Window Status

Get chat window status for a customer with given WABA number 💫

## API Endpoint

User the following endpoint to get chat window status:

```
GET https://public.doubletick.io/chat/status
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

* `customerPhoneNumber` (string, required): The customer's phone number in <<glossary:international format>>.
* `wabaNumber` (string, required): The WhatsApp Business API number in <<glossary:international format>>.

***

## Response

### Success Response (<<glossary:201>>)

```json
{
  "customerPhoneNumber": "customer_number",
  "wabaNumber": "your_waba_number",
  "isOpen": false
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

* Ensure **phone numbers** are valid before making the request.