Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Send Whatsapp Location Message

Send Whatsapp Location Message

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/whatsapp/message/location": {
      "post": {
        "operationId": "outgoing-messages-whatsapp-location",
        "summary": "Send Whatsapp Location Message",
        "description": "Send Whatsapp Location Message",
        "tags": [
          "Outgoing Messages"
        ],
        "parameters": [],
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/SendWhatsappLocationMessageDto"
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
      "SendLocationMessageContentDto": {
        "type": "object",
        "properties": {
          "latitude": {
            "type": "number",
            "description": "The latitude of the location",
            "example": 19.082502
          },
          "longitude": {
            "type": "number",
            "description": "The longitude of the location",
            "example": 72.7163737
          },
          "name": {
            "type": "string",
            "description": "The name of the location",
            "example": "Mumbai"
          },
          "address": {
            "type": "string",
            "description": "The address of the location",
            "example": "Mumbai, Maharashtra, India"
          }
        },
        "required": [
          "latitude",
          "longitude"
        ]
      },
      "SendWhatsappLocationMessageDto": {
        "type": "object",
        "properties": {
          "from": {
            "type": "string",
            "description": "The phone number from which the message is sent, with the country code.",
            "example": "+919999999999",
            "minLength": 10,
            "maxLength": 15,
            "format": "phone"
          },
          "to": {
            "type": "string",
            "description": "The phone number to which the message is sent, with the country code. Required when not using groupId.",
            "example": "+919999999999",
            "minLength": 10,
            "maxLength": 15,
            "format": "phone"
          },
          "groupId": {
            "type": "string",
            "description": "The unique identifier of the WhatsApp group to send the message to. Mutually exclusive with \"to\".",
            "example": "grp_XYZ"
          },
          "messageId": {
            "type": "string",
            "description": "Message ID (UUID v4) to be used for the message. If not provided, a random UUID v4 will be generated",
            "example": "uuid-v4",
            "minLength": 36,
            "maxLength": 36
          },
          "content": {
            "description": "Location message to be sent",
            "example": {
              "latitude": 19.082502,
              "longitude": 72.7163737,
              "name": "Mumbai",
              "address": "Mumbai, Maharashtra, India"
            },
            "allOf": [
              {
                "$ref": "#/components/schemas/SendLocationMessageContentDto"
              }
            ]
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