Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Bitrix24 Integration with DoubleTick

Connect Bitrix24 with DoubleTick to streamline WhatsApp communication, manage conversations within CRM, and improve response efficiency for your leads and customers.

You can integrate Bitrix24 CRM with DoubleTick to manage WhatsApp conversations directly inside Bitrix24.

This integration allows you to:

* Open WhatsApp chats from Bitrix24 leads
* Use DoubleTick inside Bitrix24 CRM
* Manage customer communication without switching platforms

***

## Before you begin

Make sure the following requirements are met:

* You are using a paid Bitrix24 plan
* You have access to install applications in Bitrix24
* You have received the DoubleTick App ZIP file

> 🚧 IMPORTANT
>
> Bitrix24 does not support installing local applications on the free plan.\
> You must upgrade to a paid plan to proceed.

***

## Step 1 — Verify your Bitrix24 plan

1. Log in to your Bitrix24 Dashboard.
2. Click on My Plan (top header section).
3. Confirm that your account is on a paid plan.

![](https://files.readme.io/0afa2c2fc94ee81bfb5ac6a6bbf5954e5c04eda64d2ced6035ff4e23b48595dc-image.png)

***

## Step 2 — Navigate to REST API settings

1. From the left sidebar, go to CRM.
2. Click on the Settings button.
3. From the menu, select Add-ons -> REST API

![](https://files.readme.io/bbdf5668cdf33cce08de6ba231e517d5a8d149f8c3d15930278f789101ba7770-image.png)

***

## Step 3 — Connect WhatsApp (WABA) in Bitrix24

1. Open your **Bitrix24 Admin profile**.
2. Click on the photo in the top right corner to open a profile and click the name.
3. Click Create field and select the field type.

| Field            | Type   | Value                           |
| :--------------- | :----- | :------------------------------ |
| `UF_WABA_NUMBER` | string | WABA number by which chat opens |

[block:image]
{
  "images": [
    {
      "image": [
        "https://files.readme.io/0047529a2c53c539de04cb7283b73224a422f1fc8533bfcbee2b4541413984fe-Screenshot_2026-03-17_at_1.39.56PM_1.png",
        "",
        ""
      ],
      "align": "center"
    }
  ]
}
[/block]

> 🚧 IMPORTANT
>
> Make sure the WhatsApp number connected here is the same WABA number configured in DoubleTick.

***

## Step 4 — Create a local application

1. In the REST API page, go to Developer Resources.
2. Select:

```
Other (Create inbound and outbound webhooks or a local app)
```

3. You will see multiple options. Select:

```
Local Application
```

![](https://files.readme.io/683a5881f01868468bdf67f844e19d4a1464dcab5fb322f1d3f257398aa49dc8-image.png)

***

## Step 5 — Configure application settings

1. Select the Static option.
2. Upload the DoubleTick ZIP file (provided by the DoubleTick team).

### Fill in application details

| Field              | Value                                                            |
| :----------------- | :--------------------------------------------------------------- |
| Menu item text     | DoubleTick                                                       |
| Assign permissions | `CRM (crm)`, `Users (user)`, `Application embedding (placement)` |

> 🚧 IMPORTANT
>
> All required permissions must be selected to ensure the DoubleTick app works correctly inside Bitrix24.

***

## Step 6 — Install the application

1. Click **Save**.
2. Bitrix24 may prompt you to **Install** the application.
3. Click **Install** to complete the installation.

***

## Step 7 — Access DoubleTick inside CRM

1. Go to **CRM**.
2. Open any Lead.
3. You will see a new menu item named **DoubleTick**.
4. Click on it to open the DoubleTick interface.

[block:image]
{
  "images": [
    {
      "image": [
        "https://files.readme.io/16f54380e004277178fdb961959665d06d762bf4838c50489b76ff1c3239e4bb-bitrix24_1.png",
        null,
        ""
      ],
      "align": "center"
    }
  ]
}
[/block]

5. Log in using your **DoubleTick credentials**.
6. Start sending and receiving WhatsApp messages directly from Bitrix24.

> 📘 TIP
>
> You can use this integration to manage conversations without leaving Bitrix24, improving response time and workflow efficiency.

***

## Integration flow

Once installed:

1. Bitrix24 loads the DoubleTick application inside CRM.
2. Users open a lead and access DoubleTick.
3. Messages are sent and received via DoubleTick WhatsApp.
4. Conversations can be managed directly within Bitrix24.