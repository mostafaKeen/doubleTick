Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Get all roles

Get all roles

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/roles": {
      "get": {
        "operationId": "get-all-roles",
        "summary": "Get all roles",
        "description": "Get all roles",
        "tags": [
          "Roles"
        ],
        "responses": {
          "200": {
            "description": "Operation successfully completed",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/GetAllRolesResponseDto"
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
          },
          "422": {
            "description": "Roles not found",
            "content": {
              "application/json": {
                "schema": {
                  "type": "object",
                  "properties": {
                    "statusCode": {
                      "type": "number",
                      "example": 422
                    },
                    "message": {
                      "type": "string",
                      "example": "Roles not found"
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
      "GetAllRolesResponseDto": {
        "type": "array",
        "items": {
          "type": "object",
          "properties": {
            "id": {
              "type": "string"
            },
            "name": {
              "type": "string"
            },
            "description": {
              "type": "string"
            },
            "type": {
              "type": "string",
              "enum": [
                "ORG",
                "WHATSAPP"
              ]
            },
            "permissions": {
              "type": "array",
              "items": {
                "type": "object",
                "properties": {
                  "id": {
                    "type": "string"
                  },
                  "name": {
                    "type": "string"
                  },
                  "description": {
                    "type": "string"
                  }
                }
              }
            },
            "isCustomRole": {
              "type": "boolean"
            },
            "dateCreated": {
              "type": "string",
              "format": "date-time"
            },
            "dateUpdated": {
              "type": "string",
              "format": "date-time"
            }
          },
          "required": [
            "roleId",
            "roleName",
            "description",
            "type",
            "permissions",
            "isCustomRole",
            "dateCreated",
            "dateUpdated"
          ]
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