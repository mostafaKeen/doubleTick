Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Remove participant from WhatsApp group

Removes a participant from a WhatsApp group by their phone number.

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
      "RemoveParticipantResponse": {
        "type": "object",
        "properties": {
          "messaging_product": {
            "type": "string",
            "example": "whatsapp"
          },
          "request_id": {
            "type": "string",
            "example": "ABCDEFGHIJKLMNOPQRSTUVWXYZ012345"
          }
        }
      }
    }
  },
  "paths": {
    "/whatsapp/wa-group/participants": {
      "delete": {
        "summary": "Remove participant from WhatsApp group",
        "description": "Removes a participant from a WhatsApp group by their phone number.",
        "operationId": "removeWhatsappGroupParticipant",
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
            "name": "participantPhone",
            "in": "query",
            "required": true,
            "schema": {
              "type": "string"
            },
            "description": "Phone number of the participant to remove (e.g. +15551230000)"
          }
        ],
        "responses": {
          "200": {
            "description": "Participant removed successfully",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/RemoveParticipantResponse"
                }
              }
            }
          },
          "404": {
            "description": "Group not found or group creation is still pending"
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