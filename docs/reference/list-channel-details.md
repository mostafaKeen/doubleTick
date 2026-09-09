Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# List Channel Details

List the connected WhatsApp channels assigned to the caller.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/organization/channel/profile": {
      "get": {
        "operationId": "list-channel-details",
        "summary": "List Channel Details",
        "description": "List the connected WhatsApp channels assigned to the caller.",
        "tags": [
          "Channel"
        ],
        "parameters": [
          {
            "name": "wabaNumber",
            "in": "query",
            "required": false,
            "description": "Filter to specific channels by WhatsApp number; repeat for several (`?wabaNumber=17042493212&wabaNumber=919999999999`, max 50).",
            "schema": {
              "type": "array",
              "maxItems": 50,
              "items": {
                "type": "string"
              }
            },
            "style": "form",
            "explode": true,
            "example": [
              "17042493212"
            ]
          }
        ],
        "responses": {
          "200": {
            "description": "Channels assigned to the calling user.",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/ListChannelDetailsResponseDto"
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
                      "example": "You don't have the required permissions to perform this action."
                    },
                    "error": {
                      "type": "string",
                      "example": "Forbidden"
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
      "ListedChannelProfileDto": {
        "type": "object",
        "description": "Business profile of the channel. Every key is always present. A channel commonly has only some of these fields filled in on WhatsApp — any field the channel has not set is returned as `null` (and `websites` as an empty array), never omitted.",
        "properties": {
          "websites": {
            "type": "array",
            "items": {
              "type": "string"
            },
            "description": "Empty array when the channel has no websites set.",
            "example": [
              "https://acme.com"
            ]
          },
          "email": {
            "type": "string",
            "nullable": true,
            "example": "support@acme.com"
          },
          "vertical": {
            "type": "string",
            "nullable": true,
            "example": "Medical and Health"
          },
          "address": {
            "type": "string",
            "nullable": true,
            "example": "1 Market St, San Francisco"
          },
          "description": {
            "type": "string",
            "nullable": true,
            "example": "24x7 customer support"
          },
          "about": {
            "type": "string",
            "nullable": true,
            "example": "Welcome to Acme"
          },
          "logo": {
            "type": "string",
            "nullable": true,
            "description": "URL of the stored logo. `null` when the channel has no logo.",
            "example": "https://media.doubletick.io/acme/logo.png"
          }
        }
      },
      "ListedChannelDto": {
        "type": "object",
        "properties": {
          "channelId": {
            "type": "string",
            "example": "intg_0000000001"
          },
          "wabaNumber": {
            "type": "string",
            "nullable": true,
            "description": "WhatsApp number of the channel.",
            "example": "919999999999"
          },
          "displayName": {
            "type": "string",
            "nullable": true,
            "description": "Display name of the channel.",
            "example": "Acme Support"
          },
          "status": {
            "type": "string",
            "enum": [
              "CONNECTED"
            ],
            "example": "CONNECTED"
          },
          "useMetaName": {
            "type": "boolean",
            "example": false
          },
          "profile": {
            "nullable": true,
            "description": "`null` when this channel has no business profile.",
            "allOf": [
              {
                "$ref": "#/components/schemas/ListedChannelProfileDto"
              }
            ]
          }
        }
      },
      "ListChannelDetailsResponseDto": {
        "type": "object",
        "properties": {
          "count": {
            "type": "number",
            "description": "Number of channels returned in `channels`. Present only when listing all assigned channels — it is omitted when filtering by `wabaNumber`.",
            "example": 3
          },
          "channels": {
            "type": "array",
            "items": {
              "$ref": "#/components/schemas/ListedChannelDto"
            }
          }
        },
        "example": {
          "count": 3,
          "channels": [
            {
              "channelId": "intg_0000000001",
              "wabaNumber": "919999999999",
              "displayName": "Acme Support",
              "status": "CONNECTED",
              "useMetaName": false,
              "profile": {
                "websites": [
                  "https://acme.com"
                ],
                "email": "support@acme.com",
                "vertical": "Medical and Health",
                "address": "1 Market St, San Francisco",
                "description": "24x7 customer support",
                "about": "Welcome to Acme",
                "logo": "https://media.doubletick.io/acme/logo.png"
              }
            },
            {
              "channelId": "intg_0000000002",
              "wabaNumber": "919777777777",
              "displayName": "Acme Notifications",
              "status": "CONNECTED",
              "useMetaName": false,
              "profile": {
                "websites": [
                  "https://acme.com/help"
                ],
                "email": null,
                "vertical": "Other",
                "address": null,
                "description": null,
                "about": "Notifications from Acme",
                "logo": "https://media.doubletick.io/acme/notifications.png"
              }
            },
            {
              "channelId": "intg_0000000003",
              "wabaNumber": "919888888888",
              "displayName": "Acme Sales",
              "status": "CONNECTED",
              "useMetaName": false,
              "profile": null
            }
          ]
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