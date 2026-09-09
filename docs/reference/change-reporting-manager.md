Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Change reporting manager

Change reporting manager of the user

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/team-member/reporting-manager": {
      "patch": {
        "operationId": "change-reporting-manager",
        "summary": "Change reporting manager",
        "description": "Change reporting manager of the user",
        "tags": [
          "Teams"
        ],
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/ChangeReportingManagerDto"
              }
            }
          }
        },
        "responses": {
          "200": {
            "description": "Operation successfully completed",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/ChangeReportingManagerResponseDto"
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
            "description": "One or more user does not exist.",
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
                      "example": "One or more user does not exist."
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
      "ChangeReportingManagerDto": {
        "type": "object",
        "properties": {
          "reportingManagerPhoneNumber": {
            "type": "string",
            "description": "Phone number of the reporting manager"
          },
          "memberPhoneNumber": {
            "type": "string",
            "description": "Phone number of the member"
          }
        },
        "required": [
          "reportingManagerPhoneNumber",
          "memberPhoneNumber"
        ]
      },
      "ChangeReportingManagerResponseDto": {
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