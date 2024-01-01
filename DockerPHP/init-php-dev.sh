#!/bin/bash
set -e

cd /var/www/html/
composer install -n

composer require --dev symfony/profiler-pack

# bin/console secrets:list --reveal
cat $APP_SECRET_FILE | bin/console secrets:set APP_SECRET - && cat $APP_SECRET_FILE | bin/console secrets:set APP_SECRET - --local
cat $DB_USER_FILE | bin/console secrets:set DATABASE_USER - && cat $DB_USER_FILE | bin/console secrets:set DATABASE_USER - --local
cat $DB_PASS_FILE | bin/console secrets:set DATABASE_PASS - && cat $DB_PASS_FILE | bin/console secrets:set DATABASE_PASS - --local
echo -n "$DATABASE_HOST" | bin/console secrets:set DATABASE_HOST - && echo -n "$DATABASE_HOST" | bin/console secrets:set DATABASE_HOST - --local
echo -n "$DATABASE_NAME" | bin/console secrets:set DATABASE_NAME - && echo -n "$DATABASE_NAME" | bin/console secrets:set DATABASE_NAME - --local
cat $MESSENGER_TRANSPORT_DSN_FILE | bin/console secrets:set MESSENGER_TRANSPORT_DSN - && cat $MESSENGER_TRANSPORT_DSN_FILE | bin/console secrets:set MESSENGER_TRANSPORT_DSN - --local

APP_RUNTIME_ENV=$APP_ENV bin/console secrets:decrypt-to-local --force

bin/console doctrine:migrations:migrate

(service supervisor stop || true) && service supervisor start

php-fpm
