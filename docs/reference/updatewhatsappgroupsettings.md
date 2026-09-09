Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Update WhatsApp group settings

Updates the settings (subject, description, groupId) of an existing WhatsApp group identified by its groupId.

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
      "UpdateGroupSettingsRequest": {
        "type": "object",
        "required": [
          "wabaNumber"
        ],
        "properties": {
          "wabaNumber": {
            "type": "string",
            "description": "WABA number associated with the group"
          },
          "subject": {
            "type": "string",
            "maxLength": 128,
            "description": "New group subject/name"
          },
          "description": {
            "type": "string",
            "maxLength": 2048,
            "description": "New group description"
          },
          "groupId": {
            "type": "string",
            "maxLength": 50,
            "description": "New groupId to assign"
          }
        }
      },
      "UpdateGroupSettingsResponse": {
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
    "/whatsapp/wa-group/settings": {
      "post": {
        "summary": "Update WhatsApp group settings",
        "description": "Updates the settings (subject, description, groupId) of an existing WhatsApp group identified by its groupId.",
        "operationId": "updateWhatsappGroupSettings",
        "parameters": [
          {
            "name": "groupId",
            "in": "query",
            "required": true,
            "schema": {
              "type": "string"
            },
            "description": "Unique identifier of the group to update"
          }
        ],
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/UpdateGroupSettingsRequest"
              }
            }
          }
        },
        "responses": {
          "200": {
            "description": "Group settings updated successfully",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/UpdateGroupSettingsResponse"
                }
              }
            }
          },
          "400": {
            "description": "Invalid fields or no integration found for the given WABA number"
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