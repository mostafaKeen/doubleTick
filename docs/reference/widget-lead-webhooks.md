Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Widget & Lead Webhooks

Receive real-time notifications when a customer contacts your WhatsApp Business Account for the first time, submits a DoubleTick widget form, or when that submission is later linked to a WhatsApp contact.

## Events Covered

| Event                           | Description                                                        |
| ------------------------------- | ------------------------------------------------------------------ |
| `NEW_LEAD`                      | Fires when a customer sends their first-ever message to your WABA  |
| `WIDGET_LEAD_RECEIVED`          | Fires when a customer submits a DoubleTick widget form (anonymous) |
| `VERIFIED_WIDGET_LEAD_RECEIVED` | Fires when a widget submission is matched to a WhatsApp contact    |

`NEW_LEAD` is independent of the widget flow — it can fire with or without a prior widget submission. `WIDGET_LEAD_RECEIVED` and `VERIFIED_WIDGET_LEAD_RECEIVED` represent two stages of the same form-to-WhatsApp-match flow.

***

# First Time Message from Customer

**Event type:** `NEW_LEAD`

`NEW_LEAD` is designed for lead acquisition workflows and fires when a customer sends their first-ever message to your WhatsApp Business Account (WABA). It represents the moment a new customer enters your pipeline and is permanently deduplicated, meaning it is emitted only once for a customer throughout their lifetime in your DoubleTick account.

## Trigger Conditions

The event is emitted when:

* An inbound customer message is received.
* The customer has no prior message history with the WABA.
* The customer has not previously triggered a `NEW_LEAD` event.

## Deduplication Behavior

`NEW_LEAD` is guaranteed to fire **at most once per customer for their lifetime** in your account. DoubleTick prevents duplicate events caused by retries, delayed processing, or customers with existing conversation history.

> `NEW_LEAD` is a lifetime event. It is not a first-message-per-day, first-message-per-session, or first-message-per-conversation event.

## Sample Payload

### Standard First Message

```json
{
  "customerName": "Rohan Mehta",
  "customerPhone": "+919876543210",
  "from": "+919876543210",
  "to": "+919000000000",
  "isCTWA": false,
  "wabaNumber": "+919000000000",
  "dtMessageId": "msg_01HX9abc123def456",
  "dtCustomerId": "customer_pvgpZUh3wg",
  "customerId": "customer_pvgpZUh3wg",
  "newLead": true
}
```

### Click-to-WhatsApp Ad Lead

```json
{
  "customerName": "Rohan Mehta",
  "customerPhone": "+919876543210",
  "from": "+919876543210",
  "to": "+919000000000",
  "isCTWA": true,
  "wabaNumber": "+919000000000",
  "dtMessageId": "msg_01HX9abc123def456",
  "dtCustomerId": "customer_pvgpZUh3wg",
  "customerId": "customer_pvgpZUh3wg",
  "newLead": true,
  "referral": {
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

| Field           | Type    | Required | Description                                                           |
| --------------- | ------- | -------- | --------------------------------------------------------------------- |
| `customerName`  | string  | Yes      | Display name of the customer                                          |
| `customerPhone` | string  | Yes      | Customer's WhatsApp phone number                                      |
| `from`          | string  | Yes      | Same value as `customerPhone`                                         |
| `to`            | string  | Yes      | WABA phone number that received the message                           |
| `wabaNumber`    | string  | Yes      | Same value as `to`                                                    |
| `isCTWA`        | boolean | Yes      | Indicates whether the customer originated from a Click-to-WhatsApp Ad |
| `dtMessageId`   | string  | No       | Internal DoubleTick message identifier                                |
| `dtCustomerId`  | string  | Yes      | Unique DoubleTick customer identifier                                 |
| `customerId`    | string  | Yes      | Same value as `dtCustomerId`                                          |
| `newLead`       | boolean | Yes      | Always `true`                                                         |
| `referral`      | object  | No       | Ad attribution information. Present only when `isCTWA` is `true`      |

## Referral Object

The `referral` object contains Click-to-WhatsApp Ad attribution information and is included only when the customer arrives through a Meta ad.

### Common Fields

| Field         | Type   | Description                                   |
| ------------- | ------ | --------------------------------------------- |
| `source_url`  | string | URL of the ad or post clicked by the customer |
| `source_type` | string | Origin type. Typically `ad` or `post`         |
| `source_id`   | string | Meta identifier for the ad or post            |
| `headline`    | string | Ad headline                                   |
| `body`        | string | Ad body text                                  |
| `ctwa_clid`   | string | Click-to-WhatsApp tracking identifier         |

### Cloud API Media Fields

| Field           | Type   | Condition                             |
| --------------- | ------ | ------------------------------------- |
| `media_type`    | string | Present when media attribution exists |
| `image_url`     | string | Present when `media_type` is `image`  |
| `video_url`     | string | Present when `media_type` is `video`  |
| `thumbnail_url` | string | Present when `media_type` is `video`  |

### Gupshup / 360dialog Media Fields

| Field   | Type   | Condition                         |
| ------- | ------ | --------------------------------- |
| `image` | object | Present when the ad used an image |
| `video` | object | Present when the ad used a video  |

Example:

```json
{ "image": { "id": "123456789" } }
```

## Conditional Fields — NEW\_LEAD

| Field           | Condition                                   |
| --------------- | ------------------------------------------- |
| `referral`      | Present only when `isCTWA` is `true`        |
| `image_url`     | Present only for image-based ad attribution |
| `video_url`     | Present only for video-based ad attribution |
| `thumbnail_url` | Present only for video-based ad attribution |

## Notes — NEW\_LEAD

* `from` and `customerPhone` always contain the same value.
* `to` and `wabaNumber` always contain the same value.
* `customerId` and `dtCustomerId` always contain the same value.
* `newLead` is a fixed flag and is always `true`.
* The exact structure of the `referral` object depends on the WhatsApp provider used by the integration.
* This event is emitted only for individual customer conversations and is not generated for WhatsApp group messages.

## Edge Cases — NEW\_LEAD

### Customer Re-registers WhatsApp

If a customer deletes and recreates their WhatsApp account using the same phone number, `NEW_LEAD` will not fire again because the customer has already been recorded as a lead.

### CTWA Leads

When a customer arrives through a Click-to-WhatsApp Ad, both `NEW_LEAD` and [`CALL_TO_WHATSAPP_MESSAGE_RECEIVED`](https://docs.doubletick.io/reference/messaging-webhooks-1#call_to_whatsapp_message_received) may be generated from the same inbound message. `NEW_LEAD` identifies a newly acquired customer; `CALL_TO_WHATSAPP_MESSAGE_RECEIVED` provides advertising attribution details.

## Best Practices — NEW\_LEAD

* Use `dtCustomerId` as the canonical customer identifier.
* Treat `NEW_LEAD` as a lifetime acquisition event rather than a messaging event.
* Store lead attribution data immediately if your reporting or CRM workflows depend on it.

***

# Widget Leads

Widget lead webhooks support a two-step acquisition flow:

1. A customer submits a form through a DoubleTick widget.
2. DoubleTick stores the submitted information and emits `WIDGET_LEAD_RECEIVED`.
3. The customer later sends a WhatsApp message containing their inquiry code.
4. DoubleTick verifies the submission and emits `VERIFIED_WIDGET_LEAD_RECEIVED`.

This allows you to capture anonymous leads before a WhatsApp conversation begins and later connect those submissions to an identified customer profile.

## Correlation Between the Two Events

Neither `WIDGET_LEAD_RECEIVED` nor `VERIFIED_WIDGET_LEAD_RECEIVED` includes a shared submission identifier. There is no `submissionId`, `inquiryCode`, or `widgetId` in either payload.

**To correlate an anonymous submission with its verified counterpart:** match on unique values inside the `data[]` array — for example, a submitted email address or phone number that appears in both payloads. For this to work reliably, your widget must collect at least one field that is unique per submitter.

**For multi-widget deployments:** neither payload includes a widget or form identifier, so you cannot determine which widget generated a lead from the payload alone. The recommended workaround is to add a hidden field to each widget (e.g. `"field": "widget_source"`, `"value": "homepage_hero"`) — this will appear in `data[]` on both events and allows you to identify the originating widget.

## Widget Lead Flow

```text
Customer submits widget form
           │
           ▼
WIDGET_LEAD_RECEIVED
(anonymous submission)
           │
           ▼
Customer sends WhatsApp message
containing inquiry code
           │
           ▼
VERIFIED_WIDGET_LEAD_RECEIVED
(WhatsApp identity confirmed)
```

## Shared Fields — Widget Leads

The following structure is shared by both widget lead events.

| Field          | Type   | Required | Description                              |
| -------------- | ------ | -------- | ---------------------------------------- |
| `data`         | array  | Yes      | Array of form field responses            |
| `data[].field` | string | Yes      | Form field name configured in the widget |
| `data[].value` | string | Yes      | Submitted field value                    |

### Example

```json
{
  "data": [
    { "field": "name", "value": "Rohan Mehta" },
    { "field": "email", "value": "rohan@example.com" }
  ]
}
```

***

## Lead Received from Widget

**Event type:** `WIDGET_LEAD_RECEIVED`

Fires when a customer submits a DoubleTick widget form.

This event represents an anonymous lead submission. At this stage, DoubleTick has collected form information but has not yet linked the submission to a WhatsApp contact.

### Trigger Conditions

The event is emitted when:

* A customer opens a DoubleTick widget.
* The customer completes the form.
* The form submission is successfully processed.

A separate event is generated for every submission.

### Sample Payload

```json
{
  "data": [
    { "field": "name", "value": "Rohan Mehta" },
    { "field": "email", "value": "rohan@example.com" },
    { "field": "phone", "value": "+919876543210" },
    { "field": "message", "value": "I'm interested in the Enterprise plan" },
    { "field": "company", "value": "Acme Corp" }
  ]
}
```

### Field Reference

| Field          | Type   | Required | Description                            |
| -------------- | ------ | -------- | -------------------------------------- |
| `data`         | array  | Yes      | Collection of submitted form responses |
| `data[].field` | string | Yes      | Field name configured in the widget    |
| `data[].value` | string | Yes      | User-provided value                    |

### Conditional Fields

None. The payload structure is fixed. Only the contents of the `data` array vary based on widget configuration.

### Notes

* This event does not contain a customer ID.
* This event does not contain a WhatsApp phone number.
* This event does not contain a WABA number.
* All values inside `data[].value` are delivered as strings.
* The order of entries typically reflects the form layout.
* Multiple submissions by the same user generate multiple webhook events.

***

## Verified Lead Received from Widget

**Event type:** `VERIFIED_WIDGET_LEAD_RECEIVED`

Fires when a previously submitted widget lead is successfully matched to a WhatsApp customer.

Verification occurs when a customer sends a WhatsApp message containing the inquiry code generated during widget submission. Once the match succeeds, DoubleTick links the form submission to a customer record and emits this event.

### Trigger Conditions

The event is emitted when:

* A widget submission already exists.
* The customer sends a WhatsApp message.
* The message contains a valid inquiry code.
* DoubleTick successfully matches the inquiry code to the stored submission.

### Sample Payload

```json
{
  "whatsappNumber": "+919876543210",
  "verified": true,
  "data": [
    { "field": "name", "value": "Rohan Mehta" },
    { "field": "email", "value": "rohan@example.com" },
    { "field": "preferred_product", "value": "Enterprise Plan" },
    { "field": "company", "value": "Acme Corp" }
  ],
  "dtCustomerId": "customer_pvgpZUh3wg"
}
```

### Field Reference

| Field            | Type    | Required | Description                                                              |
| ---------------- | ------- | -------- | ------------------------------------------------------------------------ |
| `whatsappNumber` | string  | Yes      | Customer's WhatsApp phone number associated with the verified submission |
| `verified`       | boolean | Yes      | Always `true`                                                            |
| `data`           | array   | Yes      | Original widget form submission data                                     |
| `data[].field`   | string  | Yes      | Form field name                                                          |
| `data[].value`   | string  | Yes      | Value submitted during form completion                                   |
| `dtCustomerId`   | string  | No       | DoubleTick customer identifier                                           |

### Conditional Fields

| Field          | Condition                                                            |
| -------------- | -------------------------------------------------------------------- |
| `dtCustomerId` | Present when the customer record can be resolved during verification |

### Notes

* `verified` is always `true`.
* There is no failed-verification variant of this event.
* The `data` array contains the original widget submission data and not any subsequently updated customer information.
* This event can occur on the same inbound WhatsApp message that generates other webhook events.
* The widget submission data is delivered exactly as captured during form submission.
* Verification links an anonymous widget lead to a known WhatsApp customer.

### Event Relationships

The same inbound message may generate multiple webhook events:

```text
Customer sends WhatsApp message
containing inquiry code

├── MESSAGE_RECEIVED
├── VERIFIED_WIDGET_LEAD_RECEIVED
└── NEW_LEAD (if first-ever message)
```

* `MESSAGE_RECEIVED` (Messaging Webhooks) tracks the inbound message.
* `VERIFIED_WIDGET_LEAD_RECEIVED` links widget data to a customer identity.
* `NEW_LEAD` (above) indicates the customer entered the pipeline for the first time.

***

## Payload Differences — Widget Leads

| Field                    | WIDGET\_LEAD\_RECEIVED | VERIFIED\_WIDGET\_LEAD\_RECEIVED |
| ------------------------ | ---------------------- | -------------------------------- |
| `whatsappNumber`         | No                     | Yes                              |
| `verified`               | No                     | Yes (`true`)                     |
| `dtCustomerId`           | No                     | Conditional                      |
| `data`                   | Yes                    | Yes                              |
| `data[].field`           | Yes                    | Yes                              |
| `data[].value`           | Yes                    | Yes                              |
| Anonymous submission     | Yes                    | No                               |
| WhatsApp identity linked | No                     | Yes                              |

***

## Best Practices — Widget Leads

### Treat Widget Leads as Anonymous Until Verified

Do not assume a widget submission belongs to a known customer until a corresponding `VERIFIED_WIDGET_LEAD_RECEIVED` event is received.

### Use dtCustomerId for Customer Linking

When available, use `dtCustomerId` as the canonical identifier for associating widget submissions with customer records.

### Do Not Depend on Field Names

`data[].field` values are configured by workspace administrators and are not standardized. Avoid hardcoding assumptions about field names across organizations.

### Treat Form Values as Strings

All values in `data[].value` are delivered as strings regardless of the original field type. Perform any required parsing or validation within your integration.

### Preserve Original Submission Data

Since verification payloads contain a snapshot of the original form submission, storing the data at receipt time can help maintain an audit trail and support attribution workflows.