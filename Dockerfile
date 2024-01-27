FROM php:8.2-apache
# Install dependencies
RUN apt-get update && \
apt-get install -y \
libzip-dev \
unzip \
libonig-dev \
libxml2-dev \
libpng-dev \
libjpeg-dev && \
docker-php-ext-configure gd --with-jpeg && \
docker-php-ext-install \
pdo_mysql \
zip \
mbstring \
exif \
pcntl \
bcmath \
gd
#Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
&& php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
&& php -r "unlink('composer-setup.php');"

