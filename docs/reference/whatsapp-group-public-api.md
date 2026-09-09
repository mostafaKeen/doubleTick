Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# WhatsApp Group Public API

The WhatsApp Group Public API allows you to **create and manage WhatsApp groups** for your customers from your own backend or applications.

This API works together with:

* **Group lifecycle webhooks** (e.g. `GROUP_CREATED`, `GROUP_PARTICIPANT_JOINED`)
* **Internal group management APIs** (for updating settings, members, etc.)

Base path:\
**`https://public.doubletick.io/whatsapp/wa-group`**

All endpoints require **Public API authentication** and are subject to **path-based rate limiting**.

***

## How WhatsApp groups work

At a high level, WhatsApp groups in DoubleTick follow this flow:

1. **Group is created** via Public API or internal UI.
2. **Group lifecycle webhooks** notify you when the group is created.
3. **Participants join/leave** – updates are processed from WhatsApp webhooks.
4. **Participant events** (join / leave / removed) are pushed to you via Group Webhooks.
5. **Your system** updates internal records, UI, and analytics accordingly.

> 📌 **Important:**
>
> For group creation and membership changes, **all state changes are delivered via webhooks**.\
> Always rely on webhooks to track group lifecycle and membership state.

👉 Refer to **[Group Webhooks](https://docs.doubletick.io/reference/group-webhooks)** documentation to understand how to consume these events.

***

## Supported join approval modes

When creating a group, you can control how people join:

| Value               | Meaning                                     |
| ------------------- | ------------------------------------------- |
| `auto_approve`      | Join requests are automatically approved.   |
| `approval_required` | Admins must approve join requests manually. |

If you omit `joinApprovalMode`, the platform uses its default behavior for the integration.

***

## Group creation and identifiers

When you create a group, you can optionally supply your own **client custom group ID**, which is stored and echoed back in future events.

* **Client custom group ID**: your stable identifier for a group (e.g. `vip_support_2026`).
* DoubleTick uses this value as `clientCustomGroupId` internally and exposes it as `groupId` in:
  * Public API responses
  * Group webhook payloads

This lets you:

* Link groups to your CRM or internal entities.
* Join events like `GROUP_CREATED` and `GROUP_PARTICIPANT_JOINED` back to your own records.

👉 See **[WA Group – Create Group Public API](https://docs.doubletick.io/reference/wa-group-create-group-public-api)** for request and response details.

***

## Webhooks are mandatory

The WhatsApp Group system is **event-driven**.

You will receive webhook events for:

* Group creation (`GROUP_CREATED`)
* Participants joining groups
* Participants leaving or being removed from groups

You **must subscribe to group webhooks** to:

* Keep your internal membership list in sync.
* Drive UI updates around group state and member presence.
* Maintain accurate audit logs and analytics.

👉 See: **[Group Webhooks – WhatsApp Groups](https://docs.doubletick.io/reference/group-webhooks)**

***

## Documentation structure

* **WhatsApp Group Public API (this page)** – Concepts & flow.
* **WA Group – Create Group Public API** – Create groups via `/whatsapp/wa-group/create`.
* **Group Webhooks** – All group lifecycle and membership events you receive.