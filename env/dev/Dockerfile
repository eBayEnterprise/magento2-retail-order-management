FROM php:5.6-cli
MAINTAINER Scott van Brug <svanbrug@ebay.com>

# Install requirements for Magento 2, composer, and some helpful tools.
RUN apt-get update && apt-get install -qqy \
        apt-utils \
        g++ \
        git \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng12-dev \
        libxslt-dev \
        mysql-client \
        re2c \
        wget \
    && docker-php-ext-configure gd \
        --with-freetype-dir=/usr/include/ \
        --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install \
        bcmath \
        gd \
        intl \
        mbstring \
        mcrypt \
        mysql \
        pdo_mysql \
        pcntl \
        xsl \
        zip \
    && apt-get autoremove -qqy

# Install XDebug
RUN cd /tmp/ \
    && wget http://xdebug.org/files/xdebug-2.3.2.tgz \
    && tar zxvf xdebug-2.3.2.tgz \
    && cd /tmp/xdebug-2.3.2/ \
    && phpize \
    && ./configure \
    && make \
    && cp modules/xdebug.so /usr/local/lib/php/extensions/no-debug-non-zts-20131226 \
    && echo \
        "zend_extension = /usr/local/lib/php/extensions/no-debug-non-zts-20131226/xdebug.so" \
        > /usr/local/etc/php/php.ini

# Install composer
WORKDIR /usr/bin
RUN curl -sS https://getcomposer.org/installer | php \
    && chmod +x composer.phar \
    && ln -s composer.phar composer

RUN mkdir -p /var/www/code

WORKDIR /var/www/code

VOLUME /var/www/code
