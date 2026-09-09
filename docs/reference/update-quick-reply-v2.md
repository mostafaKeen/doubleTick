Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Update Quick Reply

Update a quick reply by shortcut

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/quick-replies/{shortcut}": {
      "put": {
        "operationId": "update-quick-reply-v2",
        "summary": "Update Quick Reply",
        "description": "Update a quick reply by shortcut",
        "tags": [
          "Quick Replies"
        ],
        "parameters": [
          {
            "in": "path",
            "name": "shortcut",
            "required": true,
            "schema": {
              "type": "string",
              "example": "welcome"
            }
          }
        ],
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "type": "object",
                "properties": {
                  "shortcut": {
                    "type": "string",
                    "example": "/welcome-updated"
                  },
                  "accessTo": {
                    "type": "string",
                    "example": "everyone"
                  },
                  "rawMessage": {
                    "type": "object",
                    "example": {
                      "messageType": "text",
                      "text": "Hi {{customer_name}}, your query has been received."
                    }
                  }
                },
                "required": [
                  "shortcut",
                  "rawMessage"
                ]
              }
            }
          }
        },
        "responses": {
          "200": {
            "description": "Quick reply updated"
          },
          "400": {
            "description": "Incorrect payload"
          },
          "401": {
            "description": "Unauthorized"
          },
          "404": {
            "description": "NotFound"
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