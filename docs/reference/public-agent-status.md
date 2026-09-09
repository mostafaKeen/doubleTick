Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Set agent ONLINE/OFFLINE

Updates an agent's availability to online or offline. The agent is selected by `phone` (must match the phone stored on the user profile). Requires Agent Availability to be enabled for the organization. When status changes, an `ASSIGNED_AGENT_STATUS_CHANGED` webhook may be sent per WhatsApp integration where the user has assigned chats.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/agent/status": {
      "post": {
        "operationId": "public-agent-status",
        "summary": "Set agent ONLINE/OFFLINE",
        "description": "Updates an agent's availability to online or offline. The agent is selected by `phone` (must match the phone stored on the user profile). Requires Agent Availability to be enabled for the organization. When status changes, an `ASSIGNED_AGENT_STATUS_CHANGED` webhook may be sent per WhatsApp integration where the user has assigned chats.",
        "tags": [
          "Agent"
        ],
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/PublicAgentStatusBodyDto"
              }
            }
          }
        },
        "responses": {
          "200": {
            "description": "Status updated.",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/PublicAgentStatusResponseDto"
                }
              }
            }
          },
          "400": {
            "description": "Invalid status, or Agent Availability not enabled for the org."
          },
          "401": {
            "description": "Unauthorized"
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
      "PublicAgentStatusBodyDto": {
        "type": "object",
        "properties": {
          "status": {
            "type": "string",
            "enum": [
              "ONLINE",
              "OFFLINE"
            ],
            "description": "Target availability for the resolved user."
          },
          "phone": {
            "type": "string",
            "description": "Agent phone number; matched digit-only against `User.phone` for a user in `UserOrganization` for this org.",
            "example": "919999999999"
          }
        },
        "required": [
          "status",
          "phone"
        ]
      },
      "PublicAgentStatusResponseDto": {
        "type": "object",
        "properties": {
          "success": {
            "type": "boolean",
            "example": true
          },
          "previousStatus": {
            "type": "string",
            "nullable": true,
            "description": "Prior UserOrganization.status before this update (if any)."
          }
        },
        "required": [
          "success"
        ]
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