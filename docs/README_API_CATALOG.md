# DoubleTick WhatsApp API Complete Scraped Reference & Catalog

- Total Guides: **51**
- Total Endpoints: **93**
- Total Webhook Specs: **21**

## Parsed Endpoints

| Method | Endpoint | Title | Description |
|---|---|---|---|
| `POST` | `/agent/status` | [Set agent ONLINE/OFFLINE](https://docs.doubletick.io/reference/public-agent-status.md) | Updates an agent's availability to online or offline. The agent is selected by `phone` (must match t |
| `POST` | `/ccavenue/payment-link` | [Generate CCAvenue Payment Link](https://docs.doubletick.io/reference/generate-ccavenue-payment-link.md) | Generates a CCAvenue payment link for a UPI payment configuration, with optional pre-filling of the  |
| `GET` | `/channel/chat/ai-summary` | [Get AI Summary of a 1:1 Chat](https://docs.doubletick.io/reference/get-chat-ai-summary.md) | AI summary of a 1:1 conversation over an inclusive UTC date range of at most 7 days. Requires the Ch |
| `GET` | `/channel/wa-group/ai-summary` | [Get AI Summary of a WhatsApp Group](https://docs.doubletick.io/reference/get-wa-group-ai-summary.md) | AI summary of a WhatsApp group conversation over an inclusive UTC date range of at most 7 days, plus |
| `GET` | `/channel/wa-group/ai-summary` | [Get an AI summary of a WhatsApp group conversation](https://docs.doubletick.io/reference/getwhatsappgroupaisummary.md) | Returns an AI-generated summary of a WhatsApp group conversation for an exact date range. Note: the  |
| `GET` | `/chat-messages` | [Get chat messages for a customer of WABA number](https://docs.doubletick.io/reference/get-chat-messages.md) | Retrieve chat messages sent by the specified WhatsApp Business Account (WABA) number, with optional  |
| `GET` | `/chat/status` | [Get chat window status](https://docs.doubletick.io/reference/get-chat-window-status.md) | Get chat window status for a customer with given WABA number |
| `GET` | `/chats` | [Fetch chat list (inbox)](https://docs.doubletick.io/reference/get-chat-list.md) | Retrieve the chat list (inbox) for the authenticated org/user, with optional filtering and cursor-ba |
| `POST` | `/collaborators/add` | [Add chat collaborators](https://docs.doubletick.io/reference/public-chat-collaborators-add.md) | Adds team members to a chat as collaborators. Identify the chat with `from` (your WhatsApp Business  |
| `POST` | `/collaborators/remove` | [Remove chat collaborators](https://docs.doubletick.io/reference/public-chat-collaborators-remove.md) | Removes collaborators from a chat using the same `from`, `to`, and `agentNumbers` rules as add. The  |
| `DELETE` | `/conversation/chat/messages` | [Clear chat messages](https://docs.doubletick.io/reference/clear-chat-messages-v2.md) | Clear all chat messages for a chat (async) |
| `POST` | `/custom-events/fire` | [Fire Custom Event](https://docs.doubletick.io/reference/fire-custom-event.md) | Fire a custom event directly from your own systems (CRM, order management, etc.). Use it to track ev |
| `DELETE` | `/customer` | [Delete Customer](https://docs.doubletick.io/reference/delete-customer.md) | Delete customers by phone number (max 10 per request). Deletion is processed asynchronously — a 201  |
| `POST` | `/customer/assign` | [Assign team member to customer](https://docs.doubletick.io/reference/assign-team-member-to-customer.md) |  Assign team member to customer  |
| `POST` | `/customer/assign-tags-custom-fields` | [Assign Custom Fields and/or Tags to Customer](https://docs.doubletick.io/reference/customer-assign-tags-custom-fields.md) | Assign Custom Fields and/or Tags to Customer |
| `POST` | `/customer/block` | [Block a customer](https://docs.doubletick.io/reference/block-unblock-customer.md) | Block customer using phone number  |
| `GET` | `/customer/check-reverted-on-time` | [Check reverted on time](https://docs.doubletick.io/reference/check-reverted-on-time.md) | Check reverted on time |
| `GET` | `/customer/details` | [Get customer details](https://docs.doubletick.io/reference/get-customer-details.md) | Get details of customer along with custom fields  |
| `POST` | `/customer/remove-tags-custom-fields` | [Remove Custom Fields and/or Tags from Customer](https://docs.doubletick.io/reference/customer-remove-tags-custom-fields.md) | Remove Custom Fields and/or Tags from Customer |
| `POST` | `/customer/unblock` | [Unblock a customer](https://docs.doubletick.io/reference/unblock-unblock-customer.md) | Unblock customer using phone number  |
| `POST` | `/embed/url` | [Embed Chat Conversation](https://docs.doubletick.io/reference/get-embed-url.md) | Returns a ready-to-use iframe URL that opens a specific WhatsApp conversation for a given contact's  |
| `POST` | `/export-chats` | [Export Chats For a Customer For Given WabaNumber](https://docs.doubletick.io/reference/export-chats-to-excel.md) | Export Chats For a WabaNumber |
| `DELETE` | `/groups` | [Delete groups](https://docs.doubletick.io/reference/delete-groups.md) | Deletes multiple groups |
| `GET` | `/groups` | [Get paginated groups](https://docs.doubletick.io/reference/get-paginated-groups-v2.md) | Returns a list of paginated groups based on search criteria and pagination parameters |
| `POST` | `/groups` | [Create a new group](https://docs.doubletick.io/reference/create-group.md) | Create a new group |
| `POST` | `/groups/add-members` | [Add members to a group](https://docs.doubletick.io/reference/add-members-to-group.md) | Adds members to an existing group |
| `DELETE` | `/groups/remove-members` | [Remove members from a group](https://docs.doubletick.io/reference/remove-members-from-group.md) | Removes members from an existing static group |
| `POST` | `/media/upload` | [Upload Media](https://docs.doubletick.io/reference/upload-media.md) | Upload Media to use in messages |
| `GET` | `/note` | [Get Note](https://docs.doubletick.io/reference/get-note.md) | Get a single note by its identifier. Rate limit: 60 requests per minute per organization. |
| `DELETE` | `/notes` | [Delete Note](https://docs.doubletick.io/reference/delete-note.md) | Delete a customer note by its identifier. Rate limit: 60 requests per minute per organization. |
| `GET` | `/notes` | [List Notes](https://docs.doubletick.io/reference/list-notes.md) | List notes for a customer chat or WA Group chat. Use `to` + `from` for customer chats, or `groupId`  |
| `PATCH` | `/notes` | [Update Note](https://docs.doubletick.io/reference/update-note.md) | Update a customer note by its identifier. Rate limit: 60 requests per minute per organization. |
| `POST` | `/notes` | [Create Note](https://docs.doubletick.io/reference/create-note.md) | Create a note attached to a customer chat or a WA Group chat. Use `to` + `from` for customer chats,  |
| `GET` | `/organization/channel/profile` | [List Channel Details](https://docs.doubletick.io/reference/list-channel-details.md) | List the connected WhatsApp channels assigned to the caller. |
| `PATCH` | `/organization/channel/profile` | [Update Channel Details](https://docs.doubletick.io/reference/update-channel-details.md) | Update the business profile of a single WhatsApp channel (WABA). Send fields as `application/json`,  |
| `GET` | `/quick-replies` | [Get Quick Replies](https://docs.doubletick.io/reference/get-quick-replies-v2.md) | Get quick replies with optional cursor pagination |
| `POST` | `/quick-replies` | [Create Quick Reply](https://docs.doubletick.io/reference/create-quick-reply-v2.md) | Create a quick reply |
| `GET` | `/quick-replies/details` | [Get Quick Reply Details](https://docs.doubletick.io/reference/get-quick-reply-details-v2.md) | Get a quick reply by shortcut |
| `DELETE` | `/quick-replies/{shortcut}` | [Delete Quick Reply](https://docs.doubletick.io/reference/delete-quick-reply-v2.md) | Delete a quick reply by shortcut |
| `PUT` | `/quick-replies/{shortcut}` | [Update Quick Reply](https://docs.doubletick.io/reference/update-quick-reply-v2.md) | Update a quick reply by shortcut |
| `GET` | `/roles` | [Get all roles](https://docs.doubletick.io/reference/get-all-roles.md) | Get all roles |
| `GET` | `/team` | [Get Team](https://docs.doubletick.io/reference/get-team.md) | Get Team members with their Reporting Managers |
| `DELETE` | `/team-member` | [Remove team member](https://docs.doubletick.io/reference/remove-team-member.md) | Remove team member from team |
| `POST` | `/team-member/assign` | [Assign team member to chat](https://docs.doubletick.io/reference/assign-team-member-to-chat.md) | Assign a team member to an individual customer chat or a WA group chat. Provide either customerPhone |
| `POST` | `/team-member/logout` | [Logout a team member](https://docs.doubletick.io/reference/logout-team-member.md) | Logout a team member |
| `POST` | `/team-member/make-reporting-manager` | [Make a team member a reporting manager](https://docs.doubletick.io/reference/make-team-member-reporting-manager.md) | Promotes an existing team member to a reporting manager by creating a team for them. Optionally assi |
| `PATCH` | `/team-member/reporting-manager` | [Change reporting manager](https://docs.doubletick.io/reference/change-reporting-manager.md) | Change reporting manager of the user |
| `PATCH` | `/team-member/role` | [Change Team Member Role](https://docs.doubletick.io/reference/change-team-member-role.md) | Change a team member's organization role and/or their role on one or more WhatsApp Business Accounts |
| `POST` | `/team-member/unassign` | [Unassign team member from a chat](https://docs.doubletick.io/reference/unassign-team-member-from-chat.md) | Unassign a team member from an individual customer chat or a WA group chat. Provide either customerP |
| `GET` | `/team/member` | [Get Team Member Details](https://docs.doubletick.io/reference/get-team-member-details.md) | Get detailed information about a specific team member including their role, reporting managers, and  |
| `POST` | `/team/members` | [Add member under reporting manager](https://docs.doubletick.io/reference/add-member-under-reporting-manager.md) | Add member under reporting manager |
| `DELETE` | `/template` | [Delete Template](https://docs.doubletick.io/reference/delete-template.md) | Delete Template |
| `POST` | `/template` | [Create New Template](https://docs.doubletick.io/reference/create-template.md) | Create New Template |
| `PATCH` | `/template/{templateId}` | [Edit Template](https://docs.doubletick.io/reference/edit-template.md) | Edit Template |
| `GET` | `/v2/templates` | [Get Templates](https://docs.doubletick.io/reference/get-templates.md) | Get Templates |
| `DELETE` | `/v2/webhook/deregister` | [Delete Webhooks](https://docs.doubletick.io/reference/delete-webhooks.md) | Delete Webhooks |
| `POST` | `/v2/webhook/register` | [Register New Webhook](https://docs.doubletick.io/reference/register-webhook.md) | Register New Webhook |
| `POST` | `/v2/webhook/{webhookId}` | [Edit Webhooks](https://docs.doubletick.io/reference/edit-webhooks.md) | Edit Webhooks |
| `GET` | `/v2/webhooks` | [Get Webhooks](https://docs.doubletick.io/reference/get-webhooks.md) | Get Webhooks |
| `POST` | `/v2/whatsapp/message/template` | [Send Whatsapp Template Message (V2)](https://docs.doubletick.io/reference/outgoing-messages-whatsapp-template-v2.md) | Send Whatsapp template messages. Requires to, from (WABA number), templateName, language, templateDa |
| `POST` | `/wa-group/assign-tags-custom-fields` | [Assign Custom Fields and/or Tags to WA Group](https://docs.doubletick.io/reference/wa-group-assign-tags-custom-fields.md) | Assign chat-level custom fields and/or tags to a WhatsApp Group identified by its groupId. |
| `POST` | `/wa-group/remove-tags-custom-fields` | [Remove Custom Fields and/or Tags from WA Group](https://docs.doubletick.io/reference/wa-group-remove-tags-custom-fields.md) | Remove chat-level custom fields and/or tags from a WhatsApp Group identified by its groupId. |
| `GET` | `/wallet/balance` | [Get wallet balance](https://docs.doubletick.io/reference/get-wallet-balanace-for-org.md) | Get wallet balance for Org |
| `POST` | `/whatsapp/accept` | [Accept incoming call](https://docs.doubletick.io/reference/acceptincomingcall.md) | Accepts an incoming WhatsApp call and joins the session. |
| `GET` | `/whatsapp/call/call-permissions` | [Get call permissions](https://docs.doubletick.io/reference/getcallpermissions.md) | Returns WhatsApp call permission and eligibility for a customer. |
| `POST` | `/whatsapp/call/create` | [Create outgoing call](https://docs.doubletick.io/reference/createoutgoingcall.md) | Initiates an outgoing WhatsApp voice call to a customer. |
| `POST` | `/whatsapp/call/license/invoke` | [Assign a calling license to a team member](https://docs.doubletick.io/reference/invoke-call-license.md) | Assigns a calling license to a team member identified by phone number, enabling them to make/receive |
| `POST` | `/whatsapp/call/license/revoke` | [Revoke a calling license from a team member](https://docs.doubletick.io/reference/revoke-call-license.md) | Revokes a previously assigned calling license from a team member identified by phone number, disabli |
| `GET` | `/whatsapp/call/ping` | [Keep call alive](https://docs.doubletick.io/reference/pingcall.md) | Keeps an active call session alive. |
| `POST` | `/whatsapp/call/reconnect` | [Reconnect call](https://docs.doubletick.io/reference/reconnectcall.md) | Reconnects an active WhatsApp call. |
| `POST` | `/whatsapp/decline` | [Decline or end call](https://docs.doubletick.io/reference/declinecall.md) | Declines an incoming call or ends an active call. |
| `POST` | `/whatsapp/message/audio` | [Send Whatsapp Audio Message](https://docs.doubletick.io/reference/outgoing-messages-whatsapp-audio.md) | Send Whatsapp Audio Message |
| `POST` | `/whatsapp/message/broadcast` | [Send Template Whatsapp Message to Broadcast Group](https://docs.doubletick.io/reference/send-broadcast-message.md) | Send a template WhatsApp message to a broadcast group. When using placeholders in your message, plea |
| `POST` | `/whatsapp/message/document` | [Send Whatsapp Document Message](https://docs.doubletick.io/reference/outgoing-messages-whatsapp-document.md) | Send Whatsapp Document Message |
| `POST` | `/whatsapp/message/image` | [Send Whatsapp Image Message](https://docs.doubletick.io/reference/outgoing-messages-whatsapp-image.md) | Send Whatsapp Image Message |
| `POST` | `/whatsapp/message/interactive` | [Send Whatsapp Interactive Button Message](https://docs.doubletick.io/reference/outgoing-messages-whatsapp-interactive.md) | Send Whatsapp Interactive Button Message |
| `POST` | `/whatsapp/message/interactive-list` | [Send Whatsapp Interactive List Message](https://docs.doubletick.io/reference/outgoing-messages-whatsapp-interactive-list.md) | Send Whatsapp Interactive List Message |
| `POST` | `/whatsapp/message/interactive/media` | [Send Whatsapp Interactive Media Message](https://docs.doubletick.io/reference/outgoing-messages-whatsapp-interactive-media.md) | Send Whatsapp Interactive Button Message with media header (image, video or document). |
| `POST` | `/whatsapp/message/interactive/media-carousel` | [Send Whatsapp Interactive Media Carousel Message](https://docs.doubletick.io/reference/outgoing-messages-whatsapp-interactive-media-carousel.md) | Send a WhatsApp interactive media carousel message — 2-10 cards, each with an image or video header, |
| `POST` | `/whatsapp/message/location` | [Send Whatsapp Location Message](https://docs.doubletick.io/reference/outgoing-messages-whatsapp-location.md) | Send Whatsapp Location Message |
| `POST` | `/whatsapp/message/order` | [Send Whatsapp Order (Payment) Message](https://docs.doubletick.io/reference/outgoing-messages-whatsapp-order.md) | Send Whatsapp Order (Payment) Message |
| `POST` | `/whatsapp/message/order/status` | [Send Whatsapp Order Status Message](https://docs.doubletick.io/reference/outgoing-messages-whatsapp-order-status.md) | Send Whatsapp Order Status Message |
| `GET` | `/whatsapp/message/status` | [Get Message Status](https://docs.doubletick.io/reference/get-message-status.md) | Get the current delivery status of a message using the `messageId` returned by the send message / se |
| `POST` | `/whatsapp/message/template` | [Send Whatsapp Template Message](https://docs.doubletick.io/reference/outgoing-messages-whatsapp-template.md) | Send Whatsapp Template Message |
| `POST` | `/whatsapp/message/text` | [Send Whatsapp Text Message](https://docs.doubletick.io/reference/outgoing-messages-whatsapp-text.md) | Send Whatsapp Text Message |
| `POST` | `/whatsapp/message/video` | [Send Whatsapp Video Message](https://docs.doubletick.io/reference/outgoing-messages-whatsapp-video.md) | Send Whatsapp Video Message |
| `DELETE` | `/whatsapp/wa-group` | [Delete WhatsApp group](https://docs.doubletick.io/reference/deletewhatsappgroup.md) | Deletes a WhatsApp group by its groupId. |
| `POST` | `/whatsapp/wa-group/create` | [Create WhatsApp group](https://docs.doubletick.io/reference/createwhatsappgroup.md) | Creates a new WhatsApp group on the given WABA number. |
| `GET` | `/whatsapp/wa-group/info` | [Get WhatsApp group info](https://docs.doubletick.io/reference/getwhatsappgroupinfo.md) | Returns information about a WhatsApp group by its groupId. |
| `GET` | `/whatsapp/wa-group/list` | [List WhatsApp groups](https://docs.doubletick.io/reference/listwhatsappgroups.md) | Returns a paginated list of WhatsApp groups for the given WABA number. Uses cursor-based pagination  |
| `DELETE` | `/whatsapp/wa-group/participants` | [Remove participant from WhatsApp group](https://docs.doubletick.io/reference/removewhatsappgroupparticipant.md) | Removes a participant from a WhatsApp group by their phone number. |
| `GET` | `/whatsapp/wa-group/participants` | [Get WhatsApp group participants](https://docs.doubletick.io/reference/getwhatsappgroupparticipants.md) | Returns a paginated list of participants for a specific WhatsApp group. Uses cursor-based pagination |
| `POST` | `/whatsapp/wa-group/settings` | [Update WhatsApp group settings](https://docs.doubletick.io/reference/updatewhatsappgroupsettings.md) | Updates the settings (subject, description, groupId) of an existing WhatsApp group identified by its |

## Webhook Events

- **[Edit Webhook](https://docs.doubletick.io/docs/edit-webhook.md)**: Modify an existing webhook’s configuration using the DoubleTick API, allowing updates to event types, headers, URLs, and retry settings for seamless event tracking. 🚀 (`edit-webhook.md`)
- **[Delete Webhook](https://docs.doubletick.io/docs/delete-webhook.md)**: Remove a registered webhook using the DoubleTick API to stop receiving unnecessary event notifications and keep your system optimized. 🚀 (`delete-webhook.md`)
- **[Register New Webhook](https://docs.doubletick.io/docs/register-new-webhook.md)**: Set up real-time event notifications using the DoubleTick API by registering a webhook to capture message status updates, customer interactions, and system events. 🚀 (`register-new-webhook.md`)
- **[Getting Started with DoubleTick Webhooks](https://docs.doubletick.io/docs/getting-started-with-doubletick-webhooks.md)**: Learn how to register webhooks, capture events, and securely access media URLs with DoubleTick. (`getting-started-with-doubletick-webhooks.md`)
- **[Get Webhooks](https://docs.doubletick.io/docs/get-webhooks-1.md)**: Retrieve a list of registered webhooks, including their event types and associated WABA numbers, using the DoubleTick API for seamless event tracking. 🚀 (`get-webhooks-1.md`)
- **[Delete Webhooks](https://docs.doubletick.io/reference/delete-webhooks.md)**: Delete Webhooks (`delete-webhooks.md`)
- **[Get Webhooks](https://docs.doubletick.io/reference/get-webhooks.md)**: Get Webhooks (`get-webhooks.md`)
- **[Register New Webhook](https://docs.doubletick.io/reference/register-webhook.md)**: Register New Webhook (`register-webhook.md`)
- **[Edit Webhooks](https://docs.doubletick.io/reference/edit-webhooks.md)**: Edit Webhooks (`edit-webhooks.md`)
- **[Messaging Webhooks](https://docs.doubletick.io/reference/messaging-webhooks-1.md)**: Receive real-time notifications for inbound and outbound WhatsApp messaging activity, including received messages, delivery status changes, and Click-to-WhatsApp ad attribution. (`messaging-webhooks-1.md`)
- **[Customer Notes Webhooks](https://docs.doubletick.io/reference/customer-notes-webhooks-1.md)**: Receive notifications whenever notes are created, updated, or deleted on customer conversations. These events help synchronize internal conversation context, agent observations, follow-up instructions, and operational notes across external systems. (`customer-notes-webhooks-1.md`)
- **[Conversation Management Webhooks](https://docs.doubletick.io/reference/conversation-management-webhooks.md)**: Track conversation lifecycle events such as when conversations are opened, closed, assigned, or unassigned. These webhooks help you synchronize ownership, automate workflows, and maintain an up-to-date view of customer conversations within your external systems. (`conversation-management-webhooks.md`)
- **[Widget & Lead Webhooks](https://docs.doubletick.io/reference/widget-lead-webhooks.md)**: Receive real-time notifications when a customer contacts your WhatsApp Business Account for the first time, submits a DoubleTick widget form, or when that submission is later linked to a WhatsApp contact. (`widget-lead-webhooks.md`)
- **[SLA Webhooks](https://docs.doubletick.io/reference/sla-webhooks.md)**: Receive real-time notifications when a conversation's response-time SLA escalates to the next configured level. (`sla-webhooks.md`)
- **[Customer Data Webhooks](https://docs.doubletick.io/reference/customer-data-webhooks.md)**: Receive notifications whenever tags are added to or removed from customer conversations, or when a customer's custom field value is created, updated, or removed. Use these events to keep customer segmentation, CRM labels, and profile data synchronized across external systems. (`customer-data-webhooks.md`)
- **[DoubleTick Webhooks](https://docs.doubletick.io/reference/webhooks-1.md)**: Receive real-time event notifications from DoubleTick and WhatsApp Cloud API to automate workflows, synchronize data, and react instantly to customer and conversation activities. (`webhooks-1.md`)
- **[Template Webhooks](https://docs.doubletick.io/reference/template-webhooks.md)**: Receive real-time notifications whenever the approval status of a WhatsApp message template changes. (`template-webhooks.md`)
- **[Agent Presence Webhooks](https://docs.doubletick.io/reference/agent-presence-webhooks.md)**: Receive real-time notifications when an agent changes their availability status in DoubleTick, with details of which customers' conversations are affected. (`agent-presence-webhooks.md`)
- **[WhatsApp Group Webhooks](https://docs.doubletick.io/reference/whatsapp-group-webhooks.md)**: Receive real-time notifications for activity inside WhatsApp Groups managed through DoubleTick — including group creation, membership changes, and inbound group messages. (`whatsapp-group-webhooks.md`)
- **[Conversation Cost Webhooks](https://docs.doubletick.io/reference/conversation-cost-webhooks.md)**: Receive a real-time notification whenever a WhatsApp conversation fee is charged against your DoubleTick wallet balance. (`conversation-cost-webhooks.md`)
- **[Call Webhooks](https://docs.doubletick.io/reference/call-webhooks.md)**: Receive real-time notifications for inbound and outbound voice call activity on your WhatsApp Business Account, including call lifecycle events and customer permission responses. (`call-webhooks.md`)
