Fetch the complete documentation index at: https://docs.doubletick.io/llms.txt. Use this file to discover all available pages before exploring further. Append .md to any documentation page URL to get its markdown version.

# Customer Notes Webhooks

Receive notifications whenever notes are created, updated, or deleted on customer conversations. These events help synchronize internal conversation context, agent observations, follow-up instructions, and operational notes across external systems.

## Events Covered

| Event                   | Description                  |
| ----------------------- | ---------------------------- |
| `CUSTOMER_NOTE_CREATED` | Fired when a note is created |
| `CUSTOMER_NOTE_UPDATED` | Fired when a note is edited  |
| `CUSTOMER_NOTE_DELETED` | Fired when a note is deleted |

Customer Notes Webhooks track the complete lifecycle of notes associated with customer conversations.

These events are generated when agents create, modify, or remove notes inside DoubleTick.

Common use cases include:

* CRM synchronization
* Audit logging
* Agent activity tracking
* Knowledge management systems
* Internal workflow automation

***

# Shared Payload Structure

All Customer Note events share the same core payload structure.

| Field         | Type   | Required | Description                           |
| ------------- | ------ | -------- | ------------------------------------- |
| event         | string | Yes      | Type of note event                    |
| noteId        | string | Yes      | Unique identifier of the note         |
| wabaNumber    | string | Yes      | Associated WhatsApp Business number   |
| customerPhone | string | Yes      | Customer phone number                 |
| customerName  | string | Yes      | Customer display name                 |
| note          | string | Yes      | Full note content                     |
| dtCustomerId  | string | Yes      | Unique DoubleTick customer identifier |

> **Event naming:** The `event` field inside each payload uses kebab-case values (`"note-created"`, `"note-updated"`, `"note-deleted"`). This differs from the SCREAMING\_SNAKE\_CASE convention used by most other DoubleTick webhook payloads. The subscription event types (`CUSTOMER_NOTE_CREATED` etc.) follow the standard convention — only the payload body `event` field deviates.

***

# Customer Note Created

**Event type:** `CUSTOMER_NOTE_CREATED`

Fires when an agent creates a new note on a customer conversation.

## Sample Payload

```json
{
  "event": "note-created",
  "noteId": "note_01HX9abc123",
  "wabaNumber": "+919000000000",
  "customerPhone": "+919876543210",
  "customerName": "John Doe",
  "note": "Customer prefers Hindi. @David please follow up.",
  "createdByAgent": "James Smith",
  "createdByAgentNumber": "+919111222333",
  "createdAt": "2024-01-15T10:30:00.000Z",
  "dtCustomerId": "customer_pvgpZUh3wg"
}
```

## Additional Fields

All [shared fields](#shared-payload-structure) apply. The following fields are specific to this event:

| Field                | Type   | Required | Description                            |
| -------------------- | ------ | -------- | -------------------------------------- |
| createdByAgent       | string | Yes      | Agent who created the note             |
| createdByAgentNumber | string | Yes      | Agent phone number or login identifier |
| createdAt            | string | Yes      | ISO 8601 timestamp of creation         |

### Notes

* The payload contains the complete note content.
* Agent mentions are already resolved into human-readable names.

> **Privacy notice:** `createdByAgentNumber` contains personal data (agent phone number). Handle in accordance with applicable data protection regulations and avoid logging it in plain text.

***

# Customer Note Updated

**Event type:** `CUSTOMER_NOTE_UPDATED`

Fires when an existing note is edited.

## Sample Payload

```json
{
  "event": "note-updated",
  "noteId": "note_01HX9abc123",
  "wabaNumber": "+919000000000",
  "customerPhone": "+919876543210",
  "customerName": "John Doe",
  "note": "Customer prefers Hindi. @David confirmed follow-up scheduled.",
  "updatedByAgent": "James Smith",
  "updatedByAgentNumber": "+919111222333",
  "updatedAt": "2024-01-15T11:30:00.000Z",
  "dtCustomerId": "customer_pvgpZUh3wg"
}
```

## Additional Fields

All [shared fields](#shared-payload-structure) apply. The following fields are specific to this event:

| Field                | Type   | Required | Description                            |
| -------------------- | ------ | -------- | -------------------------------------- |
| updatedByAgent       | string | Yes      | Agent who updated the note             |
| updatedByAgentNumber | string | Yes      | Agent phone number or login identifier |
| updatedAt            | string | Yes      | ISO 8601 timestamp of update           |

### Notes

* The `note` field contains the complete updated note.
* Previous note content is not included in the payload.
* Only the latest version of the note is delivered.

***

# Customer Note Deleted

**Event type:** `CUSTOMER_NOTE_DELETED`

Fires when an existing note is deleted.

## Sample Payload

```json
{
  "event": "note-deleted",
  "noteId": "note_01HX9abc123",
  "wabaNumber": "+919000000000",
  "customerPhone": "+919876543210",
  "customerName": "John Doe",
  "note": "Customer prefers Hindi. @David confirmed follow-up scheduled.",
  "deletedByAgent": "James Smith",
  "deletedByAgentNumber": "+919111222333",
  "deletedAt": "2024-01-15T12:00:00.000Z",
  "dtCustomerId": "customer_pvgpZUh3wg"
}
```

## Additional Fields

All [shared fields](#shared-payload-structure) apply. The following fields are specific to this event:

| Field                | Type   | Required | Description                            |
| -------------------- | ------ | -------- | -------------------------------------- |
| deletedByAgent       | string | Yes      | Agent who deleted the note             |
| deletedByAgentNumber | string | Yes      | Agent phone number or login identifier |
| deletedAt            | string | Yes      | ISO 8601 timestamp of deletion         |

### Notes

* The deleted note content is intentionally included in the payload.
* This allows downstream systems to retain historical records even after deletion.

***

# Event Differences

| Field          | CUSTOMER\_NOTE\_CREATED | CUSTOMER\_NOTE\_UPDATED | CUSTOMER\_NOTE\_DELETED |
| -------------- | ----------------------- | ----------------------- | ----------------------- |
| event          | note-created            | note-updated            | note-deleted            |
| noteId         | Yes                     | Yes                     | Yes                     |
| note           | Yes                     | Yes                     | Yes                     |
| createdByAgent | Yes                     | No                      | No                      |
| updatedByAgent | No                      | Yes                     | No                      |
| deletedByAgent | No                      | No                      | Yes                     |
| createdAt      | Yes                     | No                      | No                      |
| updatedAt      | No                      | Yes                     | No                      |
| deletedAt      | No                      | No                      | Yes                     |
| customerPhone  | Yes                     | Yes                     | Yes                     |
| customerName   | Yes                     | Yes                     | Yes                     |
| wabaNumber     | Yes                     | Yes                     | Yes                     |
| dtCustomerId   | Yes                     | Yes                     | Yes                     |

***

# Important Considerations

### Notes Are Free-Form Text

The `note` field contains agent-authored content and may contain arbitrary text.

Applications should treat note content as unstructured text.

Do not rely on note formatting remaining consistent.

### Mentions Are Human Readable

Agent mentions are delivered as display names.

Example:

```text
@David please follow up tomorrow.
```

The webhook does not include internal user identifiers for mentions.

### Do Not Parse Note Content

The content of notes is intended for human consumption.

Avoid building business logic that depends on specific text patterns or note formatting.

### Full Content Is Always Returned

For create, update, and delete events, the webhook payload contains the complete note content rather than a partial update.

***

# Edge Cases

### No Change History

Update events contain only the latest version of the note.

The previous version is not included.

If historical tracking is required, store prior note content when processing earlier events.

### Deleted Notes Remain Available In Payload

Although the note has been removed from DoubleTick, the webhook still contains the deleted note content.

This enables external systems to maintain audit trails and historical records.

### Multiple Edits

A note edited multiple times generates multiple `CUSTOMER_NOTE_UPDATED` events.

Each event contains the complete note state at the time the update was saved.

### Mention Resolution

Agent mentions are resolved before webhook delivery.

The exact mention formatting may change over time and should not be relied upon for programmatic processing.

***

# Integration Recommendations

### Use noteId As The Primary Identifier

Store and track notes using `noteId`.

Agent names and note content may change over time, but the note identifier remains stable throughout the note lifecycle.

### Maintain Local History

If your application requires version history, store each update event as a separate revision.

DoubleTick does not provide previous note values in update payloads.

### Process Events Independently

Each note event represents a distinct lifecycle action.

Applications should handle create, update, and delete events separately rather than assuming a complete note snapshot exists elsewhere.