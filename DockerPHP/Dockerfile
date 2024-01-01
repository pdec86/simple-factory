FROM php:8.1.25-fpm-bookworm

# Use the default production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN apt-get update && apt-get install -y git supervisor
RUN apt-get install -y libzip-dev zip && docker-php-ext-install zip

RUN apt-get install -y libpq-dev libicu-dev libpng-dev libfreetype-dev librabbitmq-dev \
    && pecl install ds redis-5.3.7 amqp-2.1.1 \
    && docker-php-ext-configure gd --with-freetype \
    && docker-php-ext-install gd bcmath pdo pdo_mysql intl \
    && docker-php-ext-enable ds gd bcmath pdo_mysql redis amqp sodium opcache

RUN apt-get update && \
 apt-get install -y ca-certificates curl gnupg && \
 mkdir -p /etc/apt/keyrings && \
 curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg

ARG NODE_MAJOR=20
RUN echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_MAJOR.x nodistro main" | tee /etc/apt/sources.list.d/nodesource.list
RUN apt-get update && apt-get install nodejs -y
RUN npm install --global yarn

# Install Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('sha384', 'composer-setup.php') === 'e21205b207c3ff031906575712edab6f13eb0b361f2085f1f1237b7126d785e826a450292b6cfd1d64d92e6563bbde02') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer

COPY DockerPHP/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY DockerPHP/symfony_messenger.conf /etc/supervisor/conf.d/symfony_messenger.conf

VOLUME /var/www/html
COPY app /var/www/html
RUN chown -R www-data:www-data /var/www/html

COPY DockerPHP/init.sh /var/www/init.sh
RUN chmod 544 /var/www/init.sh

ENTRYPOINT [ "/bin/bash" ]
CMD [ "/var/www/init.sh" ]
