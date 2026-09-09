Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Outbound Calls

This section covers APIs used to **initiate and manage outgoing WhatsApp voice calls** to customers.

***

## Create Outgoing Call

Initiates an outgoing WhatsApp voice call to a customer.

**Endpoint**

```
POST /whatsapp/call/create
```

**Permission Required**

`INITIATE_CALL` (validated against `from` number)

***

### Request Body

| Field      | Type   | Required | Description                          |
| ---------- | ------ | -------- | ------------------------------------ |
| `from`     | string | Yes      | WABA number to call from             |
| `to`       | string | Yes      | Customer phone number (E.164 format) |
| `sdp`      | string | Yes      | WebRTC SDP offer from the client     |
| `sdpType`  | string | Yes      | SDP type (e.g. `offer`)              |
| `deviceId` | string | No       | Client device identifier             |

***

### Response (200)

```json
{
  "success": true,
  "message": "Call initiated",
  "data": {
    "callId": "<provider-call-id>"
  },
  "customer": {
    "name": "<customer-name-or-number>",
    "phoneNumber": "<customer-number>"
  },
  "answer": { ... }
}
```

**Notes**

* `answer` contains the **SDP answer** required to complete the WebRTC connection.
* `callId` uniquely identifies the call session.

***

### Errors

* **422 Unprocessable Entity** if:

  * Integration is invalid
  * Customer has not granted call permission
  * Wallet balance is insufficient
  * Another call is already in progress

***

## Reconnect Outgoing Call

Reconnects an **active outgoing call**, typically after a network drop or device change.

**Endpoint**

```
POST /whatsapp/call/reconnect
```

**Permission Required**

`RECEIVE_CALL` (validated against `callId`)

***

### Request Body

| Field            | Type    | Required | Description              |
| ---------------- | ------- | -------- | ------------------------ |
| `callId`         | string  | Yes      | Call ID to reconnect     |
| `callOriginType` | string  | Yes      | `OUTGOING`               |
| `sdp`            | string  | No       | New WebRTC SDP           |
| `deviceId`       | string  | No       | Client device identifier |
| `switchCall`     | boolean | No       | Indicates device switch  |

***

### Response (200)

```json
{
  "success": true,
  "message": "Call updated",
  "data": { ... },
  "callStartedAt": "<ISO8601>",
  "customer": {
    "name": "<customer-name-or-number>",
    "phoneNumber": "<customer-number>"
  }
}
```

***

#