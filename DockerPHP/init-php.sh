#!/bin/bash
set -e

cd /var/www/html/
composer install -n

# bin/console secrets:list --reveal
cat $APP_SECRET_FILE | bin/console secrets:set APP_SECRET - && cat $APP_SECRET_FILE | bin/console secrets:set APP_SECRET - --local
cat $DB_USER_FILE | bin/console secrets:set DATABASE_USER - && cat $DB_USER_FILE | bin/console secrets:set DATABASE_USER - --local
cat $DB_PASS_FILE | bin/console secrets:set DATABASE_PASS - && cat $DB_PASS_FILE | bin/console secrets:set DATABASE_PASS - --local

php-fpm
