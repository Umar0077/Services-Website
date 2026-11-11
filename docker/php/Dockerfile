# Local development image: PHP 8.4 CLI with the extensions Laravel + Filament need.
FROM php:8.4-cli

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_mysql intl zip gd exif bcmath opcache pcntl \
    && apt-get update && apt-get install -y --no-install-recommends git unzip default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN echo "memory_limit=1G\nupload_max_filesize=12M\npost_max_size=14M" > /usr/local/etc/php/conf.d/app.ini

WORKDIR /var/www
