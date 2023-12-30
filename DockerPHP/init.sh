#!/bin/bash
set -e

cp /var/www/init-php.sh /var/www/init-php-tmp.sh
chmod 544 /var/www/init-php-tmp.sh

chmod 544 /var/www/html/products-catalogue.sh
chmod 544 /var/www/html/products-manufacture.sh

source /var/www/init-php-tmp.sh
