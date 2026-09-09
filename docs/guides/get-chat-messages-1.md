Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Get chat messages for a customer of WABA number

Retrieve WhatsApp chat messages for a specific customer using the DoubleTick API, enabling seamless record-keeping and interaction analysis. 🚀

## API Endpoint

Use the following endpoint to fetch chat messages:

```
GET https://public.doubletick.io/chat-messages
```

***

## Request Headers

```json
{
  "Authorization": "YOUR_API_KEY",
  "Content-Type": "application/json"
}
```

## Query Parameters

| Parameter        | Type   | Description                                                               |
| ---------------- | ------ | ------------------------------------------------------------------------- |
| `wabaNumber`     | string | The WhatsApp Business Account Number in <<glossary:international format>> |
| `customerNumber` | string | The Customer's Phone Number in <<glossary:international format>>          |
| `startDate`      | string | The start date for the chat messages in DD-MM-YYYY format.                |
| `endDate`        | string | The end date for the chat messages in DD-MM-YYYY format                   |

***

## Responses

### Success Response (<<glossary:201>>)

```json
{
  "messages": [
    {
      "message": {
        "text": "text_message",
        "messageType": "text"
      },
      "messageOriginType": "CUSTOMER",
      "id": "message_id",
      "sentCount": 0,
      "readCount": 0,
      "deliveryCount": 0,
      "erroredCount": 0,
      "senderId": "customer_id",
      "templateId": null,
      "repliesCount": 0,
      "unreadCount": 0,
      "noResponseCount": 0,
      "isFromAd": false,
      "messageMetadata": {
        "status": null
      },
      "linkAnalytics": null,
      "senderUser": {
        "id": null,
        "name": null,
        "imageUrl": "",
        "phoneNumber": null
      },
      "integrationId": "integration_id",
      "integrationDisplayName": "DoubleTick Demo",
      "messageTime": 1737612046032,
      "quickActionButtonStats": [],
      "cardButtonStats": [],
      "totalCount": 1,
      "integrationWabaNumber": "integrated_waba_number"
    }
  ]
}
```

* `messages` (array of objects): Contains the details of the sent or received message.

  * `message` (object):
    * `text` (string): The actual text message content.
    * `messageType` (string): Type of message, e.g., `"text"`.

  * `messageOriginType` (string):
    * Indicates the source of the message.
    * Possible values: `"CUSTOMER"`, `"USER"`, `"SYSTEM"`, etc.

  * `id` (string): Unique identifier for the message.

  * `sentCount` (integer): Number of times the message was sent.

  * `readCount` (integer): Number of times the message was read.

  * `deliveryCount` (integer): Number of times the message was successfully delivered.

  * `erroredCount` (integer): Number of times the message failed to send.

  * `senderId` (string): Unique identifier of the customer sending the message.

  * `templateId` (string): ID of the template used (null if not a template message).

  * `repliesCount` (integer): Number of replies received for this message.

  * `unreadCount` (integer): Count of unread messages from the customer.

  * `noResponseCount` (integer): Number of times the customer didn’t respond.

  * `isFromAd` (boolean): Indicates if the message originated from an ad.

  * `messageMetadata` (object): Contains additional metadata.
    * `status` (string)

  * `linkAnalytics` (object): Data related to link tracking.

  * `senderUser` (object):
    * `id` (string): User ID of the sender.
    * `name` (string): Name of the sender.
    * `imageUrl` (string): URL of the sender’s profile picture.
    * `phoneNumber` (string): Sender’s phone number.

  * `integrationId` (string): ID of the integration used to send the message.

  * `integrationDisplayName` (string): Display name of the integration.

  * `integrationWabaNumber` (string): The registered **WhatsApp Business Account (WABA)** number used.

  * `messageTime` (integer): Timestamp of when the message was sent (in milliseconds).

  * `quickActionButtonStats` (array): Statistics for quick reply buttons.

  * `cardButtonStats` (array): Statistics for card-based buttons.

  * `totalCount` (integer): Total number of messages returned in this response.

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
  "statusCode": 404
}
```

***

## Best Practices

* Store retrieved messages securely for compliance and customer service tracking.
* Ensure date filters are optimized for efficient query performance.