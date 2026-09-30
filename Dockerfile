FROM dunglas/frankenphp:php8.4

RUN install-php-extensions mysqli

WORKDIR /app

COPY . /app
COPY Caddyfile /etc/frankenphp/Caddyfile
