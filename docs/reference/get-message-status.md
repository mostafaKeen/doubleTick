Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Get Message Status

Get the current delivery status of a message using the `messageId` returned by the send message / send template APIs.

Rate limited to 1 request per second, per organisation.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/whatsapp/message/status": {
      "get": {
        "operationId": "get-message-status",
        "summary": "Get Message Status",
        "description": "Get the current delivery status of a message using the `messageId` returned by the send message / send template APIs.\n\nRate limited to 1 request per second, per organisation.",
        "tags": [
          "Outgoing Messages"
        ],
        "parameters": [
          {
            "in": "query",
            "name": "messageId",
            "required": true,
            "description": "The `messageId` returned by the send message / send template APIs.",
            "schema": {
              "type": "string",
              "format": "uuid",
              "example": "c2973bf3-5f7a-4f30-a927-57cfb0c66ad3"
            }
          }
        ],
        "responses": {
          "200": {
            "description": "Delivery timestamps for the message",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/messageStatusResponse"
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
                      "type": "array",
                      "items": {
                        "type": "string",
                        "example": "messageId must be a UUID"
                      }
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
                    }
                  }
                }
              }
            }
          },
          "404": {
            "description": "No message found with this messageId",
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
                      "example": "No message found with this messageId"
                    },
                    "error": {
                      "type": "string",
                      "example": "Not Found"
                    }
                  }
                }
              }
            }
          },
          "429": {
            "description": "Too Many Requests: Rate Limit Exceeded",
            "content": {
              "application/json": {
                "schema": {
                  "type": "object",
                  "properties": {
                    "statusCode": {
                      "type": "number",
                      "example": 429
                    },
                    "message": {
                      "type": "string",
                      "example": "Too Many Requests: Rate Limit Exceeded"
                    },
                    "error": {
                      "type": "string",
                      "example": "Too Many Requests"
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
    },
    "schemas": {
      "messageStatusResponse": {
        "type": "object",
        "properties": {
          "messageId": {
            "type": "string",
            "format": "uuid",
            "example": "c2973bf3-5f7a-4f30-a927-57cfb0c66ad3"
          },
          "sentAt": {
            "type": "string",
            "format": "date-time",
            "nullable": true,
            "description": "Each timestamp stays `null` until the matching WhatsApp status webhook lands, so an all-null response means the message was accepted but not acknowledged yet.",
            "example": "2026-09-02T09:14:22.104Z"
          },
          "deliveredAt": {
            "type": "string",
            "format": "date-time",
            "nullable": true,
            "example": "2026-09-02T09:14:25.910Z"
          },
          "readAt": {
            "type": "string",
            "format": "date-time",
            "nullable": true,
            "example": "2026-09-02T09:14:31.482Z"
          },
          "repliedAt": {
            "type": "string",
            "format": "date-time",
            "nullable": true,
            "description": "When the customer replied to this message, if they did.",
            "example": null
          },
          "failedAt": {
            "type": "string",
            "format": "date-time",
            "nullable": true,
            "example": null
          },
          "failedReason": {
            "type": "string",
            "description": "Present only when the message failed.",
            "example": "Message failed to send because more than 24 hours have passed since the customer last replied to this number."
          },
          "errorCode": {
            "type": "string",
            "description": "WhatsApp error code, present only when the message failed.",
            "example": "131047"
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