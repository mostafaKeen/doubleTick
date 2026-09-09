Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Send Template WhatsApp Message to a Broadcast Group

Send WhatsApp template messages to all members of a broadcast group efficiently using the DoubleTick API for seamless bulk communication. 🚀

## API Endpoint

Use the following endpoint to send a template message to a broadcast group:

```
POST https://public.doubletick.io/whatsapp/message/broadcast
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
  "content": {
    "language": "en",
    "templateData": {
      "header": {
        "type": "TEXT",
        "placeholder": "Header text",
        "mediaUrl": "https://example.com/image.png",
        "filename": "Document caption",
        "latitude": -23.5505199,
        "longitude": -46.6333094
      },
      "body": {
        "placeholders": [
          "Body text",
          "Body text2",
          "Body text3"
        ]
      },
      "buttons": [
        {
          "type": "URL",
          "parameter": "Button text"
        },
        {
          "type": "URL",
          "parameter": "Button text2"
        }
      ],
      "cards": [
        {
          "components": {
            "header": {
              "type": "IMAGE",
              "mediaUrl": "https://example.com/image.png"
            },
            "body": {
              "placeholders": [
                "Body text",
                "Body text2"
              ]
            },
            "buttons": [
              {
                "type": "URL",
                "parameter": "Button text"
              },
              {
                "type": "URL",
                "parameter": "Button text2"
              }
            ]
          },
          "cardIndex": 0
        }
      ]
    },
    "templateName": "your_template_name"
  },
  "groupName": "broadcast_group_name",
  "from": "sender_number"
}
```

### Parameters

* `groupName` (string, required): The name of the broadcast group.
* `from` (string, required): The sender's registered WhatsApp number in <<glossary:international format>>.
* `content` (object, optional)
  * `language` (string, required): Language code of the template (e.g., Defaults to be "en" English).
  * `templateName` (string, required): The name of the WhatsApp template.
    * `header` (object, optional): Header content (e.g., text, media, location).
    * `body` (object, optional)
      * `placeholders` (array of strings, required): Dynamic values for body placeholders.
    * `buttons` (array, optional): List of interactive buttons (e.g., URL, Call-to-Action).
    * `cards` (array, optional): Card components for multi-item messaging.
  * `templateName` (string, required): Name of the template.

***

## Responses

### Success Response (<<glossary:201>>)

```json
{
  "status": "SENT",
  "messageId": "sent_message_id"
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

* Ensure all **group members have opted in** to receive messages.
* Use **personalized placeholders** to improve engagement.