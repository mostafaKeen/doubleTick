Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Get Quick Replies

Get quick replies with optional cursor pagination

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/quick-replies": {
      "get": {
        "operationId": "get-quick-replies-v2",
        "summary": "Get Quick Replies",
        "description": "Get quick replies with optional cursor pagination",
        "tags": [
          "Quick Replies"
        ],
        "parameters": [
          {
            "in": "query",
            "name": "accessTo",
            "schema": {
              "type": "string",
              "example": "everyone"
            },
            "required": false
          },
          {
            "in": "query",
            "name": "searchTerm",
            "schema": {
              "type": "string",
              "example": "welcome"
            },
            "required": false
          },
          {
            "in": "query",
            "name": "lastQuickReplyDate",
            "schema": {
              "type": "string",
              "format": "date-time",
              "example": "2026-04-08T10:00:00.000Z"
            },
            "required": false
          }
        ],
        "responses": {
          "200": {
            "description": "Quick replies list"
          },
          "400": {
            "description": "Incorrect payload"
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