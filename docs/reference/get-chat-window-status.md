Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Get chat window status

Get chat window status for a customer with given WABA number

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/chat/status": {
      "get": {
        "operationId": "get-chat-window-status",
        "summary": "Get chat window status",
        "description": "Get chat window status for a customer with given WABA number",
        "tags": [
          "Customer"
        ],
        "parameters": [
          {
            "name": "customerPhoneNumber",
            "in": "query",
            "required": true,
            "description": "The customer's phone number",
            "schema": {
              "type": "string",
              "example": "+919999999999"
            }
          },
          {
            "name": "wabaNumber",
            "in": "query",
            "required": true,
            "description": "The WhatsApp Business Account (WABA) number",
            "schema": {
              "type": "string",
              "example": "+919999999998"
            }
          }
        ],
        "responses": {
          "200": {
            "description": "Chat window status details",
            "content": {
              "application/json": {
                "schema": {
                  "type": "object",
                  "properties": {
                    "customerPhoneNumber": {
                      "type": "string",
                      "example": "+919999999999"
                    },
                    "wabaNumber": {
                      "type": "string",
                      "example": "+919999999998"
                    },
                    "isOpen": {
                      "type": "boolean",
                      "example": true
                    }
                  }
                }
              }
            }
          },
          "400": {
            "description": "Incorrect payload",
            "content": {
              "application/json": {
                "schema": {
                  "type": "object",
                  "properties": {
                    "statusCode": {
                      "type": "number",
                      "example": 400
                    },
                    "message": {
                      "type": "string",
                      "example": "Error Text"
                    },
                    "error": {
                      "type": "string",
                      "example": "Bad Request"
                    }
                  }
                }
              }
            }
          },
          "401": {
            "description": "Unauthorized",
            "content": {
              "application/json": {
                "schema": {
                  "type": "object",
                  "properties": {
                    "statusCode": {
                      "type": "number",
                      "example": 401
                    },
                    "message": {
                      "type": "string",
                      "example": "Unauthorized"
                    },
                    "error": {
                      "type": "string",
                      "example": "Unauthorized"
                    }
                  }
                }
              }
            }
          },
          "404": {
            "description": "Chat or WABA number not found",
            "content": {
              "application/json": {
                "schema": {
                  "type": "object",
                  "properties": {
                    "statusCode": {
                      "type": "number",
                      "example": 404
                    },
                    "message": {
                      "type": "string",
                      "example": "Chat or waba number not found"
                    },
                    "error": {
                      "type": "string",
                      "example": "Not Found"
                    }
                  }
                }
              }
            }
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