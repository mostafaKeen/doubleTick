Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# List Notes

List notes for a customer chat or WA Group chat. Use `to` + `from` for customer chats, or `groupId` for WA Group chats. Rate limit: 60 requests per minute per organization.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/notes": {
      "get": {
        "operationId": "list-notes",
        "summary": "List Notes",
        "description": "List notes for a customer chat or WA Group chat. Use `to` + `from` for customer chats, or `groupId` for WA Group chats. Rate limit: 60 requests per minute per organization.",
        "tags": [
          "Notes"
        ],
        "parameters": [
          {
            "name": "groupId",
            "in": "query",
            "required": false,
            "description": "The groupId of the WA Group. Use this instead of `to`/`from` when listing notes for a group chat.",
            "schema": {
              "type": "string",
              "example": "grp_AbCdEf1234"
            }
          },
          {
            "name": "from",
            "in": "query",
            "required": false,
            "description": "The WABA phone number with country code. Required when not using groupId.",
            "schema": {
              "type": "string",
              "example": "+919999999999",
              "minLength": 10,
              "maxLength": 15,
              "format": "phone"
            }
          },
          {
            "name": "to",
            "in": "query",
            "required": false,
            "description": "The customer phone number with country code. Required when not using groupId.",
            "schema": {
              "type": "string",
              "example": "+919999999999",
              "minLength": 10,
              "maxLength": 15,
              "format": "phone"
            }
          },
          {
            "name": "limit",
            "in": "query",
            "required": false,
            "description": "Maximum number of notes to return (default 30, max 100)",
            "schema": {
              "type": "number",
              "example": 30
            }
          },
          {
            "name": "lastNoteId",
            "in": "query",
            "required": false,
            "description": "Cursor: ID of the last note from the previous page for pagination",
            "schema": {
              "type": "string",
              "example": "uuid-v4"
            }
          },
          {
            "name": "lastNoteDate",
            "in": "query",
            "required": false,
            "description": "Cursor: timestamp of the last note from the previous page for pagination",
            "schema": {
              "type": "string",
              "format": "date-time",
              "example": "2026-01-28T10:23:45.123Z"
            }
          }
        ],
        "responses": {
          "200": {
            "description": "List of notes retrieved successfully",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/GetPublicNotesResponseDto"
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
                      "example": "Chat not found for customer +919999999999 and WABA +919999999998"
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
      "GetPublicNotesResponseDto": {
        "type": "object",
        "properties": {
          "notes": {
            "type": "array",
            "description": "List of notes",
            "items": {
              "$ref": "#/components/schemas/PublicNoteResponseDto"
            }
          },
          "hasMore": {
            "type": "boolean",
            "description": "Indicates if there are more notes available after this page",
            "example": true
          },
          "lastNoteId": {
            "type": "string",
            "nullable": true,
            "description": "Cursor: ID of the last note in the current page (use with lastNoteDate on the next request)",
            "example": "uuid-v4"
          },
          "lastNoteDate": {
            "type": "string",
            "format": "date-time",
            "nullable": true,
            "description": "Cursor: timestamp of the last note in the current page (use with lastNoteId on the next request)",
            "example": "2026-01-28T10:23:45.123Z"
          },
          "totalCount": {
            "type": "number",
            "description": "Total number of notes for this chat and customer",
            "example": 42
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