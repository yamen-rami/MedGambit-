#!/usr/bin/env bash

set -u

APP_DIR="/var/www/medgambit"
DOMAIN="medgambit.com"

echo "HTTP root"
curl --silent --show-error --output /dev/null --write-out '%{http_code} %{content_type}\n' \
    --header "Host: ${DOMAIN}" http://127.0.0.1/

echo "Health endpoint"
curl --silent --show-error --output /dev/null --write-out '%{http_code}\n' \
    --header "Host: ${DOMAIN}" http://127.0.0.1/up

echo "Built asset"
ASSET="$(php -r '$m=json_decode(file_get_contents("/var/www/medgambit/public/build/manifest.json"), true); echo $m["resources/js/app.js"]["file"];')"
curl --silent --show-error --output /dev/null --write-out '%{http_code}\n' \
    --header "Host: ${DOMAIN}" "http://127.0.0.1/build/${ASSET}"

echo "Redis"
redis-cli ping

echo "Database tables"
mysql --batch --skip-column-names --execute="SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='medgambit';"

echo "Application processes"
supervisorctl status
systemctl is-active nginx php8.4-fpm mariadb redis-server supervisor

echo "Firewall"
ufw status

echo "DNS"
getent ahostsv4 "${DOMAIN}" || true
getent ahostsv4 "www.${DOMAIN}" || true

echo "Recent errors"
tail -n 20 "${APP_DIR}/storage/logs/laravel.log" 2>/dev/null || true
tail -n 10 /var/log/nginx/error.log 2>/dev/null || true
tail -n 10 /var/log/supervisor/medgambit-reverb.log 2>/dev/null || true

echo "Revision"
git -C "${APP_DIR}" log -1 --oneline
