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

# Install Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php -r "if (hash_file('sha384', 'composer-setup.php') === 'edb40769019ccf227279e3bdd1f5b2e9950eb000c3233ee85148944e555d97be3ea4f40c3c2fe73b22f875385f6a5155') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php');" \
    && mv composer.phar /usr/local/bin/composer

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
RUN chown -R www-data:www-data /var/www

USER www-data
WORKDIR /var/www/html

RUN composer install -n && composer audit
RUN bin/console assets:install --symlink public
RUN npm install && npm audit && npm run build

VOLUME /var/www/html

ENTRYPOINT [ "/bin/bash" ]
CMD [ "/var/www/scripts/init-php-dev.sh" ]



FROM base-build as prod-build

COPY app /var/www/html
COPY --from=dev-build --chown=www-data:www-data /var/www/html/public/build /var/www/html/public/build
RUN chown -R www-data:www-data /var/www

VOLUME /var/www/html
USER www-data

ENTRYPOINT [ "/bin/bash" ]
CMD [ "/var/www/scripts/init-php.sh" ]



FROM prod-build as test-build

ENTRYPOINT [ "/bin/bash" ]
CMD [ "/var/www/scripts/init-php-test.sh" ]
