Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Conversation Cost Webhooks

Receive a real-time notification whenever a WhatsApp conversation fee is charged against your DoubleTick wallet balance.

Conversation Cost Webhooks fire each time a new WhatsApp conversation fee is incurred on your account. Use them to build real-time billing dashboards, track costs per customer or region, trigger low-balance alerts, or sync spend data into your own systems.

## Events Covered

| Event                        | Description                                                         |
| ---------------------------- | ------------------------------------------------------------------- |
| `CONVERSATION_COST_DEDUCTED` | A WhatsApp conversation fee has been charged to your wallet balance |

> This event is available only for **WhatsApp Cloud API** integrations. It relies on billing metadata supplied by Meta's Cloud API and is not emitted for Gupshup or 360dialog integrations.

***

# Conversation Cost Deducted

**Event type:** `CONVERSATION_COST_DEDUCTED`

Fires when a WhatsApp conversation fee is charged to your DoubleTick wallet. One event is emitted per conversation — not per message. Multiple messages exchanged within the same conversation window do not generate additional cost events.

## What Is a WhatsApp Conversation?

WhatsApp charges per **conversation window**, not per individual message. A conversation window is a 24-hour session that opens when:

* A customer sends you a message (user-initiated), or
* You send a customer an approved template message (business-initiated or marketing), or
* Meta initiates a service conversation on your behalf.

Once a conversation window is open, all messages exchanged within that 24-hour period are included at no additional charge. A new fee applies only when the next conversation window opens.

## Trigger Conditions

The event is emitted when:

* Meta Cloud API delivers billing metadata alongside a message status update.
* A new conversation window has been opened and its cost calculated.

It is **not** emitted when:

* A subsequent message is sent within an already-open conversation window (no additional charge).
* The conversation belongs to a Gupshup or 360dialog integration.

## Sample Payload

```json
{
  "conversationId": "conv_01HX9abc123def456",
  "customerName": "James Smith",
  "customerPhone": "+919876543210",
  "wabaNumber": "+919000000000",
  "conversationType": "USER_INITIATED",
  "countryCode": "IN",
  "cost": 0.0058
}
```

## Field Reference

| Field              | Type           | Required | Description                                                                     |
| ------------------ | -------------- | -------- | ------------------------------------------------------------------------------- |
| `conversationId`   | string         | Yes      | Unique identifier for this WhatsApp conversation window                         |
| `customerPhone`    | string         | Yes      | Customer's phone number                                                         |
| `wabaNumber`       | string         | Yes      | WhatsApp Business Account number for the integration                            |
| `conversationType` | string         | Yes      | The category of conversation that triggered the charge. See values below.       |
| `countryCode`      | string         | Yes      | ISO country code derived from the customer's phone number (e.g. `"IN"`, `"US"`) |
| `cost`             | number         | Yes      | Amount deducted from your wallet balance in your configured currency            |
| `customerName`     | string \| null | No       | Customer's display name. `null` if not resolvable at delivery time.             |

## Conversation Type Values

| Value                 | Description                                                                               |
| --------------------- | ----------------------------------------------------------------------------------------- |
| `USER_INITIATED`      | The customer sent the first message, opening a 24-hour reply window                       |
| `BUSINESS_INITIATED`  | Your business sent an approved template message (e.g. marketing or utility template)      |
| `SERVICE`             | A service conversation opened by the platform (e.g. automated responses or notifications) |
| `REFERRAL_CONVERSION` | Conversation opened via a Click-to-WhatsApp referral (ad or post)                         |

## Notes

* `conversationId` identifies the billing window, not an individual message. Store it if you need to deduplicate cost events or correlate them with specific conversation threads.
* `cost` reflects the amount charged to your DoubleTick wallet balance in your configured currency. The exact rate depends on the conversation type, the customer's country, and current Meta pricing.
* `countryCode` is derived from `customerPhone` — it represents the customer's country, not your business's country.
* There is no `eventType` field in this payload. The event type is conveyed only through the webhook URL path.

## Best Practices

* Index by `conversationId` if you're storing cost records — it's the unique key for a billing window.
* Group by `conversationType` to break down spend across marketing, utility, user-initiated, and service conversations.
* Group by `countryCode` to identify your highest-cost markets.
* Combine with `MESSAGE_RECEIVED` and `MESSAGE_STATUS_UPDATE` events (Messaging Webhooks) to correlate cost events with the specific messages that opened each conversation window.
* Do not assume `customerName` is always present — always fall back to `customerPhone` for identification.