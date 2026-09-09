Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Get paginated groups

Returns a list of paginated groups based on search criteria and pagination parameters

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/groups": {
      "get": {
        "summary": "Get paginated groups",
        "description": "Returns a list of paginated groups based on search criteria and pagination parameters",
        "tags": [
          "Broadcast Groups"
        ],
        "operationId": "get-paginated-groups-v2",
        "parameters": [
          {
            "in": "query",
            "name": "searchQuery",
            "schema": {
              "type": "string",
              "description": "Search query",
              "example": "group name"
            },
            "description": "The search query to filter groups"
          },
          {
            "in": "query",
            "name": "orderBy",
            "schema": {
              "type": "string",
              "description": "Order by",
              "example": "NAME",
              "enum": [
                "NAME",
                "DATE_CREATED"
              ]
            },
            "description": "The field to order the groups by"
          },
          {
            "in": "query",
            "name": "format",
            "schema": {
              "type": "string",
              "description": "Order format",
              "example": "ASCENDING",
              "enum": [
                "ASCENDING",
                "DESCENDING",
                "ascending",
                "descending"
              ]
            },
            "description": "The order format to apply to the groups"
          },
          {
            "in": "query",
            "name": "afterGroupId",
            "schema": {
              "type": "string",
              "description": "Group ID after which the groups are to be fetched",
              "example": "group_id"
            },
            "description": "The group ID to fetch the groups after"
          },
          {
            "in": "query",
            "name": "afterGroupName",
            "schema": {
              "type": "string",
              "description": "Group name after which the groups are to be fetched",
              "example": "group_name"
            },
            "description": "The group name to fetch the groups after"
          },
          {
            "in": "query",
            "name": "afterDateCreated",
            "schema": {
              "type": "string",
              "format": "date-time",
              "description": "Group date created after which the groups are to be fetched",
              "example": "2020-01-01T00:00:00.000Z"
            },
            "description": "The group creation date to fetch the groups after"
          }
        ],
        "responses": {
          "200": {
            "description": "OK",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/GetPaginatedGroupsResponseDto"
                }
              }
            }
          },
          "400": {
            "description": "Bad request, invalid input provided"
          },
          "401": {
            "description": "Invalid or missing API key"
          }
        },
        "security": [
          {
            "basic": []
          }
        ]
      }
    }
  },
  "info": {
    "title": "DoubleTick Public APIs",
    "description": "",
    "version": "2.0",
    "contact": {}
  },
  "servers": [
    {
      "url": "https://public.doubletick.io"
    }
  ],
  "components": {
    "securitySchemes": {
      "basic": {
        "type": "apiKey",
        "name": "Authorization",
        "in": "header",
        "description": "Public API key."
      }
    },
    "schemas": {
      "GetGroupsPaginationParams": {
        "type": "object",
        "properties": {
          "afterGroupId": {
            "type": "string",
            "description": "Group ID after which the groups are to be fetched",
            "example": "group_id"
          },
          "afterGroupName": {
            "type": "string",
            "description": "Group name after which the groups are to be fetched",
            "example": "group_name"
          },
          "afterDateCreated": {
            "type": "string",
            "description": "Group date created after which the groups are to be fetched",
            "example": "2020-01-01T00:00:00.000Z"
          }
        }
      },
      "GetPaginatedGroupsResponseDto": {
        "type": "object",
        "properties": {
          "groups": {
            "description": "Array of groups",
            "type": "array",
            "items": {
              "$ref": "#/components/schemas/GetGroupResponse"
            }
          },
          "paginationParams": {
            "description": "Pagination parameters",
            "allOf": [
              {
                "$ref": "#/components/schemas/GetGroupsPaginationParams"
              }
            ]
          }
        }
      },
      "GetGroupResponse": {
        "type": "object",
        "properties": {
          "groupId": {
            "type": "string",
            "description": "Group ID",
            "example": "group_id"
          },
          "groupChatId": {
            "type": "string",
            "description": "Group Chat ID",
            "example": "group_chat_id"
          },
          "groupChatName": {
            "type": "string",
            "description": "Group chat name",
            "example": "group_name"
          },
          "memberCount": {
            "type": "string",
            "description": "Total members in the group",
            "example": 10
          },
          "canManageGroupAccess": {
            "type": "boolean",
            "description": "Boolean to check if the user can manage group access",
            "example": true
          },
          "groupAccessLevel": {
            "type": "string",
            "description": "The level of access the user has to the group",
            "example": "NO_ACCESS",
            "enum": [
              "FULL_ACCESS",
              "SEND_ONLY_ACCESS",
              "NO_ACCESS"
            ]
          }
        }
      }
    }
  },
  "x-readme": {
    "explorer-enabled": true,
    "proxy-enabled": true
  },
  "_id": {
    "buffer": {
      "0": 99,
      "1": 154,
      "2": 196,
      "3": 207,
      "4": 67,
      "5": 147,
      "6": 180,
      "7": 0,
      "8": 57,
      "9": 36,
      "10": 231,
      "11": 202
    }
  }
}
```