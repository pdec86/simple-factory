# Simple factory

## Run project
1. Copy DockerPHP/sf_messenger.conf.sample to DockerPHP/sf_messenger.conf and fill with environment variables.
2. Copy .env.docker.sample to .env.docker and fill with environment variables.
3. Create file (in main directory) amqp_dsn.txt and write in it DSN for RabbitMQ connection, e.g. amqp://user:password@factory_messages_broker:5672/%2f/messages containing username and password same as in .env.docker
4. Create file (in main directory) app_secret.txt and write in it secret for Symfony
5. Create file (in main directory) db_user.txt and write in it username for database connection (same as in .env.docker)
6. Create file (in main directory) db_pass.txt and write in it password for database connection (same as in .env.docker)
7. Run following command to start containers
```shell
docker compose -f docker-compose.yml --env-file .env.docker up -d --build --force-recreate
```

## Build image PHP and push to registry
```shell
docker image build -f ./DockerPHP/Dockerfile --no-cache -t REGISTRY/factory-php:rc1 .

docker buildx create --name factory_builder --driver docker-container --bootstrap
docker buildx build --builder factory_builder --platform linux/amd64,linux/arm64 \
-f ./DockerPHP/Dockerfile --tag REGISTRY/factory-php:latest --push .
```

## Development
## Add sample import data CSV file for products
#### Put CSV file inside directory DockerPHP/sampleData and name it "products.csv".

## Run import inside PHP container
```shell
bin/console app:import-sample-products {name column number} {CSV delimiter}
```

## Development
### Run project for development
```shell
docker compose -f docker-compose.yml -f docker-compose.dev.yaml --env-file .env.docker up -d --build --force-recreate
```

### Generate self signed certificates
```shell
openssl req -x509 -newkey rsa:4096 -keyout DockerNginx/certs_dev/key.pem -out DockerNginx/certs_dev/cert.pem -sha256 -days 31 -nodes -subj "/C=PL/ST=Mazowieckie/L=Warszawa/O=Simple-Factory/OU=Simple-Factory Main/CN=*.factory.local"
```

## Tests
### Run tests
```shell
docker compose -f docker-compose.yml -f docker-compose.test.yaml --env-file .env.docker up -d --build --force-recreate
```
