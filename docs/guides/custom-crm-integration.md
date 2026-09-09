Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Custom CRM Integration

Embed DoubleTick conversations directly inside your CRM with automatic login for your CRM users.

Custom CRM integration removes the need for users to separately log in to DoubleTick using their **phone number** and **OTP**. Your CRM validates the already logged-in user, and DoubleTick returns a short-lived `token` that authenticates the user inside the embedded DoubleTick iframe.

This provides a seamless experience where users can open a DoubleTick conversation directly from your CRM without leaving your application or going through login flow.

[block:image]
{
  "images": [
    {
      "image": [
        "https://files.readme.io/f69db0d8d48433d1a843ee9fa152c1bd134ff1afa3344bf6266ee827d87207d3-image.png",
        null,
        "DoubleTick Integration - Custom CRM"
      ],
      "align": "center",
      "caption": "Custom CRM - DoubleTick Integration"
    }
  ]
}
[/block]

## How it works

The Custom CRM integration uses your CRM user's existing session to automatically authenticate them in DoubleTick:

1. Your CRM sends the logged-in user's session token to DoubleTick.
2. DoubleTick validates the user and returns a short-lived `token`.
3. Your CRM adds the `token` to the conversation URL and loads it in an iframe.
4. The user is automatically logged in to DoubleTick and can access the conversation without entering a phone number or OTP.

> 🚧 PRE-REQUISITES
>
> Complete the Custom CRM integration setup in your [DoubleTick integration](https://web.doubletick.io/v1/settings/integration/custom-crm) and get your integration **identifier**.

[block:image]
{
  "images": [
    {
      "image": [
        "https://files.readme.io/b72a38f37f3acbc7b637ce2428984c164334a4c39532fb80bb0bf4bf25dd175a-Custom_CRM_-_API_Diagram.png",
        null,
        ""
      ],
      "align": "center"
    }
  ]
}
[/block]

## 1. Validate the CRM user

From your CRM backend, call the DoubleTick Validate User API:

```curl
curl -X POST 'https://aiapi.doubletick.io/v1/custom-crm/embed/token' \
  -H 'Content-Type: application/json' \
  -d '{
    "identifier": "<YOUR_INTEGRATION_IDENTIFIER>",
    "sessionToken": "<LOGGED_IN_USER_SESSION_TOKEN>"
  }'
```

DoubleTick validates the user through your configured Validate-User API and returns an `token`.

```json
{
  "token": "<DT_SESSION_TOKEN>"
}
```

The embed token is single-use and valid for 5 minutes.

## 2. Get the conversation URL

Call the DoubleTick Embed URL API with the contact's WhatsApp number:

```curl
curl -X POST 'https://public.doubletick.io/embed/url' \
  -H 'Authorization: <YOUR_DOUBLETICK_PUBLIC_API_KEY>' \
  -H 'Content-Type: application/json' \
  -d '{
    "phone": "<CUSTOMER_PHONE>",
    "wabaNumber": "<YOUR_WABA_NUMBER>"
  }'
```

The response contains the conversation URL:

```json
{
  "url": "https://webextend.doubletick.io/embed/conversations/..."
}
```

> 📘 NOTE
>
> See [Embed WhatsApp Conversations Inside Your Product](https://docs.doubletick.io/docs/embed-chat-conversation) for the complete API reference and available URL parameters.

## 3. Load the conversation

Append the embed token to the conversation URL and use it as the iframe source:

```javascript
const embedUrl = `${url}&embedToken=${token}`;

iframe.src = embedUrl;
```

Example:

```html
<iframe
  src="YOUR_EMBED_URL"
  width="100%"
  height="600"
  frameborder="0"
></iframe>
```

## APIs required from your CRM

DoubleTick needs two APIs from your CRM:

### List Users API

DoubleTick calls this API to fetch your CRM users and map them to DoubleTick agents.

The response should include a stable user `id` along with optional user details such as name, email, and phone.

```json
{
  "users": [
    {
      "id": "usr_98271",
      "name": "Asha Rao",
      "email": "asha@doubletick.com",
      "phone": "+919812345678"
    }
  ]
}
```

### Validate User API

DoubleTick calls this API with the logged-in user's session token to identify the user.

```json
{
  "id": "usr_98271",
  "name": "Asha Rao",
  "email": "asha@doubletick.com",
  "phone": "+919812345678"
}
```

The `id` returned by the Validate-User API must match the same user's `id` returned by the List Users API. This mapping determines which DoubleTick agent the CRM user is associated with.

## Security

* Generate the embed token from your **backend**.
* Do not expose your CRM session token in frontend code.
* Keep your DoubleTick API key on your backend.
* Generate a new embed token when opening a conversation.

For the complete conversation embed API, iframe configuration, URL parameters, and authentication events, see [Embed WhatsApp Conversations Inside Your Product](https://docs.doubletick.io/docs/embed-chat-conversation).