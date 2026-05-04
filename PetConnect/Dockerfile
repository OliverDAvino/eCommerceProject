FROM php:8.2-cli

# Install all system libraries and PHP extensions the app needs
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

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy all project files
COPY . .

# Install PHP dependencies (allow root, no dev packages, optimised autoloader)
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader

# Create the router script so PHP built-in server handles static files correctly
RUN echo '<?php if (php_sapi_name() === "cli-server") { $url = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH); if ($url !== "/" && is_file(__DIR__ . $url)) { return false; } } require __DIR__ . "/index.php";' > /app/router.php

# Start PHP built-in server on Railway's dynamic port
CMD php -S 0.0.0.0:$PORT /app/router.php
