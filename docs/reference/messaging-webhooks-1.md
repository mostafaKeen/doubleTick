Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Messaging Webhooks

Receive real-time notifications for inbound and outbound WhatsApp messaging activity, including received messages, delivery status changes, and Click-to-WhatsApp ad attribution.

Messaging webhooks allow your application to receive real-time updates whenever messages are sent, delivered, read, failed, or received through your DoubleTick WhatsApp channel.

These webhooks help you keep external systems synchronized with customer conversations, automate workflows, trigger notifications, update CRMs, and build custom reporting pipelines.

## Events Covered

| Event                               | Description                                                                                           |
| ----------------------------------- | ----------------------------------------------------------------------------------------------------- |
| `MESSAGE_RECEIVED`                  | Triggered whenever a customer sends a message to your WhatsApp channel in an individual conversation. |
| `MESSAGE_STATUS_UPDATE`             | Triggered whenever the status of an outbound message changes.                                         |
| `CALL_TO_WHATSAPP_MESSAGE_RECEIVED` | Triggered when an inbound message carries Click-to-WhatsApp ad attribution.                           |

A given inbound message triggers `MESSAGE_RECEIVED` and, when ad attribution is present, additionally triggers `CALL_TO_WHATSAPP_MESSAGE_RECEIVED`. Messages sent inside WhatsApp Groups trigger `WA_GROUP_MESSAGE_RECEIVED` instead — see [**WhatsApp Group Webhooks**](https://docs.doubletick.io/reference/whatsapp-group-webhooks).

***

# Message Received

**Event type:** `MESSAGE_RECEIVED`

This webhook is triggered whenever DoubleTick receives a message from a customer in an individual (non-group) WhatsApp conversation. Messages sent inside WhatsApp Groups instead trigger `WA_GROUP_MESSAGE_RECEIVED`, and messages carrying Click-to-WhatsApp ad attribution additionally trigger `CALL_TO_WHATSAPP_MESSAGE_RECEIVED`.

The webhook is sent for all supported inbound WhatsApp message types, including:

* Text
* Image
* Video
* Audio
* Sticker
* Document
* Location
* Contacts
* Button Replies
* Flow Responses

## Sample Payload

```json
{
  "to": "11111111111",
  "from": "919999999999",
  "messageId": "wamid.HBgMOTE4OTc5NDk5OTQ5FQIAEhggQUNGOEE3REIwOUFBMjc2QTk0Mzk3Q0FEMzEyQTY4NTQA",
  "dtMessageId": "8b4abc26-2ea9-4f7d-a49a-90015e8dd7b8",
  "receivedAt": "2026-06-04T11:57:57.796Z",
  "contact": {
    "name": "John Doe"
  },
  "callbackData": null,
  "dtPairedTemplateId": null,
  "dtPairedTemplateName": null,
  "dtPairedTemplateLanguage": null,
  "dtPairedMessageMetadata": null,
  "integrationType": "WHATSAPP",
  "message": {
    "type": "TEXT",
    "text": "Hello",
    "context": {}
  },
  "dtLastMessageId": "dt_last_message_id",
  "lastMessageOrigin": "USER",
  "isAgentOffline": false,
  "customerId": "customer_xxxxxxxxxx",
  "dtCustomerId": "customer_xxxxxxxxxx"
}
```

## Common Fields

| Field                     | Type           | Description                                                                                                                                          |
| ------------------------- | -------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------- |
| `to`                      | string         | WhatsApp Business number that received the message.                                                                                                  |
| `from`                    | string         | Customer's WhatsApp number.                                                                                                                          |
| `messageId`               | string         | Original WhatsApp message identifier.                                                                                                                |
| `dtMessageId`             | string         | DoubleTick message identifier.                                                                                                                       |
| `receivedAt`              | string         | Timestamp when the message was received.                                                                                                             |
| `contact.name`            | string         | Customer display name.                                                                                                                               |
| `integrationType`         | string         | Channel type. Currently `WHATSAPP`.                                                                                                                  |
| `customerId`              | string         | DoubleTick customer identifier.                                                                                                                      |
| `dtCustomerId`            | string         | Same value as `customerId`.                                                                                                                          |
| `isAgentOffline`          | boolean        | Whether the agent currently assigned to this conversation is offline at the time the message is received. `false` if no agent is currently assigned. |
| `dtLastMessageId`         | string \| null | DoubleTick identifier of the previous message in the conversation. `null` if this is the first message.                                              |
| `lastMessageOrigin`       | string \| null | Origin of the previous message. See values below.                                                                                                    |
| `callbackData`            | string \| null | Custom data attached to the outbound message the customer is replying to. `null` when not applicable.                                                |
| `dtPairedMessageMetadata` | object \| null | Additional metadata associated with the paired outbound message. `null` when not applicable.                                                         |
| `userId`                  | string         | Meta user identifier. Present only for messages from Meta Cloud API integrations where user identity data is provided.                               |
| `parentUserId`            | string         | Parent Meta user identifier. Present only when provided by Meta's Cloud API.                                                                         |
| `username`                | string         | Meta username. Present only when provided by Meta's Cloud API.                                                                                       |
| `metaBusinessPortfolioId` | string         | Meta Business Portfolio identifier. Present only when provided by Meta's Cloud API.                                                                  |

### lastMessageOrigin Values

| Value      | Description                                                      |
| ---------- | ---------------------------------------------------------------- |
| `USER`     | The previous message was sent by a DoubleTick agent              |
| `CUSTOMER` | The previous message was sent by the customer                    |
| `SYSTEM`   | The previous message was a system-generated notification         |
| `REMINDER` | The previous message was a scheduled reminder sent by DoubleTick |

### Alias Fields

`customerId` and `dtCustomerId` always contain the same value. `dtCustomerId` is the canonical identifier — `customerId` is a legacy alias maintained for backwards compatibility. Use `dtCustomerId` in new integrations.

> **Phone number format:** `to` and `from` in `MESSAGE_RECEIVED` do **not** include a `+` prefix (e.g. `"918979499949"`). Most other DoubleTick events use E.164 format with a `+` prefix. Do not apply blanket normalization across event types — check the format per event before parsing or storing.

## Reply Context

When a customer replies to an existing message, the `context` object contains information about the original message.

```json
{
  "context": {
    "from": "919999999999",
    "id": "msg_xxxxxxxxxx"
  }
}
```

| Field          | Description                                    |
| -------------- | ---------------------------------------------- |
| `context.id`   | DoubleTick ID of the message being replied to. |
| `context.from` | Sender of the original message.                |

When the message is not a reply:

```json
{
  "context": {}
}
```

## Template Reply Information

If a customer replies to a template message, the following fields are populated:

```json
{
  "dtPairedTemplateId": 123,
  "dtPairedTemplateName": "order_confirmation",
  "dtPairedTemplateLanguage": "en"
}
```

| Field                      | Description          |
| -------------------------- | -------------------- |
| `dtPairedTemplateId`       | Template identifier. |
| `dtPairedTemplateName`     | Template name.       |
| `dtPairedTemplateLanguage` | Template language.   |

These fields are `null` when the message is not related to a template.

## Supported Message Types

### Text Message

```json
{
  "message": {
    "type": "TEXT",
    "text": "Hello",
    "context": {}
  }
}
```

### Image Message

```json
{
  "message": {
    "type": "IMAGE",
    "url": "https://cdn.doubletick.io/example.jpg",
    "caption": "Check this out",
    "context": {}
  }
}
```

### Video Message

```json
{
  "message": {
    "type": "VIDEO",
    "url": "https://cdn.doubletick.io/example.mp4",
    "caption": "Product Demo",
    "context": {}
  }
}
```

### Audio Message

```json
{
  "message": {
    "type": "AUDIO",
    "url": "https://cdn.doubletick.io/example.ogg",
    "context": {}
  }
}
```

### Sticker Message

```json
{
  "message": {
    "type": "STICKER",
    "url": "https://cdn.doubletick.io/example.webp",
    "context": {}
  }
}
```

### Document Message

```json
{
  "message": {
    "type": "DOCUMENT",
    "url": "https://cdn.doubletick.io/invoice.pdf",
    "fileName": "invoice.pdf",
    "caption": "Invoice",
    "context": {}
  }
}
```

### Location Message

```json
{
  "message": {
    "type": "LOCATION",
    "latitude": 28.6139,
    "longitude": 77.2090,
    "name": "Connaught Place",
    "address": "New Delhi, India",
    "context": {}
  }
}
```

### Contacts Message

```json
{
  "message": {
    "type": "CONTACTS",
    "contacts": [
      {
        "name": {
          "formatted_name": "Jane Doe",
          "first_name": "Jane",
          "last_name": "Doe"
        },
        "phones": [
          { "phone": "+919876543210", "type": "CELL" }
        ],
        "emails": [
          { "email": "jane@example.com", "type": "WORK" }
        ]
      }
    ]
  }
}
```

### Button Reply

```json
{
  "message": {
    "type": "BUTTON",
    "text": "Yes",
    "payload": "CONFIRM_ORDER_V2",
    "id": "btn_confirm",
    "context": {}
  }
}
```

> `id` is the button's unique identifier as defined in the template. `payload` is the developer-defined data string attached to the button — it can be set independently and is the field to use for routing logic. The two often have the same value in simple cases but can differ (e.g. `id` is a short key; `payload` carries structured data).

### Flow Response

```json
{
  "message": {
    "type": "FLOW",
    "flowResponse": [
      {
        "id": "customer_name",
        "value": "John Doe"
      }
    ],
    "context": {}
  }
}
```

***

# Message Status Update

**Event type:** `MESSAGE_STATUS_UPDATE`

This webhook is triggered whenever the delivery status of an outbound message changes.

Supported status values:

* `SENT`
* `DELIVERED`
* `READ`
* `FAILED`

> **`to` field semantics:** In `MESSAGE_STATUS_UPDATE`, `to` is the **customer's** phone number (the outbound message recipient) — not the WhatsApp Business number. This is the opposite of `MESSAGE_RECEIVED`, where `to` is the WABA number. Use `wabaNumber` when you need to identify the sending business account in this event.

## SENT Example

```json
{
  "messageId": "dt_message_id",
  "to": "919999999999",
  "wabaNumber": "+919000000000",
  "status": "SENT",
  "statusTimestamp": "2026-06-04T11:48:14.000Z",
  "customerName": "John Doe",
  "dtCustomerId": "customer_pvgpZUh3wg",
  "sentBy": "agent_01HX9abc123",
  "assignedTo": "agent_01HX9abc123",
  "message": {
    "type": "TEXT",
    "text": "Hello"
  }
}
```

## DELIVERED Example

```json
{
  "messageId": "dt_message_id",
  "to": "919999999999",
  "status": "DELIVERED",
  "statusTimestamp": "2026-06-04T11:48:16.000Z"
}
```

## READ Example

```json
{
  "messageId": "dt_message_id",
  "to": "919999999999",
  "status": "READ",
  "statusTimestamp": "2026-06-04T11:49:02.000Z"
}
```

## FAILED Example

```json
{
  "messageId": "dt_message_id",
  "to": "919999999999",
  "status": "FAILED",
  "statusTimestamp": "2026-06-04T11:48:14.000Z",
  "failMessage": "Message delivery failed",
  "message": {
    "type": "TEXT",
    "text": "Hello"
  }
}
```

## Status Fields

| Field             | Description                                                                                                 |
| ----------------- | ----------------------------------------------------------------------------------------------------------- |
| `messageId`       | DoubleTick message identifier.                                                                              |
| `to`              | Recipient phone number.                                                                                     |
| `status`          | Current message status.                                                                                     |
| `statusTimestamp` | Timestamp of the status update.                                                                             |
| `message`         | Available for `SENT` and `FAILED` statuses.                                                                 |
| `failMessage`     | Available only for `FAILED` status.                                                                         |
| `customerName`    | Customer name when available.                                                                               |
| `dtCustomerId`    | DoubleTick customer identifier.                                                                             |
| `sentBy`          | Opaque identifier of the agent or system that sent the message. Not a display name — use for tracking only. |
| `assignedTo`      | Opaque identifier of the agent the conversation is currently assigned to at delivery time.                  |
| `wabaNumber`      | WhatsApp Business number used to send the message. Use this (not `to`) to identify the sending account.     |

***

# Call to WhatsApp Message Received

**Event type:** `CALL_TO_WHATSAPP_MESSAGE_RECEIVED`

Fires whenever an inbound WhatsApp message carries Click-to-WhatsApp (CTWA) ad attribution data — i.e. the customer tapped a Meta ad or post and was taken directly into a WhatsApp conversation.

This event is an **attribution companion** to `MESSAGE_RECEIVED`, not a replacement for it. Both events are emitted for the same inbound message whenever ad attribution is present.

## Trigger Conditions

The event is emitted when:

* An inbound message is received in an individual (non-group) WhatsApp conversation.
* The message includes ad-click attribution metadata supplied by the messaging provider.

It is **not** emitted for:

* Messages without ad attribution (the ordinary `MESSAGE_RECEIVED` event still fires for those).
* Messages received inside WhatsApp Groups (see [`WA_GROUP_MESSAGE_RECEIVED`](https://docs.doubletick.io/reference/whatsapp-group-webhooks#wa_group_message_received)).

## Sample Payload

```json
{
  "to": "11111111111",
  "from": "919999999999",
  "messageId": "wa_message_id",
  "dtMessageId": "dt_message_id",
  "receivedAt": "2026-06-04T11:57:57.796Z",
  "contact": {
    "name": "John Doe"
  },
  "integrationType": "WHATSAPP",
  "message": {
    "type": "TEXT",
    "text": "Hi, I'm interested",
    "context": {}
  },
  "dtLastMessageId": null,
  "lastMessageOrigin": "USER",
  "isAgentOffline": false,
  "customerId": "customer_pvgpZUh3wg",
  "dtCustomerId": "customer_pvgpZUh3wg",
  "referralObject": {
    "source_url": "https://www.facebook.com/ads/123456789",
    "source_type": "ad",
    "source_id": "123456789",
    "headline": "Get 20% off today",
    "body": "Chat with us to claim your discount",
    "ctwa_clid": "ARAkLgKMnpFbFoAbXABC123",
    "media_type": "image",
    "image_url": "https://cdn.example.com/ad-image.jpg"
  }
}
```

## Field Reference

This payload reuses the same base structure as `MESSAGE_RECEIVED` (see the Common Fields table above: `to`, `from`, `messageId`, `dtMessageId`, `receivedAt`, `contact.name`, `integrationType`, `message`, `customerId`, `dtCustomerId`, `isAgentOffline`, `dtLastMessageId`, `lastMessageOrigin`, `callbackData`, `dtPairedTemplateId`, `dtPairedTemplateName`, `dtPairedTemplateLanguage`, `dtPairedMessageMetadata`) and adds one additional required field:

| Field            | Type   | Required | Description                                                                                               |
| ---------------- | ------ | -------- | --------------------------------------------------------------------------------------------------------- |
| `referralObject` | object | Yes      | Ad attribution data for the Click-to-WhatsApp ad or post that brought the customer into the conversation. |

## referralObject

The exact shape of `referralObject` varies slightly depending on the WhatsApp provider behind the integration.

### Common Fields (all providers)

| Field         | Type   | Description                                 |
| ------------- | ------ | ------------------------------------------- |
| `source_url`  | string | URL of the ad or post the customer clicked. |
| `source_type` | string | Origin type: `ad` or `post`.                |
| `source_id`   | string | Meta identifier for the ad or post.         |
| `headline`    | string | Ad headline.                                |
| `body`        | string | Ad body text.                               |
| `ctwa_clid`   | string | Click-to-WhatsApp tracking identifier.      |

### Media Attribution — Cloud API

| Field           | Type   | Condition                              |
| --------------- | ------ | -------------------------------------- |
| `media_type`    | string | Present when media attribution exists. |
| `image_url`     | string | Present when `media_type` is `image`.  |
| `video_url`     | string | Present when `media_type` is `video`.  |
| `thumbnail_url` | string | Present when `media_type` is `video`.  |

### Media Attribution — Gupshup / 360dialog

| Field   | Type   | Condition                                                   |
| ------- | ------ | ----------------------------------------------------------- |
| `image` | object | Present when the ad used an image. Shape: `{ "id": "..." }` |
| `video` | object | Present when the ad used a video. Shape: `{ "id": "..." }`  |

## Conditional Fields

| Field             | Condition                                                                                 |
| ----------------- | ----------------------------------------------------------------------------------------- |
| `image_url`       | Present only for image-based ad attribution (Cloud API).                                  |
| `video_url`       | Present only for video-based ad attribution (Cloud API).                                  |
| `thumbnail_url`   | Present only for video-based ad attribution (Cloud API).                                  |
| `image` / `video` | Present only on Gupshup / 360dialog integrations, in place of the Cloud API media fields. |

## Notes

* This event is **not deduplicated**. Unlike [`NEW_LEAD`](https://docs.doubletick.io/reference/widget-lead-webhooks#new_lead), it fires every time an ad-attributed message arrives — including repeat messages from a returning customer who keeps clicking ads.
* This event always co-occurs with `MESSAGE_RECEIVED` for the same inbound message.
* If the message is also the customer's first-ever message to your WhatsApp Business Account, `NEW_LEAD` is also emitted, with its own `referral` object and `isCTWA: true`.
* This event is supported across all WhatsApp provider integrations.
* This event is never emitted for messages sent inside WhatsApp Groups.

## Edge Cases

### Multiple Events Per Message

A single inbound ad-attributed message can generate up to three webhook events:

```text
Customer taps a WhatsApp ad and sends a message
            │
            ├── MESSAGE_RECEIVED
            ├── CALL_TO_WHATSAPP_MESSAGE_RECEIVED
            └── NEW_LEAD (only if this is the customer's first-ever message)
```

Each event serves a different purpose:

* `MESSAGE_RECEIVED` tracks the inbound message itself.
* `CALL_TO_WHATSAPP_MESSAGE_RECEIVED` provides ad attribution for that specific message.
* `NEW_LEAD` marks the customer's lifetime first contact.

### Returning Customers

A customer who has messaged before but clicks a new ad and sends another message will trigger `CALL_TO_WHATSAPP_MESSAGE_RECEIVED` again — `NEW_LEAD` will not fire a second time.

## Best Practices

* Use `dtMessageId` to correlate this event with the corresponding `MESSAGE_RECEIVED` event for the same message.
* Do not rely on the format of `ctwa_clid` — it is an opaque value generated by Meta.
* Branch your handling logic on which media fields are present (`image_url`/`video_url` vs `image`/`video`) rather than assuming a single shape across all integrations.