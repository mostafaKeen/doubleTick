Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Send Whatsapp Order (Payment) Message

Send Whatsapp Order (Payment) Message

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/whatsapp/message/order": {
      "post": {
        "operationId": "outgoing-messages-whatsapp-order",
        "summary": "Send Whatsapp Order (Payment) Message",
        "description": "Send Whatsapp Order (Payment) Message",
        "tags": [
          "Outgoing Messages"
        ],
        "parameters": [],
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/SendWhatsappOrderMessageDto"
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
      "OrderMessageImageHeaderDto": {
        "type": "object",
        "properties": {
          "link": {
            "type": "string",
            "description": "Public image URL for the order thumbnail header (Meta only supports image headers on order_details).",
            "example": "https://example.com/thumb.jpg"
          }
        },
        "required": [
          "link"
        ]
      },
      "OrderDetailsInputAddressDto": {
        "type": "object",
        "properties": {
          "addressLine1": {
            "type": "string",
            "example": "12 MG Road"
          },
          "addressLine2": {
            "type": "string",
            "example": "Near Central Mall"
          },
          "city": {
            "type": "string",
            "example": "Bengaluru"
          },
          "state": {
            "type": "string",
            "example": "Karnataka"
          },
          "countryCode": {
            "type": "string",
            "example": "IN"
          },
          "postalCode": {
            "type": "string",
            "example": "560001"
          },
          "zoneCode": {
            "type": "string",
            "example": "KA"
          }
        },
        "required": [
          "addressLine1",
          "city",
          "countryCode",
          "postalCode"
        ]
      },
      "OrderDetailsInputItemDto": {
        "type": "object",
        "properties": {
          "name": {
            "type": "string",
            "maxLength": 60,
            "example": "Goa 3N/4D Package"
          },
          "amount": {
            "type": "number",
            "description": "Readable price per item (e.g. 2.00). Backend converts to Meta value/offset.",
            "example": 8
          },
          "quantity": {
            "type": "integer",
            "minimum": 1,
            "example": 1
          },
          "saleAmount": {
            "type": "number",
            "description": "Optional discounted per-item price.",
            "example": 2
          },
          "retailerId": {
            "type": "string",
            "example": "SKU-GOA-01"
          },
          "countryOfOrigin": {
            "type": "string",
            "example": "IN"
          },
          "importerName": {
            "type": "string",
            "example": "Acme Imports"
          },
          "importerAddress": {
            "$ref": "#/components/schemas/OrderDetailsInputAddressDto"
          }
        },
        "required": [
          "name",
          "amount",
          "quantity"
        ]
      },
      "OrderDetailsInputDto": {
        "type": "object",
        "properties": {
          "configurationName": {
            "type": "string",
            "description": "Payment configuration name on the WABA. Required for the gateway path. For a upi_vpa config, the backend auto-generates the upi_intent_link from the config when no upiIntentLink is supplied. Omit only when passing upiIntentLink / paymentLink directly.",
            "example": "doubletick_payments_test"
          },
          "upiIntentLink": {
            "type": "string",
            "description": "Optional UPI intent link (upi://pay?…). Sent as payment_settings type \"upi_intent_link\".",
            "example": "upi://pay?pa=merchant@bank&pn=Merchant&mc=5411&tr=TEST123&purpose=00"
          },
          "paymentLink": {
            "type": "string",
            "description": "Optional hosted checkout URL (https://…). Sent as payment_settings type \"payment_link\"; takes precedence over upiIntentLink. Domain must be Meta-enabled.",
            "example": "https://checkout.example.com/pay/abc123"
          },
          "referenceId": {
            "type": "string",
            "maxLength": 35,
            "description": "Unique order reference (max 35 chars); generated if omitted.",
            "example": "pay_abc123xyz"
          },
          "provider": {
            "type": "string",
            "description": "Payment provider. Gateway types (razorpay/payu/zaakpay/billdesk) are emitted as the Meta payment_gateway type. Use upi_vpa for a dynamic-VPA config — the backend resolves it to a upi_intent_link and no gateway type is sent to Meta.",
            "enum": [
              "razorpay",
              "payu",
              "zaakpay",
              "billdesk",
              "upi_vpa"
            ],
            "example": "razorpay"
          },
          "currency": {
            "type": "string",
            "minLength": 3,
            "maxLength": 3,
            "description": "ISO 4217 code (Meta India payments support INR).",
            "example": "INR"
          },
          "type": {
            "type": "string",
            "enum": [
              "digital-goods",
              "physical-goods"
            ],
            "example": "digital-goods"
          },
          "items": {
            "type": "array",
            "minItems": 1,
            "items": {
              "$ref": "#/components/schemas/OrderDetailsInputItemDto"
            }
          },
          "tax": {
            "type": "number",
            "minimum": 0,
            "example": 0
          },
          "taxDescription": {
            "type": "string",
            "example": "GST"
          },
          "discount": {
            "type": "number",
            "minimum": 0,
            "example": 1
          },
          "shipping": {
            "type": "number",
            "minimum": 0,
            "example": 0
          },
          "catalogId": {
            "type": "string",
            "example": "123456789"
          }
        },
        "required": [
          "items"
        ]
      },
      "SendWhatsappOrderMessageContentDto": {
        "type": "object",
        "properties": {
          "header": {
            "description": "Optional image thumbnail header.",
            "allOf": [
              {
                "$ref": "#/components/schemas/OrderMessageImageHeaderDto"
              }
            ]
          },
          "body": {
            "type": "string",
            "description": "Body text of the order message.",
            "example": "Great pick! Review the details and pay to confirm your booking."
          },
          "footer": {
            "type": "string",
            "example": "Secure payment via WhatsApp"
          },
          "orderDetails": {
            "$ref": "#/components/schemas/OrderDetailsInputDto"
          }
        },
        "required": [
          "body",
          "orderDetails"
        ]
      },
      "SendWhatsappOrderMessageDto": {
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
            "$ref": "#/components/schemas/SendWhatsappOrderMessageContentDto"
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