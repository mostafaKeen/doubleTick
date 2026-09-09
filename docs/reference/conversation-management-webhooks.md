Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Conversation Management Webhooks

Track conversation lifecycle events such as when conversations are opened, closed, assigned, or unassigned. These webhooks help you synchronize ownership, automate workflows, and maintain an up-to-date view of customer conversations within your external systems.

### Available Events

| Event                    | Description                                                                           |
| ------------------------ | ------------------------------------------------------------------------------------- |
| `CHAT_ASSIGNED_TO_AGENT` | Triggered when a conversation is assigned to an agent.                                |
| `CHAT_UNASSIGNED`        | Triggered when a conversation is unassigned from an agent.                            |
| `CONVERSATION_OPENED`    | Triggered when a customer starts a new conversation or reopens a closed conversation. |
| `CLOSE_CONVERSATION`     | Triggered when a conversation is marked as done or closed.                            |

***

# Chat Assigned to Agent

**Event type:** `CHAT_ASSIGNED_TO_AGENT`

Triggered whenever a conversation is assigned to an agent, either manually or through an automated workflow.

### Sample Payload

```json
{
  "chatId": "chat_abc123",
  "assignedUserId": "user_xyz789",
  "assignedUserName": "John Doe",
  "assignedUserPhone": "+919876543210",
  "assignedUserEmail": "john@company.com",
  "assignedUserOrgRole": "Sales Manager",
  "assignedUserWabaRole": "Agent",
  "assignedUserWabaNumber": "+919000000000",
  "customerPhone": "+919111222333",
  "dtCustomerId": "customer_pvgpZUh3wg",
  "message": {
    "text": "John Doe assigned conversation to self"
  }
}
```

### Payload Fields

| Field                    | Type   | Required | Description                                           |
| :----------------------- | :----- | :------- | :---------------------------------------------------- |
| `chatId`                 | string | Yes      | Unique identifier of the conversation                 |
| `assignedUserId`         | string | Yes      | Unique identifier of the assigned agent               |
| `assignedUserName`       | string | Yes      | Display name of the assigned agent                    |
| `assignedUserPhone`      | string | Yes      | Phone number of the assigned agent                    |
| `assignedUserEmail`      | string | Yes      | Email address of the assigned agent                   |
| `assignedUserOrgRole`    | string | No       | Agent role within the organization                    |
| `assignedUserWabaRole`   | string | No       | Agent role associated with a specific WhatsApp number |
| `assignedUserWabaNumber` | string | No       | WhatsApp number associated with the assigned role     |
| `customerPhone`          | string | Yes      | Customer phone number                                 |
| `dtCustomerId`           | string | Yes      | Unique DoubleTick customer identifier                 |
| `message.text`           | string | Yes      | Human-readable description of the assignment action   |

> **Privacy notice:** `assignedUserPhone` and `assignedUserEmail` contain personal data belonging to your agents. Ensure your webhook consumer handles this data in accordance with applicable data protection regulations (e.g. GDPR, DPDP). Avoid logging these fields in plain text or exposing them to unauthorized systems.

***

# Chat Unassigned

**Event type:** `CHAT_UNASSIGNED`

Triggered when a conversation is unassigned from an agent.

### Sample Payload

```json
{
  "chatId": "chat_abc123",
  "customerPhone": "+919111222333",
  "wabaNumber": "+919000000000",
  "dtCustomerId": "customer_pvgpZUh3wg"
}
```

### Payload Fields

| Field           | Type   | Required | Description                                                     |
| :-------------- | :----- | :------- | :-------------------------------------------------------------- |
| `chatId`        | string | Yes      | Unique identifier of the conversation                           |
| `customerPhone` | string | No       | Customer phone number                                           |
| `wabaNumber`    | string | No       | WhatsApp Business phone number associated with the conversation |
| `dtCustomerId`  | string | Yes      | Unique DoubleTick customer identifier                           |

### Notes

* This webhook contains only conversation-level information.
* Agent details are not included in this event.

***

# Conversation Opened

**Event type:** `CONVERSATION_OPENED`

Triggered when a customer starts a new conversation or reopens an existing closed conversation.

### Sample Payload

```json
{
  "customerName": "Rohan Mehta",
  "customerPhone": "+919111222333",
  "from": "+919111222333",
  "to": "+919000000000",
  "dtCustomerId": "customer_pvgpZUh3wg",
  "conversationOpened": true
}
```

### Payload Fields

| Field              | Type    | Description                                      |
| ------------------ | ------- | ------------------------------------------------ |
| customerName       | string  | Customer name.                                   |
| customerPhone      | string  | Customer phone number.                           |
| from               | string  | Source phone number.                             |
| to                 | string  | Destination WhatsApp Business phone number.      |
| dtCustomerId       | string  | Unique DoubleTick customer identifier.           |
| conversationOpened | boolean | Indicates that the conversation has been opened. |

### Notes

* `conversationOpened` is always `true`.
* `from` and `customerPhone` contain the same value.
* To prevent duplicate notifications, this event may be delivered at most once within a 120-second window for the same customer and channel.

***

# Close a Conversation

**Event type:** `CLOSE_CONVERSATION`

Triggered when a conversation is marked as done or closed in DoubleTick.

### Sample Payload

```json
{
  "customerName": "Rohan Mehta",
  "customerPhone": "+919111222333",
  "from": "+919111222333",
  "to": "+919000000000",
  "dtCustomerId": "customer_pvgpZUh3wg",
  "closeConversation": true
}
```

### Payload Fields

| Field             | Type    | Description                                      |
| ----------------- | ------- | ------------------------------------------------ |
| customerName      | string  | Customer name.                                   |
| customerPhone     | string  | Customer phone number.                           |
| from              | string  | Source phone number.                             |
| to                | string  | Destination WhatsApp Business phone number.      |
| dtCustomerId      | string  | Unique DoubleTick customer identifier.           |
| closeConversation | boolean | Indicates that the conversation has been closed. |

### Notes

* `closeConversation` is always `true`.
* `from` and `customerPhone` contain the same value.
* Agent information is not included in this webhook payload. If you need to identify who closed the conversation, look for the most recent `CHAT_ASSIGNED_TO_AGENT` event for the same `dtCustomerId` — the assigned agent at that point is the likely closer. For a definitive audit trail, use DoubleTick's conversation activity log.