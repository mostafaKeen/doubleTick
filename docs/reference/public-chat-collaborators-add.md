Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Add chat collaborators

Adds team members to a chat as collaborators. Identify the chat with `from` (your WhatsApp Business number) and `to` (the customer number). `agentNumbers` must be phone numbers of users in your organization; they are matched after normalizing format (e.g. stripping + and spaces). You must have access to read the chat. The response reports per-number success and failure.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/collaborators/add": {
      "post": {
        "operationId": "public-chat-collaborators-add",
        "summary": "Add chat collaborators",
        "description": "Adds team members to a chat as collaborators. Identify the chat with `from` (your WhatsApp Business number) and `to` (the customer number). `agentNumbers` must be phone numbers of users in your organization; they are matched after normalizing format (e.g. stripping + and spaces). You must have access to read the chat. The response reports per-number success and failure.",
        "tags": [
          "Collaborators"
        ],
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/PublicChatCollaboratorsAddRemoveBodyDto"
              }
            }
          }
        },
        "responses": {
          "200": {
            "description": "Collaborators updated (partial success allowed).",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/PublicChatCollaboratorsAddRemoveResponseDto"
                }
              }
            }
          },
          "400": {
            "description": "Bad request — invalid payload, chat could not be resolved, or validation failed."
          },
          "401": {
            "description": "Unauthorized"
          },
          "403": {
            "description": "Forbidden"
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
      "PublicChatCollaboratorsAddRemoveBodyDto": {
        "type": "object",
        "properties": {
          "from": {
            "type": "string",
            "description": "WABA / WhatsApp Business number for the integration (matched against Integration.wabaNumber or credentials phone; digits only compared).",
            "example": "919999999999"
          },
          "to": {
            "type": "string",
            "description": "Customer phone number (matched against Customer.phoneNumber; digits only compared).",
            "example": "919999999999"
          },
          "agentNumbers": {
            "type": "array",
            "minItems": 1,
            "items": {
              "type": "string"
            },
            "description": "Phone numbers of agents to add or remove. Each must resolve to an org team member via User.phone."
          }
        },
        "required": [
          "from",
          "to",
          "agentNumbers"
        ]
      },
      "PublicChatCollaboratorsAddRemoveResponseDto": {
        "type": "object",
        "properties": {
          "success": {
            "type": "array",
            "description": "Inputs that resolved to a team member (phone + display name).",
            "items": {
              "type": "object",
              "properties": {
                "number": {
                  "type": "string",
                  "example": "+919999999999"
                },
                "name": {
                  "type": "string",
                  "nullable": true,
                  "example": "Agent Name"
                }
              }
            }
          },
          "failed": {
            "type": "array",
            "description": "Inputs that did not resolve to an org team member (empty number, or no matching User.phone).",
            "items": {
              "type": "object",
              "properties": {
                "number": {
                  "type": "string",
                  "example": "9999999999"
                }
              }
            }
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