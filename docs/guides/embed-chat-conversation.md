Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Embed WhatsApp Conversations Inside Your Product

Get a ready-to-use iframe URL that opens a specific WhatsApp conversation for a given contact's phone number directly inside your application.

DoubleTick allows you to embed a live WhatsApp conversation directly inside your application using a secure iframe URL.

This is useful when you want your users, agents, or support teams to view and manage WhatsApp chats without leaving your platform.

Common use cases include:

* CRM platforms showing customer conversations beside lead details
* Support dashboards with embedded WhatsApp chats
* Internal tools for sales or operations teams
* Custom admin panels with integrated messaging
* SaaS products offering WhatsApp communication inside their UI

![](https://files.readme.io/b4a0518fc0398d0c8c8bf1f7c4c2b52bb6e0227967eda134944c1c51e075de79-image.png)

The embedded interface is fully hosted and managed by DoubleTick, allowing users to interact with WhatsApp conversations directly from your platform.

## Create WhatsApp Conversation Embed URL

Use the following endpoint to get an embed URL for a WhatsApp conversation:

```
POST https://public.doubletick.io/embed/url
```

***

## Request Headers

```json
{
  "Authorization": "YOUR_API_KEY",
  "Content-Type": "application/json"
}
```

> 📘 NOTE
>
> If you do not have your DoubleTick API Key, you can obtain it by following this guide:\
> <https://docs.doubletick.io/docs/quickstart-guide#step-2-generate-your-api-key>

## Request Body Parameters

```json
{
  "phone": "919876543210",
  "integrationId": "intg_xxxxxxxxxx",
  "wabaNumber": "919000000000"
}
```

### Parameters

* `phone` (string, required):
  * WhatsApp phone number of the contact in <<glossary:international format>>.
  * No `+` or spaces.
* `integrationId` (string, optional):
  * DoubleTick integration ID (e.g., `intg_xxxxxxxxxx`).
  * If not provided, the primary connected WhatsApp integration is used automatically.
* `wabaNumber` (string, optional):
  * WhatsApp Business Account number in <<glossary:international format>>.
  * Ignored if `integrationId` is also provided.

> 📘 NOTE
>
> This API generates a secure embeddable URL for a WhatsApp conversation.
>
> The generated URL can be used inside an iframe to display:
>
> * A specific customer conversation
> * The DoubleTick messaging interface
>
> The embed automatically opens the conversation associated with the provided phone number.

***

## Responses

### Success Response (<<glossary:200>>)

```json
{
  "url": "https://webextend.doubletick.io/embed/conversations/intg_xxxxxxxxxx/customer_xxxxxxxxxx?showChatListPanel=false&showCustomerDetails=false&showSidebar=false"
}
```

### Bad Request (<<glossary:400>>)

```json
{
  "message": "error_message",
  "error": "Bad Request",
  "statusCode": 400
}
```

### Unauthorized (<<glossary:401>>)

```json
{
  "message": "Invalid public api key",
  "error": "Unauthorized",
  "statusCode": 401
}
```

### Not Found (<<glossary:404>>)

```json
{
  "message": "No connected WhatsApp integration found",
  "error": "Not Found",
  "statusCode": 404
}
```

***

## Using the URL

Once you receive the `url` in the response, use it directly as the `src` of an iframe in your application:

```html
<iframe
  src="https://webextend.doubletick.io/embed/conversations/intg_xxxxxxxxxx/customer_xxxxxxxxxx?showChatListPanel=false&showCustomerDetails=false&showSidebar=false"
  width="400"
  height="600"
  frameborder="0"
/>
```

***

## Customizing the Embed URL

The embed URL supports a set of query parameters that let you control which panels and UI elements are visible. Append them directly to the URL returned by the API.

### 1. Layout & Navigation

Controls the major structural panels of the embed.

| Parameter           | Default     | How to change    | Description                                                                                            |
| :------------------ | :---------- | :--------------- | :----------------------------------------------------------------------------------------------------- |
| `showSidebar`       | **Visible** | `=false` to hide | The left-side navigation sidebar options                                                               |
| `showChatListPanel` | **Visible** | `=false` to hide | The chat list panel. When hidden, the embed opens directly into the active chat view without the list. |
| `showChatFilters`   | **Visible** | `=false` to hide | The filters panel on the left of the chat list.                                                        |

[block:image]
{
  "images": [
    {
      "image": [
        "https://files.readme.io/1d9ee4ea8c85d422fccfdf3c5654a9023e3ff41772f4b0525f70eeafa908c65a-Screenshot_2026-06-15_at_3.55.56PM.png",
        "",
        "set `showSidebar:false` to remove options from highlighted area"
      ],
      "align": "center",
      "sizing": "400px",
      "caption": "set `showSidebar:false` to remove options from highlighted area"
    }
  ]
}
[/block]

***

[block:image]
{
  "images": [
    {
      "image": [
        "https://files.readme.io/0953362e8c2df0fe4f3f655f3b4dd974278583776eca5e34b219465785259818-Screenshot_2026-06-15_at_3.59.28PM.png",
        "",
        "set `showChatListPanel:false` to remove highlighted area"
      ],
      "align": "center",
      "sizing": "400px",
      "caption": "set `showChatListPanel:false` to remove highlighted area"
    }
  ]
}
[/block]

***

[block:image]
{
  "images": [
    {
      "image": [
        "https://files.readme.io/2038dade014905abfba6f407adb02a461f63e9e13d9abc0d4b995361c407db06-Screenshot_2026-06-15_at_4.00.17PM.png",
        "",
        "set `showChatFilters:false` to remove highlighted area"
      ],
      "align": "center",
      "sizing": "400px",
      "caption": "set `showChatFilters:false` to remove highlighted area"
    }
  ]
}
[/block]

***

### 2. Chat Header Actions

Controls the action buttons that appear in the header bar of an open conversation.

| Parameter                   | Default     | How to change    | Description                                                                            |
| :-------------------------- | :---------- | :--------------- | :------------------------------------------------------------------------------------- |
| `showChatAssignee`          | **Visible** | `=false` to hide | The agent assignee dropdown in the chat header                                         |
| `showChatMarkDone`          | **Visible** | `=false` to hide | The “Mark as Done” button. Does not appear for group chats regardless of this setting. |
| `showChatMenu`              | **Visible** | `=false` to hide | The three-dot (⋮) action menu. Applies to individual chats only.                       |
| `showChatCart`              | **Visible** | `=false` to hide | The cart / order button in the chat header.                                            |
| `showAddToBroadcastChannel` | **Visible** | `=false` to hide | The “Add to Broadcast Channel” option in the chat action menu.                         |
| `showChatBackButton`        | **Hidden**  | `=false` to hide | Shows a back button in the chat header, to the left of the contact avatar.             |

[block:image]
{
  "images": [
    {
      "image": [
        "https://files.readme.io/68171e05e688784bd98ab5703f42dea00a57a056bf8521a107d397baf90f2099-Screenshot_2026-06-16_at_7.19.20PM.png",
        "",
        "set `showChatAssignee:false` to remove highlighted area"
      ],
      "align": "center",
      "caption": "set `showChatAssignee:false` to remove highlighted area"
    }
  ]
}
[/block]

***

[block:image]
{
  "images": [
    {
      "image": [
        "https://files.readme.io/4e4d9b18aece3149d1fb24b9a0b01b55d9dc22c696fb6d56fa667a3979ebb3ad-Screenshot_2026-06-16_at_7.20.30PM.png",
        "",
        "set `showChatMarkDone:false` to remove highlighted area"
      ],
      "align": "center",
      "caption": "set `showChatMarkDone:false` to remove highlighted area"
    }
  ]
}
[/block]

***

[block:image]
{
  "images": [
    {
      "image": [
        "https://files.readme.io/505e8bdb39a28c570f5db17ac46b4cee0014a2c27377a0ed5c8519beb1fbbc70-Screenshot_2026-06-16_at_7.21.02PM.png",
        "",
        "set `showChatMenu:false` to remove highlighted area"
      ],
      "align": "center",
      "caption": "set `showChatMenu:false` to remove highlighted area"
    }
  ]
}
[/block]

***

[block:image]
{
  "images": [
    {
      "image": [
        "https://files.readme.io/0c4bf20b44cdf1024b995f1dbb889f14dd16e32339bc1aaa09c2bfbe362406f8-Screenshot_2026-06-15_at_4.06.58PM.png",
        "",
        "set `showChatCart:false` to remove highlighted area"
      ],
      "align": "center",
      "sizing": "800px",
      "caption": "set `showChatCart:false` to remove highlighted area"
    }
  ]
}
[/block]

***

[block:image]
{
  "images": [
    {
      "image": [
        "https://files.readme.io/1ae16daeaa01bcf7b1795a92231a3d422927765b9cdb37ac5793fc865e3b4290-Screenshot_2026-06-16_at_7.21.39PM.png",
        "",
        "set `showAddToBroadcastChannel:false` to remove highlighted area"
      ],
      "align": "center",
      "caption": "set `showAddToBroadcastChannel:false` to remove highlighted area"
    }
  ]
}
[/block]

***

[block:image]
{
  "images": [
    {
      "image": [
        "https://files.readme.io/523a11f7017fd2881356730595f9c790a3cefc597e85ca3b832b64bddc3927c5-image_1.png",
        "",
        "set `showChatBackButton:true` to add back button"
      ],
      "align": "center",
      "caption": "set `showChatBackButton:true` to add back button"
    }
  ]
}
[/block]

The button does not navigate. Clicking it posts a message to the parent window and nothing else — your app decides where "back" goes. Listen for it:

```javascript
window.addEventListener('message', (event) => {
  if (event.data?.type === 'DT_CHAT_BACK_CLICKED') {
    // Close the panel, pop the route, whatever back means in your app
  }
});
```

> 📘 NOTE
>
> Wire up the listener before enabling the flag, otherwise the button will appear but do nothing.

***

### 3. Chat List

Controls elements within the chat list panel.

| Parameter            | Default     | How to change    | Description                                                                                                      |
| :------------------- | :---------- | :--------------- | :--------------------------------------------------------------------------------------------------------------- |
| `showChatSearch`     | **Visible** | `=false` to hide | The search bar above the chat list.                                                                              |
| `showChatSelection`  | **Visible** | `=false` to hide | Multi-chat selection (checkboxes on each row). When hidden, users cannot select multiple chats for bulk actions. |
| `showBulkMarkAsDone` | **Visible** | `=false` to hide | The "Mark as Done" button shown in the bulk-selection toolbar when multiple chats are selected.                  |

[block:image]
{
  "images": [
    {
      "image": [
        "https://files.readme.io/331aa04dbf421d3e1b122012b2bf7e7860c54d1211ad683c9f056783c0d615d7-Screenshot_2026-06-16_at_7.22.20PM.png",
        "",
        "set `showChatSearch:false` to remove highlighted area"
      ],
      "align": "center",
      "caption": "set `showChatSearch:false` to remove highlighted area"
    }
  ]
}
[/block]

***

[block:image]
{
  "images": [
    {
      "image": [
        "https://files.readme.io/a7cff311654607cf5f0d375c426bbd8dbfe591e856c9e6456b87fbb5483db9ce-Screenshot_2026-06-16_at_7.22.49PM.png",
        "",
        "set `showChatSelection:false` to remove highlighted area"
      ],
      "align": "center",
      "caption": "set `showChatSelection:false` to remove highlighted area"
    }
  ]
}
[/block]

***

[block:image]
{
  "images": [
    {
      "image": [
        "https://files.readme.io/8c52e4dceedb07379f6be7cfa4af2ab01efa152afa87579b9263334d95df4b40-Screenshot_2026-06-16_at_7.23.25PM.png",
        "",
        "set `showBulkMarkAsDone:false` to remove highlighted area"
      ],
      "align": "center",
      "caption": "set `showBulkMarkAsDone:false` to remove highlighted area"
    }
  ]
}
[/block]

***

### 4. Customer Details Panel

Controls sections within the right-side customer details panel.

| Parameter             | Default     | How to change    | Description                                                                                                             |
| :-------------------- | :---------- | :--------------- | :---------------------------------------------------------------------------------------------------------------------- |
| `showCustomerDetails` | **Visible** | `=false` to hide | The entire customer details panel on the right side.                                                                    |
| `showNotes`           | **Visible** | `=false` to hide | The Notes section inside the customer details panel. When hidden, the custom fields section expands to full width.      |
| `showCallHistory`     | **Visible** | `=false` to hide | The Call History section. Only relevant if the calling feature is enabled on your account.                              |
| `showParticipants`    | **Visible** | `=false` to hide | The details/participants panel in broadcast channel views.                                                              |
| `showAddCustomField`  | **Visible** | `=false` to hide | The “Add Custom Field” button in the custom fields section. Hides the button but existing custom fields remain visible. |

[block:image]
{
  "images": [
    {
      "image": [
        "https://files.readme.io/6be93ac63aeb6db949332c8abc21c4b5de98d50c6b0c7e0dfdc8f409047b7294-Screenshot_2026-06-16_at_7.24.40PM.png",
        "",
        "set `showCustomerDetails:false` to remove highlighted area"
      ],
      "align": "center",
      "caption": "set `showCustomerDetails:false` to remove highlighted area"
    }
  ]
}
[/block]

***

[block:image]
{
  "images": [
    {
      "image": [
        "https://files.readme.io/15085e9b93095c115b3676591face4d345f6632e1a0ff001a203529561ac5e36-Screenshot_2026-06-16_at_7.24.25PM.png",
        "",
        "set `showNotes: false` to remove highlighted area "
      ],
      "align": "center",
      "caption": "set `showNotes: false` to remove highlighted area "
    }
  ]
}
[/block]

***

[block:image]
{
  "images": [
    {
      "image": [
        "https://files.readme.io/0948eb817e131af3e69cbd214317ad745c440f16a85eeffbd203a8427574f297-Screenshot_2026-06-16_at_7.24.33PM.png",
        "",
        "set `showCallHistory: false` to remove highlighted area"
      ],
      "align": "center",
      "caption": "set `showCallHistory: false` to remove highlighted area"
    }
  ]
}
[/block]

***

[block:image]
{
  "images": [
    {
      "image": [
        "https://files.readme.io/3cb6e0cd7f4cb67bd1672652953b064dc28eb98b8f19e9adea0ee7f9c370bdba-Screenshot_2026-06-16_at_7.25.16PM.png",
        "",
        "set `showParticipants: false` to remove highlighted area"
      ],
      "align": "center",
      "caption": "set `showParticipants: false` to remove highlighted area"
    }
  ]
}
[/block]

***

[block:image]
{
  "images": [
    {
      "image": [
        "https://files.readme.io/f27911a4e5943e2128d7828855038625515e12792caa8b3f068a37aa3d9724df-Screenshot_2026-06-16_at_7.38.28PM.png",
        "",
        "set `showAddCustomField: false` to remove highlighted area"
      ],
      "align": "center",
      "caption": "set `showAddCustomField: false` to remove highlighted area"
    }
  ]
}
[/block]

***

### 5. Account/Session

| Parameter                | Default    | How to change   | Description                                                                                                                                               |
| :----------------------- | :--------- | :-------------- | :-------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `showLogoutInMenuHeader` | **Hidden** | `=true` to show | Adds a Logout button to the menu header. Enable this when your integration requires the end-user to be able to explicitly sign out from within the embed. |

[block:image]
{
  "images": [
    {
      "image": [
        "https://files.readme.io/ff1129f3be1469bfa562b84979764dbde3b30730ff3d8ae5a4e773f054909ab0-Screenshot_2026-06-16_at_7.40.44PM.png",
        "",
        "set `showLogoutInMenuHeader: false`  to remove highlighted area"
      ],
      "align": "center",
      "caption": "set `showLogoutInMenuHeader: false`  to remove highlighted area"
    }
  ]
}
[/block]

***

### 6. Utility Parameters

These parameters are not visibility toggles — they control embed behaviour directly.

| Parameter           | Value  | Description                                                                                                                                                                           |
| :------------------ | :----- | :------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `clearLocalStorage` | `true` | Clears the cached embed visibility settings from the browser's localStorage. Append this once when you change your parameter configuration, and then remove it from subsequent loads. |

***

### Default Behaviour Summary

[block:parameters]
{
  "data": {
    "h-0": "Parameter",
    "h-1": "Parameters",
    "0-0": "**Visible by default**  \npass `=false` to hide",
    "0-1": "`showCustomerDetails`,  `showChatAssignee`,  `showChatListPanel`,  `showChatMarkDone`,  `showChatTagList`,  `showChatAddTag`,  `showChatCart`,  `showChatMenu`,  `showAddToBroadcastChannel`,  `showChatFilters`,  `showChatSearch`,  `showChatSelection`,  `showAddCustomField`,  `showNotes`,  `showParticipants`,`showSidebar`,  `showCallHistory`,  `showBulkMarkAsDone`",
    "1-0": "**Hidden by default**  \npass `=true` to enable",
    "1-1": "`showLogoutInMenuHeader`"
  },
  "cols": 2,
  "rows": 2,
  "align": [
    "left",
    "left"
  ]
}
[/block]

> 📘 Flushing the Cache
>
> If you update your URL parameters and the changes don't take effect, append clearLocalStorage=true to the URL once to reset the cached configuration, then remove it from subsequent loads.
>
> See [Utility Parameters](https://docs.doubletick.io/docs/embed-whatsapp-conversations-inside-your-product-copy#6-utility-parameters) for details.

***

## Listening for Authentication Events

The embedded iframe posts a `message` event to the parent window whenever the logged-in user's session state changes. It's useful for showing a loader, redirecting, or gating UI in your app until the embedded conversation is actually ready.

### Event Shape

#### User logged in

```json
{
  "type": "AUTH_STATE_CHANGED",
  "isLoggedIn": true,
  "user": {
    "id": "user_xxxxxxxxxx",
    "name": "John Doe"
  }
}
```

#### User logged out

```json
{  
  "type": "AUTH_STATE_CHANGED",
  "isLoggedIn": false
}
```

### Listening in Your App

```javascript
window.addEventListener("message", (event) => {
  if (event.data?.type !== "AUTH_STATE_CHANGED") return;

  if (event.data.isLoggedIn) {
    console.log("User logged in:", event.data.user);
  } else {
    console.log("User logged out");
  }
});
```

> 📘 NOTE
>
> **isLoggedIn: true** fires only once the user has fully completed login (organization selected), not at intermediate auth steps.

***

## Best Practices

* **Always pass the phone number** with the country code and no special characters (e.g., `919876543210` not `+91 98765 43210`).
* Use **`integrationId`** or **`wabaNumber`** when you want to open the conversation with particular WhatsApp business number.
* Leave **`showChatListPanel`**, **`showCustomerDetails`**, and **`showSidebar`** as **`false`** (default) for a focused single-conversation embed with no distractions.
* Set all three to `true` if you want to give users access to the full DoubleTick interface inside the embed.
* **Append `clearLocalStorage=true` once after changing parameters** — embed settings are cached in the browser. Add this param to flush the cache, then remove it from subsequent loads to avoid repeated clears.
* **`showChatMarkDone` never appears on group chats** — this is expected behaviour regardless of the parameter value. Plan your UI accordingly if you support both individual and group chat contexts.
* **`showCallHistory` is only relevant if calling is enabled** — if the section doesn't appear, confirm with your DoubleTick account manager whether the calling feature is active on your account.
* **Use `showLogoutInMenuHeader=true` only when needed** — enable this only if your integration requires users to explicitly sign out from within the embed; it's hidden by default for a reason.