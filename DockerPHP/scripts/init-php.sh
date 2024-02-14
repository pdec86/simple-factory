#!/bin/bash
set -e

cd /var/www/html/
composer install -n

# bin/console secrets:list --reveal
cat $APP_SECRET_FILE | bin/console secrets:set APP_SECRET -
cat $DB_USER_FILE | bin/console secrets:set DATABASE_USER -
cat $DB_PASS_FILE | bin/console secrets:set DATABASE_PASS -
cat $MESSENGER_TRANSPORT_DSN_FILE | bin/console secrets:set MESSENGER_TRANSPORT_DSN -
# bin/console secrets:decrypt-to-local --force

bin/console doctrine:migrations:migrate

/usr/bin/supervisord -c /etc/supervisor/conf.d/sf_messenger.conf

docker-php-entrypoint php-fpm
