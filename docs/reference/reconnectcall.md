Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Reconnect call

Reconnects an active WhatsApp call.

# OpenAPI definition

```json
{
  "openapi": "3.0.3",
  "info": {
    "title": "WhatsApp Call Public API",
    "description": "Public APIs for initiating, receiving, and managing WhatsApp voice calls.",
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
      "CallId": {
        "type": "string",
        "example": "call_abc123"
      }
    }
  },
  "paths": {
    "/whatsapp/call/reconnect": {
      "post": {
        "summary": "Reconnect call",
        "description": "Reconnects an active WhatsApp call.",
        "operationId": "reconnectCall",
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "type": "object",
                "required": [
                  "callId",
                  "callOriginType"
                ],
                "properties": {
                  "callId": {
                    "$ref": "#/components/schemas/CallId"
                  },
                  "callOriginType": {
                    "type": "string",
                    "enum": [
                      "INCOMING",
                      "OUTGOING"
                    ]
                  },
                  "sdp": {
                    "type": "string"
                  },
                  "deviceId": {
                    "type": "string"
                  },
                  "switchCall": {
                    "type": "boolean"
                  }
                }
              }
            }
          }
        },
        "responses": {
          "200": {
            "description": "Call reconnected"
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
      "1": 123,
      "2": 151,
      "3": 64,
      "4": 190,
      "5": 51,
      "6": 233,
      "7": 175,
      "8": 209,
      "9": 201,
      "10": 151,
      "11": 217
    }
  }
}
```