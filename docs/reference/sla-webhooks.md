Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# SLA Webhooks

Receive real-time notifications when a conversation's response-time SLA escalates to the next configured level.

SLA Webhooks notify you when a breached conversation escalates through your organization's configured escalation levels — letting your integration respond to persistently unanswered conversations.

## Events Covered

| Event                     | Description                                                                          |
| ------------------------- | ------------------------------------------------------------------------------------ |
| `ON_SLA_ESCALATED_PUBLIC` | Fires when a breached conversation escalates to the next configured escalation level |

## How SLA Escalation Works

Each organization can configure an SLA policy with a response-time threshold and, optionally, one or more ordered escalation levels with their own delays. When a conversation's SLA timer elapses with no agent reply and then continues through a configured escalation delay still without a reply, DoubleTick fires this event.

```text
SLA response-time threshold elapses (no agent reply)
            │
            ▼
 (if escalation levels are configured)
            │
 Escalation level 1 delay elapses, still no reply
            │
            └── ON_SLA_ESCALATED_PUBLIC (escalationLevel: 1)
            │
 Escalation level 2 delay elapses, still no reply
            │
            └── ON_SLA_ESCALATED_PUBLIC (escalationLevel: 2)
            │
           ...continues for each configured level
```

> If an agent replies before an escalation delay elapses, DoubleTick re-checks for a reply at delivery time and suppresses the notification — no webhook is sent for that level.

***

# SLA Escalated

**Event type:** `ON_SLA_ESCALATED_PUBLIC`

Fires when a breached conversation remains unanswered through an additional configured escalation delay and advances to the next escalation level.

Carries human-readable, business-level fields — escalation level number, SLA name, response time, and customer contact information — making it suitable for notifications, dashboards, or reporting use cases where you want SLA context without resolving internal IDs.

> This event is not currently delivered for Instagram conversations.

## Trigger Conditions

The event is emitted when:

* A conversation has an active SLA policy with one or more escalation levels configured.
* The SLA response-time threshold has elapsed with no agent reply.
* An escalation level's delay has also elapsed with the conversation still unanswered.

It is **not** emitted if:

* An agent replies before the escalation delay elapses.
* The SLA policy has no escalation levels configured.

Multiple escalation levels fire as separate, sequential events — one per configured level, each with its own `escalationLevel` value.

## Sample Payload

```json
{
  "customerPhone": "+919876543210",
  "wabaNumber": "+919000000000",
  "slaName": "Standard Response SLA",
  "responseTime": 900,
  "escalationLevel": 1,
  "eventType": "ON_SLA_ESCALATED_PUBLIC"
}
```

## Field Reference

| Field             | Type   | Required | Description                                             |
| ----------------- | ------ | -------- | ------------------------------------------------------- |
| `escalationLevel` | number | Yes      | The escalation level that was triggered (1, 2, 3, ...)  |
| `customerPhone`   | string | Yes      | Customer's phone number                                 |
| `wabaNumber`      | string | Yes      | WhatsApp Business number the conversation belongs to    |
| `slaName`         | string | No       | Display name of the SLA policy configured in DoubleTick |
| `responseTime`    | number | No       | The configured response-time threshold, in seconds      |
| `eventType`       | string | Yes      | Always `"ON_SLA_ESCALATED_PUBLIC"`                      |

## Conditional Fields

| Field          | Condition                                                           |
| -------------- | ------------------------------------------------------------------- |
| `slaName`      | Omitted if the SLA policy has no configured name                    |
| `responseTime` | Omitted if the SLA policy has no configured response-time threshold |

## Notes

* This payload contains no conversation identifier (`chatId` or similar). To look up the escalated conversation, match on `customerPhone` + `wabaNumber` in your system, or use `escalationLevel` alongside those fields to identify the specific escalation stage. For a direct link, use DoubleTick's conversation search API with the customer phone number.
* An escalation may also trigger a reassignment of the conversation to a different agent, depending on your SLA policy configuration. This payload does not include reassignment details — use `CHAT_ASSIGNED_TO_AGENT` ([Conversation Management Webhooks](https://docs.doubletick.io/reference/conversation-management-webhooks)) to track those.

## Edge Cases

### No Escalation Levels Configured

If your SLA policy has no escalation levels configured, this event will never fire for that policy.

## Best Practices

* Use `escalationLevel` to distinguish between escalation stages — level 1 is the first escalation, level 2 is the next, and so on.
* Combine with `CHAT_ASSIGNED_TO_AGENT` (Conversation Management Webhooks) if you need to know who an escalated conversation was reassigned to.
* Treat each event as a discrete escalation step, not a cumulative status report.
* `escalationLevel` is the only field guaranteed to be present — always check for the existence of other fields before using them.