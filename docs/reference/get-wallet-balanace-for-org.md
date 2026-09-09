Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Get wallet balance

Get wallet balance for Org

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/wallet/balance": {
      "get": {
        "summary": "Get wallet balance",
        "description": "Get wallet balance for Org",
        "tags": [
          "Wallet"
        ],
        "operationId": "get-wallet-balanace-for-org",
        "responses": {
          "200": {
            "description": "OK",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/GetWalletBalanceForOrgReponseDto"
                }
              }
            }
          },
          "401": {
            "description": "Invalid or missing API key"
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
      "GetWalletBalanceForOrgReponseDto": {
        "type": "object",
        "properties": {
          "currencyCode": {
            "type": "string",
            "description": "Currency code",
            "example": "INR"
          },
          "balance": {
            "type": "number",
            "description": "The wallet balance",
            "example": "3000"
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