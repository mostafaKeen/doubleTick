Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Getting Started with DoubleTick Webhooks

Learn how to register webhooks, capture events, and securely access media URLs with DoubleTick.

DoubleTick offers webhooks—lightweight HTTP callbacks that notify your system the moment selected events happen on the platform. Think of them as instant alerts for things like new messages, template updates, or tags being added.

## How to Register a Webhook in DoubleTick

To set up a webhook in DoubleTick, follow these steps:

1. Navigate to Settings → Webhooks in your DoubleTick dashboard.
2. Select New Webhook.
3. Enter your endpoint URL and choose the specific events you want to subscribe to.
4. Click Save to confirm.

Once configured, DoubleTick will begin delivering event notifications to your specified endpoint in real time.

## Supported Webhook Events

DoubleTick provides a wide range of webhook events to help you stay updated in real time. Some of the commonly used events include:

* **MESSAGE\_RECEIVED** – Triggered when a new incoming message is received.
* **MESSAGE\_STATUS\_UPDATE** – Notifies you when the status of a sent message changes (e.g., delivered, read).
* **TEMPLATE\_UPDATE** – Triggered when a template’s status or details are updated.
* **CUSTOMER\_UPDATE** – Fires when customer details or tags are modified.

These events ensure your systems are always in sync with activities happening on DoubleTick.

## Accessing Media URLs

When a webhook delivers a message with media (such as an image, video, audio, or document), the payload includes a url field. To retrieve the file, you need to authenticate the request by using your public api key by sending it in the request headers.

```javascript
const mediaUrl = webhook.payload.message.url;
const token = "YOUR_API_KEY";

const response = axios.get(mediaUrl, {
  headers: {
    "Authorization": token,
  }
});
```

⚠️ **Important**: This ensures only authorized requests can retrieve the media, keeping your data secure.

## Example Scenario

Consider this workflow:

1. A customer sends a **media** (image, audio, video, or document) via WhatsApp.
2. DoubleTick triggers a webhook (e.g., **MESSAGE\_RECEIVED**) containing the media URL in the payload.
3. Your backend attaches the required public API key via authorization header and securely retrieves the media file.

## Best Practices

To ensure secure and reliable webhook usage, keep these best practices in mind:

* **Secure Endpoints**: Always use HTTPS for your webhook URL.
* **Error Handling**: Implement retries and logging to handle delivery failures gracefully.