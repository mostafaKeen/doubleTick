Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Remove members from a group

Removes members from an existing static group

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/groups/remove-members": {
      "delete": {
        "summary": "Remove members from a group",
        "description": "Removes members from an existing static group",
        "tags": [
          "Broadcast Groups"
        ],
        "operationId": "remove-members-from-group",
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/RemoveMembersFromGroupRequestDto"
              }
            }
          }
        },
        "responses": {
          "200": {
            "description": "OK",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/RemoveMembersFromGroupResponseDto"
                }
              }
            }
          },
          "400": {
            "description": "Bad request, invalid input provided"
          },
          "401": {
            "description": "Invalid or missing API key"
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
      "RemoveMembersFromGroupRequestDto": {
        "type": "object",
        "properties": {
          "groupId": {
            "type": "string",
            "description": "The ID of the group to remove members from",
            "example": "group_xyz"
          },
          "phoneNumbers": {
            "type": "array",
            "description": "List of phone numbers to remove from the group",
            "items": {
              "type": "string",
              "example": "919999999999"
            },
            "minItems": 1
          }
        },
        "required": [
          "groupId",
          "phoneNumbers"
        ]
      },
      "RemoveMembersFromGroupResponseDto": {
        "type": "object",
        "properties": {
          "success": {
            "type": "array",
            "description": "List of phone numbers successfully removed from the group",
            "items": {
              "type": "string",
              "example": "919999999999"
            }
          },
          "failed": {
            "type": "array",
            "description": "List of phone numbers that could not be removed",
            "items": {
              "type": "string",
              "example": "919999999999"
            }
          }
        },
        "required": [
          "success",
          "failed"
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