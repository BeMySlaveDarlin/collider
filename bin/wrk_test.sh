#!/bin/bash

if [ -z "$1" ]; then
    echo "Usage: $0 <host> <user_id> <event_type>"
    echo "Example: $0 http://localhost 123 user.registered"
    exit 1
fi

HOST="$1"
USER_ID="${2:-123}"
TYPE="${3:-click}"
THREADS=4
CONNECTIONS=50
DURATION="10s"

# Создаем директорию для скриптов
mkdir -p runtime/wrk_scripts

# Создаем JSON данные для POST запроса
cat > runtime/event_data.json << EOF
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

echo "🚀 Запуск wrk тестов"
echo "Host: $HOST"
echo "Threads: $THREADS"
echo "Connections: $CONNECTIONS"
echo "Duration: $DURATION"
echo "User ID: $USER_ID"
echo "Event Type: $TYPE"
echo "=================================="

# Lua скрипт для POST запросов
cat > runtime/wrk_scripts/post_event.lua << 'EOF'
-- POST запрос для создания события
wrk.method = "POST"
wrk.headers["Content-Type"] = "application/json"

-- Читаем JSON данные из файла
local file = io.open("runtime/event_data.json", "r")
if file then
    wrk.body = file:read("*all")
    file:close()
else
    wrk.body = '{"error": "could not read runtime/event_data.json"}'
end

-- Функция для обработки ответа
function response(status, headers, body)
    if status ~= 200 and status ~= 201 then
        print("Error response: " .. status)
    end
end
EOF

# Lua скрипт для динамических GET запросов
cat > runtime/wrk_scripts/get_user_events.lua << 'EOF'
-- GET запрос для получения событий пользователя
local user_id = "USER_ID_PLACEHOLDER"

-- Массив различных параметров для разнообразия
local params = {
    "?limit=10",
    "?limit=50",
    "?limit=100",
    "?page=1&limit=20",
    "?page=2&limit=20"
}

local counter = 0

function request()
    counter = counter + 1
    local param = params[(counter % #params) + 1]
    local path = "/users/" .. user_id .. "/events" .. param
    return wrk.format("GET", path)
end
EOF

# Заменяем плейсхолдер на реальное значение
sed -i "s/USER_ID_PLACEHOLDER/$USER_ID/g" runtime/wrk_scripts/get_user_events.lua

# Lua скрипт для статистики
cat > runtime/wrk_scripts/get_stats.lua << 'EOF'
-- GET запрос для получения статистики
local event_type = "EVENT_TYPE_PLACEHOLDER"

local params = {
    "?from=2025-06-01%2000:00:00&to=2025-07-30%2000:00:00&limit=10&type=" .. event_type,
    "?from=2025-06-20%2000:00:00&to=2025-07-30%2000:00:00&limit=50&type=" .. event_type,
    "?from=2025-06-01%2000:00:00&to=2025-06-30%2000:00:00&limit=100&type=" .. event_type
}

local counter = 0

function request()
    counter = counter + 1
    local param = params[(counter % #params) + 1]
    local path = "/stats" .. param
    return wrk.format("GET", path)
end
EOF

# Заменяем плейсхолдер на реальное значение и экранируем спецсимволы
escaped_type=$(echo "$TYPE" | sed 's/[[\.*^$()+?{|]/\\&/g')
sed -i "s/EVENT_TYPE_PLACEHOLDER/$escaped_type/g" runtime/wrk_scripts/get_stats.lua

# Функция для красивого вывода результатов
print_results() {
    local test_name="$1"
    echo ""
    echo "📊 Results: $test_name"
    echo "=================================="
}

# Тест 1: POST /events
print_results "Create Event (POST /events)"
wrk -t$THREADS -c$CONNECTIONS -d$DURATION \
    -s runtime/wrk_scripts/post_event.lua \
    --latency \
    "$HOST/events"

# Тест 2: GET /events
print_results "Events List (GET /events)"
wrk -t$THREADS -c$CONNECTIONS -d$DURATION \
    --latency \
    "$HOST/events?page=1&limit=1000"

# Тест 3: GET /users/{uid}/events
print_results "User Events (GET /users/{uid}/events)"
wrk -t$THREADS -c$CONNECTIONS -d$DURATION \
    -s runtime/wrk_scripts/get_user_events.lua \
    --latency \
    "$HOST"

# Тест 4: GET /stats
print_results "Stats (GET /stats)"
wrk -t$THREADS -c$CONNECTIONS -d$DURATION \
    -s runtime/wrk_scripts/get_stats.lua \
    --latency \
    "$HOST"

echo ""
echo "✅ Все тесты завершены!"
echo ""
echo "📈 Что означают результаты:"
echo "  • Requests/sec - запросов в секунду"
echo "  • Transfer/sec - объем данных в секунду"
echo "  • Latency - задержка (50%, 75%, 90%, 99%)"
echo "  • Req/Sec - распределение RPS по потокам"
echo ""

# Очищаем временные файлы
rm -f runtime/event_data.json
rm -rf runtime/wrk_scripts
