FROM php:8.2-cli

# Install required PHP extensions
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    libssl-dev \
    libonig-dev \
    && docker-php-ext-install pdo pdo_mysql curl mbstring \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy files
COPY . .

# Install PHP dependencies (no dev, optimized for production)
RUN composer install --no-dev --optimize-autoloader

# Start PHP built-in server on Railway's dynamic port
CMD php -S 0.0.0.0:$PORT router.php
