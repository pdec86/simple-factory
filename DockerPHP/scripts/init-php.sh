#!/bin/bash
set -e

cd /var/www/html/
composer install --no-dev --optimize-autoloader

# bin/console secrets:list --reveal
cat $APP_SECRET_FILE | bin/console secrets:set APP_SECRET - && cat $APP_SECRET_FILE | bin/console secrets:set APP_SECRET - --local
cat $DB_USER_FILE | bin/console secrets:set DATABASE_USER - && cat $DB_USER_FILE | bin/console secrets:set DATABASE_USER - --local
cat $DB_PASS_FILE | bin/console secrets:set DATABASE_PASS - && cat $DB_PASS_FILE | bin/console secrets:set DATABASE_PASS - --local
cat $MESSENGER_TRANSPORT_DSN_FILE | bin/console secrets:set MESSENGER_TRANSPORT_DSN - && cat $MESSENGER_TRANSPORT_DSN_FILE | bin/console secrets:set MESSENGER_TRANSPORT_DSN - --local
# bin/console secrets:decrypt-to-local --force

APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear
bin/console doctrine:migrations:migrate

/usr/bin/supervisord -c /etc/supervisor/conf.d/sf_messenger.conf

docker-php-entrypoint php-fpm
