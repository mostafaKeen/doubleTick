Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Get customer details

Get details of customer along with custom fields

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/customer/details": {
      "get": {
        "operationId": "get-customer-details",
        "summary": "Get customer details ",
        "description": "Get details of customer along with custom fields ",
        "tags": [
          "Customer"
        ],
        "parameters": [
          {
            "name": "phoneNumber",
            "in": "query",
            "schema": {
              "type": "string"
            },
            "example": "917838849957",
            "required": false
          },
          {
            "name": "wabaNumber",
            "in": "query",
            "schema": {
              "type": "string"
            },
            "description": "WhatsApp Business Account number. When provided, the response will include tags associated with the customer for this WABA.",
            "example": "917838849957",
            "required": false
          },
          {
            "name": "customerId",
            "in": "query",
            "schema": {
              "type": "string"
            },
            "example": "customer_xxxxxxxxxx",
            "required": false
          }
        ],
        "responses": {
          "200": {
            "description": "Information about the customer",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/customerDetailsResponse"
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
                        "example": "phoneNumber must be a string"
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
                      "example": "NotFound"
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
      "customerDetailsResponse": {
        "type": "object",
        "properties": {
          "customer": {
            "type": "object",
            "properties": {
              "id": {
                "type": "string",
                "example": "customer_xxx"
              },
              "name": {
                "type": "string",
                "example": "customername1"
              },
              "phone": {
                "type": "string",
                "example": "+919999999999"
              },
              "tags": {
                "type": "array",
                "description": "Array of tags associated with the customer for the specified WABA number. Only returned when wabaNumber parameter is provided.",
                "items": {
                  "type": "string"
                },
                "example": [
                  "tag1",
                  "tag2"
                ]
              },
              "customFields": {
                "type": "array",
                "items": {
                  "type": "object",
                  "properties": {
                    "id": {
                      "type": "string",
                      "example": ""
                    },
                    "name": {
                      "type": "string"
                    },
                    "value": {
                      "type": "string"
                    },
                    "type ": {
                      "type": "string"
                    }
                  }
                }
              },
              "assignedToUser": {
                "type": "string"
              },
              "assignedUserNumber": {
                "type": "string"
              }
            }
          }
        },
        "required": [
          "phone"
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