Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# CleverTap WhatsApp Integration

Integrate CleverTap with DoubleTick to send WhatsApp messages, manage templates, and receive delivery and inbound message events seamlessly.

[block:image]
{
  "images": [
    {
      "image": [
        "https://files.readme.io/418e0d60315c6bad53ddff1fae46ad64074bfeb46a3c460cfed7e3b33b09aba9-Untitled_design.png",
        null,
        ""
      ],
      "align": "center"
    }
  ]
}
[/block]

This integration allows CleverTap to:

* Send WhatsApp messages using DoubleTick
* Import WhatsApp message templates
* Receive message delivery updates
* Receive replies from customers

***

## Prerequisites

Before starting the integration, make sure you have:

1. An active DoubleTick account
2. Your DoubleTick API Key
3. Your WABA Number (WhatsApp Business Account number configured in DoubleTick)
4. Access to CleverTap Dashboard

> 📘 NOTE
>
> If you do not have your DoubleTick API Key, you can obtain it by following this guide:\
> <https://docs.doubletick.io/docs/quickstart-guide#step-2-generate-your-api-key>

***

## Step 1: Configure WhatsApp provider in CleverTap

1. Login to your CleverTap Dashboard.
2. Navigate to: **Settings → Channels → WhatsApp → WhatsApp Connect**
3. Click **+ Provider Configuration** button.
4. Configure the provider with the following details:

| Field         | Value                                                     |
| :------------ | :-------------------------------------------------------- |
| Provider      | Generic (Other)                                           |
| Mobile Number | WABA Number                                               |
| HTTP Endpoint | <https://public.doubletick.io/whatsapp/message/clevertap> |

### Add Authorization Header

Under Headers, add the following:

| Key           | Value              |
| :------------ | :----------------- |
| Authorization | doubletick-api-key |

> 🚧 IMPORTANT
>
> The Authorization header must contain your **DoubleTick API Key**.\
> This allows CleverTap to securely send WhatsApp requests through DoubleTick.

***

## Step 2 — Configure template import

To allow CleverTap to fetch and sync **WhatsApp templates from DoubleTick**, enable **Template Import Setup**.

1. Check the option **Template Import Setup**.
2. Configure the following endpoint:

```text
https://public.doubletick.io/templates/import?wabaNumber=919999999999
```

3. Add the same header used earlier:

| Key           | Value              |
| :------------ | :----------------- |
| Authorization | doubletick-api-key |

> 👍 TIP
>
> Make sure the **WABA number entered in the URL matches the number configured in DoubleTick**.\
> This ensures CleverTap imports the correct WhatsApp templates.

***

## Step 3 — Send a test WhatsApp message

Once the provider configuration is complete:

1. Click Send Test WhatsApp inside CleverTap.
2. Make sure the **chat window is open on the test number in DoubleTick**.
3. Verify that the message is successfully delivered.

If the configuration is correct, the message will be sent through DoubleTick.

> 👍 TIP
>
> Sending a test message helps confirm that the integration is correctly configured before launching campaigns.

***

## Step 4 — Import WhatsApp Templates in CleverTap

Once the template import setup is configured, you need to import the approved templates into CleverTap.

1. Go to **Templates** tab aside **Setup** tab of provider configuration.
2. Click on **Import Template** button.
3. Click **Import** to fetch all approved templates.
4. All approved WhatsApp templates from DoubleTick will now be available in CleverTap.

> 🚧 IMPORTANT
>
> Only approved WhatsApp templates associated with your WABA number will be imported into CleverTap.

***

## Step 5 — Configure delivery report updates

CleverTap provides a **Delivery Report Callback URL** to receive delivery status updates.

1. Copy the **Delivery Report Callback URL** from CleverTap.
2. Open the DoubleTick Dashboard.
3. Navigate to **[Webhooks](https://web.doubletick.io/v1/settings/webhooks)**.
4. Create a new webhook.
5. Paste the copied callback URL.
6. Select the event related to **Message Delivery Updates**.

![](https://files.readme.io/c836984a69382f67bf0dc220f2ccc17e7daa144888dcd724a2019f32c05fcad3-image.png)

***

## Step 6 — Configure incoming message updates

To allow CleverTap to receive replies from customers:

1. Copy the **Inbound Message Callback URL** from CleverTap.
2. Open the DoubleTick Dashboard.
3. Navigate to **[Webhooks](https://web.doubletick.io/v1/settings/webhooks)**.
4. Create a new webhook.
5. Paste the copied callback URL.
6. Select the event for Incoming Messages.

![](https://files.readme.io/d55ab797429aae57743ecd0d69370bbb9d34a6dc140fc2541af93074cd727861-image.png)

***

## Integration Flow Overview

Once the setup is complete:

1. CleverTap sends WhatsApp campaign messages.
2. DoubleTick processes the request and delivers the message to the customer.
3. Delivery status updates are sent back to CleverTap.
4. Customer replies are forwarded to CleverTap through webhook callbacks.

This setup ensures CleverTap campaigns can track **message delivery and customer responses** while using DoubleTick as the WhatsApp messaging provider.