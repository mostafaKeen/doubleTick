Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Assign team member to chat

Assign a team member to an individual customer chat or a WA group chat. Provide either customerPhoneNumber (for individual chats) or groupId. Exactly one of the two must be supplied.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/team-member/assign": {
      "post": {
        "operationId": "assign-team-member-to-chat",
        "summary": "Assign team member to chat",
        "description": "Assign a team member to an individual customer chat or a WA group chat. Provide either customerPhoneNumber (for individual chats) or groupId. Exactly one of the two must be supplied.",
        "tags": [
          "Team Member"
        ],
        "parameters": [],
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/AssignTeamMemberChatDto"
              }
            }
          }
        },
        "responses": {
          "201": {
            "description": ""
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
                      "example": "Invalid public api key"
                    },
                    "error": {
                      "type": "string",
                      "example": "Unauthorized"
                    }
                  }
                }
              }
            }
          },
          "422": {
            "description": "User is not a member of the team",
            "content": {
              "application/json": {
                "schema": {
                  "type": "object",
                  "properties": {
                    "statusCode": {
                      "type": "number",
                      "example": 422
                    },
                    "message": {
                      "type": "string",
                      "example": "User is not a member of the team"
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
      "AssignTeamMemberChatDto": {
        "type": "object",
        "properties": {
          "customerPhoneNumber": {
            "description": "Phone number of customer (required if groupId is not provided)",
            "type": "string",
            "example": "917838849957",
            "minLength": 7,
            "maxLength": 15,
            "format": "phone"
          },
          "groupId": {
            "description": "GroupId of the WA group (required if customerPhoneNumber is not provided)",
            "type": "string",
            "example": "grp_1234"
          },
          "assignedUserPhoneNumber": {
            "description": "Phone number of the agent to assign",
            "type": "string",
            "example": "917838849957",
            "minLength": 7,
            "maxLength": 15,
            "format": "phone"
          },
          "reassign": {
            "description": "Boolean to assign user even if chat is already assigned",
            "type": "boolean",
            "example": "true",
            "format": "boolean"
          },
          "wabaNumber": {
            "type": "string",
            "description": "Integration Waba Number with country code (only applicable when customerPhoneNumber is used)",
            "example": "919876543210",
            "minLength": 7,
            "maxLength": 15,
            "format": "phone"
          }
        },
        "required": [
          "assignedUserPhoneNumber"
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