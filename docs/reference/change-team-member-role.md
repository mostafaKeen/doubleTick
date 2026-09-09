Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Change Team Member Role

Change a team member's organization role and/or their role on one or more WhatsApp Business Accounts (WABA). At least one of orgRole or wabaRoles must be provided.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "paths": {
    "/team-member/role": {
      "patch": {
        "operationId": "change-team-member-role",
        "summary": "Change Team Member Role",
        "description": "Change a team member's organization role and/or their role on one or more WhatsApp Business Accounts (WABA). At least one of orgRole or wabaRoles must be provided.",
        "tags": [
          "Teams"
        ],
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/ChangeTeamMemberRoleDto"
              }
            }
          }
        },
        "responses": {
          "200": {
            "description": "Resulting role state of the team member",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/ChangeTeamMemberRoleResponseDto"
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
                      "example": "At least one of orgRole or wabaRoles must be provided"
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
                      "example": "Unauthorized"
                    }
                  }
                }
              }
            }
          },
          "422": {
            "description": "The team member, role, or WABA could not be resolved.",
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
                      "example": "Team member not found"
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
      "RoleReferenceDto": {
        "type": "object",
        "properties": {
          "roleId": {
            "type": "string",
            "description": "Role id as returned by Get All Roles. Provide exactly one of roleId or roleName.",
            "example": "9f8b7c6d-1234-4a5b-8c9d-0e1f2a3b4c5d"
          },
          "roleName": {
            "type": "string",
            "description": "Role name as returned by Get All Roles. Provide exactly one of roleId or roleName. Matched exactly (case-sensitive).",
            "example": "Admin"
          }
        }
      },
      "WabaRoleChangeDto": {
        "type": "object",
        "properties": {
          "wabaNumber": {
            "type": "string",
            "description": "WhatsApp Business Account number (with country code) to apply the role change to.",
            "example": "911111111111"
          },
          "roleId": {
            "type": "string",
            "description": "WABA role id as returned by Get All Roles. Provide exactly one of roleId or roleName.",
            "example": "3c4d5e6f-2345-4b6c-9d0e-1f2a3b4c5d6e"
          },
          "roleName": {
            "type": "string",
            "description": "WABA role name as returned by Get All Roles. Provide exactly one of roleId or roleName. Matched exactly (case-sensitive).",
            "example": "Agent"
          }
        },
        "required": [
          "wabaNumber"
        ]
      },
      "ChangeTeamMemberRoleDto": {
        "type": "object",
        "properties": {
          "memberPhoneNumber": {
            "type": "string",
            "description": "Phone number (with country code) of the team member whose roles are being changed.",
            "example": "919999999999"
          },
          "orgRole": {
            "description": "New organization-level role for the member, referenced by EITHER roleId OR roleName (both are returned by Get All Roles). Optional, but at least one of orgRole or wabaRoles must be provided.",
            "example": {
              "roleId": "9f8b7c6d-1234-4a5b-8c9d-0e1f2a3b4c5d"
            },
            "allOf": [
              {
                "$ref": "#/components/schemas/RoleReferenceDto"
              }
            ]
          },
          "wabaRoles": {
            "type": "array",
            "description": "Per-WABA role changes for the member. Each entry references its role by EITHER roleId OR roleName (both are returned by Get All Roles) — the example below shows one of each. Optional, but at least one of orgRole or wabaRoles must be provided. Duplicate wabaNumber entries are rejected.",
            "example": [
              {
                "wabaNumber": "911111111111",
                "roleId": "3c4d5e6f-2345-4b6c-9d0e-1f2a3b4c5d6e"
              },
              {
                "wabaNumber": "912222222222",
                "roleName": "Agent"
              }
            ],
            "items": {
              "$ref": "#/components/schemas/WabaRoleChangeDto"
            }
          }
        },
        "required": [
          "memberPhoneNumber"
        ]
      },
      "ChangeTeamMemberRoleWabaRoleResponseDto": {
        "type": "object",
        "properties": {
          "wabaNumber": {
            "type": "string",
            "description": "WABA number the role change was applied to",
            "example": "911111111111"
          },
          "wabaName": {
            "type": "string",
            "nullable": true,
            "description": "Display name of the WABA",
            "example": "Acme Support"
          },
          "roleId": {
            "type": "string",
            "description": "Resulting WABA role id",
            "example": "3c4d5e6f-2345-4b6c-9d0e-1f2a3b4c5d6e"
          },
          "roleName": {
            "type": "string",
            "description": "Resulting WABA role name",
            "example": "Agent"
          }
        },
        "required": [
          "wabaNumber",
          "wabaName",
          "roleId",
          "roleName"
        ]
      },
      "ChangeTeamMemberRoleWabaRoleErrorDto": {
        "type": "object",
        "properties": {
          "wabaNumber": {
            "type": "string",
            "description": "WABA number of the entry that failed validation",
            "example": "911111111111"
          },
          "error": {
            "type": "string",
            "description": "Why the entry was not applied",
            "example": "Role not found"
          }
        },
        "required": [
          "wabaNumber",
          "error"
        ]
      },
      "ChangeTeamMemberRoleWabaRolesResultDto": {
        "type": "object",
        "properties": {
          "added": {
            "type": "array",
            "description": "Entries that were applied successfully",
            "items": {
              "$ref": "#/components/schemas/ChangeTeamMemberRoleWabaRoleResponseDto"
            }
          },
          "errored": {
            "type": "array",
            "description": "Entries that failed validation. Valid entries are still applied.",
            "items": {
              "$ref": "#/components/schemas/ChangeTeamMemberRoleWabaRoleErrorDto"
            }
          }
        },
        "required": [
          "added",
          "errored"
        ]
      },
      "ChangeTeamMemberRoleResponseDto": {
        "type": "object",
        "properties": {
          "success": {
            "type": "boolean",
            "example": true
          },
          "memberPhoneNumber": {
            "type": "string",
            "description": "Phone number of the team member whose role was changed",
            "example": "919999999999"
          },
          "orgRoleId": {
            "type": "string",
            "description": "Resulting org role id. Present only if orgRole was requested.",
            "example": "9f8b7c6d-1234-4a5b-8c9d-0e1f2a3b4c5d"
          },
          "orgRoleName": {
            "type": "string",
            "description": "Resulting org role name. Present only if orgRole was requested.",
            "example": "ADMIN"
          },
          "wabaRoles": {
            "description": "Per-entry outcome for the requested WABA role changes (partial success: valid entries are applied, invalid entries are reported in errored). Present only if wabaRoles was requested.",
            "allOf": [
              {
                "$ref": "#/components/schemas/ChangeTeamMemberRoleWabaRolesResultDto"
              }
            ]
          }
        },
        "required": [
          "success",
          "memberPhoneNumber"
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