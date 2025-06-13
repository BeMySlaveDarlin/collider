#!/bin/bash

if [ -z "$1" ]; then
    echo "Usage: $0 <host> <user_id> <event_type>"
    echo "Example: $0 http://localhost 123 user.registered"
    exit 1
fi

HOST="$1"
USER_ID="${2:-123}"
TYPE="${3:-click}"
REQUESTS=5000
CONCURRENCY=50

cat > event_data.json << EOF
{
  "user_id": $USER_ID,
  "event_type": "$TYPE",
  "timestamp": "2025-06-24 12:34:56",
  "metadata": {
    "page": "/home",
    "button": "login"
  }
}
EOF

echo "🚀 Запуск ab тестов"
echo "Host: $HOST"
echo "Requests: $REQUESTS"
echo "Concurrency: $CONCURRENCY"
echo "User ID: $USER_ID"
echo "TYPE: $TYPE"
echo "=================================="

# Функция для парсинга результатов AB
parse_ab_results() {
    local test_name="$1"
    local output="$2"

    local path=$(echo "$output" | grep "Document Path:" | sed 's/Document Path:[[:space:]]*//')
    local time=$(echo "$output" | grep "Time taken for tests:" | sed 's/Time taken for tests:[[:space:]]*//')
    local rps=$(echo "$output" | grep "Requests per second:" | sed 's/Requests per second:[[:space:]]*//')
    local time_per_req=$(echo "$output" | grep "Time per request:" | head -1 | sed 's/Time per request:[[:space:]]*//')

    echo "Results of test $test_name"
    echo "  Path: $path"
    echo "  Time: $time"
    echo "  RPS: $rps"
    echo "  Time per request: $time_per_req"
    echo "=================================="
}

echo "Testing POST /events..."
output=$(ab -n $REQUESTS -c $CONCURRENCY \
   -T "application/json" \
   -p event_data.json \
   "$HOST/event" 2>/dev/null)
parse_ab_results "Create Event:" "$output"

echo "Testing GET /events..."
output=$(ab -n $REQUESTS -c $CONCURRENCY "$HOST/events?page=1&limit=1000" 2>/dev/null)
parse_ab_results "Events List:" "$output"

echo "Testing GET /users/{uid}/events..."
output=$(ab -n $REQUESTS -c $CONCURRENCY "$HOST/users/$USER_ID/events?limit=1000" 2>/dev/null)
parse_ab_results "User Events:" "$output"

echo "Testing GET /stats..."
output=$(ab -n $REQUESTS -c $CONCURRENCY "$HOST/stats?from=2025-06-01 00:00:00&to=2025-07-30 00:00:00&limit=10&type=$TYPE" 2>/dev/null)
parse_ab_results "Stats:" "$output"

echo "Тесты завершены!"
