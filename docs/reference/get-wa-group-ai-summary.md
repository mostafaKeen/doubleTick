Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Get AI Summary of a WhatsApp Group

AI summary of a WhatsApp group conversation over an inclusive UTC date range of at most 7 days, plus the members who messaged in that range. Requires the Chat Summary feature to be enabled for the organization, and consumes one AI summarisation credit per generation.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/channel/wa-group/ai-summary": {
      "get": {
        "operationId": "get-wa-group-ai-summary",
        "summary": "Get AI Summary of a WhatsApp Group",
        "description": "AI summary of a WhatsApp group conversation over an inclusive UTC date range of at most 7 days, plus the members who messaged in that range. Requires the Chat Summary feature to be enabled for the organization, and consumes one AI summarisation credit per generation.",
        "tags": [
          "AI Summary"
        ],
        "parameters": [
          {
            "name": "groupId",
            "in": "query",
            "required": true,
            "description": "The group's identifier (the `clientCustomGroupId` you assigned when creating the group).",
            "schema": {
              "type": "string",
              "example": "vip-support-42"
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
                  "$ref": "#/components/schemas/WaGroupAiSummaryResponseDto"
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
                      "example": "Group not found for the given groupId"
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
      "WaGroupAiSummaryResponseDto": {
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
            "example": "WA_GROUP"
          },
          "groupName": {
            "type": "string",
            "example": "VIP Support"
          },
          "memberCount": {
            "type": "integer",
            "description": "Number of members who sent a message in the range.",
            "example": 2
          },
          "members": {
            "type": "array",
            "description": "Members who sent a message in the range.",
            "items": {
              "type": "object",
              "properties": {
                "id": {
                  "type": "string",
                  "example": "cust_a1b2c3"
                },
                "name": {
                  "type": "string",
                  "example": "John Doe"
                },
                "customerNumber": {
                  "type": "string",
                  "nullable": true,
                  "description": "The member's WhatsApp number.",
                  "example": "15551230000"
                }
              }
            }
          },
          "chatMessagesCount": {
            "type": "integer",
            "description": "Messages included in the summary (calls excluded).",
            "example": 61
          },
          "callsCount": {
            "type": "integer",
            "description": "Calls included in the summary.",
            "example": 0
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
                  "example": "Launch checklist coordinated"
                },
                "description": {
                  "type": "string",
                  "description": "About 30 words summarizing the topic.",
                  "example": "Members coordinated the go-live checklist; two blockers were raised and resolved, and the final launch time was confirmed for Friday morning."
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