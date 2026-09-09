Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Remove Custom Fields and/or Tags from Customer

Effortlessly remove assigned tags or custom fields from a customer using the DoubleTick API to keep customer data clean and up-to-date. 🚀

## API Endpoint

Use the following endpoint to remove fields or tags from a customer:

```
POST https://public.doubletick.io/customer/remove-tags-custom-fields
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
  "tags": ["tag_name", "tag_name"],
  "customFields": ["field_name", "field_name"],
  "wabaNumber": "your_waba_number"
}
```

### Parameters

* `phone` (string, required): The phone number of the customer in <<glossary:international format>>.
* `tags` (array of strings, optional): An array of tag names to be removed.
* `customFields` (array of strings, optional): An array of custom field names to be removed.
* `wabaNumber` (string, required, optional): Integration WABA number with country code.

***

## Response

### Success Response (201)

```json
{
  "customFields": {
    "removed": ["new field1", "new field"],
    "errored": []
  },
  "tags": {
    "removed": ["team lead", "team member"],
    "errored": []
  }
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

### Internal Server Error (<<glossary:500>>)

```json
{
  "statusCode": 500,
  "message": "Internal server error"
}
```

***

## Best Practices

* Ensure that **customer phone number** and **custom field/tag names** are valid before making the request.
* Use this API to maintain clean and relevant customer data.
* Regularly update and manage customer fields/tags for better segmentation.