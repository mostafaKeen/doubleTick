Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# List WhatsApp groups

Returns a paginated list of WhatsApp groups for the given WABA number. Uses cursor-based pagination — pass `lastGroupId` and `olderThanTimestamp` from the previous response to fetch the next page.

# OpenAPI definition

```json
{
  "openapi": "3.0.3",
  "info": {
    "title": "WhatsApp Group Public API",
    "description": "Public APIs for creating and managing WhatsApp groups.",
    "version": "1.0.0"
  },
  "servers": [
    {
      "url": "https://public.doubletick.io",
      "description": "Production"
    }
  ],
  "security": [
    {
      "ApiKeyAuth": []
    }
  ],
  "components": {
    "securitySchemes": {
      "ApiKeyAuth": {
        "type": "apiKey",
        "in": "header",
        "name": "x-public-api-key"
      }
    },
    "schemas": {
      "GroupListItem": {
        "type": "object",
        "properties": {
          "groupId": {
            "type": "string",
            "description": "Unique identifier for the group"
          },
          "subject": {
            "type": "string",
            "description": "Group name/subject"
          },
          "description": {
            "type": "string",
            "description": "Group description"
          },
          "totalParticipantCount": {
            "type": "integer",
            "description": "Current number of participants in the group"
          },
          "inviteLink": {
            "type": "string",
            "nullable": true,
            "description": "WhatsApp invite link for the group"
          },
          "status": {
            "type": "string",
            "description": "Current group status (e.g. ACTIVE)"
          },
          "groupCreatedAt": {
            "type": "string",
            "format": "date-time",
            "description": "ISO 8601 timestamp of when the group was created"
          }
        }
      },
      "ListGroupsResponse": {
        "type": "object",
        "properties": {
          "groups": {
            "type": "array",
            "items": {
              "$ref": "#/components/schemas/GroupListItem"
            }
          },
          "hasMore": {
            "type": "boolean",
            "description": "Whether more pages are available"
          },
          "lastGroupId": {
            "type": "string",
            "nullable": true,
            "description": "Cursor — pass as lastGroupId in the next request to fetch the next page"
          },
          "olderThanTimestamp": {
            "type": "string",
            "format": "date-time",
            "nullable": true,
            "description": "Cursor timestamp — pass in the next request along with lastGroupId to fetch the next page"
          }
        }
      }
    }
  },
  "paths": {
    "/whatsapp/wa-group/list": {
      "get": {
        "summary": "List WhatsApp groups",
        "description": "Returns a paginated list of WhatsApp groups for the given WABA number. Uses cursor-based pagination — pass `lastGroupId` and `olderThanTimestamp` from the previous response to fetch the next page.",
        "operationId": "listWhatsappGroups",
        "parameters": [
          {
            "name": "wabaNumber",
            "in": "query",
            "required": true,
            "schema": {
              "type": "string"
            },
            "description": "WABA number to list groups for"
          },
          {
            "name": "lastGroupId",
            "in": "query",
            "required": false,
            "schema": {
              "type": "string"
            },
            "description": "groupId of the last group from the previous page (pagination cursor)"
          },
          {
            "name": "olderThanTimestamp",
            "in": "query",
            "required": false,
            "schema": {
              "type": "string",
              "format": "date-time"
            },
            "description": "ISO 8601 timestamp — only return groups created before this time (pagination cursor)"
          }
        ],
        "responses": {
          "200": {
            "description": "Paginated list of groups",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/ListGroupsResponse"
                }
              }
            }
          },
          "400": {
            "description": "Missing wabaNumber or no integration found for the given WABA number"
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
      "0": 105,
      "1": 178,
      "2": 248,
      "3": 135,
      "4": 237,
      "5": 154,
      "6": 193,
      "7": 161,
      "8": 200,
      "9": 32,
      "10": 222,
      "11": 4
    }
  }
}
```