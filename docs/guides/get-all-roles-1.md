Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Get All Roles

Retrieve all available roles with the DoubleTick API, enabling comprehensive role-based access control and permission management. 🔍✅

## API Endpoint

Use the following endpoint to fetch all roles:

```
GET https://public.doubletick.io/roles
```

***

## Request Headers

```json
{
  "Authorization": "YOUR_API_KEY",
  "Content-Type": "application/json"
}
```

***

## Response

### Success Response (<<glossary:201>>)

```json
[
  {
    "id": "role_id",
    "name": "role_name",
    "title": "role_title",
    "description": "role_description",
    "isCustomRole": false,
    "type": "ROLE_TYPE",
    "permissions": [
      {
        "id": "permissions_related_to_roles",
        "name": "permission_name",
        "description": "permission_description"
      },
      {
        "id": "permissions_related_to_roles",
        "name": "permission_name",
        "description": "permission_description"
      }
    ]
  }
]
```

* `id` (string): Unique identifier for the role.
* `name` (string): Name of the role.
* `title` (string): Title associated with the role.
* `description` (string): Detailed description of the role.
* `isCustomRole` (boolean): Indicates if the role is a custom-created role.
* `type` (string): Specifies the type of role.
* `permissions` (array): List of permissions associated with the role.
  * `id` (string): Unique identifier for the permission.
  * `name` (string): Name of the permission.
  * `description` (string): Description of what the permission allows.

### Bad Request (<<glossary:400>>)

```json JSON
{
  "message": "error_message",
  "error": "Bad Request",
  "statusCode": 400
}
```

### Unauthorized (<<glossary:401>>)

```json
{
  "message": "Invalid public api key",
  "error": "Unauthorized",
  "statusCode": 401
}
```

### Unprocessable Entity (<<glossary:422>>)

```json
{
  "message": "invalid file type for audio: text/html; charset=utf-8",
  "error": "Unprocessable Entity",
  "statusCode": 422
}
```

***

## Best Practices

* Use role IDs to manage permissions programmatically.
* Regularly fetch and review roles for compliance and security.