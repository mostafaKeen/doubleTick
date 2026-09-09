Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Create Quick Reply

Create a quick reply

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/quick-replies": {
      "post": {
        "operationId": "create-quick-reply-v2",
        "summary": "Create Quick Reply",
        "description": "Create a quick reply",
        "tags": [
          "Quick Replies"
        ],
        "parameters": [],
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "type": "object",
                "properties": {
                  "shortcut": {
                    "type": "string",
                    "description": "Shortcut for quick reply",
                    "example": "/welcome"
                  },
                  "accessTo": {
                    "type": "string",
                    "description": "Visibility scope",
                    "example": "everyone"
                  },
                  "rawMessage": {
                    "type": "object",
                    "description": "Quick reply payload",
                    "example": {
                      "messageType": "text",
                      "text": "Hello {{customer_name}}, welcome to DoubleTick!"
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
          "201": {
            "description": "Quick reply created"
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