FROM php:8.3-cli

RUN apt-get update
RUN apt-get install -y libzip-dev zip git

RUN pear update-channels \
    && pecl update-channels \
    && pecl install xdebug redis

RUN docker-php-ext-install pdo_mysql zip
RUN docker-php-ext-enable pdo_mysql zip redis xdebug

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
