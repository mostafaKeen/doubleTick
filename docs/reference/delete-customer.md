Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Delete Customer

Delete customers by phone number (max 10 per request). Deletion is processed asynchronously — a 201 response means the request was accepted and queued, not that deletion has completed.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/customer": {
      "delete": {
        "operationId": "delete-customer",
        "summary": "Delete Customer",
        "description": "Delete customers by phone number (max 10 per request). Deletion is processed asynchronously — a 201 response means the request was accepted and queued, not that deletion has completed.",
        "tags": [
          "Customer"
        ],
        "parameters": [],
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/DeleteCustomerDto"
              }
            }
          }
        },
        "responses": {
          "201": {
            "description": "Delete Customer",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/DeleteCustomerResponseDto"
                }
              }
            }
          },
          "400": {
            "description": "More than 10 phone numbers in the request, an empty/missing phoneNumbers array, or an invalid phone number format",
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
          },
          "403": {
            "description": "Forbidden",
            "content": {
              "application/json": {
                "schema": {
                  "type": "object",
                  "properties": {
                    "statusCode": {
                      "type": "number",
                      "example": 403
                    },
                    "message": {
                      "type": "string",
                      "example": "You don't have the required permissions to perform this action."
                    },
                    "error": {
                      "type": "string",
                      "example": "Forbidden"
                    }
                  }
                }
              }
            }
          },
          "404": {
            "description": "None of the given phone numbers matched a customer for this org",
            "content": {
              "application/json": {
                "schema": {
                  "type": "object",
                  "properties": {
                    "message": {
                      "type": "string",
                      "example": "No matching customers found"
                    },
                    "notFoundPhoneNumbers": {
                      "type": "array",
                      "items": {
                        "type": "string"
                      },
                      "example": [
                        "919876500000"
                      ]
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
      "DeleteCustomerDto": {
        "type": "object",
        "properties": {
          "phoneNumbers": {
            "type": "array",
            "items": {
              "type": "string"
            },
            "description": "Array of customer phone numbers to delete (max 10 per request)",
            "example": [
              "919876543210",
              "919876500000"
            ],
            "maxItems": 10
          }
        },
        "required": [
          "phoneNumbers"
        ]
      },
      "DeleteCustomerResponseDto": {
        "type": "object",
        "properties": {
          "message": {
            "type": "string",
            "example": "Your request has been queued for deletion."
          },
          "notFoundPhoneNumbers": {
            "type": "array",
            "items": {
              "type": "string"
            },
            "description": "Phone numbers from the request that did not match any customer for this org",
            "example": [
              "919876500000"
            ]
          }
        },
        "required": [
          "message"
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