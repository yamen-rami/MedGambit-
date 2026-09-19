#!/usr/bin/env bash

set -Eeuo pipefail

APP_DIR="/var/www/medgambit"
REPOSITORY="https://github.com/yamen-rami/MedGambit-.git"
BRANCH="production"
DOMAIN="medgambit.com"
DB_NAME="medgambit"
DB_USER="medgambit"

export DEBIAN_FRONTEND=noninteractive

systemctl enable --now nginx php8.4-fpm mariadb redis-server supervisor

if [[ ! -d "${APP_DIR}/.git" ]]; then
    mkdir -p "$(dirname "${APP_DIR}")"
    git clone --branch "${BRANCH}" --single-branch "${REPOSITORY}" "${APP_DIR}"
else
    git -C "${APP_DIR}" fetch origin "${BRANCH}"
    git -C "${APP_DIR}" checkout "${BRANCH}"
    git -C "${APP_DIR}" pull --ff-only origin "${BRANCH}"
fi

cd "${APP_DIR}"

if [[ ! -f .env ]]; then
    DB_PASSWORD="$(openssl rand -hex 24)"
    APP_KEY="base64:$(openssl rand -base64 32)"
    REVERB_APP_ID="$(openssl rand -hex 8)"
    REVERB_APP_KEY="$(openssl rand -hex 16)"
    REVERB_APP_SECRET="$(openssl rand -hex 32)"

    install -m 640 -o root -g www-data /dev/null .env
    printf '%s\n' \
        'APP_NAME=MedGambit' \
        'APP_ENV=production' \
        "APP_KEY=${APP_KEY}" \
        'APP_DEBUG=false' \
        "APP_URL=https://${DOMAIN}" \
        '' \
        'APP_LOCALE=en' \
        'APP_FALLBACK_LOCALE=en' \
        'APP_FAKER_LOCALE=en_US' \
        'APP_MAINTENANCE_DRIVER=file' \
        'BCRYPT_ROUNDS=12' \
        '' \
        'LOG_CHANNEL=stack' \
        'LOG_STACK=single' \
        'LOG_LEVEL=error' \
        '' \
        'DB_CONNECTION=mysql' \
        'DB_HOST=127.0.0.1' \
        'DB_PORT=3306' \
        "DB_DATABASE=${DB_NAME}" \
        "DB_USERNAME=${DB_USER}" \
        "DB_PASSWORD=${DB_PASSWORD}" \
        '' \
        'SESSION_DRIVER=database' \
        'SESSION_LIFETIME=120' \
        'SESSION_ENCRYPT=true' \
        'SESSION_PATH=/' \
        'SESSION_DOMAIN=.medgambit.com' \
        'SESSION_SECURE_COOKIE=true' \
        'SESSION_SAME_SITE=lax' \
        '' \
        'BROADCAST_CONNECTION=reverb' \
        'FILESYSTEM_DISK=local' \
        'QUEUE_CONNECTION=redis' \
        'CACHE_STORE=redis' \
        '' \
        'REDIS_CLIENT=phpredis' \
        'REDIS_HOST=127.0.0.1' \
        'REDIS_PASSWORD=null' \
        'REDIS_PORT=6379' \
        '' \
        'MAIL_MAILER=log' \
        'MAIL_FROM_ADDRESS=noreply@medgambit.com' \
        'MAIL_FROM_NAME=${APP_NAME}' \
        '' \
        "REVERB_APP_ID=${REVERB_APP_ID}" \
        "REVERB_APP_KEY=${REVERB_APP_KEY}" \
        "REVERB_APP_SECRET=${REVERB_APP_SECRET}" \
        "REVERB_HOST=${DOMAIN}" \
        'REVERB_PORT=443' \
        'REVERB_SCHEME=https' \
        'REVERB_SERVER_HOST=127.0.0.1' \
        'REVERB_SERVER_PORT=8080' \
        '' \
        'VITE_APP_NAME=${APP_NAME}' \
        "VITE_REVERB_APP_KEY=${REVERB_APP_KEY}" \
        "VITE_REVERB_HOST=${DOMAIN}" \
        'VITE_REVERB_PORT=443' \
        'VITE_REVERB_SCHEME=https' > .env
fi

DB_PASSWORD="$(sed -n 's/^DB_PASSWORD=//p' .env | head -n 1)"
mysql --protocol=socket <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'127.0.0.1' IDENTIFIED BY '${DB_PASSWORD}';
ALTER USER '${DB_USER}'@'127.0.0.1' IDENTIFIED BY '${DB_PASSWORD}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'127.0.0.1';
FLUSH PRIVILEGES;
SQL

composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

docker run --rm \
    --volume "${APP_DIR}:/app" \
    --workdir /app \
    node:22-bookworm \
    sh -lc 'npm ci && npm run build'

mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

sudo -u www-data php artisan migrate --force
php artisan storage:link || true
sudo -u www-data php artisan optimize:clear
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan event:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

install -m 644 /dev/null /etc/nginx/sites-available/medgambit
cat > /etc/nginx/sites-available/medgambit <<'NGINX'
server {
    listen 80 default_server;
    listen [::]:80 default_server;
    server_name medgambit.com www.medgambit.com 152.239.115.13;

    root /var/www/medgambit/public;
    index index.php;
    charset utf-8;
    client_max_body_size 20M;

    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ ^/index\.php(/|$) {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ \.php$ {
        return 404;
    }

    location /app {
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_read_timeout 60s;
        proxy_pass http://127.0.0.1:8080;
    }

    location /apps {
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_pass http://127.0.0.1:8080;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
NGINX

rm -f /etc/nginx/sites-enabled/default
ln -sfn /etc/nginx/sites-available/medgambit /etc/nginx/sites-enabled/medgambit
nginx -t
systemctl reload nginx

cat > /etc/supervisor/conf.d/medgambit.conf <<'SUPERVISOR'
[program:medgambit-worker]
process_name=%(program_name)s_%(process_num)02d
command=/usr/bin/php /var/www/medgambit/artisan queue:work redis --sleep=3 --tries=3 --timeout=90 --max-time=3600
directory=/var/www/medgambit
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/supervisor/medgambit-worker.log
stopwaitsecs=3600

[program:medgambit-reverb]
command=/usr/bin/php /var/www/medgambit/artisan reverb:start --host=127.0.0.1 --port=8080
directory=/var/www/medgambit
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/supervisor/medgambit-reverb.log
stopwaitsecs=30
SUPERVISOR

supervisorctl reread
supervisorctl update
supervisorctl restart medgambit-worker:*
supervisorctl restart medgambit-reverb

cat > /etc/cron.d/medgambit <<'CRON'
* * * * * www-data cd /var/www/medgambit && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
CRON
chmod 644 /etc/cron.d/medgambit

ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw --force enable

systemctl is-active nginx php8.4-fpm mariadb redis-server supervisor
supervisorctl status
curl --fail --silent --show-error --header "Host: ${DOMAIN}" http://127.0.0.1/ > /dev/null

echo "MedGambit provisioned successfully."
