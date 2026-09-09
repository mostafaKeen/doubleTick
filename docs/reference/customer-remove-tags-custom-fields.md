Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Remove Custom Fields and/or Tags from Customer

Remove Custom Fields and/or Tags from Customer

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/customer/remove-tags-custom-fields": {
      "post": {
        "operationId": "customer-remove-tags-custom-fields",
        "summary": "Remove Custom Fields and/or Tags from Customer",
        "description": "Remove Custom Fields and/or Tags from Customer",
        "tags": [
          "Customer"
        ],
        "parameters": [],
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/RemoveTagsAndCustomFieldsFromCustomerDto"
              }
            }
          }
        },
        "responses": {
          "201": {
            "description": "Customer Tags and Custom Fields Removed",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/RemoveTagsAndCustomFieldsFromCustomerResponseDto"
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
      "RemoveTagsAndCustomFieldsFromCustomerDto": {
        "type": "object",
        "properties": {
          "phone": {
            "type": "string",
            "description": "customer phone number"
          },
          "tags": {
            "description": "Array of tags to be removed",
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
            "description": "Array of customFields to be removed",
            "example": [
              "custom field 1",
              "custom field 2"
            ],
            "type": "array",
            "items": {
              "type": "string"
            }
          },
          "wabaNumber": {
            "type": "string",
            "description": "Integration Waba Number with country code",
            "example": "919876543210"
          }
        },
        "required": [
          "phone",
          "tags",
          "customFields"
        ]
      },
      "RemoveTagsAndCustomFieldsFromCustomerResponseDto": {
        "type": "object",
        "properties": {
          "tags": {
            "description": "tags response",
            "example": {
              "removed": [
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
              "removed": [
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