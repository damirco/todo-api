FROM php:8.4-fpm-alpine

# Установка системных зависимостей для PostgreSQL
RUN apk add --no-cache \
    postgresql-dev \
    zip \
    unzip \
    git \
    curl \
    bash

# Установка расширений PHP для Laravel и PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql

# Копируем Composer из официального образа
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Копируем файлы зависимостей первыми (для кэширования слоев Docker)
COPY composer.json composer.lock ./

# Устанавливаем пакеты прямо при сборке образа, полностью отключая проверку сети и плагинов
RUN composer install --no-interaction --prefer-dist --no-scripts --no-autoloader --ignore-platform-reqs

# Копируем остальной код проекта
COPY . .

# Генерируем финальный автолоад
RUN composer dump-autoload --optimize

EXPOSE 9000
CMD ["php-fpm"]
