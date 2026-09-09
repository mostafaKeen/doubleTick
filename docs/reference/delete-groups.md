Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Delete groups

Deletes multiple groups

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/groups": {
      "delete": {
        "summary": "Delete groups",
        "description": "Deletes multiple groups",
        "operationId": "delete-groups",
        "tags": [
          "Broadcast Groups"
        ],
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/DeleteGroupRequestDto"
              }
            }
          }
        },
        "responses": {
          "200": {
            "description": "OK"
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
      "DeleteGroupRequestDto": {
        "type": "object",
        "properties": {
          "groupIds": {
            "type": "array",
            "description": "The IDs of the group to delete",
            "items": {
              "type": "string",
              "example": [
                "group_xyz",
                "group_abc"
              ]
            },
            "minItems": 1,
            "uniqueItems": true,
            "example": [
              "group_xyz",
              "group_abc"
            ]
          }
        },
        "required": [
          "groupIds"
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