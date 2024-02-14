#!/bin/bash
set -e

cd /var/www/html/

# export MESSENGER_TRANSPORT_DSN="$(cat /run/secrets/amqp_dsn)"
# export APP_ENV="$(cat /run/secrets/app_env)"
# export $(grep -v '^#' .env.$APP_ENV.local | xargs)
whoami

bin/console messenger:consume manufacturing --queues=products_catalogue --time-limit=3600
