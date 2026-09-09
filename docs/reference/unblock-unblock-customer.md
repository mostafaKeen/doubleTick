Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Unblock a customer

Unblock customer using phone number

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/customer/unblock": {
      "post": {
        "operationId": "unblock-unblock-customer",
        "summary": "Unblock a customer ",
        "description": "Unblock customer using phone number ",
        "tags": [
          "Customer"
        ],
        "parameters": [],
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/UnblockCustomerDto"
              }
            }
          }
        },
        "responses": {
          "201": {
            "description": "Operation successfully completed",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/CustomerBlockUnblockResponseDto"
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
                      "type": "array",
                      "items": {
                        "type": "string",
                        "example": "phone must be a string"
                      }
                    },
                    "error": {
                      "type": "string",
                      "example": "Not Found"
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
          },
          "404": {
            "description": "NotFound",
            "content": {
              "application/json": {
                "schema": {
                  "type": "object",
                  "properties": {
                    "statusCode": {
                      "type": "number",
                      "example": 404
                    },
                    "message": {
                      "type": "string",
                      "example": "Invalid Customer"
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
      "UnblockCustomerDto": {
        "type": "object",
        "properties": {
          "phone": {
            "type": "string",
            "description": "The phone number of customer which unblock operation will be performed",
            "example": "+919999999999",
            "minLength": 10,
            "maxLength": 15,
            "format": "phone"
          }
        },
        "required": [
          "phone"
        ]
      },
      "CustomerBlockUnblockResponseDto": {
        "type": "object",
        "properties": {}
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