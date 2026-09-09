Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Unassign team member from a chat

Unassign a team member from an individual customer chat or a WA group chat. Provide either customerPhoneNumber (for individual chats) or groupId. Exactly one of the two must be supplied.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/team-member/unassign": {
      "post": {
        "summary": "Unassign team member from a chat",
        "description": "Unassign a team member from an individual customer chat or a WA group chat. Provide either customerPhoneNumber (for individual chats) or groupId. Exactly one of the two must be supplied.",
        "operationId": "unassign-team-member-from-chat",
        "tags": [
          "Team Member"
        ],
        "parameters": [],
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/UnassignTeamMemberChatDto"
              }
            }
          }
        },
        "responses": {
          "201": {
            "description": "Success",
            "content": {
              "application/json": {
                "schema": {
                  "type": "object",
                  "properties": {
                    "success": {
                      "type": "boolean",
                      "example": "true"
                    }
                  }
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
                      "type": "array",
                      "items": {
                        "type": "string",
                        "example": "Either customerPhoneNumber or groupId must be provided"
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
      "UnassignTeamMemberChatDto": {
        "type": "object",
        "properties": {
          "customerPhoneNumber": {
            "description": "Phone number of customer (required if groupId is not provided)",
            "type": "string",
            "example": "919999999999",
            "minLength": 7,
            "maxLength": 15,
            "format": "phone"
          },
          "groupId": {
            "description": "GroupId of the WA group (required if customerPhoneNumber is not provided)",
            "type": "string",
            "example": "grp_1234"
          },
          "wabaNumber": {
            "description": "WhatsApp Business Account number (only applicable when customerPhoneNumber is used)",
            "type": "string",
            "example": "919999999999",
            "minLength": 7,
            "maxLength": 15,
            "format": "phone"
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