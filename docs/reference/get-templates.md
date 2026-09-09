Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Get Templates

Get Templates

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/v2/templates": {
      "get": {
        "operationId": "get-templates",
        "summary": "Get Templates",
        "description": "Get Templates",
        "tags": [
          "Template"
        ],
        "parameters": [
          {
            "in": "query",
            "name": "status",
            "schema": {
              "type": "string",
              "description": "Template status",
              "example": "PENDING",
              "enum": [
                "APPROVED",
                "REJECTED",
                "PENDING",
                "PAUSED",
                "ALL"
              ]
            },
            "description": "Template status",
            "required": false
          },
          {
            "in": "query",
            "name": "name",
            "schema": {
              "type": "string",
              "description": "Template name",
              "example": "test_template"
            },
            "description": "Template name",
            "required": false
          },
          {
            "in": "query",
            "name": "language",
            "schema": {
              "type": "string",
              "description": "Template language",
              "example": "en",
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
              ]
            },
            "description": "Template language",
            "required": false
          },
          {
            "in": "query",
            "name": "category",
            "schema": {
              "type": "string",
              "description": "Template category",
              "example": "MARKETING",
              "enum": [
                "MARKETING",
                "UTILITY"
              ]
            },
            "description": "Template category",
            "required": false
          },
          {
            "in": "query",
            "name": "wabaPhoneNumbers",
            "schema": {
              "type": "string",
              "description": "Comma separated waba phone numbers",
              "example": "919999999999,918888888888"
            },
            "description": "Waba Phone Numbers",
            "required": false
          },
          {
            "in": "query",
            "name": "allWabaPhoneNumbers",
            "schema": {
              "type": "boolean",
              "description": "If set to true, returns templates for all Waba Phone Numbers",
              "example": true
            },
            "description": "If set to true, returns templates for all Waba Phone Numbers",
            "required": false
          }
        ],
        "responses": {
          "201": {
            "description": "Get Templates",
            "content": {
              "application/json": {
                "schema": {
                  "type": "array",
                  "items": {
                    "$ref": "#/components/schemas/GetV2TemplatesResponseItem"
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
      "GetV2TemplatesResponseItem": {
        "type": "object",
        "properties": {
          "id": {
            "type": "number",
            "description": "Template ID"
          },
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
          "category": {
            "type": "string",
            "enum": [
              "MARKETING",
              "UTILITY"
            ],
            "description": "Template Category",
            "example": "MARKETING"
          },
          "status": {
            "type": "string",
            "enum": [
              "APPROVED",
              "REJECTED",
              "PENDING",
              "PAUSED"
            ],
            "description": "Template Status",
            "example": "PAUSED"
          },
          "rejectedReason": {
            "type": "string",
            "description": "Template Rejected Reason",
            "example": "Template Rejected Reason"
          },
          "createdBy": {
            "type": "string",
            "description": "Template Created By",
            "example": "Creater_Name"
          },
          "wabaPhoneNumber": {
            "type": "string",
            "description": "Waba Phone Number",
            "example": "14155238886"
          },
          "components": {
            "description": "Template Components",
            "type": "array",
            "items": {
              "$ref": "#/components/schemas/ComponentsDto"
            }
          }
        },
        "required": [
          "id",
          "name",
          "language",
          "category",
          "components",
          "status",
          "rejectedReason",
          "createdBy",
          "wabaPhoneNumber"
        ]
      },
      "ComponentsDto": {
        "type": "object",
        "properties": {
          "type": {
            "type": "string",
            "enum": [
              "BODY",
              "HEADER",
              "FOOTER",
              "BUTTON"
            ],
            "description": "Component Type",
            "example": "HEADER"
          },
          "format": {
            "type": "string",
            "enum": [
              "TEXT",
              "IMAGE",
              "DOCUMENT",
              "VIDEO",
              "LOCATION"
            ],
            "description": "Template Component Format (Present when type is HEADER)",
            "example": "TEXT"
          },
          "text": {
            "type": "string",
            "description": "Present WHEN type is BODY/FOOTER or type is HEADER and format is TEXT"
          },
          "variables": {
            "description": "Present when type is HEADER/BODY",
            "type": "array",
            "items": {
              "$ref": "#/components/schemas/ComponentVariableDto"
            }
          },
          "buttons": {
            "description": "Present when type is BUTTON",
            "type": "array",
            "items": {
              "$ref": "#/components/schemas/ButtonDto"
            }
          }
        },
        "required": [
          "type"
        ],
        "example": [
          {
            "type": "HEADER",
            "format": "IMAGE",
            "variables": [
              {
                "mediaUrl": "https://placeholder.com/150"
              }
            ]
          },
          {
            "type": "BODY",
            "text": "Hi {{name}}, \r\nWelcome to your shop!\r\nWe have got {{offer_name}} for you",
            "variables": [
              {
                "name": "name"
              },
              {
                "name": "offer_name"
              }
            ]
          },
          {
            "type": "FOOTER",
            "text": "powered by doubletick.io"
          },
          {
            "type": "BUTTON",
            "buttons": [
              {
                "type": "QUICK_REPLY",
                "text": "Interested"
              },
              {
                "type": "QUICK_REPLY",
                "text": "Tell me More"
              },
              {
                "type": "QUICK_REPLY",
                "text": "Stop"
              }
            ]
          }
        ]
      },
      "ComponentVariableDto": {
        "type": "object",
        "properties": {
          "name": {
            "type": "string",
            "description": "Name of the variable present in the text",
            "example": "variable_1"
          },
          "mediaUrl": {
            "type": "string",
            "description": "Present only if type is IMAGE/VIDEO/DOCUMENT",
            "example": "https://placeholder.com/150"
          },
          "fileName": {
            "type": "string",
            "description": "The filename of the document (Present only if type is DOCUMENT)",
            "example": "Example File Name"
          }
        }
      },
      "ButtonVariableDto": {
        "type": "object",
        "properties": {
          "parameter": {
            "type": "string",
            "example": "some-url-path"
          }
        },
        "required": [
          "parameter"
        ]
      },
      "ButtonDto": {
        "type": "object",
        "properties": {
          "type": {
            "type": "string",
            "description": "Button Type",
            "enum": [
              "QUICK_REPLY",
              "URL",
              "PHONE_NUMBER"
            ],
            "example": "URL"
          },
          "text": {
            "type": "string",
            "description": "Button Text",
            "example": "Button Text"
          },
          "url": {
            "type": "string",
            "description": "Button Url (Present only if type is URL)"
          },
          "phoneNumber": {
            "type": "string",
            "description": "Button Phone Number (Present only if type is PHONE_NUMBER)"
          },
          "variables": {
            "description": "Button Variables (Present when type is URL)",
            "type": "array",
            "items": {
              "$ref": "#/components/schemas/ButtonVariableDto"
            }
          }
        },
        "required": [
          "text",
          "type"
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