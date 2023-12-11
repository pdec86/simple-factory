#!/bin/bash
set -e

cd /var/www/html/
composer install -n

# bin/console secrets:list --reveal
cat $APP_SECRET_FILE | bin/console secrets:set APP_SECRET - && cat $APP_SECRET_FILE | bin/console secrets:set APP_SECRET - --local

bin/phpunit
