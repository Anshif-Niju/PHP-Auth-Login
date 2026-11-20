# Runs the Laravel API on container hosts such as Render.
FROM dunglas/frankenphp:php8.4

# Free hosts run containers without extra privileges and refuse to start a binary
# that carries the port-binding capability. Drop it; ports above 1024 don't need it.
RUN install-php-extensions pdo_mysql zip opcache \
    && setcap -r /usr/local/bin/frankenphp
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Install PHP packages first so this layer is reused when only app code changes.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

COPY . .
RUN composer dump-autoload --optimize --no-dev \
    && php artisan storage:link

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr

# The host provides PORT. Create the tables, then serve the public folder.
CMD ["sh", "-c", "php artisan migrate --force && exec frankenphp php-server --root public --listen :${PORT:-8080}"]
