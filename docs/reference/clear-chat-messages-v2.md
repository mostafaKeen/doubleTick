Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Clear chat messages

Clear all chat messages for a chat (async)

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/conversation/chat/messages": {
      "delete": {
        "operationId": "clear-chat-messages-v2",
        "summary": "Clear chat messages",
        "description": "Clear all chat messages for a chat (async)",
        "tags": [
          "Conversation"
        ],
        "parameters": [],
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/ClearChatMessagesBodyDtoV2"
              }
            }
          }
        },
        "responses": {
          "200": {
            "description": "Chat cleared (or not found).",
            "content": {
              "application/json": {
                "schema": {
                  "type": "object",
                  "properties": {
                    "success": {
                      "type": "boolean",
                      "example": true
                    },
                    "message": {
                      "type": "string",
                      "example": "Added in queue for deletion."
                    }
                  }
                }
              }
            }
          },
          "400": {
            "description": "Incorrect payload"
          },
          "401": {
            "description": "Unauthorized"
          },
          "403": {
            "description": "Forbidden"
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
    },
    "schemas": {
      "ClearChatMessagesBodyDtoV2": {
        "type": "object",
        "properties": {
          "to": {
            "type": "string",
            "example": "919999999999"
          },
          "from": {
            "type": "string",
            "example": "919999999999"
          }
        },
        "required": [
          "to",
          "from"
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