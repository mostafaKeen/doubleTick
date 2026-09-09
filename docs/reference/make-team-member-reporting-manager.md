Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Make a team member a reporting manager

Promotes an existing team member to a reporting manager by creating a team for them. Optionally assigns one or more existing members directly under them.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/team-member/make-reporting-manager": {
      "post": {
        "operationId": "make-team-member-reporting-manager",
        "summary": "Make a team member a reporting manager",
        "description": "Promotes an existing team member to a reporting manager by creating a team for them. Optionally assigns one or more existing members directly under them.",
        "tags": [
          "Teams"
        ],
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/MakeTeamMemberReportingManagerDto"
              }
            }
          }
        },
        "responses": {
          "201": {
            "description": "Operation successfully completed",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/MakeTeamMemberReportingManagerResponseDto"
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
          "422": {
            "description": "Unprocessable Entity",
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
                      "example": "At least one team member phone number is required to create a new reporting manager team."
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
      "MakeTeamMemberReportingManagerDto": {
        "type": "object",
        "properties": {
          "reportingManagerPhoneNumber": {
            "type": "string",
            "description": "Phone number of the team member to promote to reporting manager",
            "example": "919999999999"
          },
          "teamMemberPhoneNumbers": {
            "type": "array",
            "items": {
              "type": "string"
            },
            "description": "Phone numbers of existing members to assign under the new reporting manager. Required if the member does not already have a team.",
            "example": [
              "919999999999",
              "918888888888"
            ]
          }
        },
        "required": [
          "reportingManagerPhoneNumber"
        ]
      },
      "MakeTeamMemberReportingManagerResponseDto": {
        "type": "object",
        "properties": {
          "success": {
            "type": "boolean",
            "description": "Indicates whether the operation was successful"
          }
        },
        "required": [
          "success"
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