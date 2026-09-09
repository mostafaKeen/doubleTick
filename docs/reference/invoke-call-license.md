Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Assign a calling license to a team member

Assigns a calling license to a team member identified by phone number, enabling them to make/receive calls. Requires the organization to have the calling license addon enabled and available license capacity.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/whatsapp/call/license/invoke": {
      "post": {
        "operationId": "invoke-call-license",
        "summary": "Assign a calling license to a team member",
        "description": "Assigns a calling license to a team member identified by phone number, enabling them to make/receive calls. Requires the organization to have the calling license addon enabled and available license capacity.",
        "tags": [
          "Calling License"
        ],
        "parameters": [],
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/PublicCallLicensePhoneDto"
              }
            }
          }
        },
        "responses": {
          "200": {
            "description": "License assigned successfully.",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/PublicCallLicenseInvokeResponseDto"
                },
                "example": {
                  "success": true,
                  "licensesRemaining": 1
                }
              }
            }
          },
          "400": {
            "description": "Bad request. Can occur for three reasons: the org has no calling license addon, the phone doesn't match any active team member, or the org has hit its license limit.",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/PublicApiErrorResponseDto"
                },
                "example": {
                  "status": 400,
                  "reason": "BAD_REQUEST",
                  "message": "Calling is not enabled on your current plan. Purchase a calling license to enable it",
                  "extraData": {}
                }
              }
            }
          },
          "401": {
            "description": "Unauthorized — the API key is valid but the org lacks the ASSIGN_CALL_LICENSE permission.",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/PublicApiErrorResponseDto"
                },
                "example": {
                  "status": 401,
                  "reason": "UNAUTHORIZED",
                  "message": "Insufficient permissions. Missing: ASSIGN_CALL_LICENSE",
                  "extraData": {}
                }
              }
            }
          },
          "403": {
            "description": "Forbidden — invalid/revoked API key, caller IP not on the key's allowlist, or the key has browser access disabled.",
            "content": {
              "application/json": {
                "schema": {
                  "type": "object",
                  "properties": {
                    "reason": {
                      "type": "string",
                      "example": "FORBIDDEN"
                    }
                  }
                }
              }
            }
          },
          "429": {
            "description": "Rate limit exceeded — 60 requests/min, keyed per org+user.",
            "content": {
              "application/json": {
                "schema": {
                  "type": "object",
                  "properties": {
                    "status": {
                      "type": "number",
                      "example": 429
                    },
                    "reason": {
                      "type": "string",
                      "example": "RATE_LIMIT_EXCEEDED"
                    },
                    "message": {
                      "type": "string",
                      "example": "Too many requests, please try again later."
                    },
                    "extraData": {
                      "type": "object",
                      "properties": {
                        "retryAfter": {
                          "type": "number"
                        },
                        "limitType": {
                          "type": "string",
                          "example": "call-license-invoke"
                        },
                        "resetAt": {
                          "type": "number"
                        }
                      }
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
      "PublicCallLicensePhoneDto": {
        "type": "object",
        "required": [
          "phone"
        ],
        "properties": {
          "phone": {
            "type": "string",
            "example": "919881376321",
            "description": "Phone number of the team member, as stored on their user profile."
          }
        }
      },
      "PublicCallLicenseInvokeResponseDto": {
        "type": "object",
        "properties": {
          "success": {
            "type": "boolean",
            "example": true
          },
          "licensesRemaining": {
            "type": "number",
            "example": 1,
            "description": "Number of unassigned license slots left in the org after this call."
          }
        }
      },
      "PublicApiErrorResponseDto": {
        "type": "object",
        "properties": {
          "status": {
            "type": "number",
            "example": 400
          },
          "reason": {
            "type": "string",
            "example": "BAD_REQUEST"
          },
          "message": {
            "type": "string"
          },
          "extraData": {
            "type": "object"
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