Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Get Team

Get Team members with their Reporting Managers

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/team": {
      "get": {
        "operationId": "get-team",
        "summary": "Get Team",
        "description": "Get Team members with their Reporting Managers",
        "tags": [
          "Teams"
        ],
        "responses": {
          "200": {
            "description": "Operation successfully completed",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/GetTeamResponseDto"
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
      "GetTeamResponseDto": {
        "type": "object",
        "properties": {
          "data": {
            "type": "array",
            "items": {
              "type": "object",
              "properties": {
                "id": {
                  "type": "string",
                  "description": "ID of the Team member"
                },
                "name": {
                  "type": "string",
                  "description": "Name of the Team member"
                },
                "phone": {
                  "type": "string",
                  "description": "Phone number of the Team member"
                },
                "email": {
                  "type": "string",
                  "description": "Email of the Team member"
                },
                "joinDate": {
                  "type": "string",
                  "format": "date-time",
                  "description": "Date when Team member joined"
                },
                "reportingManager": {
                  "type": "array",
                  "items": {
                    "type": "object",
                    "properties": {
                      "id": {
                        "type": "string",
                        "description": "ID of the Reporting Manager"
                      },
                      "name": {
                        "type": "string",
                        "description": "Name of the Reporting Manager"
                      },
                      "phone": {
                        "type": "string",
                        "description": "Phone number of the Reporting Manager"
                      },
                      "email": {
                        "type": "string",
                        "description": "Email of the Reporting Manager"
                      },
                      "joinDate": {
                        "type": "string",
                        "format": "date-time",
                        "description": "Date when Reporting Manager joined"
                      }
                    }
                  }
                },
                "orgRoleName": {
                  "type": "string",
                  "description": "Name of the Organization Role"
                },
                "orgRoleId": {
                  "type": "string",
                  "description": "ID of the Organization Role"
                },
                "isOrganizationOwner": {
                  "type": "boolean",
                  "description": "Indicates whether the Team member is the owner of the Organization"
                }
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