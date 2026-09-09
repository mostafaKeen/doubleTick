Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Get chat messages for a customer of WABA number

Retrieve chat messages sent by the specified WhatsApp Business Account (WABA) number, with optional date filtering.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/chat-messages": {
      "get": {
        "operationId": "get-chat-messages",
        "summary": "Get chat messages for a customer of WABA number",
        "description": "Retrieve chat messages sent by the specified WhatsApp Business Account (WABA) number, with optional date filtering.",
        "tags": [
          "Chat Messages"
        ],
        "parameters": [
          {
            "name": "wabaNumber",
            "in": "query",
            "required": true,
            "description": "The WhatsApp Business Account (WABA) number",
            "schema": {
              "type": "string",
              "example": "11234567890"
            }
          },
          {
            "name": "customerNumber",
            "in": "query",
            "required": true,
            "description": "Customer's phone number",
            "schema": {
              "type": "string",
              "example": "911234567890"
            }
          },
          {
            "name": "startDate",
            "in": "query",
            "required": false,
            "description": "The start date for the chat messages (DD-MM-YYYY)",
            "schema": {
              "type": "string",
              "example": "01-01-2024"
            }
          },
          {
            "name": "endDate",
            "in": "query",
            "required": false,
            "description": "The end date for the chat messages (DD-MM-YYYY)",
            "schema": {
              "type": "string",
              "example": "31-12-2024"
            }
          }
        ],
        "responses": {
          "200": {
            "description": "Success",
            "content": {
              "application/json": {
                "schema": {
                  "type": "object",
                  "properties": {
                    "success": {
                      "type": "boolean",
                      "example": true
                    },
                    "messages": {
                      "type": "array",
                      "items": {
                        "type": "object",
                        "properties": {
                          "messageId": {
                            "type": "string",
                            "example": "1234567890"
                          },
                          "sender": {
                            "type": "string",
                            "example": "19876543210"
                          },
                          "recipient": {
                            "type": "string",
                            "example": "919876543210"
                          },
                          "message": {
                            "type": "string",
                            "example": "Hello, how can I help you?"
                          },
                          "mediaUrl": {
                            "type": "string",
                            "example": "https://example.com/media/abc.jpg"
                          },
                          "timestamp": {
                            "type": "string",
                            "format": "date-time",
                            "example": "2024-01-01T10:00:00Z"
                          },
                          "status": {
                            "type": "string",
                            "example": "delivered"
                          }
                        }
                      }
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
                      "example": "Bad Request"
                    },
                    "error": {
                      "type": "string",
                      "example": "Error Text"
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
                      "example": "Invalid public api key"
                    },
                    "error": {
                      "type": "string",
                      "example": "Unauthorized"
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