Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Add members to a group

Adds members to an existing group

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/groups/add-members": {
      "post": {
        "summary": "Add members to a group",
        "description": "Adds members to an existing group",
        "tags": [
          "Broadcast Groups"
        ],
        "operationId": "add-members-to-group",
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/AddGroupMembersRequestDto"
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
                  "$ref": "#/components/schemas/AddGroupMembersResponse"
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
      "AddGroupMembersRequestDto": {
        "type": "object",
        "properties": {
          "groupId": {
            "type": "string",
            "description": "The ID of the group to add members to",
            "example": "group_xyz"
          },
          "members": {
            "type": "array",
            "description": "List of members to add",
            "items": {
              "$ref": "#/components/schemas/GroupMemberDto"
            }
          }
        },
        "required": [
          "groupId",
          "members"
        ]
      },
      "AddGroupMembersResponse": {
        "type": "object",
        "properties": {
          "invalidMembers": {
            "type": "array",
            "description": "List of members that could not be added",
            "items": {
              "$ref": "#/components/schemas/GroupMemberDto"
            }
          },
          "createdMembers": {
            "type": "array",
            "description": "List of members that were successfully added",
            "items": {
              "type": "string",
              "example": [
                "919999999999",
                "919999999998"
              ]
            }
          },
          "membersAlreadyPresent": {
            "type": "array",
            "description": "List of phone numbers of members that were already in the group",
            "items": {
              "type": "string",
              "example": [
                "919999999999",
                "919999999998"
              ]
            }
          },
          "groupId": {
            "type": "string",
            "description": "The ID of the group that members were added to",
            "example": "group_inpkqRL8G2"
          }
        },
        "required": [
          "invalidMembers",
          "createdMembers",
          "membersAlreadyPresent",
          "groupId"
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