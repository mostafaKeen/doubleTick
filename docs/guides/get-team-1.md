Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Get Team

Retrieve a complete list of team members, including their roles and reporting hierarchy, using the DoubleTick API for efficient team management. 🚀

## API Endpoint

Use the following endpoint to retrieve the team details:

```
GET https://public.doubletick.io/team
```

***

## Request Headers

```json
{
  "Authorization": "YOUR_API_KEY"
}
```

***

## Response

### Success Response (<<glossary:201>>)

```json
{
    "data": [
        {
            "id": "user_id",
            "name": "agent_name",
            "phone": "agent_number",
            "email": "agent_email_address",
            "joinDate": "2024-11-26T11:02:31.855Z",
            "reportingManager": [
                {
                    "id": "manager_id",
                    "name": "manager_name",
                    "phone": "manager_number",
                    "email": "manager_email",
                    "joinDate": "2023-08-25T13:14:26.037Z"
                }
            ],
            "orgRoleName": "TEAM_LEAD",
            "orgRoleId": "organization_role_id",
            "isOrganizationOwner": false
        }
    ]
}
```

* `data` (array): List of agents in the organization.
  * `id` (string): Unique identifier of the agent.
  * `name` (string): Name of the agent.
  * `phone` (string): Phone number of the agent.
  * `email` (string): Email address of the agent.
  * `joinDate` (string): Date and time when the agent joined (ISO format).
  * `reportingManager` (array): List of managers assigned to the agent.
    * `id` (string): Unique identifier of the reporting manager.
    * `name` (string): Name of the reporting manager.
    * `phone` (string): Phone number of the reporting manager.
    * `email` (string): Email address of the reporting manager.
    * `joinDate` (string): Date and time when the manager joined (<<glossary:ISO format>>).
  * `orgRoleName` (string): Role of the agent within the organization (e.g., `"TEAM_LEAD"`).
  * `orgRoleId` (string): Unique identifier for the organization role.
  * `isOrganizationOwner` (boolean): Indicates whether the agent is the owner of the organization (`true` or `false`).

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

* Regularly update team information to maintain accuracy.
* Use the **Get Team** API to sync your internal database with the latest team hierarchy.