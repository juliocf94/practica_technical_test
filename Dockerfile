# Imagen oficial multi-arquitectura (amd64 / arm64): funciona en Windows, Linux y Mac (Intel y Apple Silicon).
FROM php:8.3-cli-alpine

# unzip: lo usa Composer para descomprimir paquetes.
RUN apk add --no-cache unzip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# 1) Dependencias primero
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-scripts --no-autoloader --prefer-dist

# 2) Código de la aplicación
COPY . .
RUN composer dump-autoload --optimize \
    && sed -i 's/\r$//' docker/entrypoint.sh \
    && chmod +x docker/entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["docker/entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
