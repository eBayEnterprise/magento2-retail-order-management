FROM php:5.6-apache
MAINTAINER Scott van Brug <svanbrug@ebay.com>

# Install PHP deps for Magento 2 / ROM Extension
RUN apt-get update && apt-get install -qqy \
        apt-utils \
        git \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng12-dev \
        libxslt-dev \
        mysql-client \
        re2c \
    && docker-php-ext-configure gd \
        --with-freetype-dir=/usr/include/ \
        --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install \
        bcmath \
        gd \
        mbstring \
        mcrypt \
        mysql \
        pdo_mysql \
        pcntl \
        xsl \
        zip

# Install composer
WORKDIR /usr/local/bin
RUN curl -sS https://getcomposer.org/installer | php \
    && chmod +x composer.phar \
    && ln -s composer.phar composer

# Setup Apache dirs and configuration.

# Create a "default" user. Any volume mounted files should be owned by this
# user, so web source files can be shared via a volume mount. Apache will be run
# as this user by `apache_safe_start_perms` to have the necessary permissions
# on any volume mounted files and directories.
RUN adduser --system --uid=1000 default

# Extension repo should be mounted to /var/www/code. Magento 2 will be built
# and served from /var/www/code/build/magento.
RUN mkdir -p /var/www/code/build/magento
ENV MAGENTO_ROOT_DIR /var/www/code/build/magento
WORKDIR /var/www/code

# Add apache configuration and enable mod_rewrite
COPY env/config/apache2.conf /etc/apache2/apache2.conf
RUN a2enmod rewrite

COPY . /var/www/magento
RUN composer --no-ansi --no-interaction update
RUN chown -R default /var/www/magento

VOLUME /var/www/magento

COPY env/bin/* /usr/local/bin/

# `apache_safe_start_perms` will start apache as the "default" user.
CMD ["apache_safe_start_perms", "-DFOREGROUND"]
