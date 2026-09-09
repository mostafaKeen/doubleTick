Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Add Members to a Broadcast Group

Easily add new members to an existing WhatsApp broadcast group using the DoubleTick API for dynamic audience management and targeted messaging. 🚀

## API Endpoint

Use the following endpoint to add members to a broadcast group:

```
POST https://public.doubletick.io/groups/add-members
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
  "groupId": "group_unique_id",
  "members": [
    {
      "name": "John Doe",
      "phone": "customer_number"
    },
    {
      "name": "Jane Smith",
      "phone": "customer_number"
    }
  ]
}
```

### Parameters

* `groupId` (string, required): The unique ID of the broadcast group.
* `members` (array of objects, required): A list of members to be added.
  * `name` (string, optional): The name of the customer.
  * `phone` (string, required): The phone number of the customer in <<glossary:international format>>.

***

## Responses

### Success Response (<<glossary:201>>)

```json
{
  "invalidMembers": [
    {
      "name": "customer_name",
      "phone": "customer_phone_number"
    }
  ],
  "createdMembers": [
    "added_new_customer_phone_number"
  ],
  "membersAlreadyPresent": [
    "already_present_customer_phone_number"
  ],
  "groupId": "group_id"
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
* Verify that the group ID is correct before sending the request.
* Regularly update group members for accurate audience segmentation.