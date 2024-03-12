#!/bin/bash
set -e

cd /var/www/html/
bin/console doctrine:migrations:migrate

/usr/bin/supervisord -c /etc/supervisor/conf.d/sf_messenger.conf

docker-php-entrypoint php-fpm &

bin/phpunit
