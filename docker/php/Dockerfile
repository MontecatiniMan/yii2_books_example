FROM php:8.3-fpm

# Установка зависимостей
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Очистка кэша
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Установка расширений PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Установка рабочей директории
WORKDIR /var/www/html

# Копирование файлов проекта
COPY . /var/www/html

# Установка зависимостей проекта
RUN composer install

CMD ["php-fpm"] 