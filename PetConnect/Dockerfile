FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    libssl-dev \
    libonig-dev \
    libzip-dev \
    libicu-dev \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    unzip \
    zip \
    git \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        curl \
        mbstring \
        zip \
        intl \
        fileinfo \
        gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader

RUN echo '<?php if (php_sapi_name() === "cli-server") { $url = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH); if ($url !== "/" && is_file(__DIR__ . $url)) { return false; } } require __DIR__ . "/index.php";' > /app/router.php

# PHP_CLI_SERVER_WORKERS runs multiple worker processes to handle concurrent requests
CMD php -S 0.0.0.0:$PORT /app/router.php
