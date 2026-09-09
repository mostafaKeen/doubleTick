Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Get WhatsApp group participants

Returns a paginated list of participants for a specific WhatsApp group. Uses cursor-based pagination — pass `lastPhoneNumber` and `olderThanTimestamp` from the previous response to fetch the next page.

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
      "Participant": {
        "type": "object",
        "properties": {
          "name": {
            "type": "string",
            "nullable": true,
            "description": "Participant display name"
          },
          "phoneNumber": {
            "type": "string",
            "description": "Participant phone number"
          },
          "dateJoined": {
            "type": "string",
            "format": "date-time",
            "nullable": true,
            "description": "When the participant joined the group"
          },
          "dateRemoved": {
            "type": "string",
            "format": "date-time",
            "nullable": true,
            "description": "When the participant was removed (null if still active)"
          },
          "dateLeft": {
            "type": "string",
            "format": "date-time",
            "nullable": true,
            "description": "When the participant left (null if still active)"
          }
        }
      },
      "ListParticipantsResponse": {
        "type": "object",
        "properties": {
          "participants": {
            "type": "array",
            "items": {
              "$ref": "#/components/schemas/Participant"
            }
          },
          "hasMore": {
            "type": "boolean",
            "description": "Whether more pages are available"
          },
          "lastPhoneNumber": {
            "type": "string",
            "nullable": true,
            "description": "Cursor — pass as lastPhoneNumber in the next request to fetch the next page"
          },
          "olderThanTimestamp": {
            "type": "string",
            "format": "date-time",
            "nullable": true,
            "description": "Cursor timestamp — pass in the next request along with lastPhoneNumber to fetch the next page"
          }
        }
      }
    }
  },
  "paths": {
    "/whatsapp/wa-group/participants": {
      "get": {
        "summary": "Get WhatsApp group participants",
        "description": "Returns a paginated list of participants for a specific WhatsApp group. Uses cursor-based pagination — pass `lastPhoneNumber` and `olderThanTimestamp` from the previous response to fetch the next page.",
        "operationId": "getWhatsappGroupParticipants",
        "parameters": [
          {
            "name": "groupId",
            "in": "query",
            "required": true,
            "schema": {
              "type": "string"
            },
            "description": "Unique identifier for the group"
          },
          {
            "name": "lastPhoneNumber",
            "in": "query",
            "required": false,
            "schema": {
              "type": "string"
            },
            "description": "Phone number of the last participant from the previous page (pagination cursor)"
          },
          {
            "name": "olderThanTimestamp",
            "in": "query",
            "required": false,
            "schema": {
              "type": "string",
              "format": "date-time"
            },
            "description": "ISO 8601 timestamp — only return participants who joined before this time (pagination cursor)"
          }
        ],
        "responses": {
          "200": {
            "description": "Paginated list of participants",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/ListParticipantsResponse"
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