FROM php:8.2-apache

# Install system deps + PHP extensions needed by dompdf and the app
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libcurl4-openssl-dev \
    zip \
    unzip \
    curl \
    && docker-php-ext-install pdo pdo_mysql mysqli zip curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy project files
COPY . /var/www/html/

# Install Composer dependencies (dompdf etc.)
RUN cd /var/www/html && composer install --no-dev --optimize-autoloader --no-interaction

# Create required directories and set permissions
RUN mkdir -p /var/www/html/storage/logs \
    /var/www/html/storage/uploads \
    /var/www/html/uploads/profiles \
    && chown -R www-data:www-data /var/www/html/

EXPOSE 80