Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Check Reverted on Time

Track customer responsiveness with the DoubleTick API by verifying if a reply was received within a specified timeframe for better engagement monitoring. 🚀

## Prerequisites

* The **customer's phone number** is valid
* The **waiting time for a response** is defined
* The **agent details** are provided

## API Endpoint

Use the following endpoint to check if a customer has responded within the given timeframe:

```
POST https://public.doubletick.io/customer/check-reverted-on-time
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
  "phoneNumber": "customer_number",
  "waitingForResponseInSec": 1,
  "defaultAgentName": "agent_name",
  "defaultAgentNumber": "agent_waba_number",
  "wabaNumber": "your_waba_number"
}
```

### Parameters

* `phoneNumber` (string, required): The phone number of the customer in <<glossary:international format>>.
* `waitingForResponseInSec` (number, required): Wait time for response in seconds.
* `defaultAgentName` (string, required): The default agent's name.
* `defaultAgentNumber` (string, required): The default agent's phone number in <<glossary:international format>>.
* `wabaNumber` (string, required): The WhatsApp Business API number in <<glossary:international format>>.

***

## Response

### Success Response (201)

```json
{
  "agentName": "agent_name",
  "agentPhoneNumber": "agent_number",
  "revertedOnTime": true
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

* Ensure **phone number** and **agent details** are valid before making the request.
* Use this API to monitor customer response efficiency and improve engagement tracking.
* Set a reasonable timeframe to evaluate customer response behavior effectively.