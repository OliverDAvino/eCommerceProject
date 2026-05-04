FROM php:8.2-apache

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
    && a2enmod rewrite \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader

# Allow .htaccess overrides for Slim routing
RUN echo '<Directory /var/www/html>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/app.conf \
    && a2enconf app

# Railway injects $PORT dynamically — patch Apache to listen on it at startup
CMD sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf \
    && sed -i "s/*:80>/*:${PORT}>/" /etc/apache2/sites-available/000-default.conf \
    && apache2-foreground
