Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Get Webhooks

Get Webhooks

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/v2/webhooks": {
      "get": {
        "operationId": "get-webhooks",
        "summary": "Get Webhooks",
        "description": "Get Webhooks",
        "tags": [
          "Webhook"
        ],
        "parameters": [
          {
            "in": "query",
            "name": "sortBy",
            "schema": {
              "type": "string",
              "description": "Sort by",
              "example": "name",
              "enum": [
                "name",
                "url",
                "createdBy",
                "integrationCount"
              ]
            },
            "description": "Sort by",
            "required": false
          },
          {
            "in": "query",
            "name": "sortDirection",
            "schema": {
              "type": "string",
              "description": "Sort direction",
              "example": "DESC",
              "enum": [
                "ASC",
                "DESC"
              ]
            },
            "description": "Sort direction",
            "required": false
          }
        ],
        "responses": {
          "201": {
            "description": "Get Webhooks",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/FetchWebhooksQueryResponseV2"
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
      "FetchWebhooksQueryResponseV2": {
        "type": "object",
        "properties": {
          "webhookId": {
            "type": "string",
            "description": "The unique identifier of the webhook",
            "example": "abc123"
          },
          "url": {
            "type": "string",
            "format": "uri",
            "description": "The URL of the webhook",
            "example": "https://example.com/webhook"
          },
          "eventTypes": {
            "type": "array",
            "description": "A list of event types that the webhook listens to",
            "items": {
              "type": "string",
              "example": "MESSAGE_RECEIVED"
            },
            "example": [
              "MESSAGE_RECEIVED",
              "MESSAGE_SENT"
            ]
          },
          "wabaNumbers": {
            "type": "array",
            "description": "A list of WABA Numbers associated with the webhook",
            "items": {
              "type": "string",
              "example": "+919999999999"
            },
            "example": [
              "+919999999999",
              "+918888888888"
            ]
          },
          "integrationCount": {
            "type": "integer",
            "description": "The count of integrations associated with the webhook",
            "example": 2
          },
          "createdBy": {
            "type": "string",
            "description": "The user who created the webhook",
            "example": "admin"
          },
          "name": {
            "type": "string",
            "description": "The name of the webhook",
            "example": "My Webhook"
          }
        },
        "required": [
          "webhookId",
          "url",
          "eventTypes",
          "wabaNumbers",
          "integrationCount",
          "createdBy",
          "name"
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