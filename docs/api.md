# API Documentation

## Base URL

```
http://localhost
```

## Response Format

```json
{
  "data": {},
  "query": {}
}
```

## Error Format

```json
{
  "error": "Error message",
  "code": 400
}
```

## Endpoints

### Events

#### Create Event

**POST** `/events`

Create a new user event.

**Request Body:**

```json
{
  "user_id": 123,
  "event_type": "click",
  "timestamp": "2025-05-28T12:34:56Z",
  "metadata": {
    "page": "/home"
  }
}
```

**Response (201):**

```json
{
  "data": {
    "id": 1,
    "user_id": 123,
    "type": "click",
    "timestamp": "2025-05-28T12:34:56+00:00",
    "metadata": {
      "page": "/home"
    }
  }
}
```

#### Batch Create Event

**POST** `/events/batch`

Create a new user event.

**Request Body:**

```json
[
  {
    "user_id": 8,
    "event_type": "page_view",
    "timestamp": "2025-06-08T10:30:00Z",
    "metadata": {
      "page": "/dashboard",
      "referrer": "https://google.com"
    }
  }
]
```

**Response (201):**

```json
{
  "data": "queued"
}
```

#### List Events

**GET** `/events?page=1&limit=100`

Get paginated list of events sorted by timestamp.

**Query Parameters:**

- `page` (int, default: 1) - Page number
- `limit` (int, default: 1) - Items per page

**Response (200):**

```json
{
  "data": [
    {
      "id": 1,
      "user_id": 123,
      "type": "click",
      "timestamp": "2025-05-28T12:34:56+00:00",
      "metadata": {
        "page": "/home"
      }
    }
  ],
  "query": {
    "page": 1,
    "limit": 100,
    "total": 1000
  }
}
```

#### Delete Events

**DELETE** `/events?before=2025-01-01T00:00:00Z`

Delete events before specified date.

**Query Parameters:**

- `before` (string, required) - ISO 8601 timestamp

**Response (200):**

```json
{
  "data": {
    "deleted_events": 1234567
  }
}
```

### Users

#### Create User

**POST** `/users`

Create a new user with random name.

**Response (201):**

```json
{
  "data": {
    "id": 122,
    "name": "Ms. Pamela Heathcote"
  }
}
```

#### Get User Events

**GET** `/users/events?user_id=123&limit=1000`

Get last events for specific user.

**Query Parameters:**

- `user_id` (int, required) - User ID
- `limit` (int, default: 1) - Maximum events to return

**Response (200):**

```json
{
  "data": [
    {
      "id": 1,
      "type": "click",
      "timestamp": "2025-05-28T12:34:56+00:00",
      "metadata": {
        "page": "/home"
      }
    }
  ],
  "query": {
    "user_id": 123,
    "limit": 1000,
    "total": 1000
  }
}
```

### Statistics

#### Get Stats

**GET** `/stats?from=2025-01-01T00:00:00Z&to=2025-12-31T23:59:59Z&type=click&limit=5`

Get aggregated statistics for events.

**Query Parameters:**

- `from` (string, optional) - Start date (ISO 8601)
- `to` (string, optional) - End date (ISO 8601)
- `type` (string, optional) - Event type filter
- `limit` (int, default: 3) - Number of top pages to return

**Response (200):**

```json
{
  "data": {
    "total_events": 2331940,
    "unique_users": 100,
    "top_pages": {
      "/notifications/sent": 106653,
      "/cart/remove": 106391,
      "/login": 106253,
      "/emails/click": 106250,
      "/registration": 106181
    }
  },
  "query": {
    "from": "2025-06-01T00:00:00Z",
    "to": "2025-06-08T00:00:00Z",
    "limit": 5
  }
}
```

## Status Codes

| Code | Description           |
|------|-----------------------|
| 200  | Success               |
| 201  | Created               |
| 400  | Bad Request           |
| 404  | Not Found             |
| 500  | Internal Server Error |

## Examples

### cURL

```bash
# Create event
curl -X POST http://localhost/events \
  -H "Content-Type: application/json" \
  -d '{"user_id": 1, "event_type": "click", "timestamp": "2025-01-01T00:00:00Z"}'

# Get events
curl http://localhost/events?page=1&limit=100

# Get statistics
curl "http://localhost/stats?from=2025-01-01T00:00:00Z&to=2025-01-31T23:59:59Z"

# Delete old events
curl -X DELETE "http://localhost/events?before=2025-01-01T00:00:00Z"
```
