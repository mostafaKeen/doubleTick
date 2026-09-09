Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Incoming Calls

This section covers APIs used to **receive, accept, decline, and maintain incoming WhatsApp calls**.

***

## Accept Incoming Call

Accepts an incoming WhatsApp call and joins the call session.

**Endpoint**

```
POST /whatsapp/accept
```

**Permission Required**

`RECEIVE_CALL` (validated against `callId`)

***

### Request Body

| Field       | Type    | Required | Description                      |
| ----------- | ------- | -------- | -------------------------------- |
| `callId`    | string  | Yes      | Call ID from incoming call event |
| `sdp`       | string  | Yes      | WebRTC SDP offer                 |
| `sdpType`   | string  | Yes      | SDP type ("answer")              |
| `reconnect` | boolean | No       | Set to `true` for reconnections  |
| `deviceId`  | string  | No       | Client device identifier         |

***

### Response (200)

```json
{
  "success": true,
  "message": "Call answered",
  "data": { ... },
  "event": "CALL_ACCEPTED",
  "wabaName": "<waba-display-name>",
  "wabaNumber": "<waba-number>",
  "customerName": "<customer-name>",
  "customerNumber": "<customer-number>",
  "callStartedAt": "<ISO8601>",
  "chatId": "<chat-id>",
  "customer": {
    "name": "<customer-name-or-number>",
    "phoneNumber": "<customer-number>"
  }
}
```

***

## Decline or End Call

Declines an incoming call or ends an active call.

**Endpoint**

```
POST /whatsapp/decline
```

**Permission Required**

`RECEIVE_CALL` (validated against `callId`)

***

### Request Body

| Field    | Type   | Required | Description               |
| -------- | ------ | -------- | ------------------------- |
| `callId` | string | Yes      | Call ID to decline or end |

***

### Response (200)

```json
{
  "success": true,
  "message": "Call declined",
  "data": { ... },
  "callStatus": "REJECT",
  "customer": {
    "name": "<customer-name-or-number>",
    "phoneNumber": "<customer-number>"
  }
}
```

**callStatus values**

* `REJECT` – Call was not answered
* `END` – Call was already in progress

***

## Keep Call Alive (Ping)

Prevents the call from closing due to inactivity.

**Endpoint**

```
GET /whatsapp/call/ping
```

**Permission Required**

`RECEIVE_CALL` (validated against `callId`)

***

### Query Parameters

| Parameter | Type   | Required | Description           |
| --------- | ------ | -------- | --------------------- |
| `callId`  | string | Yes      | Call ID to keep alive |

***

### Response (200)

```json
{}
```

***

### Errors

* **400** – `callId` missing
* **422** – Call not found, already closed, or user is not the active call participant