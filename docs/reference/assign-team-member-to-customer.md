Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Assign team member to customer

Assign team member to customer

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/customer/assign": {
      "post": {
        "operationId": "assign-team-member-to-customer",
        "summary": "Assign team member to customer",
        "description": " Assign team member to customer ",
        "tags": [
          "Customer"
        ],
        "parameters": [],
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/AssignTeamMemberCustomerDto"
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
          "403": {
            "description": "Access forbidden",
            "content": {
              "application/json": {
                "schema": {
                  "type": "object",
                  "properties": {
                    "statusCode": {
                      "type": "number",
                      "example": 403
                    },
                    "message": {
                      "type": "string",
                      "example": "Team member +1234567890 does not have access to WhatsApp API Number +9876543210"
                    },
                    "error": {
                      "type": "string",
                      "example": "Forbidden"
                    }
                  }
                }
              }
            }
          },
          "404": {
            "description": "Resource not found",
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
                      "example": "wabaNumber not found"
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
                      "example": "User is not a member of the team"
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
      "AssignTeamMemberCustomerDto": {
        "type": "object",
        "properties": {
          "customerPhoneNumber": {
            "description": "Phone number of customer",
            "type": "string",
            "example": "917838849957",
            "minLength": 7,
            "maxLength": 15,
            "format": "phone"
          },
          "assignedUserPhoneNumber": {
            "description": "Phone number of user",
            "type": "string",
            "example": "917838849957",
            "minLength": 7,
            "maxLength": 15,
            "format": "phone"
          },
          "wabaNumber": {
            "type": "string",
            "description": "Integration Waba Number with country code",
            "example": "919876543210",
            "minLength": 7,
            "maxLength": 15,
            "format": "phone"
          }
        },
        "required": [
          "customerPhoneNumber",
          "assignedUserPhoneNumber",
          "wabaNumber"
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