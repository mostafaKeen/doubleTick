Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Edit Webhooks

Edit Webhooks

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/v2/webhook/{webhookId}": {
      "post": {
        "operationId": "edit-webhooks",
        "summary": "Edit Webhooks",
        "description": "Edit Webhooks",
        "tags": [
          "Webhook"
        ],
        "parameters": [
          {
            "in": "path",
            "name": "webhookId",
            "required": true,
            "schema": {
              "type": "string"
            },
            "description": "The webhook ID"
          }
        ],
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/RegisterPublicWebhookDtoV2"
              }
            }
          }
        },
        "responses": {
          "201": {
            "description": "Edit Webhook",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/RegisterPublicWebhookResponseV2"
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
      "RegisterPublicWebhookDtoV2": {
        "type": "object",
        "properties": {
          "url": {
            "type": "string",
            "format": "uri",
            "description": "URL of the webhook",
            "example": "https://example.com/webhook"
          },
          "method": {
            "type": "string",
            "description": "HTTP method used for the webhook",
            "enum": [
              "GET",
              "POST",
              "PUT",
              "DELETE",
              "PATCH",
              "HEAD"
            ],
            "example": "POST"
          },
          "headers": {
            "type": "object",
            "description": "Custom headers to be sent with the webhook",
            "additionalProperties": true,
            "example": {
              "Authorization": "Bearer token"
            }
          },
          "body": {
            "type": "object",
            "description": "Payload to be sent with the webhook",
            "additionalProperties": true
          },
          "query": {
            "type": "object",
            "description": "Query parameters to be included in the webhook URL",
            "additionalProperties": true
          },
          "bodyFormat": {
            "type": "string",
            "description": "Format of the body content",
            "enum": [
              "JSON",
              "FORM_DATA"
            ],
            "example": "JSON"
          },
          "authorization": {
            "type": "object",
            "description": "Authorization details for the webhook",
            "properties": {
              "type": {
                "type": "string",
                "description": "The type of authorization used for the webhook",
                "enum": [
                  "BASIC",
                  "BEARER"
                ],
                "example": "BEARER"
              },
              "payload": {
                "type": "string",
                "description": "The authorization payload, such as a token or credentials",
                "example": "some-secure-token"
              }
            },
            "required": [
              "type",
              "payload"
            ]
          },
          "webhookEvents": {
            "type": "array",
            "description": "Events that trigger the webhook",
            "items": {
              "type": "string",
              "enum": [
                "MESSAGE_RECEIVED",
                "MESSAGE_STATUS_UPDATE",
                "CHAT_ASSIGNED_TO_AGENT",
                "ASSIGNED_AGENT_STATUS_CHANGED",
                "CHAT_UNASSIGNED",
                "UPDATE_CUSTOMER_CUSTOM_FIELD",
                "WIDGET_LEAD_RECEIVED",
                "VERIFIED_WIDGET_LEAD_RECEIVED",
                "NEW_LEAD",
                "RAW_CLOUD_API_WEBHOOK",
                "CLOSE_CONVERSATION",
                "TEMPLATE_UPDATE",
                "ADD_TAG",
                "REMOVE_TAG",
                "CALL_TO_WHATSAPP_MESSAGE_RECEIVED",
                "CONVERSATION_OPENED",
                "CUSTOMER_BUSINESS_CHAT_OPEN",
                "CUSTOMER_NOTE_CREATED",
                "CUSTOMER_NOTE_UPDATED",
                "CUSTOMER_NOTE_DELETED",
                "POST_CALL_DISPOSITION",
                "CALL_INCOMING_INITIATED",
                "CALL_OUTGOING_INITIATED",
                "CALL_ACCEPTED",
                "CALL_ENDED",
                "CALL_REJECTED",
                "CALL_MISSED",
                "CALL_NOT_PICKED",
                "STANDBY_MESSAGE_RECEIVED",
                "MESSAGING_HANDOVER"
              ]
            },
            "example": [
              "MESSAGE_RECEIVED",
              "CLOSE_CONVERSATION"
            ]
          },
          "retryOnTimeout": {
            "type": "boolean",
            "description": "Retry the webhook call on timeout"
          },
          "name": {
            "type": "string",
            "description": "Name of the webhook"
          },
          "wabaNumbers": {
            "type": "array",
            "description": "List of WABA numbers associated with the webhook",
            "items": {
              "type": "string",
              "example": "+919999999999",
              "minLength": 10,
              "maxLength": 15,
              "format": "phone"
            }
          }
        },
        "required": [
          "url",
          "method",
          "webhookEvents",
          "name",
          "wabaNumbers"
        ]
      },
      "PublicWebhook": {
        "type": "object",
        "properties": {
          "wabaNumber": {
            "type": "string",
            "description": "The WABA (WhatsApp Business Account) number associated with the webhook",
            "example": "+919999999999"
          },
          "webhookEventType": {
            "type": "string",
            "description": "The type of event that triggers the webhook",
            "enum": [
              "MESSAGE_RECEIVED",
              "MESSAGE_STATUS_UPDATE",
              "CHAT_ASSIGNED_TO_AGENT",
              "ASSIGNED_AGENT_STATUS_CHANGED",
              "CHAT_UNASSIGNED",
              "UPDATE_CUSTOMER_CUSTOM_FIELD",
              "WIDGET_LEAD_RECEIVED",
              "VERIFIED_WIDGET_LEAD_RECEIVED",
              "NEW_LEAD",
              "RAW_CLOUD_API_WEBHOOK",
              "CLOSE_CONVERSATION",
              "TEMPLATE_UPDATE",
              "ADD_TAG",
              "REMOVE_TAG",
              "CALL_TO_WHATSAPP_MESSAGE_RECEIVED",
              "CONVERSATION_OPENED",
              "CUSTOMER_BUSINESS_CHAT_OPEN",
              "POST_CALL_DISPOSITION",
              "CALL_INCOMING_INITIATED",
              "CALL_OUTGOING_INITIATED",
              "CALL_ACCEPTED",
              "CALL_ENDED",
              "CALL_REJECTED",
              "CALL_MISSED",
              "CALL_NOT_PICKED",
              "STANDBY_MESSAGE_RECEIVED",
              "MESSAGING_HANDOVER"
            ],
            "example": "MESSAGE_RECEIVED"
          }
        },
        "required": [
          "wabaNumber",
          "webhookEventType"
        ]
      },
      "RegisterPublicWebhookResponseV2": {
        "type": "object",
        "properties": {
          "validWebhooks": {
            "type": "array",
            "description": "A list of valid webhooks",
            "items": {
              "$ref": "#/components/schemas/PublicWebhook"
            },
            "example": [
              {
                "wabaNumber": "+919999999999",
                "webhookEventType": "MESSAGE_RECEIVED"
              }
            ]
          },
          "invalidWebhooks": {
            "type": "array",
            "description": "A list of invalid webhooks",
            "items": {
              "$ref": "#/components/schemas/PublicWebhook"
            },
            "example": [
              {
                "wabaNumber": "+918888888888",
                "webhookEventType": "INVALID_EVENT"
              }
            ]
          },
          "invalidWabaNumbers": {
            "type": "array",
            "description": "A list of invalid WABA numbers",
            "items": {
              "type": "string",
              "example": "+918888888880"
            }
          }
        },
        "required": [
          "validWebhooks",
          "invalidWebhooks",
          "invalidWabaNumbers"
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