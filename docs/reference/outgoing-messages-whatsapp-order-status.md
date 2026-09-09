Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Send Whatsapp Order Status Message

Send Whatsapp Order Status Message

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/whatsapp/message/order/status": {
      "post": {
        "operationId": "outgoing-messages-whatsapp-order-status",
        "summary": "Send Whatsapp Order Status Message",
        "description": "Send Whatsapp Order Status Message",
        "tags": [
          "Outgoing Messages"
        ],
        "parameters": [],
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/SendWhatsappOrderStatusMessageDto"
              }
            }
          }
        },
        "responses": {
          "201": {
            "description": ""
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
    },
    "schemas": {
      "OrderStatusInputDto": {
        "type": "object",
        "properties": {
          "referenceId": {
            "type": "string",
            "description": "Reference id of the order (the reference_id used in the order_details message).",
            "example": "pay_abc123xyz"
          },
          "status": {
            "type": "string",
            "enum": [
              "pending",
              "processing",
              "partially_shipped",
              "shipped",
              "completed",
              "canceled"
            ],
            "description": "Fulfillment status to transition the order to.",
            "example": "completed"
          },
          "description": {
            "type": "string",
            "description": "Optional status description.",
            "example": "Booking confirmed"
          }
        },
        "required": [
          "referenceId",
          "status"
        ]
      },
      "SendWhatsappOrderStatusContentDto": {
        "type": "object",
        "properties": {
          "body": {
            "type": "string",
            "maxLength": 1024,
            "description": "Body text of the order status message.",
            "example": "Your booking is confirmed"
          },
          "footer": {
            "type": "string",
            "example": "Thanks for choosing us"
          },
          "orderStatus": {
            "$ref": "#/components/schemas/OrderStatusInputDto"
          }
        },
        "required": [
          "body",
          "orderStatus"
        ]
      },
      "SendWhatsappOrderStatusMessageDto": {
        "type": "object",
        "properties": {
          "to": {
            "type": "string",
            "description": "The phone number to which the message is sent, with the country code.",
            "example": "9199999999",
            "minLength": 10,
            "maxLength": 15,
            "format": "phone"
          },
          "from": {
            "type": "string",
            "description": "The WABA phone number the message is sent from, with the country code.",
            "example": "9199999999",
            "minLength": 10,
            "maxLength": 15,
            "format": "phone"
          },
          "messageId": {
            "type": "string",
            "description": "Message ID (UUID v4). Generated if not provided.",
            "example": "uuid-v4",
            "minLength": 36,
            "maxLength": 36
          },
          "content": {
            "$ref": "#/components/schemas/SendWhatsappOrderStatusContentDto"
          }
        },
        "required": [
          "content"
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