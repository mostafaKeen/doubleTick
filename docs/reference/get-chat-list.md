Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Fetch chat list (inbox)

Retrieve the chat list (inbox) for the authenticated org/user, with optional filtering and cursor-based pagination.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/chats": {
      "get": {
        "operationId": "get-chat-list",
        "summary": "Fetch chat list (inbox)",
        "description": "Retrieve the chat list (inbox) for the authenticated org/user, with optional filtering and cursor-based pagination.",
        "tags": [
          "Chats"
        ],
        "parameters": [
          {
            "name": "limit",
            "in": "query",
            "required": false,
            "description": "Number of chats to return. Min 1, max 100. Defaults to 10.",
            "schema": {
              "type": "number",
              "minimum": 1,
              "maximum": 100,
              "example": 10
            }
          },
          {
            "name": "status",
            "in": "query",
            "required": false,
            "description": "Filter chats by status.",
            "schema": {
              "type": "string",
              "enum": [
                "all",
                "unread",
                "unresolved",
                "done",
                "waiting_for_response",
                "open",
                "open_cart"
              ],
              "example": "unresolved"
            }
          },
          {
            "name": "tags",
            "in": "query",
            "required": false,
            "description": "Comma-separated tag names to filter chats.",
            "schema": {
              "type": "string",
              "example": "vip,lead"
            }
          },
          {
            "name": "assignedUserPhone",
            "in": "query",
            "required": false,
            "description": "Phone number of the assigned agent (with or without leading +). Use the literal string 'null' to fetch unassigned chats.",
            "schema": {
              "type": "string",
              "example": "+919999999999"
            }
          },
          {
            "name": "wabaNumbers",
            "in": "query",
            "required": false,
            "description": "Comma-separated WhatsApp WABA phone numbers to filter chats. Only applies to WhatsApp chats. Use instagramHandles to filter Instagram chats.",
            "schema": {
              "type": "string",
              "example": "11234567890,19876543210"
            }
          },
          {
            "name": "instagramHandles",
            "in": "query",
            "required": false,
            "description": "Comma-separated Instagram account handles to filter chats. Accepts handles with or without leading @. Only applies to Instagram chats. Use wabaNumbers to filter WhatsApp chats.",
            "schema": {
              "type": "string",
              "example": "myaccount,otheraccount"
            }
          },
          {
            "name": "nextChatId",
            "in": "query",
            "required": false,
            "description": "Pagination cursor. Pass the value of paginationOptions.nextChatId from the previous response.",
            "schema": {
              "type": "string"
            }
          },
          {
            "name": "nextChatTimestamp",
            "in": "query",
            "required": false,
            "description": "Pagination cursor. Pass the value of paginationOptions.nextChatTimestamp from the previous response (epoch milliseconds).",
            "schema": {
              "type": "number",
              "example": 1711929600000
            }
          }
        ],
        "responses": {
          "200": {
            "description": "Success",
            "content": {
              "application/json": {
                "schema": {
                  "type": "object",
                  "properties": {
                    "chats": {
                      "type": "array",
                      "items": {
                        "type": "object",
                        "properties": {
                          "chatType": {
                            "type": "string",
                            "example": "INDIVIDUAL",
                            "description": "Type of chat. INDIVIDUAL for 1:1 customer chats (WhatsApp and Instagram), WA_GROUP for WhatsApp groups."
                          },
                          "name": {
                            "type": "string",
                            "example": "John Doe"
                          },
                          "phoneNumber": {
                            "type": "string",
                            "nullable": true,
                            "example": "919876543210",
                            "description": "Customer phone number (digits only, no leading +). Present for WhatsApp INDIVIDUAL chats. Null for Instagram chats."
                          },
                          "imageUrl": {
                            "type": "string",
                            "nullable": true,
                            "example": "https://example.com/profile.jpg"
                          },
                          "unreadCount": {
                            "type": "number",
                            "example": 3
                          },
                          "isDone": {
                            "type": "boolean",
                            "example": false
                          },
                          "optIn": {
                            "type": "boolean",
                            "example": true,
                            "description": "Whether the customer has opted in to receive messages. Always false for Instagram chats."
                          },
                          "chatUpdatedTime": {
                            "type": "number",
                            "example": 1711929600000,
                            "description": "Epoch milliseconds of the last chat update."
                          },
                          "lastMessageTime": {
                            "type": "number",
                            "example": 1711929600000,
                            "description": "Epoch milliseconds of the last message in the chat."
                          },
                          "wabaNumber": {
                            "type": "string",
                            "nullable": true,
                            "example": "11234567890",
                            "description": "Channel identifier. For WhatsApp: the WABA phone number (digits only, no leading +). For Instagram: the account handle without the @ prefix (e.g. myaccount)."
                          },
                          "wabaPhoneName": {
                            "type": "string",
                            "nullable": true,
                            "example": "Support Line"
                          },
                          "wabaDisplayName": {
                            "type": "string",
                            "nullable": true,
                            "example": "Acme Support"
                          },
                          "assignedUserName": {
                            "type": "string",
                            "nullable": true,
                            "example": "Jane Smith"
                          },
                          "assignedUserNumber": {
                            "type": "string",
                            "nullable": true,
                            "example": "919999999999",
                            "description": "Phone number of the assigned agent (digits only, no leading +). Null if unassigned."
                          },
                          "tagNames": {
                            "type": "array",
                            "items": {
                              "type": "string"
                            },
                            "example": [
                              "vip",
                              "lead"
                            ]
                          },
                          "customFields": {
                            "type": "array",
                            "items": {
                              "type": "object",
                              "properties": {
                                "name": {
                                  "type": "string",
                                  "example": "Country"
                                },
                                "type": {
                                  "type": "string",
                                  "example": "single_select"
                                },
                                "value": {
                                  "type": "string",
                                  "nullable": true,
                                  "example": "India"
                                },
                                "values": {
                                  "type": "array",
                                  "items": {
                                    "type": "object"
                                  },
                                  "description": "Present for multi_select type fields."
                                }
                              }
                            }
                          },
                          "lastMessage": {
                            "type": "object",
                            "description": "Last message in the chat. Internal IDs are stripped from this object."
                          },
                          "isSlaActive": {
                            "type": "boolean",
                            "example": false
                          },
                          "slaDueDate": {
                            "type": "string",
                            "format": "date-time",
                            "nullable": true
                          },
                          "slaBreachLevel": {
                            "type": "string",
                            "nullable": true,
                            "example": null
                          },
                          "eligibleMessageTypes": {
                            "type": "array",
                            "items": {
                              "type": "string"
                            },
                            "example": [
                              "ANY"
                            ]
                          },
                          "isReminderTriggered": {
                            "type": "boolean",
                            "example": false
                          },
                          "providerType": {
                            "type": "string",
                            "nullable": true,
                            "enum": [
                              "INSTAGRAM",
                              "GENERIC_CHANNEL",
                              null
                            ],
                            "example": null,
                            "description": "Channel provider type. Null for WhatsApp chats. INSTAGRAM for Instagram DMs."
                          }
                        }
                      }
                    },
                    "paginationOptions": {
                      "type": "object",
                      "properties": {
                        "hasMoreChats": {
                          "type": "boolean",
                          "example": true
                        },
                        "nextChatId": {
                          "type": "string",
                          "nullable": true,
                          "description": "Pass as the nextChatId query param to fetch the next page. Null when there are no more chats."
                        },
                        "nextChatTimestamp": {
                          "type": "number",
                          "nullable": true,
                          "description": "Pass as the nextChatTimestamp query param to fetch the next page (epoch milliseconds)."
                        }
                      }
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