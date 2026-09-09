Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Get Wallet Balance

Retrieve your current DoubleTick wallet balance instantly to ensure sufficient funds for seamless API operations. 🚀

## API Endpoint

Use the following endpoint to retrieve the wallet balance:

```
GET https://public.doubletick.io/wallet/balance
```

***

## Request Headers

```json
{
  "Authorization": "YOUR_API_KEY"
}
```

***

## Response

### Success Response (<<glossary:201>>)

```json
{
    "balance": 10.34577189385891,
    "currencyCode": "INR"
}
```

* `balance` (number): Available wallet balance.
* `currencyCode` (string): Currency of the balance (e.g., `"INR"` for Indian Rupee\`).

### Unauthorized (<<glossary:401>>)

```json
{
    "message": "Invalid public api key",
    "error": "Unauthorized",
    "statusCode": 401
}
```

***

## Best Practices

* Regularly check your **wallet balance** to avoid service disruptions.
* Maintain **sufficient funds** for uninterrupted API operations.