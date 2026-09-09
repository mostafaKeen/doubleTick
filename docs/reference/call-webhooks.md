Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Call Webhooks

Receive real-time notifications for inbound and outbound voice call activity on your WhatsApp Business Account, including call lifecycle events and customer permission responses.

Call Webhooks cover two distinct but related areas: the full lifecycle of voice calls placed through your WhatsApp Business Account, and the customer's response to a call permission request.

## Events Covered

### Voice Call Lifecycle

| Event                     | Description                                                            |
| ------------------------- | ---------------------------------------------------------------------- |
| `CALL_INCOMING_INITIATED` | Fires when an inbound call from a customer begins ringing on your WABA |
| `CALL_OUTGOING_INITIATED` | Fires when an agent initiates an outbound call to a customer           |
| `CALL_ACCEPTED`           | Fires when a call is answered and becomes active                       |
| `CALL_ENDED`              | Fires when an active call ends                                         |
| `CALL_REJECTED`           | Fires when a call is rejected before being answered                    |
| `CALL_MISSED`             | Fires when an inbound call goes unanswered                             |

### Agent Notification

| Event             | Description                                                                                               |
| ----------------- | --------------------------------------------------------------------------------------------------------- |
| `CALL_NOT_PICKED` | Fires when a specific agent is notified of a call but does not answer before their notification times out |

### Call Permission

| Event                            | Description                                                          |
| -------------------------------- | -------------------------------------------------------------------- |
| `CALL_PERMISSION_REPLY_RECEIVED` | Fires when a customer responds to a WhatsApp call permission request |

***

# Voice Call Lifecycle Events

The six voice call lifecycle events share the same 19-field payload structure. The `callStatus` and `callDirection` fields indicate what happened during the call, and which other fields are populated.

## Shared Payload Structure

| Field               | Type   | Required | Description                                                                                                                                                                                                                                         |
| ------------------- | ------ | -------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `event`             | string | Yes      | The event type name (e.g. `"CALL_INCOMING_INITIATED"`)                                                                                                                                                                                              |
| `callId`            | string | Yes      | Unique identifier for the call                                                                                                                                                                                                                      |
| `chatMessageId`     | string | Yes      | Message identifier associated with the call                                                                                                                                                                                                         |
| `chatId`            | string | Yes      | Conversation identifier                                                                                                                                                                                                                             |
| `wabaNumber`        | string | Yes      | WhatsApp Business number involved in the call                                                                                                                                                                                                       |
| `customerNumber`    | string | Yes      | Customer's phone number                                                                                                                                                                                                                             |
| `customerName`      | string | Yes      | Customer's display name                                                                                                                                                                                                                             |
| `customerId`        | string | Yes      | DoubleTick customer identifier                                                                                                                                                                                                                      |
| `callDirection`     | string | Yes      | Direction of the call. See values below.                                                                                                                                                                                                            |
| `callStatus`        | string | Yes      | Current call status. See values below.                                                                                                                                                                                                              |
| `failedReason`      | string | Yes      | Reason for failure. Empty string unless `callStatus` is `FAILED`.                                                                                                                                                                                   |
| `durationInSeconds` | number | Yes      | Call duration in seconds. `0` until the call ends.                                                                                                                                                                                                  |
| `callStartedAt`     | string | Yes      | ISO 8601 timestamp when the call was picked up. Empty string until accepted.                                                                                                                                                                        |
| `callEndedAt`       | string | Yes      | ISO 8601 timestamp when the call ended. Empty string until the call ends.                                                                                                                                                                           |
| `pickedBy`          | string | Yes      | For `INCOMING` calls: phone number of the agent who answered. Empty string until accepted. For `OUTGOING` calls: phone number of the agent who placed the call — populated from `CALL_OUTGOING_INITIATED` onward, before the customer has answered. |
| `rejectedBy`        | string | Yes      | Phone number of the agent who rejected the call. Empty string unless the call was rejected.                                                                                                                                                         |
| `endedBy`           | string | Yes      | Phone number of the agent who ended the call. Empty string until the call ends.                                                                                                                                                                     |
| `recordingUrl`      | string | Yes      | Signed URL of the call recording. Empty string if no recording — see [Accessing Recording URLs](#accessing-recording-urls).                                                                                                                         |
| `deviceId`          | string | Yes      | Opaque identifier for the device or client used during the call. Suitable for audit logging — do not rely on its format or value for routing logic                                                                                                  |

### callDirection Values

| Value      | Description                                         |
| ---------- | --------------------------------------------------- |
| `INCOMING` | The call was placed by the customer to the business |
| `OUTGOING` | The call was placed by the business to the customer |

### callStatus Values

| Value         | Description                                       |
| ------------- | ------------------------------------------------- |
| `INITIATED`   | The call is ringing and has not been answered yet |
| `IN_PROGRESS` | The call has been accepted and is active          |
| `END`         | The call has ended normally                       |
| `REJECT`      | The call was rejected before being answered       |
| `MISSED`      | The inbound call went unanswered                  |
| `FAILED`      | The call could not be established                 |

> When a call ends, **callStatus** is **"END"** — not **"ENDED"**. When a call is rejected, **callStatus** is **"REJECT"** — not **"REJECTED"**. Checking for **"ENDED"** or **"REJECTED"** in your code will silently fail.

## Field Population by Event

| Field               | CALL\_INCOMING\_INITIATED | CALL\_OUTGOING\_INITIATED | CALL\_ACCEPTED | CALL\_ENDED | CALL\_REJECTED | CALL\_MISSED |
| ------------------- | ------------------------- | ------------------------- | -------------- | ----------- | -------------- | ------------ |
| `callDirection`     | `INCOMING`                | `OUTGOING`                | Either         | Either      | Either         | `INCOMING`   |
| `callStatus`        | `INITIATED`               | `INITIATED`               | `IN_PROGRESS`  | `END`       | `REJECT`       | `MISSED`     |
| `callStartedAt`     | `""`                      | `""`                      | Populated      | Populated   | `""`           | `""`         |
| `callEndedAt`       | `""`                      | `""`                      | `""`           | Populated   | Populated      | Populated    |
| `durationInSeconds` | `0`                       | `0`                       | `0`            | Populated   | `0`            | `0`          |
| `pickedBy`          | `""`                      | Populated                 | Populated      | Populated   | `""`           | `""`         |
| `rejectedBy`        | `""`                      | `""`                      | `""`           | `""`        | Populated      | `""`         |
| `endedBy`           | `""`                      | `""`                      | `""`           | Populated   | `""`           | `""`         |
| `recordingUrl`      | `""`                      | `""`                      | `""`           | Conditional | `""`           | `""`         |
| `failedReason`      | `""`                      | `""`                      | `""`           | `""`        | `""`           | `""`         |

> `failedReason` is non-empty only when `callStatus` is `FAILED`. `recordingUrl` is non-empty on `CALL_ENDED` only when call recording is enabled for the integration.
>
> **`pickedBy` means something different depending on `callDirection`.** For `INCOMING` calls, it is populated only once the call is answered (`callStatus` reaches `IN_PROGRESS`) — it identifies who picked up. For `OUTGOING` calls, it is populated as soon as the call is placed (`callStatus` is still `INITIATED`) — it identifies the agent who initiated the call, not who answered it, since the customer is the one answering. Do not treat a populated `pickedBy` as proof that an outgoing call was answered — check `callStatus` for that.

## Accessing Recording URLs

`recordingUrl` is a **signed URL** — it cannot be accessed without authentication.

To fetch a recording, append your [DoubleTick developer API](https://docs.doubletick.io/docs/quickstart-guide#step-2-generate-your-api-key) key as a `token` query parameter:

```
GET {recordingUrl}?token=<your_api_key>
```

**Example:**

```
GET https://recordings.doubletick.io/call_01HX9abc123.mp3?token=dt_live_abc123xyz
```

> Treat the `token` parameter like a credential. Do not expose it in client-side code, browser-accessible URLs, or plain-text logs.

***

## Incoming Call Initiated

**Event type:** `CALL_INCOMING_INITIATED`

Fires when an inbound call from a customer begins ringing on your WhatsApp Business Account.

### Sample Payload

```json
{
  "event": "CALL_INCOMING_INITIATED",
  "callId": "call_01HX9abc123",
  "chatMessageId": "msg_01HX9abc123",
  "chatId": "",
  "wabaNumber": "919000000000",
  "customerNumber": "919876543210",
  "customerName": "Rohan Mehta",
  "customerId": "customer_pvgpZUh3wg",
  "callDirection": "INCOMING",
  "callStatus": "INITIATED",
  "failedReason": "",
  "durationInSeconds": 0,
  "callStartedAt": "",
  "callEndedAt": "",
  "pickedBy": "",
  "rejectedBy": "",
  "endedBy": "",
  "recordingUrl": "",
  "deviceId": "device_abc123"
}
```

***

## Outgoing Call Initiated

**Event type:** `CALL_OUTGOING_INITIATED`

Fires when an agent initiates an outbound call to a customer.

### Sample Payload

```json
{
  "event": "CALL_OUTGOING_INITIATED",
  "callId": "call_01HX9abc123",
  "chatMessageId": "msg_01HX9abc123",
  "chatId": "chat_abc123",
  "wabaNumber": "919000000000",
  "customerNumber": "919876543210",
  "customerName": "Rohan Mehta",
  "customerId": "customer_pvgpZUh3wg",
  "callDirection": "OUTGOING",
  "callStatus": "INITIATED",
  "failedReason": "",
  "durationInSeconds": 0,
  "callStartedAt": "",
  "callEndedAt": "",
  "pickedBy": "James Smith",
  "rejectedBy": "",
  "endedBy": "",
  "recordingUrl": "",
  "deviceId": "device_abc123"
}
```

***

## Call Accepted

**Event type:** `CALL_ACCEPTED`

Fires when a call is answered and becomes active. `callStartedAt` and `pickedBy` are populated at this point.

### Sample Payload — Incoming Call Accepted by Agent

```json
{
  "event": "CALL_ACCEPTED",
  "callId": "call_01HX9abc123",
  "chatMessageId": "msg_01HX9abc123",
  "chatId": "chat_abc123",
  "wabaNumber": "919000000000",
  "customerNumber": "919876543210",
  "customerName": "Rohan Mehta",
  "customerId": "customer_pvgpZUh3wg",
  "callDirection": "INCOMING",
  "callStatus": "IN_PROGRESS",
  "failedReason": "",
  "durationInSeconds": 0,
  "callStartedAt": "2026-01-15T10:30:00.000Z",
  "callEndedAt": "",
  "pickedBy": "James Smith",
  "rejectedBy": "",
  "endedBy": "",
  "recordingUrl": "",
  "deviceId": "device_abc123"
}
```

### Sample Payload — Outgoing Call Accepted by Customer

```json
{
  "event": "CALL_ACCEPTED",
  "callId": "call_01HX9def456",
  "chatMessageId": "msg_01HX9def456",
  "chatId": "chat_def456",
  "wabaNumber": "919000000000",
  "customerNumber": "919876543210",
  "customerName": "Rohan Mehta",
  "customerId": "customer_pvgpZUh3wg",
  "callDirection": "OUTGOING",
  "callStatus": "IN_PROGRESS",
  "failedReason": "",
  "durationInSeconds": 0,
  "callStartedAt": "2026-01-15T11:00:00.000Z",
  "callEndedAt": "",
  "pickedBy": "James Smith",
  "rejectedBy": "",
  "endedBy": "",
  "recordingUrl": "",
  "deviceId": "device_abc123"
}
```

***

## Call Ended

**Event type:** `CALL_ENDED`

Fires when an active call ends. `callEndedAt`, `durationInSeconds`, and `endedBy` are populated. A recording URL may be included if call recording is enabled.

### Sample Payload — Incoming Call Ended by Agent (with recording)

```json
{
  "event": "CALL_ENDED",
  "callId": "call_01HX9abc123",
  "chatMessageId": "msg_01HX9abc123",
  "chatId": "chat_abc123",
  "wabaNumber": "919000000000",
  "customerNumber": "919876543210",
  "customerName": "Rohan Mehta",
  "customerId": "customer_pvgpZUh3wg",
  "callDirection": "INCOMING",
  "callStatus": "END",
  "failedReason": "",
  "durationInSeconds": 145,
  "callStartedAt": "2026-01-15T10:30:00.000Z",
  "callEndedAt": "2026-01-15T10:32:25.000Z",
  "pickedBy": "James Smith",
  "rejectedBy": "",
  "endedBy": "James Smith",
  "recordingUrl": "https://recordings.example.com/call_01HX9abc123.mp3",
  "deviceId": "device_abc123"
}
```

### Sample Payload — Outgoing Call Ended by Customer (no recording)

```json
{
  "event": "CALL_ENDED",
  "callId": "call_01HX9def456",
  "chatMessageId": "msg_01HX9def456",
  "chatId": "chat_def456",
  "wabaNumber": "919000000000",
  "customerNumber": "919876543210",
  "customerName": "Rohan Mehta",
  "customerId": "customer_pvgpZUh3wg",
  "callDirection": "OUTGOING",
  "callStatus": "END",
  "failedReason": "",
  "durationInSeconds": 87,
  "callStartedAt": "2026-01-15T11:00:00.000Z",
  "callEndedAt": "2026-01-15T11:01:27.000Z",
  "pickedBy": "James Smith",
  "rejectedBy": "",
  "endedBy": "Rohan Mehta",
  "recordingUrl": "",
  "deviceId": "device_abc123"
}
```

### Sample Payload — Outgoing Call Ended by Agent Before Customer Answers

```json
{
  "event": "CALL_ENDED",
  "callId": "call_01HX9ghi789",
  "chatMessageId": "msg_01HX9ghi789",
  "chatId": "chat_ghi789",
  "wabaNumber": "919000000000",
  "customerNumber": "919876543210",
  "customerName": "Rohan Mehta",
  "customerId": "customer_pvgpZUh3wg",
  "callDirection": "OUTGOING",
  "callStatus": "END",
  "failedReason": "",
  "durationInSeconds": 0,
  "callStartedAt": "",
  "callEndedAt": "2026-01-15T11:05:30.000Z",
  "pickedBy": "James Smith",
  "rejectedBy": "",
  "endedBy": "James Smith",
  "recordingUrl": "",
  "deviceId": "device_abc123"
}
```

***

## Call Rejected

**Event type:** `CALL_REJECTED`

Fires when a call is rejected before being answered. `rejectedBy` identifies who rejected the call.

### Sample Payload

```json
{
  "event": "CALL_REJECTED",
  "callId": "call_01HX9abc123",
  "chatMessageId": "msg_01HX9abc123",
  "chatId": "chat_abc123",
  "wabaNumber": "919000000000",
  "customerNumber": "919876543210",
  "customerName": "Rohan Mehta",
  "customerId": "customer_pvgpZUh3wg",
  "callDirection": "INCOMING",
  "callStatus": "REJECT",
  "failedReason": "",
  "durationInSeconds": 0,
  "callStartedAt": "",
  "callEndedAt": "2026-01-15T10:30:25.000Z",
  "pickedBy": "",
  "rejectedBy": "James Smith",
  "endedBy": "",
  "recordingUrl": "",
  "deviceId": "device_abc123"
}
```

***

## Call Missed

**Event type:** `CALL_MISSED`

Fires when an inbound call terminates with no one having answered. In addition to the standard 19 call fields, this event includes two extra fields — `isNewCaller` and `isChatAssigned` — that capture the customer and conversation state at the time the call was missed.

### Sample Payload

```json
{
  "event": "CALL_MISSED",
  "callId": "call_01HX9abc123",
  "chatMessageId": "msg_01HX9abc123",
  "chatId": "chat_abc123",
  "wabaNumber": "919000000000",
  "customerNumber": "919876543210",
  "customerName": "Rohan Mehta",
  "customerId": "customer_pvgpZUh3wg",
  "callDirection": "INCOMING",
  "callStatus": "MISSED",
  "failedReason": "",
  "durationInSeconds": 0,
  "callStartedAt": "",
  "callEndedAt": "2026-01-15T10:33:10.000Z",
  "pickedBy": "",
  "rejectedBy": "",
  "endedBy": "",
  "recordingUrl": "",
  "deviceId": "device_abc123",
  "isNewCaller": false,
  "isChatAssigned": false
}
```

### Additional Fields (CALL\_MISSED only)

| Field            | Type    | Required | Description                                                        |
| ---------------- | ------- | -------- | ------------------------------------------------------------------ |
| `isNewCaller`    | boolean | No       | `true` if this customer has no prior message history with the WABA |
| `isChatAssigned` | boolean | No       | `true` if the conversation was assigned to an agent at call time   |

***

# Call Not Picked

**Event type:** `CALL_NOT_PICKED`

Fires when an agent is notified of an incoming call but does not answer within their notification timeout window.

DoubleTick can route incoming calls using a cascading notification sequence — ringing agents one by one (or in groups) with a configurable timeout per step. `CALL_NOT_PICKED` fires for each agent who is skipped in this sequence. The call itself remains active and ringing for the next agent when this event fires.

## Trigger Conditions

The event is emitted when:

* An incoming call is being distributed using cascading agent notifications.
* A specific agent is notified to answer the call.
* The agent does not answer within their configured notification timeout.
* The system moves on to notify the next agent in the sequence.

It is **not** emitted when the call terminates — see `CALL_MISSED` for the end-of-call event when no one answers.

## Payload

`CALL_NOT_PICKED` uses the same [19-field lifecycle payload structure](#shared-payload-structure) as all other voice call events.

At the time this event fires:

* `callStatus` is `INITIATED` — the call is still ringing.
* `callStartedAt`, `pickedBy`, `rejectedBy`, `endedBy`, and `recordingUrl` are all empty strings — no one has answered or ended the call yet.

### Sample Payload

```json
{
  "event": "CALL_NOT_PICKED",
  "callId": "call_01HX9abc123",
  "chatMessageId": "msg_01HX9abc123",
  "chatId": "",
  "wabaNumber": "919000000000",
  "customerNumber": "919876543210",
  "customerName": "Rohan Mehta",
  "customerId": "customer_pvgpZUh3wg",
  "callDirection": "INCOMING",
  "callStatus": "INITIATED",
  "failedReason": "",
  "durationInSeconds": 0,
  "callStartedAt": "",
  "callEndedAt": "",
  "pickedBy": "",
  "rejectedBy": "",
  "endedBy": "",
  "recordingUrl": "",
  "deviceId": "device_abc123"
}
```

## Notes

* A single incoming call may generate multiple `CALL_NOT_PICKED` events — one for each agent who times out in the cascading sequence.
* The agent who timed out is not included in this payload. `CALL_NOT_PICKED` only signals that the call progressed past one agent in the queue — it does not identify who was skipped.
* The call is still active when this event fires. If the call ends with no one answering, a `CALL_MISSED` event follows once the call terminates.
* Correlate `CALL_NOT_PICKED` events to each other and to `CALL_MISSED` using `callId`.

***

# Call Permission Reply Received

**Event type:** `CALL_PERMISSION_REPLY_RECEIVED`

Fires when a customer responds to a WhatsApp call permission request sent by your business.

Before an agent can place a WhatsApp voice call to a customer, WhatsApp may require the customer's explicit consent. DoubleTick sends the permission request on your behalf, and this event delivers the customer's response — accepted or declined — so your system can act accordingly.

## Trigger Conditions

The event is emitted when:

* Your business has sent a call permission request to a customer via WhatsApp.
* The customer taps **Accept** or **Decline** in their WhatsApp app.

> This event is generated for Cloud API integrations only. It is not emitted for Gupshup or 360-Dialog integrations.

## Sample Payload

### Customer Accepts

```json
{
  "from": "+919876543210",
  "to": "+919000000000",
  "receivedAt": "2026-01-15T10:30:00.000Z",
  "messageId": "wa_message_id",
  "pairedMessageId": "wa_message_id",
  "response": "accept",
  "responseSource": "user_initiated",
  "expirationTimestamp": 1768470600,
  "isPermanent": false,
  "dtCustomerId": "customer_pvgpZUh3wg"
}
```

### Customer Declines

```json
{
  "from": "+919876543210",
  "to": "+919000000000",
  "receivedAt": "2026-01-15T10:30:00.000Z",
  "messageId": "wa_message_id",
  "pairedMessageId": "wa_message_id",
  "response": "reject",
  "responseSource": "user_initiated"
}
```

## Field Reference

| Field                 | Type    | Required | Description                                                                 |
| --------------------- | ------- | -------- | --------------------------------------------------------------------------- |
| `from`                | string  | Yes      | Customer's WhatsApp phone number                                            |
| `to`                  | string  | Yes      | WhatsApp Business number that sent the permission request                   |
| `receivedAt`          | string  | Yes      | ISO 8601 timestamp of when the response was received                        |
| `messageId`           | string  | Yes      | WhatsApp message ID of the customer's response message                      |
| `pairedMessageId`     | string  | No       | WhatsApp message ID of the original permission request sent to the customer |
| `response`            | string  | Yes      | Customer's decision. See response values below.                             |
| `responseSource`      | string  | Yes      | Origin of the response as reported by Meta.                                 |
| `expirationTimestamp` | number  | No       | Unix timestamp (in seconds) indicating when the granted permission expires  |
| `isPermanent`         | boolean | No       | Whether the customer granted permanent permission, without an expiration    |
| `dtCustomerId`        | string  | No       | DoubleTick customer identifier, if the customer record was resolved         |

## Response Values

| Value    | Description                                       |
| -------- | ------------------------------------------------- |
| `accept` | The customer accepted the call permission request |
| `reject` | The customer declined the call permission request |

## Conditional Fields

| Field                 | Condition                                                                                          |
| --------------------- | -------------------------------------------------------------------------------------------------- |
| `pairedMessageId`     | Present when the customer's response includes a message context linking it to the original request |
| `expirationTimestamp` | Present when the granted permission has a defined expiry time                                      |
| `isPermanent`         | Present when Meta explicitly includes this flag in the response                                    |
| `dtCustomerId`        | Present when the customer record can be resolved. Absent if not linked.                            |

## Notes

* `expirationTimestamp` and `isPermanent` are mutually informative — a permanent permission has no expiration timestamp.
* `pairedMessageId` can be used to correlate this response to the original permission request your system sent.
* Only check `response` to determine whether to proceed with placing a call.

***

## Best Practices

* Use `callId` to correlate voice call lifecycle events for the same call — a single call will produce multiple events (`CALL_INCOMING_INITIATED` → `CALL_ACCEPTED` → `CALL_ENDED`) all sharing the same `callId`.
* Always check `callDirection` alongside the event type when determining whether a call was customer-initiated or agent-initiated.
* Treat `recordingUrl` as conditional — always check for an empty string before attempting to fetch or display a recording. When non-empty, append your developer API key as `?token=<your_api_key>` to authenticate the request.
* `pickedBy`, `rejectedBy`, and `endedBy` contain the agent's phone number, not a stable identifier. Phone numbers can change when an agent updates their profile. Do not use these fields for programmatic matching or routing logic — they are intended for display and audit purposes only.
* For `CALL_PERMISSION_REPLY_RECEIVED`, only proceed with placing a call if `response` is `"accept"`. A `"reject"` response means the customer has declined.
* Do not rely on `responseSource` for automated logic — it is intended for display and audit purposes.