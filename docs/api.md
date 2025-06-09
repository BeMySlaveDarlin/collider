# API Documentation

## Events

### Create Event

**POST** `/event`

Create a new user event.

**Request Body:**

```json
{
  "user_id": 123,
  "event_type": "click",
  "timestamp": "2025-05-28T12:34:56Z",
  "metadata": {
    "page": "/home",
    "button": "cta"
  }
}
```

**Response (201):**

```json
{
  "id": 1,
  "user_id": "123",
  "type": "click",
  "timestamp": "2025-05-28T12:34:56+00:00",
  "metadata": {
    "page": "/home",
    "referrer": "https://google.com"
  }
}
```

### Batch Create Events

**POST** `/events`

Create multiple events.

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
  },
  {
    "user_id": 9,
    "event_type": "page_view",
    "timestamp": "2025-06-08T10:30:00Z",
    "metadata": {
      "page": "/main",
      "referrer": "https://ya.ru"
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

```json
{
  "data": "failed to queue"
}
```

### List Events

**GET** `/events?page=1&limit=100`

Get paginated list of events sorted by timestamp.

**Query Parameters:**

- `page` (int, default: 1) - Page number
- `limit` (int, default: 100, max: 1000) - Items per page

**Response (200):**

```json
{
  "data": [
    {
      "id": 1,
      "user_id": "123",
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

### Total Events Count

**GET** `/events/total`

Get total count of events.

**Response (200):**

```json
{
  "data": [
    {
      "id": 1,
      "user_id": "123",
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

### Delete Events

**DELETE** `/events?before=2025-01-01T00:00:00Z`

Delete events before specified date.

**Query Parameters:**

- `before` (string, required) - ISO 8601 timestamp

**Response (200):**

```json
{
  "data": {
    "deleted_events": 0
  }
}
```

## Users

### Create User

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

### Get User Events

**GET** `/users/events?user_id=123&limit=1000`

Get last events for specific user.

**Query Parameters:**

- `user_id` (int, required) - User ID
- `limit` (int, default: 1000, max: 1000) - Maximum events to return

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
    "page": 1,
    "limit": 1000,
    "total": 1000
  }
}
```

## Statistics

### Get Stats

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
      "/registration": 106181,
      "/order/create": 106168,
      "/logout": 106141,
      "/notifications/read": 106139,
      "/payment/complete": 106042,
      "/product/view": 106040
    }
  },
  "query": {
    "from": "2025-06-01 00:00:00",
    "to": "2025-06-08 00:00:00",
    "limit": "10"
  }
}
```

### Clear cache

**GET** `/clear_cache`

Clears caches

**Response (200):**

```json
{
  "data": "Ok"
}
```

## Error Responses

All endpoints may return error responses in the following format:

**Response (400/500):**

```json
{
  "error": "Error description"
}
```

### Common HTTP Status Codes

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `404` - Not Found
- `500` - Internal Server Error

## Rate Limiting

No rate limiting is currently implemented.

## Authentication

No authentication is currently required.
