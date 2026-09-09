Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Send Whatsapp Template Message

Send Whatsapp Template Message

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/whatsapp/message/template": {
      "post": {
        "operationId": "outgoing-messages-whatsapp-template",
        "summary": "Send Whatsapp Template Message",
        "description": "Send Whatsapp Template Message",
        "tags": [
          "Outgoing Messages"
        ],
        "parameters": [],
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/SendWhatsappTemplateMessagesDto"
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
      "SendWhatsappTemplateMessageDto": {
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
            "description": "The phone number to which the message is sent (with country code). Omit when sending to a group using groupId.",
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
            "description": "Template message to be sent",
            "example": {
              "templateName": "text_template",
              "language": "en",
              "templateData": {
                "header": {
                  "type": "TEXT",
                  "placeholder": "Saurav"
                },
                "body": {
                  "placeholders": [
                    "Saurav",
                    "https://saurav.catalog.to"
                  ]
                }
              }
            },
            "allOf": [
              {
                "$ref": "#/components/schemas/SendTemplateMessageContentDto"
              }
            ]
          },
          "customData": {
            "type": "object",
            "description": "Arbitrary key-value pairs (string values only) stored against this message. Each entry is spread at the root level of outgoing webhooks: status update webhooks (sent / delivered / read / failed) and received-message webhooks when a customer replies to this template.",
            "additionalProperties": {
              "type": "string"
            },
            "example": {
              "orderId": "12345",
              "campaignId": "summer-sale"
            }
          }
        },
        "required": [
          "content"
        ]
      },
      "SendWhatsappTemplateMessagesDto": {
        "type": "object",
        "properties": {
          "messages": {
            "description": "Array of template messages to be sent",
            "type": "array",
            "items": {
              "$ref": "#/components/schemas/SendWhatsappTemplateMessageDto"
            }
          }
        },
        "required": [
          "messages"
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