Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Initiate AI Bot Call

Start an outbound AI-powered voice call to a customer over WhatsApp or PSTN.

Starts an outbound voice call that is fully handled by your configured AI agent. The AI agent manages the entire conversation automatically, so no WebRTC client or manual agent intervention is required.

> 📘 NOTE
>
> This endpoint is designed only for AI-powered voice calls. To initiate a human-handled call, use the Human Call endpoint. [Link](https://docs.doubletick.io/reference/whatsapp-call-public-api)

## Endpoint

```http
POST https://public.doubletick.io/v1/call/ai-bot
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

| Parameter     | Type   | Required | Description                                                                                                                                                                                                                   |
| ------------- | ------ | -------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `from`        | string | Yes      | The phone number used to initiate the call. For `WHATSAPP` calls, provide your **WhatsApp Business Account (WABA)** number. For `PSTN` calls, provide your **PSTN-enabled phone number**. The number must be in E.164 format. |
| `to`          | string | Yes      | Customer phone number in E.164 format. Example: `+919876543210`.                                                                                                                                                              |
| `channel`     | string | Yes      | Calling channel. Supported values are `WHATSAPP` and `PSTN`.                                                                                                                                                                  |
| `aiAgentName` | string | Yes      | Name of the active AI agent that should handle the call present on DoubleTick.                                                                                                                                                |

***

## Example Request

```bash Shell
curl --request POST \
  --url https://public.doubletick.io/v1/call/ai-bot \
  --header "Authorization: <your-api-key>" \
  --header "Content-Type: application/json" \
  --data '{
    "from": "+911234567890",
    "to": "+919876543210",
    "channel": "WHATSAPP",
    "aiAgentName": "agent_abc123"
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
| `callId`          | string | Unique identifier for the initiated AI voice call.          |
| `pstnPhoneNumber` | string | Phone number used for the PSTN connection, when applicable. |

### customer

| Field         | Type   | Description            |
| ------------- | ------ | ---------------------- |
| `name`        | string | Customer name.         |
| `phoneNumber` | string | Customer phone number. |

***

## Error Responses

| Status Code | Description                                                                   |
| ----------- | ----------------------------------------------------------------------------- |
| **400**     | Invalid `channel` value or the specified AI agent is not configured.          |
| **401**     | Invalid or missing API key.                                                   |
| **403**     | PSTN calling is not enabled for your organization.                            |
| **422**     | WABA number not found, coex number, or the customer is blocked.               |
| **429**     | Rate limit exceeded. Check the `Retry-After` response header before retrying. |

***

## Things to Know

* The `to` phone number must be provided in **E.164** format.
* The `from` number depends on the selected **channel**:
  * For `WHATSAPP`, provide your configured **WhatsApp Business Account (WABA)** number.
  * For `PSTN`, provide your configured **PSTN-enabled phone number**.
* `channel` is **case-sensitive** and must be either `WHATSAPP` or `PSTN`.
* `aiAgentName` must exactly match an active AI agent configured in your organization.
* If `channel` is `PSTN`, PSTN calling must be enabled for your organization.
* A successful response indicates that the AI call has been queued for processing and a unique `callId` has been generated.
* Use the returned `callId` to track the call through webhooks or other call-related APIs (if applicable).

***

## Common Use Cases

* AI-powered customer support calls.
* Appointment reminders and confirmations.
* Automated lead qualification.
* Customer onboarding conversations.
* Payment reminders and follow-ups.
* Feedback collection after purchases or services.