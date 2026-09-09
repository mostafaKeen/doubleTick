Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Export Chats For a Customer For Given WabaNumber

Export Chats For a WabaNumber

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/export-chats": {
      "post": {
        "operationId": "export-chats-to-excel",
        "summary": "Export Chats For a Customer For Given WabaNumber",
        "description": "Export Chats For a WabaNumber",
        "tags": [
          "Chat Messages"
        ],
        "parameters": [],
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/ExportChatsDto"
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
                  "type": "object",
                  "properties": {
                    "success": {
                      "type": "boolean",
                      "example": "true"
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
      "ExportChatsDto": {
        "type": "object",
        "properties": {
          "customerPhoneNumber": {
            "type": "string",
            "description": "The phone number of customer",
            "example": "919999999999",
            "minLength": 10,
            "maxLength": 15,
            "format": "phone"
          },
          "wabaNumber": {
            "type": "string",
            "description": "The wabaNumber of integration for which you want to export chats",
            "example": "919999999999",
            "minLength": 10,
            "maxLength": 15,
            "format": "phone"
          },
          "startDate": {
            "type": "string",
            "description": "The starting date from which chat messages will be exported. This date should be formatted as 'DD-MM-YYYY'.",
            "example": "14-05-2024"
          },
          "endDate": {
            "type": "string",
            "description": "The ending date up to which chat messages will be exported. This date should also be formatted as 'DD-MM-YYYY'.",
            "example": "20-05-2024"
          },
          "includeMedia": {
            "type": "boolean",
            "description": "If true then it will include shared media link",
            "example": true
          }
        },
        "required": [
          "customerPhoneNumber",
          "wabaNumber",
          "startDate",
          "endDate",
          "includeMedia"
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