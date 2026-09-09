Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Customer Data Webhooks

Receive notifications whenever tags are added to or removed from customer conversations, or when a customer's custom field value is created, updated, or removed. Use these events to keep customer segmentation, CRM labels, and profile data synchronized across external systems.

## Events Covered

| Event                          | Description                                                                  |
| ------------------------------ | ---------------------------------------------------------------------------- |
| `ADD_TAG`                      | Fired when a tag is added to a customer conversation                         |
| `REMOVE_TAG`                   | Fired when a tag is removed from a customer conversation                     |
| `UPDATE_CUSTOMER_CUSTOM_FIELD` | Fired whenever a customer custom field value is created, updated, or removed |

***

# Tags

## Shared Payload Structure

`ADD_TAG` and `REMOVE_TAG` share the same core payload structure and differ only in the action being performed.

| Field            | Type   | Required | Description                           |
| ---------------- | ------ | -------- | ------------------------------------- |
| `tagId`          | string | Yes      | Unique identifier of the tag          |
| `tagName`        | string | Yes      | Display name of the tag               |
| `from`           | string | Yes      | Customer's phone number               |
| `dtCustomerId`   | string | Yes      | Unique DoubleTick customer identifier |
| `customerHandle` | string | No       | Non-WhatsApp channel identifier       |
| `channelType`    | string | No       | Channel provider type                 |

### channelType Values

| Value             | Description                                |
| ----------------- | ------------------------------------------ |
| `WHATSAPP`        | Standard WhatsApp Business Account channel |
| `INSTAGRAM`       | Instagram Direct Messages channel          |
| `GENERIC_CHANNEL` | Custom or third-party channel integration  |
| `PSTN`            | PSTN (phone/voice) channel                 |

### Conditional Fields

| Field            | Available When                                                                               |
| ---------------- | -------------------------------------------------------------------------------------------- |
| `customerHandle` | Non-WhatsApp channels such as Instagram, Generic Channel, and PSTN                           |
| `channelType`    | Present when the channel type can be determined; omitted for standard WhatsApp conversations |

***

## Tag Added

**Event type:** `ADD_TAG`

Fires when a tag is applied to a customer conversation.

This event may be triggered by:

* An agent manually adding a tag
* A bulk tagging operation

### Sample Payload

```json
{
  "tagId": "tag_01HX9abc123",
  "tagName": "Premium Customer",
  "from": "+919876543210",
  "dtCustomerId": "customer_pvgpZUh3wg",
  "channelType": "WHATSAPP",
  "tagAdded": true
}
```

### Additional Fields

All [shared fields](#shared-payload-structure) apply. The following field is specific to this event:

| Field      | Type    | Required | Description               |
| ---------- | ------- | -------- | ------------------------- |
| `tagAdded` | boolean | Yes      | Indicates a tag was added |

### Notes

* One webhook event is emitted per tag that is added.
* If multiple tags are added during a bulk operation, each tag generates its own webhook event.
* This event is delivered only for individual customer conversations.

### Edge Cases

#### Bulk Tagging

If a bulk action adds the same tag to multiple customers, a separate webhook event is generated for each customer conversation.

#### Multiple Tags Added

If three tags are added to a single conversation at the same time, three separate `ADD_TAG` webhook events are emitted.

***

## Tag Removed

**Event type:** `REMOVE_TAG`

Fires when a tag is removed from a customer conversation.

This event may be triggered by:

* An agent manually removing a tag
* A bulk tag removal operation

### Sample Payload

```json
{
  "tagId": "tag_01HX9abc123",
  "tagName": "Premium Customer",
  "from": "+919876543210",
  "dtCustomerId": "customer_pvgpZUh3wg",
  "channelType": "WHATSAPP",
  "tagRemoved": true
}
```

### Additional Fields

All [shared fields](#shared-payload-structure) apply. The following field is specific to this event:

| Field        | Type    | Required | Description                 |
| ------------ | ------- | -------- | --------------------------- |
| `tagRemoved` | boolean | Yes      | Indicates a tag was removed |

### Notes

* One webhook event is emitted per tag that is removed.
* Removing multiple tags from a conversation generates multiple webhook events.
* This event is delivered only for individual customer conversations.

### Edge Cases

#### Bulk Untag Operations

If a bulk action removes a tag from multiple customers, a separate webhook event is generated for each customer conversation.

#### Multiple Tags Removed

If three tags are removed in a single operation, three separate `REMOVE_TAG` webhook events are emitted.

***

## Payload Differences — Tags

| Field            | ADD\_TAG    | REMOVE\_TAG |
| ---------------- | ----------- | ----------- |
| `tagId`          | Yes         | Yes         |
| `tagName`        | Yes         | Yes         |
| `from`           | Yes         | Yes         |
| `dtCustomerId`   | Yes         | Yes         |
| `tagAdded`       | Yes         | No          |
| `tagRemoved`     | No          | Yes         |
| `customerHandle` | Conditional | Conditional |
| `channelType`    | Conditional | Conditional |

***

## Important Considerations — Tags

### Group Conversations

Tag webhooks are not emitted for group conversations. Only individual customer conversations generate these events.

### One Event Per Tag

Webhook delivery is performed at the tag level rather than the operation level. A single bulk operation may result in multiple webhook events.

### Customer Synchronization

If your CRM or external system mirrors customer tags, process each webhook independently and update tag membership incrementally rather than assuming a complete tag snapshot is provided in the payload.

***

# Custom Fields

A single webhook event type, `UPDATE_CUSTOMER_CUSTOM_FIELD`, is used to represent all custom field changes.

The type of change is identified by the `customFieldEventType` field within the payload.

Possible values:

| Value               | Description                                       |
| ------------------- | ------------------------------------------------- |
| `ATTRIBUTE_SET`     | A value is assigned to a field for the first time |
| `ATTRIBUTE_UPDATE`  | An existing value is modified                     |
| `ATTRIBUTE_REMOVED` | A field value is cleared or deleted               |

One webhook event is emitted per field change. For example, if a bulk update modifies five different custom fields, five separate webhook events are generated.

***

## ATTRIBUTE\_SET

Fires when a custom field receives a value for the first time.

### Sample Payload

```json
{
  "customerId": "customer_pvgpZUh3wg",
  "customerPhone": "+919876543210",
  "customFieldId": "cf_01HX9abc456",
  "customFieldUniqueName": "preferred_language",
  "value": "Hindi",
  "customFieldType": "text",
  "customFieldEventType": "ATTRIBUTE_SET",
  "dtCustomerId": "customer_pvgpZUh3wg",
  "name": "John Doe",
  "nameOnWhatsapp": "John",
  "customerHandle": null,
  "wabaNumber": "+919000000000",
  "changedAt": "2026-01-15T10:30:00.000Z"
}
```

***

## ATTRIBUTE\_UPDATE

Fires when an existing field value changes.

### Sample Payload

```json
{
  "customerId": "customer_pvgpZUh3wg",
  "customerPhone": "+919876543210",
  "customFieldId": "cf_01HX9abc456",
  "customFieldUniqueName": "preferred_language",
  "value": "English",
  "customFieldType": "text",
  "customFieldEventType": "ATTRIBUTE_UPDATE",
  "dtCustomerId": "customer_pvgpZUh3wg",
  "name": "John Doe",
  "nameOnWhatsapp": "John",
  "customerHandle": null,
  "wabaNumber": "+919000000000",
  "changedAt": "2026-01-15T11:00:00.000Z"
}
```

***

## ATTRIBUTE\_REMOVED

Fires when a field value is cleared or deleted.

### Sample Payload

```json
{
  "customerId": "customer_pvgpZUh3wg",
  "customerPhone": "+919876543210",
  "customFieldId": "cf_01HX9abc456",
  "customFieldUniqueName": "preferred_language",
  "value": null,
  "customFieldType": "text",
  "customFieldEventType": "ATTRIBUTE_REMOVED",
  "dtCustomerId": "customer_pvgpZUh3wg",
  "name": "John Doe",
  "nameOnWhatsapp": "John",
  "customerHandle": null,
  "wabaNumber": "+919000000000",
  "changedAt": "2026-01-15T12:00:00.000Z"
}
```

***

## Payload Fields — Custom Fields

| Field                   | Type           | Required | Description                                 |
| ----------------------- | -------------- | -------- | ------------------------------------------- |
| `customerId`            | string         | Yes      | Unique DoubleTick customer identifier       |
| `dtCustomerId`          | string         | Yes      | Same value as `customerId`                  |
| `customerPhone`         | string         | Yes      | Customer's phone number                     |
| `customFieldId`         | string         | Yes      | Unique identifier of the custom field       |
| `customFieldUniqueName` | string         | No       | Developer-defined unique field name         |
| `value`                 | string \| null | Yes      | Current value of the field                  |
| `customFieldType`       | string         | Yes      | Data type of the field                      |
| `customFieldEventType`  | string         | Yes      | Type of field change                        |
| `name`                  | string         | Yes      | Customer display name in DoubleTick         |
| `nameOnWhatsapp`        | string         | Yes      | Customer name from WhatsApp                 |
| `customerHandle`        | string \| null | Yes      | Channel-specific customer identifier        |
| `wabaNumber`            | string \| null | Yes      | Associated WhatsApp Business number         |
| `changedAt`             | string         | Yes      | ISO 8601 timestamp when the change occurred |

***

## customFieldType Values

| `customFieldType` | Value Format                          |
| ----------------- | ------------------------------------- |
| `text`            | Plain string                          |
| `number`          | Numeric value represented as a string |
| `date`            | Date string                           |
| `boolean`         | `"true"` or `"false"`                 |
| `single_select`   | Selected option display name          |
| `multi_select`    | Comma-separated option display names  |

### Examples

#### Text

```json
{ "customFieldType": "text", "value": "English" }
```

#### Number

```json
{ "customFieldType": "number", "value": "42" }
```

#### Date

```json
{ "customFieldType": "date", "value": "2024-01-15" }
```

> Date values are always delivered in `YYYY-MM-DD` format (ISO 8601 date, no time component).

#### Boolean

```json
{ "customFieldType": "boolean", "value": "true" }
```

#### Single Select

```json
{ "customFieldType": "single_select", "value": "Premium" }
```

#### Multi Select

```json
{ "customFieldType": "multi_select", "value": "Sales, Enterprise, Priority" }
```

> Multiple selected options are joined with `", "` (comma + space). If an option's display name itself contains a comma, parsing with a simple `split(",")` will produce incorrect results. Trim whitespace from each element after splitting and validate results against your known option list.

***

## Conditional Fields — Custom Fields

| Field                   | Condition                                                         |
| ----------------------- | ----------------------------------------------------------------- |
| `customFieldUniqueName` | Present only when a unique name has been configured for the field |

***

## Important Considerations — Custom Fields

### Display Names Are Returned For Select Fields

For `single_select` and `multi_select` fields, the webhook returns the display name of the selected option rather than its internal identifier. Do not use the value as a database key or attempt to map it directly to internal option IDs.

### Internal Fields Are Excluded

Only user-created custom fields generate this webhook. Changes to internal or system-managed customer fields do not trigger webhook delivery.

### One Event Per Field

Webhook events are generated at the field level. If a bulk update changes multiple fields, a separate webhook event is emitted for each field that changed.

***

## Edge Cases — Custom Fields

### Value Removal

When a value is removed, the `value` field is always returned as `null`. An empty string is never used to indicate removal.

```json
{ "customFieldEventType": "ATTRIBUTE_REMOVED", "value": null }
```

### Duplicate Events Across Integrations

If customer data is accessible through multiple integrations or WABA numbers, the same change may generate multiple webhook deliveries. Applications should deduplicate events when necessary.

### Name Snapshot Behavior

The `name` and `nameOnWhatsapp` fields represent the customer information at the moment the webhook was generated. If a customer name changes at the same time as a custom field update, the values included in the payload may not reflect the final state.

### Bulk Updates

If ten custom fields are updated in a single operation, ten separate webhook events are generated. Each event contains information about only one changed field.

***

## Integration Recommendations — Custom Fields

### Store Field IDs

Use `customFieldId` as the primary identifier when mapping fields in external systems. Field names and labels may change over time.

### Handle Event Types Explicitly

| Event Type          | Recommended Action       |
| ------------------- | ------------------------ |
| `ATTRIBUTE_SET`     | Create or populate value |
| `ATTRIBUTE_UPDATE`  | Replace existing value   |
| `ATTRIBUTE_REMOVED` | Clear stored value       |

### Avoid Parsing Value Types

Although field types are provided, values are delivered as strings (or null). Always validate and convert values according to your application's requirements.