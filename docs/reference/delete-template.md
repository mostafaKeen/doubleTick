Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Delete Template

Delete Template

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/template": {
      "delete": {
        "operationId": "delete-template",
        "summary": "Delete Template",
        "description": "Delete Template",
        "tags": [
          "Template"
        ],
        "parameters": [],
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/DeleteTemplateDto"
              }
            }
          }
        },
        "responses": {
          "201": {
            "description": "Delete Template",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/DeleteTemplateViaPublicAPIResponseDto"
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
      "DeleteTemplateDto": {
        "type": "object",
        "properties": {
          "name": {
            "type": "string",
            "description": "Template Name",
            "example": "template_name"
          },
          "wabaPhoneNumber": {
            "type": "string",
            "description": "Waba Number",
            "example": "14155238886"
          }
        },
        "required": [
          "name",
          "wabaNumber"
        ]
      },
      "DeleteTemplateViaPublicAPIResponseDto": {
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