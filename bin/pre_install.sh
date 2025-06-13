#!/bin/bash

# Скрипт установки основных инструментов для Debian 12
# Автор: Установочный скрипт
# Дата: $(date +%Y-%m-%d)

set -e  # Выход при ошибке

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Функция для вывода цветных сообщений
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Проверка прав root
check_root() {
    if [[ $EUID -ne 0 ]]; then
        print_error "Этот скрипт должен быть запущен с правами root (sudo)"
        exit 1
    fi
}

# Обновление системы
update_system() {
    print_status "Обновление списка пакетов..."
    apt update

    print_status "Обновление системы..."
    apt upgrade -y

    print_success "Система обновлена"
}

# Установка базовых инструментов
install_basic_tools() {
    print_status "Установка базовых инструментов..."

    local packages=(
        "wrk"
        "curl"
        "git"
        "wget"
        "nano"
        "mc"
        "htop"
        "vim"
        "tree"
        "unzip"
        "zip"
        "net-tools"
        "software-properties-common"
        "apt-transport-https"
        "ca-certificates"
        "gnupg"
        "lsb-release"
        "build-essential"
        "sudo"
    )

    for package in "${packages[@]}"; do
        print_status "Установка $package..."
        apt install -y "$package"
    done

    print_success "Базовые инструменты установлены"
}

# Установка Docker
install_docker() {
    print_status "Установка Docker..."

    # Удаление старых версий
    apt remove -y docker docker-engine docker.io containerd runc 2>/dev/null || true

    # Добавление GPG ключа Docker
    curl -fsSL https://download.docker.com/linux/debian/gpg | gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg

    # Добавление репозитория Docker
    echo "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/debian $(lsb_release -cs) stable" | tee /etc/apt/sources.list.d/docker.list > /dev/null

    # Обновление списка пакетов
    apt update

    # Установка Docker Engine
    apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin

    # Запуск и включение автозапуска Docker
    systemctl start docker
    systemctl enable docker

    # Добавление текущего пользователя в группу docker (если скрипт запущен через sudo)
    if [[ -n "$SUDO_USER" ]]; then
        usermod -aG docker "$SUDO_USER"
        print_warning "Пользователь $SUDO_USER добавлен в группу docker. Необходимо перелогиниться."
    fi

    print_success "Docker установлен"
}

# Установка Docker Compose
install_docker_compose() {
    print_status "Установка Docker Compose..."

    # Установка через официальный плагин (рекомендуется)
    apt install -y docker-compose-plugin

    print_success "Docker Compose установлен"
}

# Проверка установки
verify_installation() {
    print_status "Проверка установленных пакетов..."

    local commands=(
        "docker --version"
        "docker compose version"
        "curl --version"
        "git --version"
        "wget --version"
        "nano --version"
        "mc --version"
        "htop --version"
    )

    for cmd in "${commands[@]}"; do
        if $cmd &>/dev/null; then
            print_success "$cmd - OK"
        else
            print_error "$cmd - FAILED"
        fi
    done
}

# Дополнительные настройки
additional_setup() {
    print_status "Выполнение дополнительных настроек..."

    # Настройка Git (если нужно)
    if [[ -n "$SUDO_USER" ]]; then
        print_status "Для настройки Git выполните следующие команды от имени пользователя:"
        echo "git config --global user.name \"Ваше Имя\""
        echo "git config --global user.email \"your.email@example.com\""
    fi

    # Создание полезных алиасов
    if [[ -n "$SUDO_USER" ]]; then
        local user_home="/home/$SUDO_USER"
        if [[ ! -f "$user_home/.bash_aliases" ]]; then
            cat > "$user_home/.bash_aliases" << 'EOF'
# Docker алиасы
alias d='docker'
alias dc='docker compose'
alias dps='docker ps'
alias di='docker images'

# Системные алиасы
alias ll='ls -alF'
alias la='ls -A'
alias l='ls -CF'
alias ..='cd ..'
alias ...='cd ../..'

# Git алиасы
alias gs='git status'
alias ga='git add'
alias gc='git commit'
alias gp='git push'
alias gl='git log --oneline'
EOF
            chown "$SUDO_USER:$SUDO_USER" "$user_home/.bash_aliases"
            print_success "Добавлены полезные алиасы в ~/.bash_aliases"
        fi
    fi
}

# Основная функция
main() {
    echo "=================================================="
    echo "  Скрипт установки инструментов для Debian 12    "
    echo "=================================================="
    echo

    check_root
    update_system
    install_basic_tools
    install_docker
    install_docker_compose
    additional_setup
    verify_installation

    echo
    echo "=================================================="
    print_success "Установка завершена успешно!"
    echo "=================================================="
    echo
    print_warning "ВАЖНО: Если вы планируете использовать Docker без sudo,"
    print_warning "необходимо перелогиниться или выполнить: newgrp docker"
    echo
    print_status "Установленные инструменты:"
    echo "  - Docker & Docker Compose"
    echo "  - curl, wget"
    echo "  - git, nano, vim"
    echo "  - mc (Midnight Commander)"
    echo "  - htop, tree"
    echo "  - Дополнительные утилиты для разработки"
    echo
}

# Запуск основной функции
main "$@"
