Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Delete WhatsApp group

Deletes a WhatsApp group by its groupId.

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
      "DeleteGroupResponse": {
        "type": "object",
        "properties": {
          "success": {
            "type": "boolean",
            "example": true
          }
        }
      }
    }
  },
  "paths": {
    "/whatsapp/wa-group": {
      "delete": {
        "summary": "Delete WhatsApp group",
        "description": "Deletes a WhatsApp group by its groupId.",
        "operationId": "deleteWhatsappGroup",
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
            "description": "Group deleted successfully",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/DeleteGroupResponse"
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