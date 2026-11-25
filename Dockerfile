# syntax=docker/dockerfile:1
FROM php:8.4-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libicu-dev \
    && docker-php-ext-install pdo_mysql intl \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:lts /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# Apache: document root = public/, fallback na index.php (zamiast mod_rewrite)
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!/var/www/html/public!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && printf '<Directory /var/www/html/public>\n    AllowOverride None\n    Require all granted\n    FallbackResource /index.php\n</Directory>\n' > /etc/apache2/conf-available/symfony.conf \
    && a2enconf symfony

WORKDIR /var/www/html

# Zależności osobno, żeby Docker mógł cache'ować tę warstwę
COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-interaction --no-scripts --no-autoloader

# Cały projekt, potem autoloader i skrypty Symfony (już z bin/console)
COPY . .
RUN composer dump-autoload --optimize \
    && composer run-script auto-scripts \
    && mkdir -p var public/uploads/posters \
    && chown -R www-data:www-data var public/uploads