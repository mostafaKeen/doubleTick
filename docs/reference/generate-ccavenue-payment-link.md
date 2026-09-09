Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Generate CCAvenue Payment Link

Generates a CCAvenue payment link for a UPI payment configuration, with optional pre-filling of the customer's billing details. Returns the payment link and its reference ID — include both in the order details message so the payment can be tracked.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/ccavenue/payment-link": {
      "post": {
        "operationId": "generate-ccavenue-payment-link",
        "summary": "Generate CCAvenue Payment Link",
        "description": "Generates a CCAvenue payment link for a UPI payment configuration, with optional pre-filling of the customer's billing details. Returns the payment link and its reference ID — include both in the order details message so the payment can be tracked.",
        "tags": [
          "Payments"
        ],
        "parameters": [],
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/GenerateCCAvenuePaymentLinkDto"
              }
            }
          }
        },
        "responses": {
          "201": {
            "description": "",
            "content": {
              "application/json": {
                "schema": {
                  "type": "object",
                  "properties": {
                    "paymentLink": {
                      "type": "string",
                      "description": "CCAvenue hosted payment URL to send as orderDetails.paymentLink"
                    },
                    "referenceId": {
                      "type": "string",
                      "description": "Order reference baked into the link; send as orderDetails.referenceId"
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
      "GenerateCCAvenuePaymentLinkDto": {
        "type": "object",
        "properties": {
          "configurationName": {
            "type": "string",
            "description": "Payment configuration name on the WABA (must have CCAvenue credentials attached)."
          },
          "referenceId": {
            "type": "string",
            "description": "Unique order reference (max 35 chars, [A-Za-z0-9_.-]); generated if omitted. Pass this same value as orderDetails.referenceId in the order_details send.",
            "maxLength": 35
          },
          "items": {
            "type": "array",
            "description": "Line items — must match the items sent in the order_details message so the WhatsApp total equals the CCAvenue charge.",
            "items": {
              "type": "object",
              "properties": {
                "name": {
                  "type": "string",
                  "maxLength": 60
                },
                "amount": {
                  "type": "number",
                  "description": "Readable ₹ price per item, e.g. 934.00"
                },
                "quantity": {
                  "type": "number",
                  "description": "Quantity (integer >= 1)"
                },
                "saleAmount": {
                  "type": "number",
                  "description": "Discounted ₹ price per item"
                }
              },
              "required": [
                "name",
                "amount",
                "quantity"
              ]
            }
          },
          "tax": {
            "type": "number",
            "description": "Readable ₹ tax"
          },
          "shipping": {
            "type": "number",
            "description": "Readable ₹ shipping"
          },
          "discount": {
            "type": "number",
            "description": "Readable ₹ discount"
          },
          "billing": {
            "type": "object",
            "description": "Optional pre-fill of the CCAvenue billing page.",
            "properties": {
              "name": {
                "type": "string"
              },
              "email": {
                "type": "string"
              },
              "phone": {
                "type": "string"
              },
              "addressLine": {
                "type": "string"
              },
              "city": {
                "type": "string"
              },
              "state": {
                "type": "string"
              },
              "zip": {
                "type": "string"
              },
              "country": {
                "type": "string"
              }
            }
          }
        },
        "required": [
          "configurationName",
          "items"
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