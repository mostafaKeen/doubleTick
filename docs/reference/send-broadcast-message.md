Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Send Template Whatsapp Message to Broadcast Group

Send a template WhatsApp message to a broadcast group. When using placeholders in your message, please follow the format outlined below:

- {{Name}}: Used to include the name of the customer.
- {{Phone Number}}: Used to include the phone number of the customer.
- {{<Custom Field Name>}}: Used to include custom fields. This will only function correctly if the custom field has been set for all customers within the broadcast group.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/whatsapp/message/broadcast": {
      "post": {
        "tags": [
          "Broadcast Groups"
        ],
        "summary": "Send Template Whatsapp Message to Broadcast Group",
        "description": "Send a template WhatsApp message to a broadcast group. When using placeholders in your message, please follow the format outlined below:\n\n- {{Name}}: Used to include the name of the customer.\n- {{Phone Number}}: Used to include the phone number of the customer.\n- {{<Custom Field Name>}}: Used to include custom fields. This will only function correctly if the custom field has been set for all customers within the broadcast group.",
        "operationId": "send-broadcast-message",
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/SendWhatsAppBroadcastRequestDto"
              }
            }
          }
        },
        "responses": {
          "200": {
            "description": "OK",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/SendWhatsAppBroadcastResponseDto"
                }
              }
            }
          },
          "400": {
            "description": "Bad request, invalid input provided"
          },
          "401": {
            "description": "Invalid or missing API key"
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
      "SendTemplateMessageContentTemplateDataHeaderDto": {
        "type": "object",
        "properties": {
          "type": {
            "type": "string",
            "description": "Template header type",
            "enum": [
              "TEXT",
              "IMAGE",
              "DOCUMENT",
              "VIDEO",
              "LOCATION"
            ],
            "example": "TEXT"
          },
          "placeholder": {
            "type": "string",
            "description": "Template header text",
            "example": "Header text"
          },
          "mediaUrl": {
            "type": "string",
            "description": "Template header media link (only for IMAGE, VIDEO or DOCUMENT type)",
            "example": "https://example.com/image.png"
          },
          "filename": {
            "type": "string",
            "description": "Template header document caption",
            "example": "Document caption"
          },
          "latitude": {
            "type": "number",
            "description": "Template header location latitude",
            "example": -23.5505199
          },
          "longitude": {
            "type": "number",
            "description": "Template header location longitude",
            "example": -46.6333094
          }
        }
      },
      "SendTemplateMessageContentTemplateDataBodyDto": {
        "type": "object",
        "properties": {
          "placeholders": {
            "description": "Template body components",
            "example": [
              "Body text"
            ],
            "type": "array",
            "items": {
              "type": "string"
            }
          }
        },
        "required": [
          "placeholders"
        ]
      },
      "SendTemplateMessageContentTemplateDataButtonDto": {
        "type": "object",
        "properties": {
          "type": {
            "type": "string",
            "description": "Template button type",
            "enum": [
              "URL"
            ],
            "example": "URL"
          },
          "parameter": {
            "type": "string",
            "description": "Template button text (Required only when the URL is dynamic)",
            "example": "Button text"
          }
        },
        "required": [
          "type"
        ]
      },
      "SendTemplateMessageContentTemplateCarouselCardsDto": {
        "type": "object",
        "properties": {
          "cardIndex": {
            "type": "number",
            "description": "Index of the card - 0 Represents the first card",
            "example": "0"
          },
          "components": {
            "type": "object",
            "description": "Carousel cards components",
            "properties": {
              "header": {
                "description": "Carousel card header",
                "type": "object",
                "properties": {
                  "type": {
                    "type": "string",
                    "description": "Carousel card header type",
                    "enum": [
                      "IMAGE",
                      "VIDEO"
                    ],
                    "example": "IMAGE"
                  },
                  "mediaUrl": {
                    "type": "string",
                    "description": "Carousel card header media link (only for IMAGE, VIDEO type)",
                    "example": "https://example.com/image.png"
                  }
                }
              },
              "body": {
                "description": "Carousel card body",
                "allOf": [
                  {
                    "$ref": "#/components/schemas/SendTemplateMessageContentTemplateDataBodyDto"
                  }
                ]
              },
              "buttons": {
                "description": "Carousel card buttons",
                "type": "array",
                "items": {
                  "$ref": "#/components/schemas/SendTemplateMessageContentTemplateDataButtonDto"
                }
              }
            }
          }
        },
        "required": [
          "type"
        ]
      },
      "SendTemplateMessageContentTemplateDataDto": {
        "type": "object",
        "properties": {
          "header": {
            "description": "Template header",
            "allOf": [
              {
                "$ref": "#/components/schemas/SendTemplateMessageContentTemplateDataHeaderDto"
              }
            ]
          },
          "body": {
            "description": "Template body",
            "allOf": [
              {
                "$ref": "#/components/schemas/SendTemplateMessageContentTemplateDataBodyDto"
              }
            ]
          },
          "buttons": {
            "description": "Template buttons",
            "type": "array",
            "items": {
              "$ref": "#/components/schemas/SendTemplateMessageContentTemplateDataButtonDto"
            }
          },
          "cards": {
            "description": "Template carousel cards",
            "type": "array",
            "items": {
              "$ref": "#/components/schemas/SendTemplateMessageContentTemplateCarouselCardsDto"
            }
          }
        }
      },
      "SendTemplateMessageContentDto": {
        "type": "object",
        "properties": {
          "templateName": {
            "type": "string",
            "description": "Template name",
            "example": "template_name"
          },
          "language": {
            "type": "string",
            "description": "Template language",
            "example": "en",
            "default": "en",
            "enum": [
              "en",
              "en_US",
              "en_GB",
              "pt_BR",
              "es",
              "id"
            ]
          },
          "templateData": {
            "description": "Template data",
            "allOf": [
              {
                "$ref": "#/components/schemas/SendTemplateMessageContentTemplateDataDto"
              }
            ]
          }
        },
        "required": [
          "templateName",
          "language"
        ]
      },
      "SendWhatsAppBroadcastRequestDto": {
        "type": "object",
        "properties": {
          "groupName": {
            "type": "string",
            "description": "The name of the WhatsApp group to send the message to",
            "example": "Test Group"
          },
          "from": {
            "type": "string",
            "description": "The phone number from which the message is sent, with the country code",
            "example": "+919999999999",
            "minLength": 10,
            "maxLength": 15,
            "format": "phone"
          },
          "content": {
            "$ref": "#/components/schemas/SendTemplateMessageContentDto"
          }
        },
        "required": [
          "groupName"
        ]
      },
      "SendWhatsAppBroadcastResponseDto": {
        "type": "object",
        "properties": {
          "status": {
            "type": "string",
            "description": "The status of the sent message (SENT or FAILED)",
            "example": "SENT",
            "enum": [
              "SENT",
              "FAILED"
            ]
          },
          "messageId": {
            "type": "string",
            "description": "The ID of the sent message",
            "example": "uuid-v4"
          },
          "errorMessage": {
            "type": "string",
            "description": "The error message in case of a failed message",
            "example": "Sample Error"
          }
        },
        "required": [
          "status",
          "messageId"
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