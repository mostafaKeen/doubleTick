Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Check reverted on time

Check reverted on time

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/customer/check-reverted-on-time": {
      "get": {
        "summary": "Check reverted on time",
        "description": "Check reverted on time",
        "operationId": "check-reverted-on-time",
        "tags": [
          "Customer"
        ],
        "parameters": [],
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/CheckRevertedOnTimeDto"
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
                    "agentName": {
                      "type": "string",
                      "example": "abc"
                    },
                    "agentPhoneNumber": {
                      "type": "string",
                      "example": "+919999999999"
                    },
                    "revertedOnTime": {
                      "type": "boolean",
                      "example": false
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
      "CheckRevertedOnTimeDto": {
        "type": "object",
        "properties": {
          "phoneNumber": {
            "description": "Phone number of customer",
            "type": "string",
            "example": "+919999999999",
            "minLength": 7,
            "maxLength": 15,
            "format": "phone"
          },
          "waitingForResponseInSec": {
            "description": "Wait time for response in seconds.",
            "type": "number",
            "example": 60
          },
          "defaultAgentName": {
            "description": "Default name for the agent",
            "type": "string",
            "example": "abc"
          },
          "defaultAgentNumber": {
            "description": "Default number of the agent",
            "type": "string",
            "example": "+919999999999",
            "minLength": 7,
            "maxLength": 15,
            "format": "phone"
          },
          "wabaNumber": {
            "description": "Waba Number",
            "type": "string",
            "example": "+919999999999",
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