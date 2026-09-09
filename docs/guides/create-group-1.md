Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Create a New Broadcast Group

Easily create WhatsApp broadcast groups with the DoubleTick API to manage and send messages to multiple recipients efficiently. 🚀

## API Endpoint

Use the following endpoint to create a new broadcast group:

```
POST https://public.doubletick.io/groups
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
  "members": [
    {
      "name": "test_1",
      "phone": "customer_phone_number"
    },
    {
      "name": "test_2",
      "phone": "customer_phone_number"
    }
  ],
  "name": "your_group_name"
}
```

### Parameters

* `name` (string, required): The name of the broadcast group.
* `members` (array, required): A list of members to be added to the group.
  * `name` (string, required): The name of the customer.
  * `phone` (string, required): The phone number of the customer in <<glossary:international format>>.

***

## Responses

### Success Response (<<glossary:201>>)

```json
{
  "groupData": {
    "groupId": "group_id",
    "memberCount": 2,
    "groupChatName": "broadcast_group_name"
  },
  "invalidMembers": [
    {
      "name": "customer_name",
      "phone": "customer_phone_number"
    }
  ]
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

* Ensure all phone numbers follow the <<glossary:international format>>.
* Choose a **unique and meaningful group name** for easy identification.
* Keep group sizes optimized for efficient message delivery.