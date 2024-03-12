#!/bin/bash
set -e

cd /var/www/html/
composer install --no-dev --no-interaction --optimize-autoloader && composer audit

APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear
APP_ENV=prod APP_DEBUG=0 php bin/console cache:warmup

bin/console doctrine:migrations:migrate

/usr/bin/supervisord -c /etc/supervisor/conf.d/sf_messenger.conf

docker-php-entrypoint php-fpm
