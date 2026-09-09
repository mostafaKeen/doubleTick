Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Assign Custom Fields and/or Tags to Customer

Easily assign custom fields and tags to customers using the DoubleTick API, enabling better segmentation and personalized interactions. 🏷️✅

## API Endpoint

Use the following endpoint to assign custom fields and tags to a customer:

```
POST https://public.doubletick.io/customer/assign-tags-custom-fields
```

***

## Request Headers

```json
{
  "Authorization": "YOUR_API_KEY",
  "Content-Type": "application/json"
}
```

## Request Body Parameters

```json
{
    "phone": "customer_number",
    "tags": ["tag_name"],
    "name": "customer_name",
    "optIn": true,
    "customFields": [
        {
            "name": "field_name",
            "value": "field_value"
        }
    ]
}
```

### Parameters

* `phone` (string, required): The customer’s phone number in <<glossary:international format>>.
* `tags` (array of strings): An array of tags to be assigned to the customer.
* `name` (string, optional): The customer’s name.
* `optIn` (boolean, required): Indicates whether the customer has opted in for messages.
* `customFields` (array of objects, optional): An array of key-value pairs representing custom fields.
  * `name` (string, required): The name of the custom field.
  * `value` (string, required): The value associated with the custom field.

***

## Response

### Success Response (<<glossary:201>>)

```json
{
    "customFields": {
        "added": ["new field"],
        "errored": []
    },
    "tags": {
        "added": ["team lead"],
        "errored": []
    },
    "customerId": "customer_id"
}
```

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

### Unprocessable Entity (<<glossary:422>>)

```json
{
  "message": "invalid file type for audio: text/html; charset=utf-8",
  "error": "Unprocessable Entity",
  "statusCode": 422
}
```

***

## Best Practices

* Ensure that the customer exists before assigning fields or tags.
* Use meaningful tags and custom fields for better segmentation.
* Maintain customer data accuracy by updating fields regularly.