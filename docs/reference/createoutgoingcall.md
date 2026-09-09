Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Create outgoing call

Initiates an outgoing WhatsApp voice call to a customer.

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
    }
  },
  "paths": {
    "/whatsapp/call/create": {
      "post": {
        "summary": "Create outgoing call",
        "description": "Initiates an outgoing WhatsApp voice call to a customer.",
        "operationId": "createOutgoingCall",
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "type": "object",
                "required": [
                  "from",
                  "to",
                  "sdp",
                  "sdpType"
                ],
                "properties": {
                  "from": {
                    "type": "string",
                    "description": "WABA number to call from"
                  },
                  "to": {
                    "type": "string",
                    "description": "Customer phone number in E.164 format"
                  },
                  "sdp": {
                    "type": "string",
                    "description": "WebRTC SDP offer"
                  },
                  "sdpType": {
                    "type": "string",
                    "example": "offer"
                  },
                  "deviceId": {
                    "type": "string"
                  }
                }
              }
            }
          }
        },
        "responses": {
          "200": {
            "description": "Call initiated"
          },
          "422": {
            "description": "Business rule failure"
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