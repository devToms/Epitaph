FROM php:8.3-fpm

# Zainstaluj wymagane rozszerzenia
RUN apt-get update && apt-get install -y \
    gnupg \
    g++ \
    procps \
    openssl \
    git \
    unzip \
    zlib1g-dev \
    libzip-dev \
    libfreetype6-dev \
    libpng-dev \
    libjpeg-dev \
    libicu-dev \
    libonig-dev \
    libxslt1-dev \
    acl \
    redis-server \
    curl \
    && apt-get clean

# Instalacja rozszerzeń PHP
RUN docker-php-ext-install \
    pdo pdo_mysql zip xsl gd intl opcache exif mbstring

# Instalacja Redis
RUN pecl install redis && docker-php-ext-enable redis

# Instalacja Xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Instalacja Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Kopiowanie pliku konfiguracyjnego xdebug.ini

# COPY ./docker/php/xdebug.ini /usr/local/etc/php/conf.d/





# Ustawienie katalogu roboczego
WORKDIR /var/www/html
