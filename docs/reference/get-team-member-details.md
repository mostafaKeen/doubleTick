Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Get Team Member Details

Get detailed information about a specific team member including their role, reporting managers, and organization details

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/team/member": {
      "get": {
        "operationId": "get-team-member-details",
        "summary": "Get Team Member Details",
        "description": "Get detailed information about a specific team member including their role, reporting managers, and organization details",
        "tags": [
          "Teams"
        ],
        "parameters": [
          {
            "name": "agentNumber",
            "in": "query",
            "required": true,
            "description": "Phone number of the team member/agent to get details for",
            "schema": {
              "type": "string",
              "example": "919999999999"
            }
          }
        ],
        "responses": {
          "200": {
            "description": "Team member details retrieved successfully",
            "content": {
              "application/json": {
                "schema": {
                  "type": "object",
                  "properties": {
                    "id": {
                      "type": "string",
                      "description": "Unique identifier of the team member"
                    },
                    "name": {
                      "type": "string",
                      "description": "Name of the team member"
                    },
                    "phone": {
                      "type": "string",
                      "description": "Phone number of the team member"
                    },
                    "email": {
                      "type": "string",
                      "description": "Email address of the team member"
                    },
                    "joinDate": {
                      "type": "string",
                      "format": "date-time",
                      "description": "Date when the team member joined"
                    },
                    "reportingManager": {
                      "type": "array",
                      "items": {
                        "$ref": "#/components/schemas/ReportingManagerDetails"
                      }
                    },
                    "orgRoleName": {
                      "type": "string",
                      "description": "Name of the team member's role in the organization"
                    },
                    "orgRoleId": {
                      "type": "string",
                      "description": "ID of the team member's role in the organization"
                    },
                    "isOrganizationOwner": {
                      "type": "boolean",
                      "description": "Whether the team member is the owner of the organization"
                    },
                    "wabaRoles": {
                      "type": "array",
                      "items": {
                        "type": "object",
                        "properties": {
                          "wabaName": {
                            "type": "string",
                            "description": "Name of the WhatsApp Business Account"
                          },
                          "wabaNumber": {
                            "type": "string",
                            "description": "WhatsApp Business Account Number"
                          },
                          "roleName": {
                            "type": "string",
                            "description": "Name of the role assigned for this WABA"
                          }
                        }
                      }
                    }
                  }
                }
              }
            }
          },
          "400": {
            "description": "Invalid request parameters",
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
                      "example": "agentNumber must be a valid phone number"
                    },
                    "error": {
                      "type": "string",
                      "example": "Bad Request"
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
            "description": "Team member not found",
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
                      "example": "Team member not found"
                    },
                    "error": {
                      "type": "string",
                      "example": "Not Found"
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
                      "example": "Invalid phone number:"
                    },
                    "error": {
                      "type": "string",
                      "example": "Unprocessable Entity"
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
      "ReportingManagerDetails": {
        "type": "object",
        "properties": {
          "id": {
            "type": "string",
            "description": "Unique identifier of the reporting manager"
          },
          "name": {
            "type": "string",
            "description": "Name of the reporting manager"
          },
          "phone": {
            "type": "string",
            "description": "Phone number of the reporting manager"
          },
          "email": {
            "type": "string",
            "description": "Email of the reporting manager (optional)"
          },
          "joinDate": {
            "type": "string",
            "format": "date-time",
            "description": "Date when the reporting manager joined"
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