Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Agent Presence Webhooks

Receive real-time notifications when an agent changes their availability status in DoubleTick, with details of which customers' conversations are affected.

Agent Presence Webhooks notify your system when an agent transitions between online and offline states — but only when that agent has active assigned conversations. This allows you to react in real time to agent availability changes that directly affect open customer interactions.

## Events Covered

| Event                           | Description                                                                                   |
| ------------------------------- | --------------------------------------------------------------------------------------------- |
| `ASSIGNED_AGENT_STATUS_CHANGED` | Fires when an agent with at least one assigned conversation changes their availability status |

***

# Assigned Agent Online/Offline

**Event type:** `ASSIGNED_AGENT_STATUS_CHANGED`

Fires when an agent who has at least one assigned conversation changes their availability to `ONLINE` or `OFFLINE`.

DoubleTick groups all of the agent's assigned conversations by WhatsApp Business Account and sends one webhook per integration, with the full list of affected customer phone numbers included in that single payload.

## Trigger Conditions

The event is emitted when:

* An agent changes their availability status (from `ONLINE` to `OFFLINE`, or vice versa).
* The agent has at least one assigned conversation with an active WhatsApp integration.
* The Agent Availability feature is enabled for your organization.

It is **not** emitted when:

* The agent's status changes but no conversations are currently assigned to them.
* The new status is the same as the previous status (no-op changes are suppressed).

## Delivery Semantics

One webhook is sent **per WhatsApp Business Account** where the agent has assigned conversations. If an agent has assigned conversations across multiple WABAs, each WABA generates its own webhook with its own `customerPhones` list.

```text
Agent changes status to OFFLINE

  ├── WABA: +919000000001
  │   └── ASSIGNED_AGENT_STATUS_CHANGED
  │       customerPhones: ["+919876543210", "+919123456789"]
  │
  └── WABA: +919000000002
      └── ASSIGNED_AGENT_STATUS_CHANGED
          customerPhones: ["+918888888888"]
```

## Sample Payload

### Agent Goes Offline

```json
{
  "event": "ASSIGNED_AGENT_STATUS_CHANGED",
  "assignedUserName": "James Smith",
  "assignedUserPhone": "+919111222333",
  "status": "OFFLINE",
  "previousStatus": "ONLINE",
  "wabaNumber": "+919000000000",
  "customerPhones": [
    "+919876543210",
    "+919123456789",
    "+918765432100"
  ]
}
```

### Agent Comes Online (No Prior Status)

```json
{
  "event": "ASSIGNED_AGENT_STATUS_CHANGED",
  "assignedUserName": "James Smith",
  "assignedUserPhone": "+919111222333",
  "status": "ONLINE",
  "previousStatus": null,
  "wabaNumber": "+919000000000",
  "customerPhones": [
    "+919876543210"
  ]
}
```

## Field Reference

| Field               | Type           | Required | Description                                                                           |
| ------------------- | -------------- | -------- | ------------------------------------------------------------------------------------- |
| `event`             | string         | Yes      | Always `"ASSIGNED_AGENT_STATUS_CHANGED"`                                              |
| `status`            | string         | Yes      | The agent's new availability status. See values below.                                |
| `previousStatus`    | string \| null | Yes      | The agent's prior status before this change. `null` if no prior status was set.       |
| `wabaNumber`        | string         | Yes      | The WhatsApp Business Account number for the integration                              |
| `customerPhones`    | string\[]      | Yes      | Phone numbers of all customers with conversations assigned to this agent in this WABA |
| `assignedUserName`  | string \| null | No       | Display name of the agent whose status changed                                        |
| `assignedUserPhone` | string \| null | No       | Phone number of the agent whose status changed                                        |

> **Privacy notice:** `assignedUserPhone` is personal data belonging to your agent. `customerPhones` is an array of customer phone numbers (PII). Both fields are delivered to all subscribed webhook endpoints. Ensure your consumer handles this data in accordance with applicable data protection regulations (e.g. GDPR, DPDP) — restrict access, avoid plain-text logging, and do not forward to unauthorized third parties.

## Status Values

| Value     | Description                      |
| --------- | -------------------------------- |
| `ONLINE`  | The agent is now available       |
| `OFFLINE` | The agent is no longer available |

## Conditional Fields

| Field               | Condition                                                                   |
| ------------------- | --------------------------------------------------------------------------- |
| `assignedUserName`  | May be `null` if the agent's profile could not be resolved at delivery time |
| `assignedUserPhone` | May be `null` if the agent's profile could not be resolved at delivery time |
| `previousStatus`    | `null` when the agent is setting their status for the first time            |

## Notes

* `customerPhones` may be an empty array if assigned conversations were resolved or unassigned between the status change and webhook delivery.
* This event fires per WABA, not per conversation — a single status change can generate multiple webhooks if the agent has conversations across multiple WhatsApp Business Accounts.
* `customerPhones` contains all customers assigned to the agent within that WABA, not just those who messaged most recently.

## Edge Cases

### Agent Has No Assigned Conversations

If the agent has no assigned conversations at the time of the status change, no webhook is sent — even if the agent's status changes to `ONLINE` or `OFFLINE`.

### Status Unchanged

If the system detects that the agent's new status matches their previous status, the webhook is suppressed.

## Best Practices

* Use `customerPhones` to identify which customer conversations may need attention due to the agent's unavailability.
* Check `status` to determine whether the agent is going offline (and may need coverage) or coming online (and can resume conversations).
* If you receive multiple webhooks for the same status change, they represent different WABAs — use `wabaNumber` to distinguish them.
* Combine with `CHAT_ASSIGNED_TO_AGENT` ([Conversation Management Webhooks](https://docs.doubletick.io/reference/conversation-management-webhooks)) to build a complete picture of which agents are handling which conversations.