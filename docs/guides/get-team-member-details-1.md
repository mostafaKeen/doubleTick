Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Get Team Member Details

Get detailed information about a specific team member including their role, reporting managers, and organization details 👨‍👩‍👧‍👦

## API Endpoint

User the following endpoint to retrieve team member information:

```
GET https://public.doubletick.io/team/member
```

***

## Request Headers

```json
{
  "Authorization": "YOUR_API_KEY",
  "Content-Type": "application/json"
}
```

## Request Body Parameters

```json
{
  "agentNumber": "team_member_number"
}
```

### Parameters

* `agentNumber` (string, required): Phone Number of the team member/agent in <<glossary:international format>>.

***

## Response

### Success Response (<<glossary:201>>)

```json
{
  "id": "agnet_id",
  "name": "agent_name",
  "phone": "agent_number",
  "email": null,
  "joinDate": "2025-02-14T04:35:15.535Z",
  "orgRoleId": "organization_role_id",
  "orgRoleName": "OWNER",
  "wabaRoles": [
    {
      "wabaName": "WhatsApp Business Name",
      "wabaNumber": "WhatsApp API Number",
      "roleName": "OWNER"
    }
  ],
  "isOrganizationOwner": false,
  "reportingManager": [
    {
      "id": "reporting_manager_id",
      "name": "reporting_manager_name",
      "email": "reporting_manager_email",
      "phone": "reporting_manager_number",
      "joinDate": "2023-08-25T13:14:26.037Z"
    }
  ]
}
```

* `id` (string): Unique identifier for the agent.
* `name` (string): Name of the agent.
* `phone` (string): Agent’s phone number.
* `email` (string or null): Email address of the agent, null if not provided.
* `joinDate` (string): Date and time when the agent joined, in <<glossary:ISO format>>.
* `orgRoleId` (string): Unique identifier for the agent’s role in the organization.
* `orgRoleName` (string): Name of the agent’s role, such as `"OWNER"`.
* `wabaRoles` (array): List of WhatsApp Business Accounts (WABA) associated with the agent.
  * `wabaName` (string): Name of the WhatsApp Business Account.
  * `wabaNumber` (string): WhatsApp API number linked to the account.
  * `roleName` (string): Role of the agent in the WABA, such as `"OWNER"`.
* `isOrganizationOwner` (boolean): Indicates if the agent is the owner of the organization.
* `reportingManager` (array): List of reporting managers assigned to the agent.
  * `id` (string): Unique identifier for the reporting manager.
  * `name` (string): Name of the reporting manager.
  * `email` (string): Email address of the reporting manager.
  * `phone` (string): Phone number of the reporting manager.
  * `joinDate` (string): Date and time when the reporting manager joined, in <<glossary:ISO format>>.

### Unauthorized (<<glossary:401>>)

```json
{
  "message": "Invalid public api key",
  "error": "Unauthorized",
  "statusCode": 401
}
```

***

## Best Practices

* Ensure **agent phone number** is valid before making the request.