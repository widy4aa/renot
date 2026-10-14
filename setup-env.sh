#!/usr/bin/env bash

# ==============================================================================
# ReNot - Interactive Environment Setup Wizard (TUI)
# ==============================================================================

set -eo pipefail

# ANSI Color Codes & Styles
BOLD='\033[1m'
DIM='\033[2m'
RED='\033[38;2;237;27;47m'       # Pertamina Red #ED1B2F
BLUE='\033[38;2;0;108;184m'      # Pertamina Blue #006CB8
GREEN='\033[38;2;172;196;42m'    # Pertamina Lime #ACC42A
CYAN='\033[36m'
YELLOW='\033[33m'
GRAY='\033[90m'
NC='\033[0m'                     # Reset

# Root and project dirs
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="${SCRIPT_DIR}/laravel-vue-template"

if [ ! -d "$APP_DIR" ]; then
    echo -e "${RED}Error:${NC} Direktori laravel-vue-template tidak ditemukan di ${SCRIPT_DIR}"
    exit 1
fi

# Helper functions
generate_app_key() {
    if command -v php >/dev/null 2>&1; then
        php -r 'echo "base64:" . base64_encode(random_bytes(32));'
    elif command -v openssl >/dev/null 2>&1; then
        echo "base64:$(openssl rand -base64 32)"
    else
        head -c 32 /dev/urandom | base64 | sed 's/^/base64:/'
    fi
}

generate_random_pass() {
    local length="${1:-24}"
    if command -v openssl >/dev/null 2>&1; then
        openssl rand -hex "$((length / 2))"
    else
        tr -dc 'a-zA-Z0-9' < /dev/urandom | head -c "$length"
    fi
}

print_header() {
    clear
    echo -e "${RED}${BOLD}  ██████╗ ███████╗███╗   ██╗ ██████╗ ████████╗${NC}"
    echo -e "${RED}${BOLD}  ██╔══██╗██╔════╝████╗  ██║██╔═══██╗╚══██╔══╝${NC}"
    echo -e "${RED}${BOLD}  ██████╔╝█████╗  ██╔██╗ ██║██║   ██║   ██║   ${NC}"
    echo -e "${BLUE}${BOLD}  ██╔══██╗██╔══╝  ██║╚██╗██║██║   ██║   ██║   ${NC}"
    echo -e "${BLUE}${BOLD}  ██║  ██║███████╗██║ ╚████║╚██████╔╝   ██║   ${NC}"
    echo -e "${GRAY}  Reminder & Notification - PT Pertamina (Persero)${NC}"
    echo -e "${GRAY}─────────────────────────────────────────────────────────────${NC}\n"
}

draw_box() {
    local title="$1"
    shift
    local lines=("$@")
    local max_len=${#title}
    for line in "${lines[@]}"; do
        local clean_line
        clean_line=$(echo -e "$line" | sed 's/\x1b\[[0-9;]*m//g')
        local cur_len=${#clean_line}
        if [ "$cur_len" -gt "$max_len" ]; then
            max_len=$cur_len
        fi
    done
    local width=$((max_len + 4))

    echo -e "${BLUE}┌─ ${BOLD}${title}${NC}${BLUE} $(printf '─%.0s' $(seq 1 $((width - ${#title} - 5))))┐${NC}"
    for line in "${lines[@]}"; do
        local clean_line
        clean_line=$(echo -e "$line" | sed 's/\x1b\[[0-9;]*m//g')
        local pad=$((width - ${#clean_line} - 2))
        echo -e "${BLUE}│${NC} ${line}$(printf ' %.0s' $(seq 1 $pad))${BLUE}│${NC}"
    done
    echo -e "${BLUE}└$(printf '─%.0s' $(seq 1 $((width - 2))))┘${NC}\n"
}

prompt_input() {
    local prompt_text="$1"
    local default_val="$2"
    local is_secret="${3:-false}"
    local user_val=""

    if [ -n "$default_val" ]; then
        echo -ne "  ${BOLD}${prompt_text}${NC} ${GRAY}[Default: ${default_val}]${NC}: " >&2
    else
        echo -ne "  ${BOLD}${prompt_text}${NC}: " >&2
    fi

    if [ "$is_secret" = "true" ]; then
        read -rs user_val
        echo "" >&2
    else
        read -r user_val
    fi

    if [ -z "$user_val" ]; then
        echo "$default_val"
    else
        echo "$user_val"
    fi
}

prompt_choice() {
    local prompt_text="$1"
    shift
    local options=("$@")
    
    echo -e "  ${BOLD}${prompt_text}${NC}" >&2
    for i in "${!options[@]}"; do
        echo -e "    ${CYAN}$((i+1)))${NC} ${options[$i]}" >&2
    done
    
    local choice=""
    while true; do
        echo -ne "  Pilih [1-${#options[@]}]: " >&2
        read -r choice
        if [[ "$choice" =~ ^[0-9]+$ ]] && [ "$choice" -ge 1 ] && [ "$choice" -le "${#options[@]}" ]; then
            echo "$((choice - 1))"
            break
        fi
        echo -e "  ${RED}Pilihan tidak valid. Silakan coba lagi.${NC}" >&2
    done
}

# --- Main Wizard Flow ---

print_header

draw_box "Interactive Environment Configurator" \
    "Wizard ini akan mengonfigurasi variabel environment (.env)" \
    "Bisa otomatis men-generate App Key & DB credentials yang aman," \
    "atau meminta input manual untuk konfigurasi custom."

echo -e "${BOLD}Target Environment:${NC}"
ENV_TYPE_IDX=$(prompt_choice "Pilih mode konfigurasi:" \
    "Docker / Production (.env.docker)" \
    "Local Development (.env)")
echo ""

if [ "$ENV_TYPE_IDX" -eq 0 ]; then
    ENV_TARGET="${APP_DIR}/.env.docker"
    DEFAULT_APP_ENV="production"
    DEFAULT_APP_DEBUG="false"
    DEFAULT_APP_URL="http://127.0.0.1:8000"
    DEFAULT_DB_HOST="db"
    DEFAULT_DB_USER="renot_user"
    DEFAULT_SESSION_ENCRYPT="true"
    DEFAULT_QUEUE_CONN="sync"
    DEFAULT_LOG_LEVEL="error"
    DEFAULT_LOG_CHANNEL="stderr"
    TARGET_NAME="Production (Docker)"
else
    ENV_TARGET="${APP_DIR}/.env"
    DEFAULT_APP_ENV="local"
    DEFAULT_APP_DEBUG="true"
    DEFAULT_APP_URL="http://localhost:8000"
    DEFAULT_DB_HOST="127.0.0.1"
    DEFAULT_DB_USER="root"
    DEFAULT_SESSION_ENCRYPT="false"
    DEFAULT_QUEUE_CONN="database"
    DEFAULT_LOG_LEVEL="debug"
    DEFAULT_LOG_CHANNEL="stack"
    TARGET_NAME="Local Development"
fi

# 1. Application Settings
echo -e "\n${BOLD}${BLUE}==> [1/4] Application Settings${NC}"
APP_NAME=$(prompt_input "Nama Aplikasi" "ReNot")
APP_ENV=$(prompt_input "APP_ENV" "$DEFAULT_APP_ENV")
APP_DEBUG=$(prompt_input "APP_DEBUG (true/false)" "$DEFAULT_APP_DEBUG")
APP_URL=$(prompt_input "APP_URL" "$DEFAULT_APP_URL")

echo -e "\n${BOLD}${BLUE}==> [2/4] Application Encryption Key (APP_KEY)${NC}"
KEY_CHOICE=$(prompt_choice "Opsi APP_KEY:" \
    "Generate otomatis key acak baru (Disarankan)" \
    "Masukkan APP_KEY sendiri secara manual" \
    "Gunakan key yang ada (jika sudah ada di file)")

if [ "$KEY_CHOICE" -eq 0 ]; then
    APP_KEY=$(generate_app_key)
    echo -e "  ${GREEN}✓ Generated:${NC} ${DIM}${APP_KEY}${NC}"
elif [ "$KEY_CHOICE" -eq 1 ]; then
    APP_KEY=$(prompt_input "Masukkan APP_KEY (format base64:...)" "")
else
    EXISTING_KEY=""
    if [ -f "$ENV_TARGET" ]; then
        EXISTING_KEY=$(grep -E "^APP_KEY=" "$ENV_TARGET" | cut -d '=' -f2- || true)
    fi
    if [ -z "$EXISTING_KEY" ]; then
        echo -e "  ${YELLOW}! Key lama tidak ditemukan, membuat key baru otomatis.${NC}"
        APP_KEY=$(generate_app_key)
    else
        APP_KEY="$EXISTING_KEY"
        echo -e "  ${GREEN}✓ Memakai key yang ada:${NC} ${DIM}${APP_KEY}${NC}"
    fi
fi

# 2. Database Settings
echo -e "\n${BOLD}${BLUE}==> [3/4] Database Configuration (PostgreSQL)${NC}"
DB_CONNECTION="pgsql"
DB_HOST=$(prompt_input "Database Host" "$DEFAULT_DB_HOST")
DB_PORT=$(prompt_input "Database Port" "5432")
DB_DATABASE=$(prompt_input "Database Name" "renot")
DB_USERNAME=$(prompt_input "Database Username" "$DEFAULT_DB_USER")

DB_PASS_CHOICE=$(prompt_choice "Password Database:" \
    "Generate otomatis password acak yang kuat (32 karakter)" \
    "Input password manual")

if [ "$DB_PASS_CHOICE" -eq 0 ]; then
    DB_PASSWORD=$(generate_random_pass 32)
    echo -e "  ${GREEN}✓ Generated secure password:${NC} ${DIM}${DB_PASSWORD}${NC}"
else
    DB_PASSWORD=$(prompt_input "Password Database" "" "true")
fi

# 3. Mail / SMTP Configuration
echo -e "\n${BOLD}${BLUE}==> [4/4] Mail / SMTP Notification Settings${NC}"
MAIL_CHOICE=$(prompt_choice "Pilih driver email:" \
    "SMTP Server (Gmail / Corporate SMTP)" \
    "Log driver (Email hanya dicatat di storage/logs, aman untuk tes)")

if [ "$MAIL_CHOICE" -eq 0 ]; then
    MAIL_MAILER="smtp"
    MAIL_HOST=$(prompt_input "SMTP Host" "smtp.gmail.com")
    MAIL_PORT=$(prompt_input "SMTP Port" "587")
    MAIL_SCHEME=$(prompt_input "SMTP Scheme (tls/null)" "null")
    MAIL_USERNAME=$(prompt_input "SMTP Username / Email" "")
    MAIL_PASSWORD=$(prompt_input "SMTP Password / App Password" "" "true")
    MAIL_FROM_ADDRESS=$(prompt_input "Mail From Address" "$MAIL_USERNAME")
    MAIL_FROM_NAME=$(prompt_input "Mail From Name" "ReNot - Pertamina")
else
    MAIL_MAILER="log"
    MAIL_HOST="127.0.0.1"
    MAIL_PORT="2525"
    MAIL_SCHEME="null"
    MAIL_USERNAME=""
    MAIL_PASSWORD=""
    MAIL_FROM_ADDRESS="noreply@pertamina.com"
    MAIL_FROM_NAME="ReNot - Pertamina"
fi

# Summary Confirmation
echo -e "\n"
draw_box "Konfirmasi Konfigurasi (${TARGET_NAME})" \
    "File target: ${ENV_TARGET}" \
    "APP_NAME:    ${APP_NAME}" \
    "APP_ENV:     ${APP_ENV}" \
    "APP_DEBUG:   ${APP_DEBUG}" \
    "APP_URL:     ${APP_URL}" \
    "DB_HOST:     ${DB_HOST}:${DB_PORT} (${DB_DATABASE})" \
    "DB_USER:     ${DB_USERNAME}" \
    "MAIL_MAILER: ${MAIL_MAILER} (${MAIL_HOST:-N/A})"

echo -ne "  ${BOLD}Terapkan konfigurasi ini sekarang? [y/N]:${NC} "
read -r CONFIRM
if [[ ! "$CONFIRM" =~ ^[Yy]$ ]]; then
    echo -e "\n${YELLOW}Setup dibatalkan. Tidak ada file yang diubah.${NC}"
    exit 0
fi

# Write file
cat > "$ENV_TARGET" <<EOF
# ─────────────────────────────────────────────
# Generated by ReNot TUI Setup Wizard
# Date: $(date)
# ─────────────────────────────────────────────

APP_NAME="${APP_NAME}"
APP_ENV=${APP_ENV}
APP_DEBUG=${APP_DEBUG}
APP_URL=${APP_URL}

APP_KEY=${APP_KEY}

# ── Database ──────────────────────────────────
DB_CONNECTION=${DB_CONNECTION}
DB_HOST=${DB_HOST}
DB_PORT=${DB_PORT}
DB_DATABASE=${DB_DATABASE}
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}

# ── Cache / Session / Queue ───────────────────
CACHE_DRIVER=file
SESSION_DRIVER=file
SESSION_ENCRYPT=${DEFAULT_SESSION_ENCRYPT}
QUEUE_CONNECTION=${DEFAULT_QUEUE_CONN}

# ── Logging ───────────────────────────────────
LOG_CHANNEL=${DEFAULT_LOG_CHANNEL}
LOG_LEVEL=${DEFAULT_LOG_LEVEL}

# ── Mail ──────────────────────────────────────
MAIL_MAILER=${MAIL_MAILER}
MAIL_HOST=${MAIL_HOST}
MAIL_PORT=${MAIL_PORT}
MAIL_SCHEME=${MAIL_SCHEME}
MAIL_USERNAME=${MAIL_USERNAME}
MAIL_PASSWORD="${MAIL_PASSWORD}"
MAIL_FROM_ADDRESS="${MAIL_FROM_ADDRESS}"
MAIL_FROM_NAME="${MAIL_FROM_NAME}"
EOF

chmod 600 "$ENV_TARGET"

echo -e "\n${GREEN}${BOLD}✓ Konfigurasi berhasil disimpan ke:${NC} ${ENV_TARGET}"
echo -e "  File permission diatur ke 600 (read/write owner only).\n"

if [ "$ENV_TYPE_IDX" -eq 0 ]; then
    echo -e "${CYAN}${BOLD}Langkah deployment selanjutnya:${NC}"
    echo -e "  1. Jalankan container: ${BOLD}podman-compose up -d --build${NC}"
    echo -e "  2. Migrasi DB:         ${BOLD}podman-compose exec app php artisan migrate --force${NC}"
    echo -e "  3. Seed (pertama kali):${BOLD}podman-compose exec app php artisan db:seed --force${NC}\n"
else
    echo -e "${CYAN}${BOLD}Langkah local run selanjutnya:${NC}"
    echo -e "  1. Jalankan migrasi:   ${BOLD}php artisan migrate${NC}"
    echo -e "  2. Build assets:       ${BOLD}npm run build${NC}"
    echo -e "  3. Start dev server:   ${BOLD}php artisan serve${NC}\n"
fi
