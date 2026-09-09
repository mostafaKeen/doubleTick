Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Getting Started with DoubleTick

Power up your business with DoubleTick – the ultimate WhatsApp API for automation, engagement, and seamless customer communication! 🚀

**DoubleTick** is your all-in-one WhatsApp API solution, built to **supercharge your marketing and customer communication**. From **automated workflows** to **bulk messaging**, we make it effortless to reach and engage customers at scale.

Whether you're a **startup or an enterprise**, **DoubleTick API** ensures instant, reliable, and automated WhatsApp messaging—**built for developers, loved by businesses**.

## ✨ Why Developers & Businesses Love DoubleTick API

✅ **Plug & Play Integration** – Get started in minutes with a simple API call.

✅ **Blazing Fast Delivery** – Reach your customers instantly with near-zero latency.

✅ **Secure & Compliant** – End-to-end encryption & enterprise-grade security.

***

## 🚀 Your First WhatsApp Message in Just 3 Steps

1️⃣ **Get Your API Key** – Sign up and grab your unique API key.

2️⃣ **Make an API Request** – Use our endpoint to send a test message.

3️⃣ **See It Live on WhatsApp** – Your message is delivered instantly!

[block:image]
{
  "images": [
    {
      "image": [
        "https://files.readme.io/1b6e2126c88feca5506ca85dc1a6c12cd67648ec45f9b2418caea21a7d426e68-api-request.gif",
        "",
        "Your first API call"
      ],
      "align": "center",
      "border": true,
      "caption": "[Send WhatsApp Template Message](https://docs.doubletick.io/reference/outgoing-messages-whatsapp-template)"
    }
  ]
}
[/block]

### 🔗 Base URL

```
https://public.doubletick.io
```

### 👉 Sample API Request

```bash CURL
curl --request POST \
     --url https://public.doubletick.io/whatsapp/message/template \
     --header 'Authorization: key_xxxxxxxxxx' \
     --header 'accept: application/json' \
     --header 'content-type: application/json' \
     --data '
{
  "messages": [
    {
      "content": {
        "language": "en",
        "templateName": "template_name"
      },
      "from": "sender_number",
      "to": "customer_number"
    }
  ]
}
'
```
```node
const axios = require('axios');

const apiUrl = 'https://public.doubletick.io/whatsapp/message/template';
const apiKey = 'key_xxxxxxxxxx';

const payload = {
  messages: [
    {
      content: {
        language: 'en',
        templateName: 'template_name'
      },
      from: 'sender_number',
      to: 'customer_number'
    }
  ]
};

axios.post(apiUrl, payload, {
  headers: {
    'Authorization': `key ${apiKey}`,
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  }
})
.then(response => console.log(response.data))
.catch(error => console.error(error.response ? error.response.data : error.message));
```
```php
<?php
$apiUrl = "https://public.doubletick.io/whatsapp/message/template";
$apiKey = "key_xxxxxxxxxx";

$data = [
    "messages" => [
        [
            "content" => [
                "language" => "en",
                "templateName" => "template_name"
            ],
            "from" => "sender_number",
            "to" => "customer_number"
        ]
    ]
];

$payload = json_encode($data);

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: key $apiKey",
    "Accept: application/json",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

$response = curl_exec($ch);
curl_close($ch);

echo $response;
?>
```

***

## 📌 Explore the API Sections

| API End-point                                                           | Description                                      |
| :---------------------------------------------------------------------- | :----------------------------------------------- |
| [Outgoing Messaging](https://docs.doubletick.io/docs/outgoing-messages) | Send text, images, videos & documents            |
| [Broadcast Groups](https://docs.doubletick.io/docs/broadcast-groups-1)  | Scale up with group messaging                    |
| [Templates](https://docs.doubletick.io/docs/template-1)                 | Automate with WhatsApp templates                 |
| [Customers](https://docs.doubletick.io/docs/customer-1)                 | Manage customer data & interactions              |
| [Team Member](https://docs.doubletick.io/docs/team-member-1)            | Assign roles & permissions effortlessly          |
| [Wallet](https://docs.doubletick.io/docs/wallet-1)                      | Track usage & manage wallet balance              |
| [Webhooks](https://docs.doubletick.io/docs/webhook-1)                   | Get real-time updates on message status & events |

***

## 📣 Need Help? We're Here!

💡 **[Guides & Tutorials](https://docs.doubletick.io/docs/getting-started)** – Walkthroughs for every feature.

🛠️ **[API Reference](https://docs.doubletick.io/reference/outgoing-messages-whatsapp-template)** – Detailed endpoints & parameters.

📖 **[Recipes](https://docs.doubletick.io/recipes)** – Ready-to-use API workflows for common use cases.