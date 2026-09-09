Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Update Channel Details

Update the business profile of a single WhatsApp channel (WABA). Send fields as `application/json`, or as `multipart/form-data` to upload a logo. At least one updatable field is required. Supply the logo as either the binary `logo` (multipart) or `logoUrl` (a public https image URL) — not both. Rate limits: 60 requests/hour per organization and at most 1 successful update per channel per hour.

# OpenAPI definition

````json
{
  "openapi": "3.0.0",
  "paths": {
    "/organization/channel/profile": {
      "patch": {
        "operationId": "update-channel-details",
        "summary": "Update Channel Details",
        "description": "Update the business profile of a single WhatsApp channel (WABA). Send fields as `application/json`, or as `multipart/form-data` to upload a logo. At least one updatable field is required. Supply the logo as either the binary `logo` (multipart) or `logoUrl` (a public https image URL) — not both. Rate limits: 60 requests/hour per organization and at most 1 successful update per channel per hour.",
        "tags": [
          "Channel"
        ],
        "parameters": [],
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/UpdateChannelDetailsDto"
              }
            },
            "multipart/form-data": {
              "schema": {
                "allOf": [
                  {
                    "$ref": "#/components/schemas/UpdateChannelDetailsDto"
                  },
                  {
                    "type": "object",
                    "properties": {
                      "logo": {
                        "type": "string",
                        "format": "binary",
                        "description": "Logo image (JPG or PNG, square, max 640x640px, max 800KB). Alternative to `logoUrl`."
                      }
                    }
                  }
                ]
              }
            }
          }
        },
        "responses": {
          "200": {
            "description": "Channel profile updated; returns the re-synced profile.",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/UpdateChannelDetailsResponseDto"
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
                      "example": "Provide either logo or logoUrl, not both"
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
                      "example": "You do not have access to this channel"
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
                      "example": "Integration not found"
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
          "409": {
            "description": "Conflict",
            "content": {
              "application/json": {
                "schema": {
                  "type": "object",
                  "properties": {
                    "statusCode": {
                      "type": "number",
                      "example": 409
                    },
                    "message": {
                      "type": "string",
                      "example": "Integration is not connected"
                    },
                    "error": {
                      "type": "string",
                      "example": "Conflict"
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
                      "example": "Logo must be a JPG or PNG image (square, max 640x640, max 800KB)"
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
                      "example": "This channel was already updated in the last hour. Please try again after an hour."
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
      "UpdateChannelDetailsDto": {
        "type": "object",
        "required": [
          "integrationId"
        ],
        "properties": {
          "integrationId": {
            "type": "string",
            "description": "ID of the channel (WABA integration) to update. Must belong to your organization.",
            "example": "intg_0000000001"
          },
          "displayName": {
            "type": "string",
            "maxLength": 256,
            "example": "Acme Support"
          },
          "address": {
            "type": "string",
            "maxLength": 256,
            "example": "1 Market St, San Francisco"
          },
          "description": {
            "type": "string",
            "maxLength": 512,
            "example": "24x7 customer support"
          },
          "email": {
            "type": "string",
            "format": "email",
            "maxLength": 128,
            "example": "support@acme.com"
          },
          "vertical": {
            "type": "string",
            "enum": [
              "Automotive",
              "Beauty, Spa and Salon",
              "Clothing and Apparel",
              "Education",
              "Entertainment",
              "Event Planning and Service",
              "Finance and Banking",
              "Food and Grocery",
              "Public Service",
              "Hotel and Lodging",
              "Medical and Health",
              "Non-profit",
              "Professional Services",
              "Shopping and Retail",
              "Travel and Transportation",
              "Restaurant",
              "Other"
            ],
            "example": "Medical and Health"
          },
          "about": {
            "type": "string",
            "minLength": 1,
            "maxLength": 139,
            "description": "Profile 'about' text. Must not contain ~, ```, *, or _.",
            "example": "Welcome to Acme"
          },
          "websites": {
            "type": "array",
            "maxItems": 2,
            "items": {
              "type": "string",
              "format": "uri",
              "maxLength": 256
            },
            "example": [
              "https://acme.com"
            ]
          },
          "logoUrl": {
            "type": "string",
            "format": "uri",
            "maxLength": 2048,
            "description": "Public https URL of a JPG or PNG logo (square, max 640x640px, max 800KB). Alternative to the binary `logo`; both cannot be sent together.",
            "example": "https://acme.com/logo.png"
          }
        }
      },
      "ChannelProfileDto": {
        "type": "object",
        "properties": {
          "websites": {
            "type": "array",
            "items": {
              "type": "string"
            },
            "example": [
              "https://acme.com"
            ]
          },
          "email": {
            "type": "string",
            "example": "support@acme.com"
          },
          "vertical": {
            "type": "string",
            "example": "Medical and Health"
          },
          "address": {
            "type": "string",
            "example": "1 Market St, San Francisco"
          },
          "description": {
            "type": "string",
            "example": "24x7 customer support"
          },
          "logo": {
            "type": "string",
            "description": "URL of the stored logo.",
            "example": "https://media.doubletick.io/acme/logo.png"
          },
          "about": {
            "type": "string",
            "example": "Welcome to Acme"
          },
          "orgName": {
            "type": "string",
            "example": "Acme Inc"
          },
          "displayName": {
            "type": "string",
            "example": "Acme Support"
          },
          "useMetaName": {
            "type": "boolean",
            "example": false
          }
        }
      },
      "UpdateChannelDetailsResponseDto": {
        "type": "object",
        "properties": {
          "status": {
            "type": "string",
            "example": "SUCCESS"
          },
          "integrationId": {
            "type": "string",
            "example": "intg_0000000001"
          },
          "profile": {
            "$ref": "#/components/schemas/ChannelProfileDto"
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
````