Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Send Whatsapp Interactive Button Message

Send Whatsapp Interactive Button Message

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/whatsapp/message/interactive": {
      "post": {
        "operationId": "outgoing-messages-whatsapp-interactive",
        "summary": "Send Whatsapp Interactive Button Message",
        "description": "Send Whatsapp Interactive Button Message",
        "tags": [
          "Outgoing Messages"
        ],
        "parameters": [],
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/SendWhatsappInteractiveMessageDto"
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
      "SendWhatsappInteractiveMessageDto": {
        "type": "object",
        "properties": {
          "from": {
            "type": "string",
            "description": "The phone number from which the message is sent, with the country code",
            "example": "+919999999999",
            "minLength": 10,
            "maxLength": 15,
            "format": "phone"
          },
          "to": {
            "type": "string",
            "description": "The phone number to which the message is sent, with the country code",
            "example": "+919999999999",
            "minLength": 10,
            "maxLength": 15,
            "format": "phone"
          },
          "messageId": {
            "type": "string",
            "description": "Message ID (UUID v4) to be used for the message. If not provided, a random UUID v4 will be generated",
            "example": "uuid-v4",
            "minLength": 36,
            "maxLength": 36
          },
          "content": {
            "description": "Text message to be sent",
            "oneOf": [
              {
                "$ref": "#/components/schemas/InteractiveReplyButtonsContent"
              },
              {
                "$ref": "#/components/schemas/InteractiveCtaButtonContent"
              }
            ]
          }
        },
        "required": [
          "to",
          "from",
          "content"
        ]
      },
      "InteractiveReplyButtonsContent": {
        "title": "Reply buttons",
        "type": "object",
        "properties": {
          "header": {
            "type": "string",
            "description": "The header text to be sent",
            "maxLength": 60,
            "example": "Header text"
          },
          "body": {
            "type": "string",
            "description": "The body of the message to be sent",
            "maxLength": 1024,
            "example": "Message body"
          },
          "footer": {
            "type": "string",
            "description": "The footer text to be sent",
            "maxLength": 20,
            "example": "Footer text"
          },
          "buttons": {
            "type": "array",
            "description": "Array of reply buttons to be send",
            "items": {
              "$ref": "#/components/schemas/SendInteractiveMessageButtonDto"
            }
          }
        },
        "required": [
          "body",
          "buttons"
        ]
      },
      "InteractiveCtaButtonContent": {
        "title": "URL button",
        "type": "object",
        "properties": {
          "header": {
            "type": "string",
            "description": "The header text to be sent",
            "maxLength": 60,
            "example": "Header text"
          },
          "body": {
            "type": "string",
            "description": "The body of the message to be sent",
            "maxLength": 1024,
            "example": "Message body"
          },
          "footer": {
            "type": "string",
            "description": "The footer text to be sent",
            "maxLength": 20,
            "example": "Footer text"
          },
          "ctaButton": {
            "description": "URL button to send",
            "allOf": [
              {
                "$ref": "#/components/schemas/SendInteractiveMessageCtaUrlButtonDto"
              }
            ]
          }
        },
        "required": [
          "body",
          "ctaButton"
        ]
      },
      "SendInteractiveMessageButtonDto": {
        "type": "object",
        "properties": {
          "id": {
            "type": "string",
            "description": "A unique identifier",
            "maxLength": 60,
            "example": "uuid-v4"
          },
          "title": {
            "type": "string",
            "description": "The title of button to be displayed",
            "maxLength": 20,
            "example": "Button title"
          }
        },
        "required": [
          "id",
          "title"
        ]
      },
      "SendInteractiveMessageCtaUrlButtonDto": {
        "type": "object",
        "properties": {
          "display_text": {
            "type": "string",
            "description": "Visible URL button label (Max 20 chars)",
            "maxLength": 20,
            "example": "See Dates"
          },
          "url": {
            "type": "string",
            "description": "URL which will be opened when clicked on button",
            "example": "https://example.com/schedule"
          }
        },
        "required": [
          "display_text",
          "url"
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