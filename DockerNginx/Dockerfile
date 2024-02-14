FROM bitnami/nginx:1.25

RUN mkdir -p /certs/factory
RUN openssl req -x509 -newkey rsa:4096 -keyout /certs/factory/key.pem -out /certs/factory/cert.pem -sha256 -days 31 -nodes -subj "/C=PL/ST=Mazowieckie/L=Warszawa/O=Simple-Factory/OU=Simple-Factory Wildcart/CN=*.factory.local"

VOLUME /var/www/html
