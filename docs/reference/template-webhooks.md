Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Template Webhooks

Receive real-time notifications whenever the approval status of a WhatsApp message template changes.

## Events Covered

| Event             | Description                                                          |
| ----------------- | -------------------------------------------------------------------- |
| `TEMPLATE_UPDATE` | Fires whenever a WhatsApp message template's approval status changes |

***

# Template Updated

**Event type:** `TEMPLATE_UPDATE`

Fires whenever a WhatsApp message template transitions from one approval status to another — for example, from pending review to approved, or from approved to rejected.

This event only reports **status** transitions. Changes to a template's quality rating or category are not delivered through this webhook.

## Trigger Conditions

The event is emitted when:

* A template's approval status changes to a different value than it was before.

It is **not** emitted when:

* The reported status is the same as the previous status (no-op changes are suppressed).
* Only a template's quality rating or category changes, with no status change.

## Sample Payload

### Status Change

```json
{
  "wabaPhoneNumber": "+919000000000",
  "templateName": "order_confirmation",
  "templateLanguage": "en",
  "newStatus": "APPROVED",
  "oldStatus": "PENDING",
  "rejectedReason": "",
  "agentName": "James Smith",
  "agentNumber": "+919111222333",
  "event": "TEMPLATE_STATUS_UPDATE"
}
```

### Rejection

```json
{
  "wabaPhoneNumber": "+919000000000",
  "templateName": "promo_offer",
  "templateLanguage": "en",
  "newStatus": "REJECTED",
  "oldStatus": "PENDING",
  "rejectedReason": "The template was rejected for promotional content that does not match its declared category.",
  "agentName": "James Smith",
  "agentNumber": "+919111222333",
  "event": "TEMPLATE_STATUS_UPDATE"
}
```

## Field Reference

| Field              | Type           | Required | Description                                                                                                                                                                                     |
| ------------------ | -------------- | -------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `wabaPhoneNumber`  | string         | Yes      | WhatsApp Business number the template belongs to                                                                                                                                                |
| `templateName`     | string         | Yes      | Name of the template                                                                                                                                                                            |
| `templateLanguage` | string         | Yes      | Language code of the template                                                                                                                                                                   |
| `newStatus`        | string         | Yes      | The template's status after this change. See status values below.                                                                                                                               |
| `oldStatus`        | string \| null | Yes      | The template's status before this change                                                                                                                                                        |
| `rejectedReason`   | string         | Yes      | Rejection reason text. Empty for non-rejection status changes.                                                                                                                                  |
| `agentName`        | string         | No       | Display name of the DoubleTick agent who **submitted** the template for approval. This is not the reviewer — Meta determines approval status independently                                      |
| `agentNumber`      | string         | No       | Phone number of the DoubleTick agent who submitted the template for approval                                                                                                                    |
| `event`            | string         | Yes      | Subscribe using **TEMPLATE\_UPDATE** in DoubleTick settings. In delivered payloads, the **event** field will be ***"TEMPLATE\_STATUS\_UPDATE"***. These are different names for the same event. |

## Status Values

| Value      | Description                                        |
| ---------- | -------------------------------------------------- |
| `APPROVED` | The template has been approved and is ready to use |
| `REJECTED` | The template was rejected during review            |
| `PENDING`  | The template is awaiting review                    |
| `PAUSED`   | The template has been temporarily paused           |

## Conditional Fields

| Field            | Condition                                                                        |
| ---------------- | -------------------------------------------------------------------------------- |
| `agentName`      | Omitted if the creating agent could not be resolved                              |
| `agentNumber`    | Omitted if the creating agent could not be resolved                              |
| `oldStatus`      | May be `null` if there was no previous recorded status                           |
| `rejectedReason` | Present only when `newStatus` is `REJECTED`. Empty string for other transitions. |

## Notes

* `rejectedReason` is passed through as raw text from your WhatsApp provider and is not normalized into a fixed set of reason codes. Treat it as free text, not an enum.
* This event reports status changes only — quality rating changes and category changes on a template do not trigger `TEMPLATE_UPDATE`.
* If the same status is reported twice in a row with no actual change, no webhook is sent.

## Edge Cases

### Duplicate Status Reports

If your WhatsApp provider reports the same status more than once (for example, due to a retried delivery), only the first one results in a webhook — repeated reports of an unchanged status are suppressed.

### Rapid Status Changes

If a template changes status multiple times in quick succession (for example, `PENDING` → `APPROVED` → `PAUSED`), each transition is delivered as its own separate webhook event, in order.

## Best Practices

* Use `templateName` and `templateLanguage` together to identify a specific template — template names are not unique across languages.
* Don't parse or pattern-match `rejectedReason` — it's intended for display to a human reviewer, not for automated branching logic.
* Track `oldStatus` alongside `newStatus` if you need to detect specific transitions (e.g. only act when a template moves from `PENDING` to `REJECTED`).