Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Get an AI summary of a WhatsApp group conversation

Returns an AI-generated summary of a WhatsApp group conversation for an exact date range. Note: the legacy `/whatsapp/wa-group/ai-summary` spelling still resolves via the generic group passthrough, but `/channel/wa-group/ai-summary` is the canonical path. The range is inclusive, interpreted in UTC at day granularity, and may span at most 7 days (Monday to Sunday is allowed; an 8th day is rejected). The summary is generated once per distinct date range and stored, so repeating the same request returns the stored copy without a further AI call. It is regenerated automatically when the conversation inside the range changes. Each generation consumes one AI summarisation credit; a stored result costs nothing. Requires the chat-summary feature to be enabled for the organization.

# OpenAPI definition

```json
{
  "openapi": "3.0.3",
  "info": {
    "title": "WhatsApp Group Public API",
    "description": "Public APIs for creating and managing WhatsApp groups.",
    "version": "1.0.0"
  },
  "servers": [
    {
      "url": "https://public.doubletick.io",
      "description": "Production"
    }
  ],
  "security": [
    {
      "ApiKeyAuth": []
    }
  ],
  "components": {
    "securitySchemes": {
      "ApiKeyAuth": {
        "type": "apiKey",
        "in": "header",
        "name": "x-public-api-key"
      }
    },
    "schemas": {
      "GroupAiSummaryResponse": {
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
            "example": "2026-06-17T23:59:59.999Z"
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
            "description": "Number of members who sent a message in the range",
            "example": 2
          },
          "members": {
            "type": "array",
            "description": "Members who sent a message in the range",
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
                  "example": "+15551230000",
                  "description": "The member's WhatsApp number"
                }
              }
            }
          },
          "chatMessagesCount": {
            "type": "integer",
            "example": 61
          },
          "callsCount": {
            "type": "integer",
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
  "paths": {
    "/channel/wa-group/ai-summary": {
      "get": {
        "summary": "Get an AI summary of a WhatsApp group conversation",
        "description": "Returns an AI-generated summary of a WhatsApp group conversation for an exact date range. Note: the legacy `/whatsapp/wa-group/ai-summary` spelling still resolves via the generic group passthrough, but `/channel/wa-group/ai-summary` is the canonical path. The range is inclusive, interpreted in UTC at day granularity, and may span at most 7 days (Monday to Sunday is allowed; an 8th day is rejected). The summary is generated once per distinct date range and stored, so repeating the same request returns the stored copy without a further AI call. It is regenerated automatically when the conversation inside the range changes. Each generation consumes one AI summarisation credit; a stored result costs nothing. Requires the chat-summary feature to be enabled for the organization.",
        "operationId": "getWhatsappGroupAiSummary",
        "parameters": [
          {
            "name": "groupId",
            "in": "query",
            "required": true,
            "schema": {
              "type": "string"
            },
            "description": "Unique identifier for the group"
          },
          {
            "name": "startDate",
            "in": "query",
            "required": true,
            "schema": {
              "type": "string",
              "format": "date"
            },
            "description": "Inclusive start of the range (UTC). Example: 2026-06-15"
          },
          {
            "name": "endDate",
            "in": "query",
            "required": true,
            "schema": {
              "type": "string",
              "format": "date"
            },
            "description": "Inclusive end of the range (UTC). Must be on or after startDate, and at most 7 days from it. Example: 2026-06-21"
          }
        ],
        "responses": {
          "200": {
            "description": "Summary retrieved successfully",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/GroupAiSummaryResponse"
                }
              }
            }
          },
          "400": {
            "description": "Invalid parameters, or the date range is inverted or longer than 7 days"
          },
          "401": {
            "description": "Missing or invalid API key, or insufficient permissions for this group"
          },
          "403": {
            "description": "The chat-summary feature is not enabled for this organization"
          },
          "404": {
            "description": "Group not found for the given groupId"
          },
          "422": {
            "description": "Insufficient AI credits"
          },
          "429": {
            "description": "Rate limit exceeded"
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
      "0": 105,
      "1": 178,
      "2": 248,
      "3": 135,
      "4": 237,
      "5": 154,
      "6": 193,
      "7": 161,
      "8": 200,
      "9": 32,
      "10": 222,
      "11": 4
    }
  }
}
```