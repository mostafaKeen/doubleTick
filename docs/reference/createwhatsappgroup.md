Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Create WhatsApp group

Creates a new WhatsApp group on the given WABA number.

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
      "GroupId": {
        "type": "string",
        "example": "wagrp_abc123"
      },
      "JoinApprovalMode": {
        "type": "string",
        "enum": [
          "auto_approve",
          "approval_required"
        ]
      },
      "CreateGroupRequest": {
        "type": "object",
        "required": [
          "wabaNumber",
          "subject"
        ],
        "properties": {
          "wabaNumber": {
            "type": "string",
            "description": "WABA number on which the group will be created"
          },
          "subject": {
            "type": "string",
            "description": "Name of the WhatsApp group"
          },
          "description": {
            "type": "string",
            "description": "Optional group description"
          },
          "joinApprovalMode": {
            "$ref": "#/components/schemas/JoinApprovalMode",
            "description": "Join approval mode for the group"
          },
          "groupId": {
            "type": "string",
            "description": "Your unique group identifier. If omitted, one will be generated for you."
          }
        }
      },
      "CreateGroupResponse": {
        "type": "object",
        "properties": {
          "success": {
            "type": "boolean",
            "example": true
          },
          "groupId": {
            "$ref": "#/components/schemas/GroupId",
            "description": "Unique identifier for the created group"
          },
          "wabaNumber": {
            "type": "string",
            "description": "WABA number associated with the group"
          }
        }
      }
    }
  },
  "paths": {
    "/whatsapp/wa-group/create": {
      "post": {
        "summary": "Create WhatsApp group",
        "description": "Creates a new WhatsApp group on the given WABA number.",
        "operationId": "createWhatsappGroup",
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/CreateGroupRequest"
              }
            }
          }
        },
        "responses": {
          "200": {
            "description": "Group creation initiated",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/CreateGroupResponse"
                }
              }
            }
          },
          "422": {
            "description": "Business rule failure (e.g., unsupported integration, messaging tier too low, limits reached)"
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