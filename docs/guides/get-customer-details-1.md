Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Get Customer Details

Quickly retrieve customer details using the DoubleTick API, ensuring seamless access to contact information, assigned users, and custom fields. 🔍✅

## API Endpoint

Use the following endpoint to get customer details:

```
GET https://public.doubletick.io/customer/details?phoneNumber={customer_phone}&customerId={customer_id}
```

***

## Request Headers

```json
{
  "Authorization": "YOUR_API_KEY",
  "Content-Type": "application/json"
}
```

## Query Parameters

| Parameter     | Type   | Description                                                       |
| ------------- | ------ | ----------------------------------------------------------------- |
| `phoneNumber` | string | The customer's phone number in <<glossary:international format>>. |
| `customerId`  | string | The unique ID of the customer                                     |

***

## Response

### Success Response (<<glossary:201>>)

```json
{
    "customer": {
        "name": "customer_name",
        "assignedToUser": "client_name",
        "phone": "customer_phone_number",
        "assignedUserNumber": "client_phone_number",
        "customFields": [
            {
                "id": "custom_field_id",
                "name": "message",
                "value": "2025-01-21T09:16:18.000Z",
                "type": "date"
            },
            {
                "id": "custom_field_id",
                "name": "message",
                "value": "2025-01-21T09:17:20.000Z",
                "type": "date"
            }
        ]
    }
}
```

* `customer` (object): Contains details about the customer.
  * `name` (string): Name of the customer.
  * `assignedToUser` (string): Name of the client assigned to the customer.
  * `phone` (string): Customer’s phone number.
  * `assignedUserNumber` (string): Phone number of the assigned client.
  * `customFields` (array of objects): List of custom fields associated with the customer.
    * `id` (string): Unique identifier of the custom field.
    * `name` (string): Name of the custom field.
    * `value` (string): Value stored in the custom field (in this case, a timestamp).
    * `type` (string): Data type of the custom field (e.g., `"date"`).

### Bad Request (<<glossary:400>>)

```json JSON
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
  "message": "error_message",
  "statusCode": 404
}
```

***

## Best Practices

* Always include either `phoneNumber` or `customerId` in the request.
* Ensure that customer data is updated regularly for accurate retrieval.
* Use the correct API key and verify permissions before making a request.