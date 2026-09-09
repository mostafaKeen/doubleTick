Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Create Note

Create a note attached to a customer chat or a WA Group chat. Use `to` + `from` for customer chats, or `groupId` for WA Group chats. Rate limit: 60 requests per minute per organization.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/notes": {
      "post": {
        "operationId": "create-note",
        "summary": "Create Note",
        "description": "Create a note attached to a customer chat or a WA Group chat. Use `to` + `from` for customer chats, or `groupId` for WA Group chats. Rate limit: 60 requests per minute per organization.",
        "tags": [
          "Notes"
        ],
        "parameters": [],
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/CreatePublicNoteDto"
              }
            }
          }
        },
        "responses": {
          "201": {
            "description": "Note created successfully",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/PublicNoteResponseDto"
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
            "description": "WABA, customer or chat not found",
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
                      "example": "Customer with phone number +919999999999 does not exist"
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
      "PublicNoteResponseDto": {
        "type": "object",
        "properties": {
          "id": {
            "type": "string",
            "description": "Unique identifier of the note",
            "example": "uuid-v4"
          },
          "note": {
            "type": "string",
            "description": "Text content of the note",
            "example": "Spoke with customer, follow-up demo scheduled for tomorrow."
          },
          "createdBy": {
            "type": "string",
            "description": "Name of the user who created the note",
            "example": "Mitul Varshney"
          },
          "updatedBy": {
            "type": "string",
            "description": "Name of the user who last updated the note",
            "example": "Mitul Varshney"
          },
          "dateCreated": {
            "type": "string",
            "format": "date-time",
            "description": "Timestamp when the note was created",
            "example": "2026-02-06T11:55:03.170Z"
          },
          "dateUpdated": {
            "type": "string",
            "format": "date-time",
            "description": "Timestamp when the note was last updated",
            "example": "2026-02-06T11:55:03.170Z"
          }
        }
      },
      "CreatePublicNoteDto": {
        "type": "object",
        "properties": {
          "note": {
            "type": "string",
            "description": "Text content of the note",
            "example": "Spoke with customer, follow-up demo scheduled for tomorrow."
          },
          "groupId": {
            "type": "string",
            "description": "The groupId of the WA Group. Use this instead of `to`/`from` when creating a note for a group chat.",
            "example": "grp_AbCdEf1234"
          },
          "from": {
            "type": "string",
            "description": "The WABA phone number with country code. Required when not using groupId.",
            "example": "+919999999999",
            "minLength": 10,
            "maxLength": 15,
            "format": "phone"
          },
          "to": {
            "type": "string",
            "description": "The customer phone number with country code. Required when not using groupId.",
            "example": "+919999999999",
            "minLength": 10,
            "maxLength": 15,
            "format": "phone"
          },
          "entities": {
            "type": "array",
            "description": "Optional entities (e.g., users) mentioned in the note that should receive notifications",
            "items": {
              "type": "object",
              "properties": {
                "type": {
                  "type": "string",
                  "description": "Type of the entity",
                  "example": "USER"
                },
                "id": {
                  "type": "string",
                  "description": "Unique identifier of the entity",
                  "example": "user_mitul00000"
                }
              }
            }
          }
        },
        "required": [
          "note"
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