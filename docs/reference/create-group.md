Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Create a new group

Create a new group

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/groups": {
      "post": {
        "summary": "Create a new group",
        "description": "Create a new group",
        "tags": [
          "Broadcast Groups"
        ],
        "operationId": "create-group",
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/CreateGroupDto"
              }
            }
          }
        },
        "responses": {
          "201": {
            "description": "Group created successfully",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/CreateGroupResponseDto"
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
      "CreateGroupDto": {
        "type": "object",
        "properties": {
          "name": {
            "type": "string",
            "description": "The name of the group",
            "example": "Test Group"
          },
          "members": {
            "type": "array",
            "description": "List of members in the group",
            "items": {
              "$ref": "#/components/schemas/GroupMemberDto"
            }
          }
        },
        "required": [
          "name"
        ],
        "additionalProperties": false
      },
      "GroupMemberDto": {
        "type": "object",
        "properties": {
          "name": {
            "type": "string",
            "description": "The name of the member",
            "example": "Name"
          },
          "phone": {
            "type": "string",
            "description": "The phone number of the member",
            "example": "919999999999"
          }
        },
        "required": [
          "phone"
        ]
      },
      "CreateGroupResponseDto": {
        "type": "object",
        "properties": {
          "groupData": {
            "type": "object",
            "properties": {
              "groupId": {
                "type": "string",
                "description": "The ID of the newly created group",
                "example": "group_CG0wH3AIQw"
              },
              "memberCount": {
                "type": "integer",
                "description": "The number of members in the group",
                "example": 1
              },
              "groupChatName": {
                "type": "string",
                "description": "The name of the created group",
                "example": "Test Group"
              }
            },
            "required": [
              "groupId",
              "memberCount",
              "groupChatName"
            ]
          },
          "invalidMembers": {
            "type": "array",
            "description": "List of invalid members (if any)",
            "items": {
              "$ref": "#/components/schemas/GroupMemberDto"
            }
          }
        },
        "required": [
          "groupData",
          "invalidMembers"
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