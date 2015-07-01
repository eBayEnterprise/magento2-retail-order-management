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
        re2c \
        wget \
        vim \
    && pecl install -o -f xdebug \
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
    && apt-get autoremove -qqy \
    && until rm -rf /tmp/pear /var/lib/apt/lists; do sleep 1; done

# Install composer
WORKDIR /usr/bin
RUN curl -sS https://getcomposer.org/installer | php \
    && chmod +x composer.phar \
    && ln -s composer.phar composer

RUN mkdir -p /var/www/code

# Add PHP configuration
COPY config/magento.php.ini /usr/local/etc/php/conf.d/magento.php.ini

WORKDIR /var/www/code

VOLUME /var/www/code
