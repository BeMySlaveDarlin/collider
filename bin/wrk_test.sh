#!/bin/bash

if [ -z "$1" ]; then
    echo "Usage: $0 <host> <user_id> <event_type>"
    echo "Example: $0 http://localhost 1 usr_reg"
    exit 1
fi

HOST="$1"
USER_ID="${2:-1}"
TYPE="${3:-usr_reg}"
THREADS=4
CONNECTIONS=50
DURATION="10s"

mkdir -p runtime/wrk_scripts

POST_DATE=$(date +"%Y-%m-%dT%H:%M:%SZ")
MONTH_START="2025-01-01%2000:00:00"
MONTH_END="2026-01-01%2000:00:00"

cat > runtime/event_data.json << EOF
{
  "user_id": $USER_ID,
  "event_type": "$TYPE",
  "timestamp": "$POST_DATE",
  "metadata": {
    "page": "/dashboard",
    "referrer": "https://google.com"
  }
}
EOF

DONE_FUNC='
function done(summary, latency, requests)
    local duration_sec = summary.duration / 1e6

    print("Response times (ms):")
    print(string.format("| %-8s | %-8s | %-8s |", "Min", "Avg", "Max"))
    print(string.format("| %-8.2f | %-8.2f | %-8.2f |", latency.min / 1000, latency.mean / 1000, latency.max / 1000))
    print(string.format("Total requests: %d/%.0fs", summary.requests, duration_sec))
    print(string.format("Requests/s: %.0f", summary.requests / duration_sec))
    print(string.format("Transfer/s: %.2fMB", (summary.bytes / 1024 / 1024) / duration_sec))
end
'

cat > runtime/wrk_scripts/post_event.lua << 'EOF'
wrk.method = "POST"
wrk.headers["Content-Type"] = "application/json"

local file = io.open("runtime/event_data.json", "r")
if file then
    wrk.body = file:read("*all")
    file:close()
else
    wrk.body = '{"error": "could not read runtime/event_data.json"}'
end
EOF
echo "$DONE_FUNC" >> runtime/wrk_scripts/post_event.lua

cat > "runtime/wrk_scripts/done.lua" << EOF
$DONE_FUNC
EOF

echo "Warming up all endpoints..."
for i in {1..100}; do
    printf "\rWarmup progress: %d%%" "$i"

    curl -s -X POST "$HOST/events" \
        -H "Content-Type: application/json" \
        -d @runtime/event_data.json > /dev/null
    curl -s "$HOST/events?page=1&limit=1000" > /dev/null
    curl -s "$HOST/users/$USER_ID/events" > /dev/null
    curl -s "$HOST/stats?from=$MONTH_START&to=$MONTH_END&limit=10&type=$TYPE" > /dev/null

    sleep_time=$(awk -v min=0.005 -v max=0.01 'BEGIN{srand(); print min+rand()*(max-min)}')
    sleep "$sleep_time"
done
echo ""

echo ""
echo "Starting load tests with parameters:"
printf "%-12s %s\n" "Host:" "$HOST"
printf "%-12s %s\n" "User ID:" "$USER_ID"
printf "%-12s %s\n" "Event Type:" "$TYPE"
printf "%-12s %s\n" "Threads:" "$THREADS"
printf "%-12s %s\n" "Connections:" "$CONNECTIONS"
printf "%-12s %s\n" "Duration:" "$DURATION"

run_wrk_test() {
    local test_name="$1"
    local script_path="$2"
    local url="$3"

    echo ""
    echo "$test_name $url"

    local output
    output=$(wrk -t$THREADS -c$CONNECTIONS -d$DURATION -s "$script_path" "$url" 2>&1)

    echo "$output" | grep -A6 "Response times (ms):"
    local errors
    errors=$(echo "$output" | grep 'Non-2xx or 3xx responses' | awk '{print $5}')
    errors=${errors:-0}
    echo "Error responses: $errors"
}

run_wrk_test "Test 1: POST" "runtime/wrk_scripts/post_event.lua" "$HOST/events"
run_wrk_test "Test 2: GET" "runtime/wrk_scripts/done.lua" "$HOST/events?page=1&limit=1000"
run_wrk_test "Test 3: GET" "runtime/wrk_scripts/done.lua" "$HOST/users/$USER_ID/events"
run_wrk_test "Test 4: GET" "runtime/wrk_scripts/done.lua" "$HOST/stats?from=$MONTH_START&to=$MONTH_END&limit=10&type=$TYPE"

rm -f runtime/event_data.json
rm -rf runtime/wrk_scripts
