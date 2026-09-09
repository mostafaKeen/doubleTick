Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# WhatsApp Group Webhooks

Receive real-time notifications for activity inside WhatsApp Groups managed through DoubleTick — including group creation, membership changes, and inbound group messages.

## Events Covered

| Event                       | Description                                                             |
| --------------------------- | ----------------------------------------------------------------------- |
| `GROUP_CREATED`             | A new WhatsApp Group was created through DoubleTick                     |
| `GROUP_PARTICIPANT_JOINED`  | A participant joined one of your WhatsApp Groups                        |
| `GROUP_PARTICIPANT_LEFT`    | A participant voluntarily left one of your WhatsApp Groups              |
| `GROUP_PARTICIPANT_REMOVED` | A participant was removed from one of your WhatsApp Groups by an admin  |
| `WA_GROUP_MESSAGE_RECEIVED` | A message was sent inside a WhatsApp Group recognized by your workspace |

> All five events are currently available only for **WhatsApp Cloud API** integrations. Gupshup and 360dialog integrations do not support WhatsApp Group events.

***

# Group Lifecycle Events

Group lifecycle events fire when a group is created through DoubleTick. Membership events fire when participants join or leave via the group.

## Common Fields — Lifecycle and Membership Events

All four lifecycle/membership events share the following base fields:

| Field           | Type           | Required | Description                                                                                               |
| --------------- | -------------- | -------- | --------------------------------------------------------------------------------------------------------- |
| `event`         | string         | Yes      | The event type. See each section for the fixed value.                                                     |
| `orgId`         | string         | Yes      | Opaque routing identifier for your DoubleTick organization.                                               |
| `integrationId` | string         | Yes      | Opaque routing identifier for the WABA integration. Use `groupId` or `callId` for cross-event correlation |
| `groupId`       | string \| null | Yes      | DoubleTick identifier for the group. `null` if not yet resolvable.                                        |
| `groupName`     | string \| null | Yes      | The group's display name (subject). `null` if not available at delivery time                              |

> `groupId` is a DoubleTick-assigned identifier for the group. It may be `null` for brief moments during group creation before the identifier is confirmed.

***

## WhatsApp Group Created

**Event type:** `GROUP_CREATED`

Fires when a WhatsApp Group is successfully created through your DoubleTick integration. This event is emitted after Meta confirms the group was created successfully.

### Trigger Conditions

The event is emitted when:

* A WhatsApp Group creation request completes successfully.
* Meta Cloud API confirms the group is active and the external group ID is available.

It is **not** emitted when:

* Group creation fails or returns an error.
* A group is deleted.

### Sample Payload

```json
{
  "event": "GROUP_CREATED",
  "orgId": "org_abc123",
  "integrationId": "int_xyz789",
  "groupId": "grp_01HX9abc123",
  "groupName": "Q1 Promotions Team",
  "inviteLink": "https://chat.whatsapp.com/abc123xyz",
  "groupCreatedAt": "2026-01-15T09:30:00.000Z"
}
```

### Field Reference

All [common fields](#common-fields--lifecycle-and-membership-events) apply. The following fields are specific to this event:

| Field            | Type           | Required | Description                                                                                                                                                                                                    |
| ---------------- | -------------- | -------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `inviteLink`     | string \| null | Yes      | The group's invite link. `null` if not generated yet. **Handle like an access credential** — anyone with this URL can join the group. Avoid plain-text logging and restrict access to authorized systems only. |
| `groupCreatedAt` | string         | Yes      | ISO 8601 timestamp of when the group was created                                                                                                                                                               |

***

## WhatsApp Group Participant Joined

**Event type:** `GROUP_PARTICIPANT_JOINED`

Fires when a participant joins one of your WhatsApp Groups — whether added by an admin or by following an invite link.

### Sample Payload

```json
{
  "event": "GROUP_PARTICIPANT_JOINED",
  "orgId": "org_abc123",
  "integrationId": "int_xyz789",
  "groupId": "grp_01HX9abc123",
  "groupName": "Q1 Promotions Team",
  "customerPhone": "+919876543210",
  "participantJoinedAt": "2026-01-15T10:00:00.000Z"
}
```

### Field Reference

All [common fields](#common-fields--lifecycle-and-membership-events) apply. The following fields are specific to this event:

| Field                 | Type   | Required | Description                                         |
| --------------------- | ------ | -------- | --------------------------------------------------- |
| `customerPhone`       | string | Yes      | WhatsApp phone number of the participant who joined |
| `participantJoinedAt` | string | Yes      | ISO 8601 timestamp of when the participant joined   |

***

## WhatsApp Group Participant Left

**Event type:** `GROUP_PARTICIPANT_LEFT`

Fires when a participant **voluntarily leaves** one of your WhatsApp Groups. Use `GROUP_PARTICIPANT_REMOVED` to detect admin-initiated removals.

### Trigger Conditions

The event fires when:

* A participant exits the group themselves (not removed by an admin).

It is **not** emitted when:

* An admin removes the participant — that fires `GROUP_PARTICIPANT_REMOVED` instead.

### Sample Payload

```json
{
  "event": "GROUP_PARTICIPANT_LEFT",
  "orgId": "org_abc123",
  "integrationId": "int_xyz789",
  "groupId": "grp_01HX9abc123",
  "groupName": "Q1 Promotions Team",
  "customerPhone": "+919876543210",
  "participantLeftAt": "2026-01-15T11:15:00.000Z"
}
```

### Field Reference

All [common fields](#common-fields--lifecycle-and-membership-events) apply. The following fields are specific to this event:

| Field               | Type   | Required | Description                                       |
| ------------------- | ------ | -------- | ------------------------------------------------- |
| `customerPhone`     | string | Yes      | WhatsApp phone number of the participant who left |
| `participantLeftAt` | string | Yes      | ISO 8601 timestamp of when the participant left   |

***

## WhatsApp Group Participant Removed

**Event type:** `GROUP_PARTICIPANT_REMOVED`

Fires when a participant is **removed by an admin** from one of your WhatsApp Groups. This is distinct from `GROUP_PARTICIPANT_LEFT`, which fires when a participant exits voluntarily.

### Sample Payload

```json
{
  "event": "GROUP_PARTICIPANT_REMOVED",
  "orgId": "org_abc123",
  "integrationId": "int_xyz789",
  "groupId": "grp_01HX9abc123",
  "groupName": "Q1 Promotions Team",
  "customerPhone": "+919876543210",
  "participantRemovedAt": "2026-01-15T12:00:00.000Z"
}
```

### Field Reference

All [common fields](#common-fields--lifecycle-and-membership-events) apply. The following fields are specific to this event:

| Field                  | Type   | Required | Description                                              |
| ---------------------- | ------ | -------- | -------------------------------------------------------- |
| `customerPhone`        | string | Yes      | WhatsApp phone number of the participant who was removed |
| `participantRemovedAt` | string | Yes      | ISO 8601 timestamp of when the removal occurred          |

***

## Left vs. Removed

Both `GROUP_PARTICIPANT_LEFT` and `GROUP_PARTICIPANT_REMOVED` carry an identical payload shape (except the event type and its timestamp field name). The only practical difference is intent:

| Event                       | Initiator       | Timestamp field        |
| --------------------------- | --------------- | ---------------------- |
| `GROUP_PARTICIPANT_LEFT`    | The participant | `participantLeftAt`    |
| `GROUP_PARTICIPANT_REMOVED` | A group admin   | `participantRemovedAt` |

***

# WhatsApp Group Message Received

**Event type:** `WA_GROUP_MESSAGE_RECEIVED`

Fires whenever a message is sent inside a WhatsApp Group that is recognized within your DoubleTick workspace. This is the group-conversation counterpart to `MESSAGE_RECEIVED` ([Messaging Webhooks](https://docs.doubletick.io/reference/messaging-webhooks-1)), which only covers individual (1:1) conversations.

## Trigger Conditions

The event is emitted when:

* An inbound message is sent inside a WhatsApp Group.
* The group is already known to your DoubleTick workspace.

It is **not** emitted for:

* Messages in individual (1:1) conversations (see `MESSAGE_RECEIVED`).
* System messages generated by group activity (e.g. membership changes).
* Reactions sent inside a group.
* Messages sent inside groups your workspace has not yet recognized.

## Sample Payload

```json
{
  "to": "120363012345678901",
  "from": "919999999999",
  "messageId": "wa_message_id",
  "dtMessageId": "dt_message_id",
  "receivedAt": "2026-01-15T11:57:57.796Z",
  "contact": {
    "name": "John Doe"
  },
  "integrationType": "WHATSAPP",
  "type": "wa_group",
  "message": {
    "type": "TEXT",
    "text": "Hello everyone",
    "context": {}
  },
  "dtLastMessageId": null,
  "lastMessageOrigin": "USER",
  "isAgentOffline": false,
  "customerId": "customer_pvgpZUh3wg",
  "dtCustomerId": "customer_pvgpZUh3wg"
}
```

## Field Reference

This payload reuses the same base structure as `MESSAGE_RECEIVED` (see Messaging Webhooks: [Common Fields](https://docs.doubletick.io/reference/messaging-webhooks-1#common-fields)). Two fields behave differently in this event, and one fixed field is added:

| Field                         | Type   | Required | Description                                                                           |
| ----------------------------- | ------ | -------- | ------------------------------------------------------------------------------------- |
| `to`                          | string | Yes      | **The group's identifier**, not your WhatsApp Business number. See note below.        |
| `from`                        | string | Yes      | The phone number of the individual participant who sent the message inside the group. |
| `type`                        | string | Yes      | Always `"wa_group"`. Identifies this payload as a group message.                      |
| `customerId` / `dtCustomerId` | string | Yes      | Identifies the sending **participant**, not the group itself.                         |

### Supported Message Types

Group messages support the same message types as individual messages:

* Text
* Image, Video, Audio, Sticker
* Document
* Location
* Contacts
* Button Replies
* Flow Responses

## Notes

* `to` carries the group's identifier instead of your WABA phone number. Do not assume `to` is always a phone number when handling this event — branch on `type: "wa_group"` to know `to` should be treated as a group identifier. The value is a numeric string assigned by Meta's Cloud API and may be up to 20 digits long. Do not store it in a column sized or validated for phone numbers.
* This payload never includes ad attribution data (no [`referral`](https://docs.doubletick.io/reference/messaging-webhooks-1#referralobject) object).
* This payload does not include the group's display name. Only the group identifier is provided in `to`.
* `customerId` / `dtCustomerId` always identify the individual participant who sent the message.
* System messages and reactions sent inside the group are not forwarded through this webhook.

## Edge Cases

### Unrecognized Groups

If a message arrives from a WhatsApp Group that is not yet recognized in your DoubleTick workspace, no webhook is delivered for that message. A group becomes recognized once it has been created through DoubleTick or appears in your DoubleTick inbox.

## Best Practices

* Treat `to` as an opaque group identifier for this event type rather than a phone number.
* Use `dtCustomerId` to attribute the message to the sending participant in your CRM, separately from however you track the group itself.
* Since this event is mutually exclusive with `MESSAGE_RECEIVED`, route both events into the same downstream message-handling logic, branching on `type: "wa_group"` where group-specific handling is required.