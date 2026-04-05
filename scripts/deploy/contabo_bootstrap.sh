#!/usr/bin/env bash
set -euo pipefail

export DEBIAN_FRONTEND=noninteractive

apt-get update
apt-get install -y software-properties-common ca-certificates curl gnupg lsb-release unzip git ufw fail2ban

add-apt-repository -y ppa:ondrej/php
apt-get update
apt-get install -y \
  nginx \
  mysql-server \
  redis-server \
  supervisor \
  certbot python3-certbot-nginx \
  php8.3 php8.3-fpm php8.3-cli php8.3-mysql php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip php8.3-bcmath php8.3-intl php8.3-gd php8.3-redis

if ! command -v composer >/dev/null 2>&1; then
  curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
  php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer
fi

systemctl enable nginx
systemctl enable php8.3-fpm
systemctl enable mysql
systemctl enable redis-server
systemctl enable supervisor

systemctl restart nginx
systemctl restart php8.3-fpm
systemctl restart mysql
systemctl restart redis-server
systemctl restart supervisor

ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw --force enable

mkdir -p /opt/scripts
chmod 755 /opt/scripts

echo "Bootstrap completed. Next: deploy app into /var/www/pearlhub-api and run scripts/deploy/deploy_laravel.sh"
