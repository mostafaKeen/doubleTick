Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Assign Custom Fields and/or Tags to Customer

Assign Custom Fields and/or Tags to Customer

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/customer/assign-tags-custom-fields": {
      "post": {
        "operationId": "customer-assign-tags-custom-fields",
        "summary": "Assign Custom Fields and/or Tags to Customer",
        "description": "Assign Custom Fields and/or Tags to Customer",
        "tags": [
          "Customer"
        ],
        "parameters": [],
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/AssignCustomFieldsChatTagsToCustomerDto"
              }
            }
          }
        },
        "responses": {
          "201": {
            "description": "Customer Tags and Custom Fields Assigned",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/AssignCustomFieldsChatTagsToCustomerResponseDto"
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
      "CustomFieldDto": {
        "type": "object",
        "properties": {
          "name": {
            "type": "string",
            "description": "custom field name",
            "example": "field name"
          },
          "value": {
            "type": "string",
            "description": "custom field value",
            "example": "value"
          }
        },
        "required": [
          "name",
          "value"
        ]
      },
      "AssignCustomFieldsChatTagsToCustomerDto": {
        "type": "object",
        "properties": {
          "phone": {
            "type": "string",
            "description": "Customer Phone Number with country code. Example, 919876543210",
            "example": "919876543210"
          },
          "tags": {
            "description": "Array of tags to be assigned",
            "example": [
              "tag1",
              "tag2"
            ],
            "type": "array",
            "items": {
              "type": "string"
            }
          },
          "customFields": {
            "description": "Array of custom fields to be assigned",
            "example": [
              {
                "name": "field name",
                "value": "value"
              }
            ],
            "type": "array",
            "items": {
              "$ref": "#/components/schemas/CustomFieldDto"
            }
          },
          "name": {
            "type": "string",
            "description": "Customer Name",
            "example": "John Doe"
          },
          "optIn": {
            "type": "boolean",
            "description": "Opt in status of the customer",
            "example": true
          },
          "agentPhone": {
            "type": "string",
            "description": "Agent Phone Number with country code (null if unassigned)",
            "example": "919876543210"
          },
          "wabaNumber": {
            "type": "string",
            "description": "Integration Waba Number with country code",
            "example": "919876543210"
          }
        },
        "required": [
          "phone"
        ]
      },
      "TagsResponseErrorDto": {
        "type": "object",
        "properties": {
          "tag": {
            "type": "string",
            "description": "tag name"
          },
          "error": {
            "type": "string",
            "description": "error message"
          }
        },
        "required": [
          "tag",
          "error"
        ]
      },
      "TagsResponseDto": {
        "type": "object",
        "properties": {
          "added": {
            "description": "Array of tags added",
            "type": "array",
            "items": {
              "type": "string"
            }
          },
          "errored": {
            "description": "Array of tags errored",
            "type": "array",
            "items": {
              "$ref": "#/components/schemas/TagsResponseErrorDto"
            }
          }
        },
        "required": [
          "added",
          "errored"
        ]
      },
      "CustomFieldsResponseErrorDto": {
        "type": "object",
        "properties": {
          "customField": {
            "type": "string",
            "description": "custom field name"
          },
          "error": {
            "type": "string",
            "description": "error message"
          }
        },
        "required": [
          "customField",
          "error"
        ]
      },
      "CustomFieldsResponseDto": {
        "type": "object",
        "properties": {
          "added": {
            "description": "Array of custom fields added",
            "type": "array",
            "items": {
              "type": "string"
            }
          },
          "errored": {
            "description": "Array of custom fields errored",
            "type": "array",
            "items": {
              "$ref": "#/components/schemas/CustomFieldsResponseErrorDto"
            }
          }
        },
        "required": [
          "added",
          "errored"
        ]
      },
      "AssignCustomFieldsChatTagsToCustomerResponseDto": {
        "type": "object",
        "properties": {
          "tags": {
            "description": "tags response",
            "example": {
              "added": [
                "tag name"
              ],
              "errored": [
                {
                  "tag": "tag name 2",
                  "error": "error message"
                }
              ]
            },
            "allOf": [
              {
                "$ref": "#/components/schemas/TagsResponseDto"
              }
            ]
          },
          "customFields": {
            "description": "custom fields response",
            "example": {
              "added": [
                "custom field name"
              ],
              "errored": [
                {
                  "customField": "custom field name 2",
                  "error": "error message"
                }
              ]
            },
            "allOf": [
              {
                "$ref": "#/components/schemas/CustomFieldsResponseDto"
              }
            ]
          },
          "customerId": {
            "type": "string",
            "description": "Customer ID",
            "example": "customer_xxx"
          }
        },
        "required": [
          "tags",
          "customFields"
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