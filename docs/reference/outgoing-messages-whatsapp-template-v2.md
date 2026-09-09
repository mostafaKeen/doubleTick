Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Send Whatsapp Template Message (V2)

Send Whatsapp template messages. Requires to, from (WABA number), templateName, language, templateData. Body placeholders must be key-value pairs (objects), not strings. e.g. [{ "name": "John" }].

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/v2/whatsapp/message/template": {
      "post": {
        "operationId": "outgoing-messages-whatsapp-template-v2",
        "summary": "Send Whatsapp Template Message (V2)",
        "description": "Send Whatsapp template messages. Requires to, from (WABA number), templateName, language, templateData. Body placeholders must be key-value pairs (objects), not strings. e.g. [{ \"name\": \"John\" }].",
        "tags": [
          "Outgoing Messages"
        ],
        "parameters": [],
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/SendWhatsappTemplateMessagesDtoV2"
              }
            }
          }
        },
        "responses": {
          "201": {
            "description": "Success",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/SendWhatsappTemplateMessageResponseV2"
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
      "SendTemplateMessageContentTemplateDataHeaderDtoV2": {
        "type": "object",
        "properties": {
          "type": {
            "type": "string",
            "description": "Template header type",
            "enum": [
              "TEXT",
              "IMAGE",
              "DOCUMENT",
              "VIDEO"
            ],
            "example": "TEXT"
          },
          "mediaUrl": {
            "type": "string",
            "description": "Media link (IMAGE, VIDEO, DOCUMENT)",
            "example": "https://example.com/image.png"
          }
        }
      },
      "SendTemplateMessageContentTemplateDataBodyDtoV2": {
        "type": "object",
        "properties": {
          "placeholders": {
            "description": "Key-value pairs only. One object per variable e.g. [{ \"name\": \"John\" }, { \"url\": \"https://x.com\" }]",
            "type": "array",
            "items": {
              "type": "object",
              "additionalProperties": {
                "type": "string"
              },
              "example": {
                "name": "John"
              }
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
      "SendTemplateMessageContentTemplateCarouselCardsDtoV2": {
        "type": "object",
        "properties": {
          "cardIndex": {
            "type": "number",
            "description": "Index of the card",
            "example": 0
          },
          "components": {
            "type": "object",
            "properties": {
              "header": {
                "type": "object",
                "properties": {
                  "type": {
                    "type": "string",
                    "enum": [
                      "IMAGE",
                      "VIDEO"
                    ]
                  },
                  "mediaUrl": {
                    "type": "string"
                  }
                }
              },
              "body": {
                "description": "Card body. Placeholders: key-value pairs only.",
                "allOf": [
                  {
                    "$ref": "#/components/schemas/SendTemplateMessageContentTemplateDataBodyDtoV2"
                  }
                ]
              },
              "buttons": {
                "type": "array",
                "items": {
                  "$ref": "#/components/schemas/SendTemplateMessageContentTemplateDataButtonDto"
                }
              }
            }
          }
        }
      },
      "SendTemplateMessageContentTemplateDataDtoV2": {
        "type": "object",
        "properties": {
          "header": {
            "description": "Template header (type, mediaUrl)",
            "allOf": [
              {
                "$ref": "#/components/schemas/SendTemplateMessageContentTemplateDataHeaderDtoV2"
              }
            ]
          },
          "body": {
            "description": "Template body. Placeholders must be key-value pairs, not strings.",
            "allOf": [
              {
                "$ref": "#/components/schemas/SendTemplateMessageContentTemplateDataBodyDtoV2"
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
            "description": "Carousel cards. Card body placeholders: key-value pairs only.",
            "type": "array",
            "items": {
              "$ref": "#/components/schemas/SendTemplateMessageContentTemplateCarouselCardsDtoV2"
            }
          }
        }
      },
      "SendTemplateMessageContentDtoV2": {
        "type": "object",
        "properties": {
          "templateName": {
            "type": "string",
            "example": "template_name"
          },
          "language": {
            "type": "string",
            "enum": [
              "en",
              "en_US",
              "en_GB",
              "pt_BR",
              "es",
              "id"
            ],
            "example": "en"
          },
          "templateData": {
            "allOf": [
              {
                "$ref": "#/components/schemas/SendTemplateMessageContentTemplateDataDtoV2"
              }
            ]
          }
        },
        "required": [
          "templateName",
          "language"
        ]
      },
      "SendWhatsappTemplateMessagesDtoV2": {
        "type": "object",
        "properties": {
          "messages": {
            "description": "Array of template messages. Each requires to, from, content.templateName, content.language, content.templateData",
            "type": "array",
            "items": {
              "$ref": "#/components/schemas/SendWhatsappTemplateMessageDtoV2"
            }
          },
          "byPassMediaUrlValidation": {
            "type": "boolean",
            "description": "Bypass media URL validation",
            "example": false
          }
        },
        "required": [
          "messages"
        ]
      },
      "SendWhatsappTemplateMessageDtoV2": {
        "type": "object",
        "properties": {
          "from": {
            "type": "string",
            "description": "WABA number (with country code). Required when not using groupId.",
            "example": "+919999999999",
            "minLength": 10,
            "maxLength": 15
          },
          "to": {
            "type": "string",
            "description": "Recipient phone number with country code. Required when not using groupId.",
            "example": "+919999999999",
            "minLength": 10,
            "maxLength": 15
          },
          "groupId": {
            "type": "string",
            "description": "The unique identifier of the WhatsApp group to send the message to. Mutually exclusive with \"to\".",
            "example": "grp_XYZ"
          },
          "content": {
            "description": "Template content. Body placeholders: key-value pairs only.",
            "allOf": [
              {
                "$ref": "#/components/schemas/SendTemplateMessageContentDtoV2"
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
      "SendWhatsappTemplateMessageResponseV2": {
        "type": "object",
        "properties": {
          "messages": {
            "type": "array",
            "description": "Per-message result",
            "items": {
              "type": "object",
              "properties": {
                "status": {
                  "type": "string",
                  "enum": [
                    "SENT",
                    "FAILED",
                    "ENQUEUED"
                  ],
                  "description": "Status of the message"
                },
                "recipient": {
                  "type": "string",
                  "description": "Recipient phone number"
                },
                "messageId": {
                  "type": "string",
                  "description": "Message ID"
                },
                "errorMessage": {
                  "type": "string",
                  "description": "Error (when FAILED)"
                }
              }
            }
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