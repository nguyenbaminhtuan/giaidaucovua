# Stage 1: Build Frontend Assets
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Stage 2: PHP Application Server
FROM serversideup/php:8.3-fpm-nginx

# Automatically optimize and run migrations on startup
ENV AUTORUN_LARAVEL_MIGRATION=true \
    AUTORUN_LARAVEL_STORAGE_LINK=true \
    AUTORUN_LARAVEL_CONFIG_CACHE=true \
    AUTORUN_LARAVEL_ROUTE_CACHE=true \
    AUTORUN_LARAVEL_VIEW_CACHE=true

WORKDIR /var/www/html

# Copy application source
COPY --chown=www-data:www-data . /var/www/html
COPY --from=frontend --chown=www-data:www-data /app/public/build /var/www/html/public/build

# Install production Composer dependencies
USER www-data
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

USER root
