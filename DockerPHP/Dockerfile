FROM composer:lts as dev-deps
WORKDIR /app
RUN --mount=type=bind,source=app/composer.json,target=composer.json \
    --mount=type=bind,source=app/composer.lock,target=composer.lock \
    --mount=type=cache,target=/tmp/cache \
    composer install --no-interaction --ignore-platform-req=ext-amqp --no-scripts



FROM composer:lts as prod-deps
WORKDIR /app
RUN --mount=type=bind,source=app/composer.json,target=composer.json \
    --mount=type=bind,source=app/composer.lock,target=composer.lock \
    --mount=type=cache,target=/tmp/cache \
    composer install --no-dev --no-interaction --optimize-autoloader --ignore-platform-req=ext-amqp --no-scripts



FROM php:8.2.15-fpm-alpine3.19 as base-build

# Use the default production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN apk update && apk add --no-cache bash git supervisor
RUN apk add --no-cache libzip-dev zip && docker-php-ext-install zip

RUN apk add --no-cache ${PHPIZE_DEPS} libpq-dev icu-dev rabbitmq-c-dev libpng-dev freetype-dev \
    && pecl install redis-5.3.7 amqp-2.1.1 \
    && docker-php-ext-configure gd --with-freetype \
    && docker-php-ext-install gd bcmath pdo pdo_mysql intl \
    && docker-php-ext-enable gd bcmath pdo_mysql redis amqp sodium opcache \
    && apk del ${PHPIZE_DEPS}

COPY DockerPHP/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY DockerPHP/sf_messenger.conf /etc/supervisor/conf.d/sf_messenger.conf
RUN mkdir -p /var/log/supervisord/
RUN chown -R www-data:www-data /var/log/supervisord

COPY DockerPHP/conf/php.ini "$PHP_INI_DIR/conf.d/custom-php.ini"

COPY DockerPHP/scripts /var/www/scripts
RUN chmod 500 /var/www/scripts/*
RUN chown -R www-data:www-data /var/www


FROM base-build as dev-build

RUN apk add --no-cache nodejs npm && npm remove ip

COPY app /var/www/html
COPY --from=dev-deps app/vendor/ /var/www/html/vendor
RUN chown -R www-data:www-data /var/www

USER www-data
WORKDIR /var/www/html

RUN bin/console assets:install --symlink public
RUN npm install && npm audit && npm run build

VOLUME /var/www/html

ENTRYPOINT [ "/bin/bash" ]
CMD [ "/var/www/scripts/init-php-dev.sh" ]



FROM base-build as prod-build

COPY app /var/www/html
COPY --from=prod-deps app/vendor/ /var/www/html/vendor
COPY --from=dev-build --chown=www-data:www-data /var/www/html/public/build /var/www/html/public/build
RUN chown -R www-data:www-data /var/www

USER www-data

RUN bin/console assets:install --symlink public

VOLUME /var/www/html

ENTRYPOINT [ "/bin/bash" ]
CMD [ "/var/www/scripts/init-php.sh" ]



FROM prod-build as test-build

ENTRYPOINT [ "/bin/bash" ]
CMD [ "/var/www/scripts/init-php-test.sh" ]
