Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Edit Template

Edit Template

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/template/{templateId}": {
      "patch": {
        "operationId": "edit-template",
        "summary": "Edit Template",
        "description": "Edit Template",
        "tags": [
          "Template"
        ],
        "parameters": [
          {
            "in": "path",
            "name": "templateId",
            "required": true,
            "schema": {
              "type": "string"
            },
            "description": "The template ID"
          }
        ],
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/EditTemplateViaPublicAPIDto"
              }
            }
          }
        },
        "responses": {
          "201": {
            "description": "Edit Template",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/EditTemplateViaPublicAPIResponseDto"
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
      "CreateTemplateHeaderComponentExample": {
        "type": "object",
        "properties": {
          "link": {
            "type": "string",
            "description": "Header Example Media Url (Present only if format is IMAGE/VIDEO/DOCUMENT)"
          }
        }
      },
      "CreateTemplateHeaderComponent": {
        "type": "object",
        "properties": {
          "format": {
            "type": "string",
            "description": "Header Format",
            "enum": [
              "TEXT",
              "IMAGE",
              "VIDEO",
              "DOCUMENT"
            ]
          },
          "text": {
            "type": "string",
            "description": "Header Text (Present only if format is TEXT)",
            "maxLength": 60
          },
          "example": {
            "description": "Header Example (add link only if format is Media type or add variable and their values if format is TEXT and text contains variable)",
            "additionalProperties": {
              "type": "string"
            },
            "allOf": [
              {
                "$ref": "#/components/schemas/CreateTemplateHeaderComponentExample"
              }
            ]
          }
        },
        "required": [
          "format"
        ]
      },
      "CreateTemplateBodyComponent": {
        "type": "object",
        "properties": {
          "text": {
            "type": "string",
            "description": "Body Text",
            "maxLength": 1024
          },
          "example": {
            "type": "object",
            "description": "Body Variable Example (Present only if text contains variable)",
            "additionalProperties": {
              "type": "string"
            }
          }
        },
        "required": [
          "text"
        ]
      },
      "CreateTemplateFooterComponent": {
        "type": "object",
        "properties": {
          "text": {
            "type": "string",
            "description": "Footer Text",
            "maxLength": 60
          }
        },
        "required": [
          "text"
        ]
      },
      "CreateTemplateButtonDto": {
        "type": "object",
        "properties": {
          "type": {
            "type": "string",
            "description": "Button Type",
            "enum": [
              "QUICK_REPLY",
              "URL",
              "PHONE_NUMBER"
            ]
          },
          "text": {
            "type": "string",
            "description": "Button Text",
            "maxLength": 25,
            "minLength": 1
          },
          "url": {
            "type": "string",
            "description": "Button Url (Present only if type is URL)"
          },
          "example": {
            "type": "string",
            "description": "Button Url Example Parameter (Present only if type is URL and url contains variable)"
          },
          "phoneNumber": {
            "type": "string",
            "description": "Button Phone Number (Present only if type is PHONE_NUMBER)"
          }
        },
        "required": [
          "type",
          "text"
        ]
      },
      "CreateTemplateComponentDto": {
        "type": "object",
        "properties": {
          "header": {
            "description": "Header Component",
            "allOf": [
              {
                "$ref": "#/components/schemas/CreateTemplateHeaderComponent"
              }
            ]
          },
          "body": {
            "description": "Body Component",
            "allOf": [
              {
                "$ref": "#/components/schemas/CreateTemplateBodyComponent"
              }
            ]
          },
          "footer": {
            "description": "Footer Component",
            "allOf": [
              {
                "$ref": "#/components/schemas/CreateTemplateFooterComponent"
              }
            ]
          },
          "buttons": {
            "description": "Buttons Component",
            "type": "array",
            "items": {
              "$ref": "#/components/schemas/CreateTemplateButtonDto"
            }
          }
        },
        "required": [
          "body"
        ]
      },
      "EditTemplateViaPublicAPIDto": {
        "type": "object",
        "properties": {
          "name": {
            "type": "string",
            "description": "Template Name",
            "minLength": 1,
            "maxLength": 512
          },
          "language": {
            "type": "string",
            "enum": [
              "af",
              "sq",
              "ar",
              "az",
              "bn",
              "bg",
              "ca",
              "zh_CN",
              "zh_HK",
              "zh_TW",
              "hr",
              "cs",
              "da",
              "nl",
              "en",
              "en_GB",
              "en_US",
              "et",
              "fil",
              "fi",
              "fr",
              "ka",
              "de",
              "el",
              "gu",
              "ha",
              "he",
              "hi",
              "hu",
              "id",
              "ga",
              "it",
              "ja",
              "kn",
              "kk",
              "rw_RW",
              "ko",
              "ky_KG",
              "lo",
              "lv",
              "lt",
              "mk",
              "ms",
              "ml",
              "mr",
              "nb",
              "fa",
              "pl",
              "pt_BR",
              "pt_PT",
              "pa",
              "ro",
              "ru",
              "sr",
              "sk",
              "sl",
              "es",
              "es_AR",
              "es_ES",
              "es_MX",
              "sw",
              "sv",
              "ta",
              "te",
              "th",
              "tr",
              "uk",
              "ur",
              "uz"
            ],
            "description": "Template Language",
            "example": "en"
          },
          "components": {
            "description": "Template Components",
            "allOf": [
              {
                "$ref": "#/components/schemas/CreateTemplateComponentDto"
              }
            ]
          },
          "category": {
            "type": "string",
            "enum": [
              "MARKETING",
              "UTILITY"
            ],
            "description": "Template Category",
            "example": "MARKETING"
          },
          "wabaNumbers": {
            "type": "array",
            "items": {
              "type": "string",
              "description": "The phone number from which the message is sent, with the country code",
              "example": "+919999999999",
              "minLength": 10,
              "maxLength": 15,
              "format": "phone"
            }
          },
          "allowCategoryUpdate": {
            "type": "boolean",
            "default": true,
            "description": "Set to true to allow Meta to automatically assign a category. If omitted, the template may be rejected due to miscategorization."
          }
        },
        "required": [
          "name",
          "language",
          "components",
          "category"
        ]
      },
      "EditTemplateViaPublicAPIResponseDto": {
        "type": "object",
        "properties": {
          "success": {
            "type": "boolean",
            "example": true
          }
        },
        "required": [
          "success"
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