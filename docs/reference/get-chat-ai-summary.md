Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Get AI Summary of a 1:1 Chat

AI summary of a 1:1 conversation over an inclusive UTC date range of at most 7 days. Requires the Chat Summary feature to be enabled for the organization, and consumes one AI summarisation credit per generation.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/channel/chat/ai-summary": {
      "get": {
        "operationId": "get-chat-ai-summary",
        "summary": "Get AI Summary of a 1:1 Chat",
        "description": "AI summary of a 1:1 conversation over an inclusive UTC date range of at most 7 days. Requires the Chat Summary feature to be enabled for the organization, and consumes one AI summarisation credit per generation.",
        "tags": [
          "AI Summary"
        ],
        "parameters": [
          {
            "name": "wabaNumber",
            "in": "query",
            "required": true,
            "description": "The WhatsApp Business Account (WABA) number the conversation belongs to. Digits only, no leading +.",
            "schema": {
              "type": "string",
              "example": "919999999998"
            }
          },
          {
            "name": "customerNumber",
            "in": "query",
            "required": true,
            "description": "The customer's WhatsApp number, with country code. Digits only, no leading +.",
            "schema": {
              "type": "string",
              "example": "15551230000"
            }
          },
          {
            "name": "startDate",
            "in": "query",
            "required": true,
            "description": "Inclusive start of the range (UTC, day granularity).",
            "schema": {
              "type": "string",
              "format": "date",
              "example": "2026-06-15"
            }
          },
          {
            "name": "endDate",
            "in": "query",
            "required": true,
            "description": "Inclusive end of the range (UTC). Must be on or after `startDate`, and at most 7 days from it.",
            "schema": {
              "type": "string",
              "format": "date",
              "example": "2026-06-21"
            }
          }
        ],
        "responses": {
          "200": {
            "description": "Summary retrieved (freshly generated or served from cache).",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/ChatAiSummaryResponseDto"
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
                      "example": "endDate must be on or after startDate and at most 7 days from it"
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
                      "example": "The Chat Summary feature is not enabled for this organization"
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
                      "example": "No chat found for the given wabaNumber and customerNumber"
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
                      "example": "Insufficient AI credits"
                    },
                    "error": {
                      "type": "string",
                      "example": "Unprocessable Entity"
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
      "ChatAiSummaryResponseDto": {
        "type": "object",
        "properties": {
          "startDate": {
            "type": "string",
            "format": "date-time",
            "example": "2026-06-15T00:00:00.000Z"
          },
          "endDate": {
            "type": "string",
            "format": "date-time",
            "example": "2026-06-21T23:59:59.999Z"
          },
          "chatType": {
            "type": "string",
            "example": "INDIVIDUAL"
          },
          "chatMessagesCount": {
            "type": "integer",
            "description": "Messages included in the summary (calls excluded).",
            "example": 34
          },
          "callsCount": {
            "type": "integer",
            "description": "Calls included in the summary.",
            "example": 1
          },
          "summaries": {
            "type": "array",
            "description": "One item per distinct topic discussed in the range, oldest first. An empty array when the range has no substantive activity.",
            "items": {
              "type": "object",
              "properties": {
                "title": {
                  "type": "string",
                  "description": "Short, sentence-case title of the topic (at most 7 words).",
                  "example": "Order shipment delay reported"
                },
                "description": {
                  "type": "string",
                  "description": "About 30 words summarizing the topic.",
                  "example": "John Doe reported that order #4821 had not shipped and asked for an update; the agent confirmed a two-day delay, apologised, and shared a revised delivery date, which was accepted."
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