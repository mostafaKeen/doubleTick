Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Get Paginated Broadcast Groups

Retrieve and manage WhatsApp broadcast groups efficiently using the DoubleTick API with advanced filtering, sorting, and pagination support. 🚀

## API Endpoint

Use the following endpoint to retrieve paginated broadcast groups:

```
GET https://public.doubletick.io/groups
```

***

## Request Headers

```json
{
  "Authorization": "YOUR_API_KEY",
  "Content-Type": "application/json"
}
```

## Query Parameters

| Parameter          | Type      | Option                | Description                                       |
| ------------------ | --------- | :-------------------- | ------------------------------------------------- |
| `searchQuery`      | string    | *group\_name*         | The search query to filter groups                 |
| `orderBy`          | string    | NAME, DATE\_CREATED   | The field to order the groups by                  |
| `format`           | string    | ASCENDING, DESCENDING | The order format to apply to the groups           |
| `afterGroupId`     | string    | *group\_id*           | The group ID to fetch the groups after            |
| `afterGroupName`   | string    | *group\_name*         | The group name to fetch the groups after          |
| `afterDateCreated` | date-time | *YYYY-MM-DDTHH:MM:SS* | The group creation date to fetch the groups after |

***

## Response

### Success Response (<<glossary:201>>)

```json
{
  "groups": [
    {
      "groupId": "group_id",
      "groupChatName": "broadcast_group_name",
      "memberCount": 5,
      "dateCreated": 1737568528728,
      "lastMessageAt": "2025-01-23T10:04:35.188Z",
      "groupType": "STATIC_SEGMENT",
      "isInternal": false
    }
  ],
  "paginationParams": {
    "hasMore": false
  }
}
```

* `groups` (array): Contains broadcast group details.
  * `groupId` (string): Unique identifier of the group.
  * `groupChatName` (string): Name of the broadcast group.
  * `memberCount` (number): Number of members in the group.
  * `dateCreated` (number): Timestamp of when the group was created (in milliseconds).
  * `lastMessageAt` (string): Date and time of the last message sent in the group (ISO format).
  * `groupType` (string): Type of group, e.g., `"STATIC_SEGMENT"`.
  * `isInternal` (boolean): Indicates whether the group is internal.

* `paginationParams` (object): Contains pagination details.
  * `hasMore` (boolean): Indicates if more groups are available to fetch.

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

***

## Best Practices

* Use pagination (`afterGroupId`, `afterDateCreated`) for handling large lists efficiently.
* Keep track of **group IDs** for future operations such as adding members or sending messages.
* Store retrieved data securely for business insights and reporting.