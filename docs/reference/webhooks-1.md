Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# DoubleTick Webhooks

Receive real-time event notifications from DoubleTick and WhatsApp Cloud API to automate workflows, synchronize data, and react instantly to customer and conversation activities.

> 📌 **Getting Started**
>
> Before you can receive webhook events, you must register a webhook endpoint and subscribe to the events you want to receive. Ensure your endpoint is publicly accessible and capable of handling incoming HTTP requests from DoubleTick.

Webhooks allow DoubleTick to send real-time notifications to your application whenever specific events occur. Instead of repeatedly polling APIs for updates, you can subscribe to webhook events and receive event data automatically as changes happen.

Using webhooks, you can build automated workflows, synchronize customer data, track message activity, monitor conversations, manage agent actions, and integrate DoubleTick with your internal systems.

## How Webhooks Work

1. Create a webhook endpoint in DoubleTick.
2. Subscribe the webhook to the required event types.
3. DoubleTick sends an HTTP request to your endpoint whenever a subscribed event occurs.
4. Process the payload and perform the required action in your application.

## Setting Up a Webhook in DoubleTick

You can configure webhooks directly from the DoubleTick application by following these steps:

1. Log in to the DoubleTick application at [**https://web.doubletick.io**](https://web.doubletick.io).
2. Navigate to **Settings → Webhooks**.
3. Click **Create New Webhook**.
4. Enter the webhook URL where you want DoubleTick to send event notifications.
5. Select the WhatsApp Business Accounts (WABAs) for which webhook events should be delivered.
6. Choose the event categories and specific events you want to subscribe to.
7. Review your configuration and click **Save**.

Once saved, DoubleTick will start delivering the selected webhook events to your configured endpoint whenever those events occur.

## Available Webhook Categories

DoubleTick webhooks are organized into the following categories:

| Category                         | Description                                                                                               |
| -------------------------------- | --------------------------------------------------------------------------------------------------------- |
| Messaging Webhooks               | Receive events related to inbound and outbound messages, message status updates, and delivery activities. |
| Conversation Management Webhooks | Track conversation lifecycle events such as assignment, closure, reopening, and ownership changes.        |
| Customer Data Webhooks           | Stay informed about customer profile creation, updates, and data changes.                                 |
| Customer Notes Webhooks          | Track the creation, modification, and deletion of customer notes.                                         |
| Widget & Lead Webhooks           | Receive events generated from website widgets, lead capture forms, and related interactions.              |
| SLA Webhooks                     | Receive notifications when a breached conversation escalates through your configured escalation levels.   |
| Template Webhooks                | Monitor template creation, approval, rejection, and status updates.                                       |
| Call Webhooks                    | Receive notifications related to call activities and call status changes.                                 |
| Agent Presence Webhooks          | Monitor agent availability and presence status changes within DoubleTick.                                 |
| WhatsApp Group Webhooks          | Track events associated with WhatsApp group creation, updates, and participant activities.                |
| Conversation Cost Webhooks       | Receive a notification whenever a WhatsApp conversation fee is charged to your wallet balance.            |

## Webhook Delivery

When an event occurs, DoubleTick sends an HTTP request containing the event details and related data to your registered webhook URL.

Each webhook payload includes:

* The event type
* Event-specific data
* Associated customer, conversation, message, or system information (depending on the event)

## Next Steps

* Configure a webhook endpoint in DoubleTick
* Subscribe to the required webhook events
* Review the webhook category relevant to your use case
* Implement payload handling in your application
* Test your endpoint by triggering subscribed events