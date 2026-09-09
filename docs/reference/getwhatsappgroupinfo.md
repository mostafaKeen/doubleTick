Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Get WhatsApp group info

Returns information about a WhatsApp group by its groupId.

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
      "GroupInfoResponse": {
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
      }
    }
  },
  "paths": {
    "/whatsapp/wa-group/info": {
      "get": {
        "summary": "Get WhatsApp group info",
        "description": "Returns information about a WhatsApp group by its groupId.",
        "operationId": "getWhatsappGroupInfo",
        "parameters": [
          {
            "name": "groupId",
            "in": "query",
            "required": true,
            "schema": {
              "type": "string"
            },
            "description": "Unique identifier for the group"
          }
        ],
        "responses": {
          "200": {
            "description": "Group info retrieved successfully",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/GroupInfoResponse"
                }
              }
            }
          },
          "404": {
            "description": "Group not found for the given groupId"
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