Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Get call permissions

Returns WhatsApp call permission and eligibility for a customer.

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
    "/whatsapp/call/call-permissions": {
      "get": {
        "summary": "Get call permissions",
        "description": "Returns WhatsApp call permission and eligibility for a customer.",
        "operationId": "getCallPermissions",
        "parameters": [
          {
            "name": "wabaNumber",
            "in": "query",
            "required": true,
            "schema": {
              "type": "string"
            }
          },
          {
            "name": "customerId",
            "in": "query",
            "required": true,
            "schema": {
              "type": "string"
            }
          },
          {
            "name": "client",
            "in": "query",
            "required": false,
            "schema": {
              "type": "string"
            }
          },
          {
            "name": "version",
            "in": "query",
            "required": false,
            "schema": {
              "type": "string"
            }
          }
        ],
        "responses": {
          "200": {
            "description": "Permission status returned"
          },
          "400": {
            "description": "Invalid request"
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