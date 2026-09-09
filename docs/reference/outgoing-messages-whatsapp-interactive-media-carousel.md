Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Send Whatsapp Interactive Media Carousel Message

Send a WhatsApp interactive media carousel message — 2-10 cards, each with an image or video header, optional body text, and either a single URL button (cta_url) or one or more quick-reply buttons. All cards must use the same button type and same number of buttons.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/whatsapp/message/interactive/media-carousel": {
      "post": {
        "operationId": "outgoing-messages-whatsapp-interactive-media-carousel",
        "summary": "Send Whatsapp Interactive Media Carousel Message",
        "description": "Send a WhatsApp interactive media carousel message — 2-10 cards, each with an image or video header, optional body text, and either a single URL button (cta_url) or one or more quick-reply buttons. All cards must use the same button type and same number of buttons.",
        "tags": [
          "Outgoing Messages"
        ],
        "parameters": [],
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/SendWhatsappInteractiveMediaCarouselMessageDto"
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
      "CarouselCardHeaderMediaDto": {
        "type": "object",
        "properties": {
          "link": {
            "type": "string",
            "description": "Publicly accessible URL of the card header media (must be a `link`; uploaded media `id` is not accepted by Meta for carousel cards).",
            "example": "https://www.example.com/assets/blue-echeveria.jpeg"
          }
        },
        "required": [
          "link"
        ]
      },
      "CarouselCardHeaderDto": {
        "type": "object",
        "properties": {
          "type": {
            "type": "string",
            "enum": [
              "image",
              "video"
            ],
            "description": "Header type. Only image or video are supported on carousel cards.",
            "example": "image"
          },
          "image": {
            "$ref": "#/components/schemas/CarouselCardHeaderMediaDto"
          },
          "video": {
            "$ref": "#/components/schemas/CarouselCardHeaderMediaDto"
          }
        },
        "required": [
          "type"
        ]
      },
      "CarouselCardBodyDto": {
        "type": "object",
        "properties": {
          "text": {
            "type": "string",
            "description": "Card body text. Max 160 characters and up to 2 line breaks.",
            "maxLength": 160,
            "example": "*Blue Echeveria*\\n\\nA rosette-shaped succulent."
          }
        },
        "required": [
          "text"
        ]
      },
      "CarouselCardCtaUrlParametersDto": {
        "type": "object",
        "properties": {
          "display_text": {
            "type": "string",
            "description": "Visible URL button label. Max 20 chars.",
            "maxLength": 20,
            "example": "Buy now"
          },
          "url": {
            "type": "string",
            "description": "URL the button opens.",
            "example": "https://shop.example.com/latest/blue-echeveria"
          }
        },
        "required": [
          "display_text",
          "url"
        ]
      },
      "CarouselCardQuickReplyButtonDto": {
        "type": "object",
        "properties": {
          "id": {
            "type": "string",
            "description": "Quick-reply button id. Max 256 chars.",
            "maxLength": 256,
            "example": "learn-blue-echeveria"
          },
          "title": {
            "type": "string",
            "description": "Quick-reply button label. Max 20 chars.",
            "maxLength": 20,
            "example": "Learn more"
          }
        },
        "required": [
          "id",
          "title"
        ]
      },
      "CarouselCardActionDto": {
        "type": "object",
        "properties": {
          "type": {
            "type": "string",
            "enum": [
              "cta_url",
              "quick_reply"
            ],
            "description": "Action type. Use `cta_url` for a single URL button or `quick_reply` for one or more quick-reply buttons.",
            "example": "cta_url"
          },
          "name": {
            "type": "string",
            "enum": [
              "cta_url"
            ],
            "description": "Required when type is `cta_url`. Always the literal `cta_url`."
          },
          "parameters": {
            "$ref": "#/components/schemas/CarouselCardCtaUrlParametersDto"
          },
          "buttons": {
            "type": "array",
            "description": "Required when type is `quick_reply`. 1-2 quick-reply buttons.",
            "minItems": 1,
            "maxItems": 2,
            "items": {
              "$ref": "#/components/schemas/CarouselCardQuickReplyButtonDto"
            }
          }
        },
        "required": [
          "type"
        ]
      },
      "CarouselCardDto": {
        "type": "object",
        "properties": {
          "header": {
            "$ref": "#/components/schemas/CarouselCardHeaderDto"
          },
          "body": {
            "$ref": "#/components/schemas/CarouselCardBodyDto"
          },
          "action": {
            "$ref": "#/components/schemas/CarouselCardActionDto"
          }
        },
        "required": [
          "header",
          "action"
        ]
      },
      "SendInteractiveMediaCarouselBodyDto": {
        "type": "object",
        "properties": {
          "text": {
            "type": "string",
            "description": "Main message body text shown above the carousel. Max 1024 characters.",
            "maxLength": 1024,
            "example": "Of course! Here are three of our latest arrivals, each under $25:"
          }
        },
        "required": [
          "text"
        ]
      },
      "SendInteractiveMediaCarouselContentDto": {
        "type": "object",
        "properties": {
          "body": {
            "$ref": "#/components/schemas/SendInteractiveMediaCarouselBodyDto"
          },
          "cards": {
            "type": "array",
            "description": "Cards to display in the carousel. Min 2, max 10. All cards must use the same button type and same number of buttons.",
            "minItems": 2,
            "maxItems": 10,
            "items": {
              "$ref": "#/components/schemas/CarouselCardDto"
            }
          }
        },
        "required": [
          "body",
          "cards"
        ]
      },
      "SendWhatsappInteractiveMediaCarouselMessageDto": {
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
            "$ref": "#/components/schemas/SendInteractiveMediaCarouselContentDto"
          }
        },
        "required": [
          "from",
          "to",
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