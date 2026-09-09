Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Remove Custom Fields and/or Tags from WA Group

Remove chat-level custom fields and/or tags from a WhatsApp Group identified by its groupId.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/wa-group/remove-tags-custom-fields": {
      "post": {
        "operationId": "wa-group-remove-tags-custom-fields",
        "summary": "Remove Custom Fields and/or Tags from WA Group",
        "description": "Remove chat-level custom fields and/or tags from a WhatsApp Group identified by its groupId.",
        "tags": [
          "WA Group"
        ],
        "parameters": [],
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/RemoveTagsAndCustomFieldsFromWaGroupDto"
              }
            }
          }
        },
        "responses": {
          "201": {
            "description": "WA Group Tags and Custom Fields Removed",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/RemoveTagsAndCustomFieldsFromWaGroupResponseDto"
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
          },
          "404": {
            "description": "Group or chat not found",
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
                      "example": "Group 'grp_AbCdEf1234' not found"
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
      "RemoveTagsAndCustomFieldsFromWaGroupDto": {
        "type": "object",
        "properties": {
          "groupId": {
            "type": "string",
            "description": "WhatsApp group ID (client custom group ID)",
            "example": "grp_AbCdEf1234"
          },
          "tags": {
            "type": "array",
            "description": "Array of tags to remove from the group chat",
            "example": [
              "tag1",
              "tag2"
            ],
            "items": {
              "type": "string"
            }
          },
          "customFields": {
            "type": "array",
            "description": "Array of custom field names to remove from the group",
            "example": [
              "custom field 1",
              "custom field 2"
            ],
            "items": {
              "type": "string"
            }
          },
          "shouldCreateChatLog": {
            "type": "boolean",
            "description": "Whether to create a chat log entry for this removal",
            "default": true,
            "example": true
          }
        },
        "required": [
          "groupId"
        ]
      },
      "RemoveTagsAndCustomFieldsFromWaGroupResponseDto": {
        "type": "object",
        "properties": {
          "tags": {
            "description": "Tags removal result",
            "example": {
              "removed": [
                "tag1"
              ],
              "errored": [
                {
                  "tag": "tag2",
                  "error": "Tag not found"
                }
              ]
            },
            "type": "object",
            "properties": {
              "removed": {
                "type": "array",
                "items": {
                  "type": "string"
                },
                "description": "Tags successfully removed"
              },
              "errored": {
                "type": "array",
                "items": {
                  "type": "object",
                  "properties": {
                    "tag": {
                      "type": "string"
                    },
                    "error": {
                      "type": "string"
                    }
                  }
                },
                "description": "Tags that could not be removed"
              }
            }
          },
          "customFields": {
            "description": "Custom fields removal result",
            "example": {
              "removed": [
                "field name"
              ],
              "errored": [
                {
                  "customField": "other field",
                  "error": "Field not found"
                }
              ]
            },
            "type": "object",
            "properties": {
              "removed": {
                "type": "array",
                "items": {
                  "type": "string"
                },
                "description": "Custom fields successfully removed"
              },
              "errored": {
                "type": "array",
                "items": {
                  "type": "object",
                  "properties": {
                    "customField": {
                      "type": "string"
                    },
                    "error": {
                      "type": "string"
                    }
                  }
                },
                "description": "Custom fields that could not be removed"
              }
            }
          }
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