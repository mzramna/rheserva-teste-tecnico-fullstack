FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    libpng-dev libonig-dev libxml2-dev zip unzip git curl \
    && docker-php-ext-install pdo_mysql mbstring gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . /app

# Cria os diretórios necessários de storage e cache do Laravel com permissões de escrita
RUN mkdir -p storage/framework/views storage/framework/sessions storage/framework/cache storage/logs bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache

# Executa obrigatoriamente o composer install na compilação da imagem Docker
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
