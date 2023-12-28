# Simple factory

## Run project
```shell
docker compose -f docker-compose.yml --env-file .env.docker up -d --build --force-recreate
```

## Development
### Generate self signed certificates
```shell
openssl req -x509 -newkey rsa:4096 -keyout DockerNginx/certs_dev/key.pem -out DockerNginx/certs_dev/cert.pem -sha256 -days 31 -nodes -subj "/C=PL/ST=Mazowieckie/L=Warszawa/O=Simple-Factory/OU=Simple-Factory Main/CN=simple-factory.local"
```

### Run project for development
```shell
docker compose -f docker-compose.yml -f docker-compose.dev.yaml --env-file .env.docker up -d --build --force-recreate
```

## Tests
### Run tests
```shell
docker compose -f docker-compose.yml -f docker-compose.test.yaml --env-file .env.docker up -d --build --force-recreate
```
