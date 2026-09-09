Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Initiate Agent Call

Initiate a PSTN call between your team member and a customer through DoubleTick.

Initiates an outgoing PSTN call handled by a human agent on your team — not the AI bot. It dials your agent's phone first, then the customer's phone, and connects the two legs once the agent picks up.

## Endpoint

```http
POST https://public.doubletick.io/v1/calls/agent
```

## Authentication

Include your API key in the `Authorization` header.

| Header        | Value              |
| ------------- | ------------------ |
| Authorization | `<your-api-key>`   |
| Content-Type  | `application/json` |

> 📘 NOTE
>
> If you do not have your DoubleTick API Key, you can obtain it by following this guide [link](https://docs.doubletick.io/docs/quickstart-guide#step-2-generate-your-api-key).

***

## Request Body

| Parameter     | Type   | Required | Description                                                              |
| ------------- | ------ | -------- | ------------------------------------------------------------------------ |
| `from`        | string | Yes      | The `PSTN` phone number used to initiate the call. (e.g. `911234567890`) |
| `to`          | string | Yes      | Customer phone number in E.164 format (e.g. `919876543210`).             |
| `channel`     | string | Yes      | Calling channel. Supported values are `PSTN`.                            |
| `agentNumber` | string | Yes      | Phone number of the team member who should take the call                 |

***

## Example Request

```bash Shell
curl --request POST \
  --url https://public.doubletick.io/v1/call/agent-call \
  --header 'Authorization: <your-api-key>' \
  --header 'Content-Type: application/json' \
  --data '{
    "channel": "PSTN",
    "from": "+911234567890",
    "to": "+919876543210",
    "agentNumber": "+919812345678"
  }'
```

***

## Example Response

```json 200 OK
{
  "success": true,
  "data": {
    "callId": "call_xyz789",
    "pstnPhoneNumber": "+911234567890"
  },
  "customer": {
    "name": "John Doe",
    "phoneNumber": "+919876543210"
  }
}
```

***

## Response Fields

### data

| Field             | Type   | Description                                                 |
| ----------------- | ------ | ----------------------------------------------------------- |
| `callId`          | string | Unique identifier for the initiated agent call.             |
| `pstnPhoneNumber` | string | Phone number used for the PSTN connection, when applicable. |

### customer

| Field         | Type   | Description            |
| ------------- | ------ | ---------------------- |
| `name`        | string | Customer name.         |
| `phoneNumber` | string | Customer phone number. |

***

## Error Responses

| Status Code | Description                                                                                                                |
| ----------- | -------------------------------------------------------------------------------------------------------------------------- |
| **400**     | Invalid/missing body field, channel other than `PSTN`, or agentNumber doesn't match exactly one active member of your org. |
| **401**     | Invalid or missing API key.                                                                                                |
| **403**     | PSTN calling not enabled for your org, or the resolved agent doesn't have calling permission on this PSTN integration.     |
| **422**     | PSTN number (from) not found for your org, or customer is blocked.                                                         |
| **429**     | Rate limit exceeded. Check the `Retry-After` response header before retrying.                                              |

***

## Things to Know

* The `agentNumber` field is required — there is no default/fallback agent.
* `channel` must be exactly PSTN (**case-sensitive**). `PSTN` only value accepted.
* `agentNumber` must match exactly one active member of your org by phone number.
* The agent must also have calling permission on the PSTN integration tied to `from`.
* This endpoint is for human-agent calls only. AI-handled calls use a different endpoint ([link](https://docs.doubletick.io/docs/initiate-ai-bot-call)).