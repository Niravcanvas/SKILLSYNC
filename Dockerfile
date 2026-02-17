FROM php:8.2-apache

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Copy all project files into the web root
COPY . /var/www/html/

# Create required directories and set permissions
RUN mkdir -p /var/www/html/storage/logs \
    /var/www/html/storage/uploads \
    /var/www/html/uploads/profiles \
    && chown -R www-data:www-data /var/www/html/