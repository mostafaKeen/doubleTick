Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Fire Custom Event

Fire a custom event directly from your own systems (CRM, order management, etc.). Use it to track events wherever they happen, and see them in Funnel alongside your WhatsApp conversation data.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/custom-events/fire": {
      "post": {
        "operationId": "fire-custom-event",
        "summary": "Fire Custom Event",
        "description": "Fire a custom event directly from your own systems (CRM, order management, etc.). Use it to track events wherever they happen, and see them in Funnel alongside your WhatsApp conversation data.",
        "tags": [
          "Custom Events"
        ],
        "parameters": [],
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/FireCustomEventDto"
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
                      "example": true
                    }
                  }
                }
              }
            }
          },
          "400": {
            "description": "Bad Request",
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
                      "example": "Custom event execution limit of 20000 reached for today"
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
            "description": "Forbidden",
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
                      "example": "Contact Support to enable enterprise feature."
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
            "description": "Not Found",
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
                      "example": "Custom event not found"
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
          "429": {
            "description": "Too Many Requests",
            "content": {
              "application/json": {
                "schema": {
                  "type": "object",
                  "properties": {
                    "statusCode": {
                      "type": "number",
                      "example": 429
                    },
                    "message": {
                      "type": "string",
                      "example": "Too many requests, please try again later."
                    },
                    "error": {
                      "type": "string",
                      "example": "Too Many Requests"
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
      "FireCustomEventDto": {
        "type": "object",
        "properties": {
          "eventName": {
            "type": "string",
            "description": "Name of the custom event to fire. Must match the name of an event already configured for the organization.",
            "example": "order_placed",
            "minLength": 3,
            "maxLength": 100
          },
          "botId": {
            "type": "string",
            "description": "Optional. ID of the bot to associate with this event execution, if applicable — the event can be fired without one."
          },
          "wabaNumber": {
            "type": "string",
            "description": "The WABA number of the WhatsApp integration the event is fired on.",
            "example": "919999999999"
          },
          "customerPhoneNumber": {
            "type": "string",
            "description": "The phone number of the customer the event is fired for.",
            "example": "919999999998"
          }
        },
        "required": [
          "eventName",
          "wabaNumber",
          "customerPhoneNumber"
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