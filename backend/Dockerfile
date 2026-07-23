FROM php:8.3-cli

WORKDIR /app

RUN apt-get update && apt-get install -y \
    git curl zip unzip libpq-dev \
    && docker-php-ext-install pdo_pgsql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy only composer files first
COPY composer.json composer.lock ./

RUN mkdir -p bootstrap/cache storage/framework/views storage/framework/sessions storage/framework/cache

RUN composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs

# Copy the rest of the application
COPY . .

EXPOSE 8000

CMD php artisan serve --host=0.0.0.0 --port=8000