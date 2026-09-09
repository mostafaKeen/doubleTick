Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Call Permissions

Returns WhatsApp call permission and eligibility status for a customer.

**Endpoint**

```
GET /whatsapp/call/call-permissions
```

***

### Query Parameters

| Parameter    | Type   | Required | Description              |
| ------------ | ------ | -------- | ------------------------ |
| `wabaNumber` | string | Yes      | WhatsApp Business number |
| `customerId` | string | Yes      | Customer ID              |
| `client`     | string | No       | Client identifier        |
| `version`    | string | No       | Client version           |

***

### Example: Eligible and Allowed

```json
{
  "isBlocked": false,
  "isEligible": { "allowed": true, "reason": "" },
  "startCall": {
    "allowed": true,
    "maxAllowed": 100,
    "currentUsage": 3
  },
  "callPermissionRequest": {
    "allowed": true,
    "numberOfRequestLeftForToday": 1,
    "allowedRequestLeftForWeek": 2,
    "totalRequestLimit": 2
  }
}
```

***

### Example: Not Eligible

```json
{
  "isEligible": {
    "allowed": false,
    "reason": "PROVIDER_NOT_SUPPORTED"
  }
}
```

### Example : Customer is Blocked

```json
{
  "isBlocked": true
}
```