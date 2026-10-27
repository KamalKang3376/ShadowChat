
FROM php:8.4-apache

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libicu-dev \
    libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring bcmath intl zip \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

RUN if ! getent group 1000 >/dev/null; then \
        groupadd -g 1000 render-secrets; \
    fi && usermod -a -G 1000 www-data

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

WORKDIR /var/www/html

COPY . .

RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf

RUN COMPOSER_MAX_PARALLEL_HTTP=2 \
    COMPOSER_PROCESS_TIMEOUT=600 \
    composer install --prefer-dist --no-dev \
    --optimize-autoloader --no-interaction

RUN mkdir -p storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80
