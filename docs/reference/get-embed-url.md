Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Embed Chat Conversation

Returns a ready-to-use iframe URL that opens a specific WhatsApp conversation for a given contact's phone number directly inside your application. The contact's phone number is never exposed in the returned URL.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/embed/url": {
      "post": {
        "operationId": "get-embed-url",
        "summary": "Embed Chat Conversation",
        "description": "Returns a ready-to-use iframe URL that opens a specific WhatsApp conversation for a given contact's phone number directly inside your application. The contact's phone number is never exposed in the returned URL.",
        "tags": [
          "Embed"
        ],
        "parameters": [],
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/GetEmbedUrlDto"
              }
            }
          }
        },
        "responses": {
          "200": {
            "description": "Success",
            "content": {
              "application/json": {
                "schema": {
                  "type": "object",
                  "properties": {
                    "url": {
                      "type": "string",
                      "description": "Full embed URL. Use directly as the src of an iframe.",
                      "example": "https://web.doubletick.io/embed/conversations/intg_xxx/customer_xxx?showChatListPanel=false&showCustomerDetails=false&showSidebar=false"
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
                      "example": "phone must be a valid phone number"
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
            "description": "NotFound",
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
                      "example": "No connected WhatsApp integration found"
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
      "GetEmbedUrlDto": {
        "type": "object",
        "required": [
          "phone"
        ],
        "properties": {
          "phone": {
            "type": "string",
            "description": "WhatsApp phone number of the contact in international format. No + or spaces.",
            "example": "919876543210"
          },
          "integrationId": {
            "type": "string",
            "description": "DoubleTick integration ID (intg_xxx). If omitted, the primary connected WhatsApp integration is used automatically.",
            "example": "intg_xxx"
          },
          "wabaNumber": {
            "type": "string",
            "description": "WhatsApp Business Account Number",
            "example": "919000000000"
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