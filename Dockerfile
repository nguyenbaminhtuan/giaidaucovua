# Stage 1: Build Frontend Assets
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Stage 2: PHP Application Server
FROM serversideup/php:8.3-fpm-nginx

# Install PostgreSQL PHP extensions
USER root
RUN install-php-extensions pgsql pdo_pgsql

# Direct logs to container stderr so errors appear on Render console
ENV LOG_CHANNEL=stderr

WORKDIR /var/www/html

# Copy application source
COPY --chown=www-data:www-data . /var/www/html
COPY --from=frontend --chown=www-data:www-data /app/public/build /var/www/html/public/build

# Copy startup initialization script
COPY docker/99-laravel-init.sh /etc/entrypoint.d/99-laravel-init.sh
RUN chmod +x /etc/entrypoint.d/99-laravel-init.sh

# Install production Composer dependencies
USER www-data
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

USER root
