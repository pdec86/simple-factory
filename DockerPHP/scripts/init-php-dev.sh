#!/bin/bash
set -e

cd /var/www/html/
composer install -n

bin/console secrets:list --reveal
cat $APP_SECRET_FILE | bin/console secrets:set APP_SECRET - && cat $APP_SECRET_FILE | bin/console secrets:set APP_SECRET - --local
cat $DB_USER_FILE | bin/console secrets:set DATABASE_USER - && cat $DB_USER_FILE | bin/console secrets:set DATABASE_USER - --local
cat $DB_PASS_FILE | bin/console secrets:set DATABASE_PASS - && cat $DB_PASS_FILE | bin/console secrets:set DATABASE_PASS - --local
cat $MESSENGER_TRANSPORT_DSN_FILE | bin/console secrets:set MESSENGER_TRANSPORT_DSN - && cat $MESSENGER_TRANSPORT_DSN_FILE | bin/console secrets:set MESSENGER_TRANSPORT_DSN - --local
# APP_RUNTIME_ENV=$APP_ENV bin/console secrets:decrypt-to-local --force

bin/console doctrine:migrations:migrate

/usr/bin/supervisord -c /etc/supervisor/conf.d/sf_messenger.conf

docker-php-entrypoint php-fpm
