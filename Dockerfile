# syntax=docker/dockerfile:1

# ==========================================
# Base Stage: PHP 8.2 Apache & Core Tooling
# ==========================================
FROM php:8.2-apache AS base

# Install system dependencies required by Drupal 10, Composer, and local testing tools
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    zip \
    curl \
    default-mysql-client \
    less \
    vim-tiny \
    ca-certificates \
    libpng-dev \
    libjpeg-dev \
    libzip-dev \
    libicu-dev \
    && rm -rf /var/lib/apt/lists/*

# Install required and recommended PHP extensions for Drupal 10, MySQL/MariaDB, Apigee, and Redis
COPY --from=ghcr.io/mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions \
    gd \
    opcache \
    pdo_mysql \
    mysqli \
    zip \
    intl \
    bcmath \
    exif \
    pcntl \
    sysvsem \
    redis \
    apcu \
    uploadprogress

# Configure OPcache and JIT optimization
COPY docker/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Configure Apache DocumentRoot to point to Drupal web/ directory and enable required Apache modules
ENV APACHE_DOCUMENT_ROOT=/var/www/html/web
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && a2enmod rewrite headers

# Install Composer 2
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory and add vendor/bin to PATH for CLI convenience
WORKDIR /var/www/html
ENV PATH="${PATH}:/var/www/html/vendor/bin"

# Install and configure entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

CMD ["apache2-foreground"]

# ==========================================
# Development / Local Testing Stage (Default)
# ==========================================
FROM base AS development

# Set mock client in CI / local testing
ENV CI=true
ENV APIGEE_EDGE_MOCK_CLIENT=true
ENV COMPOSER_ALLOW_SUPERUSER=1

# Copy the actual profile code into the path repository destination
COPY . /var/www/html-profile

# Copy composer project template and lock file
COPY docker/composer.project.json ./composer.json
COPY docker/composer.project.lock ./composer.lock

# Install all dependencies including require-dev.
# Since apigee_devportal_kickstart is defined as a path repository at /var/www/html-profile,
# Composer will symlink /var/www/html-profile into /var/www/html/web/profiles/contrib/apigee_devportal_kickstart
RUN composer install --prefer-dist --no-interaction --no-progress --no-security-blocking

# Ensure required Drupal directories exist and grant permissions to www-data
RUN mkdir -p /var/www/html/web/sites/default/files \
             /var/www/html/private \
             /var/www/html/config && \
    chown -R www-data:www-data /var/www/html/web/sites/default/files \
                               /var/www/html/private

# ==========================================
# Production Stage
# ==========================================
FROM base AS production

ENV COMPOSER_ALLOW_SUPERUSER=1

# Copy the actual profile code
COPY . /var/www/html-profile

# Copy composer project template and lock file
COPY docker/composer.project.json ./composer.json
COPY docker/composer.project.lock ./composer.lock

# Install production-only dependencies
RUN composer install --prefer-dist --no-dev --no-interaction --no-progress --no-autoloader --no-security-blocking

RUN composer dump-autoload --optimize && \
    mkdir -p /var/www/html/web/sites/default/files \
             /var/www/html/private \
             /var/www/html/config && \
    chown -R www-data:www-data /var/www/html/web/sites/default/files \
                               /var/www/html/private
