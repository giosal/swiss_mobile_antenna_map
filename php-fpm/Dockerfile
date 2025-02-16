FROM php:8.4-fpm-alpine

LABEL maintainer="George Salukvadze <gsalukvadze@iotlab.com>"

#WORKDIR /tmp
#
#COPY ./tmp/*.sh /tmp/

RUN apk update && apk add bash

# COPY INSTALL SCRIPT
#RUN chmod +x /tmp/*.sh \
#  && apk add --update --no-cache bash \
#  && bash ./packages.sh \
#  && bash ./extensions.sh

RUN set -euo pipefail

RUN apk --update --no-cache add \
    g++ \
    gcc \
    git \
    grep \
    jq \
    libc-dev \
    make \
    nodejs \
    npm \
    openssh-client \
    openssl \
    patch \
    python3 \
    rsync \
    sudo \
    zip

RUN python -v

RUN apk add py3-pip

# persistent / runtime deps
RUN apk add --update --no-cache --virtual .persistent-deps \
		ca-certificates \
		tar \
		xz \
    curl

RUN apk add --update --no-cache \
    autoconf \
    build-base \
    file \
    openssl-dev

RUN apk --update --no-cache add \
  bzip2 \
  bzip2-dev \
  cassandra-cpp-driver \
  curl-dev \
  cyrus-sasl-dev \
  freetype-dev \
  gmp-dev \
  icu-dev \
  icu-data-full \
  imagemagick \
  imagemagick-libs \
  imagemagick-dev \
  imap-dev \
  krb5-dev \
  libbz2 \
  libedit-dev \
  libintl \
  libjpeg-turbo-dev \
  libltdl \
  libmemcached-dev \
  libpng-dev \
  libtool \
  libxml2-dev \
  libxslt-dev \
  libzip-dev \
  linux-headers \
  openldap-dev \
  openssh-client \
  pcre-dev \
  rabbitmq-c \
  rabbitmq-c-dev \
  readline-dev \
  sqlite-dev \
  zlib-dev

RUN apk --update --no-cache add libzip-dev libsodium-dev

RUN docker-php-ext-install -j "$(nproc)" exif pcntl bcmath bz2 calendar intl mysqli opcache pdo_mysql soap xsl zip gmp && \
      docker-php-source delete

RUN docker-php-ext-configure gd --with-freetype --with-jpeg

RUN docker-php-ext-install -j "$(nproc)" gd && docker-php-ext-install zip

RUN docker-php-source extract \
        && mkdir /usr/src/php/ext/amqp \
        && curl -L https://github.com/php-amqp/php-amqp/archive/master.tar.gz | tar -xzC /usr/src/php/ext/amqp --strip-components=1 \
        && docker-php-ext-install amqp \
        && docker-php-source delete

      #Imagick
RUN mkdir /usr/local/src \
        && cd /usr/local/src \
        && git clone https://github.com/Imagick/imagick.git \
        && cd imagick \
        && phpize \
        && ./configure CPPFLAGS='-Dphp_strtolower=zend_str_tolower' \
        && make \
        && make install \
        && cd .. \
        && rm -rf imagick \
        && docker-php-ext-enable imagick

      #XMLRPC
RUN mkdir /usr/local/src/xmlrpc \
        && cd /usr/local/src/xmlrpc \
        && curl -L https://pecl.php.net/get/xmlrpc | tar -xzC /usr/local/src/xmlrpc --strip-components=1 \
        && phpize \
        && ./configure \
        && make \
        && make install \
        && cd .. \
        && rm -rf xmlrpc \
        && docker-php-ext-enable xmlrpc

RUN docker-php-source extract \
    && apk add --no-cache --virtual .phpize-deps-configure $PHPIZE_DEPS \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && apk del .phpize-deps-configure \
    && docker-php-source delete

RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.revalidate_freq=0'; \
    echo 'opcache.validate_timestamps=1'; \
    echo 'opcache.max_accelerated_files=10000'; \
    echo 'opcache.memory_consumption=192'; \
    echo 'opcache.max_wasted_percentage=10'; \
    echo 'opcache.interned_strings_buffer=16'; \
    echo 'opcache.fast_shutdown=1'; \
} > /usr/local/etc/php/conf.d/opcache-recommended.ini

RUN { \
    echo 'apc.shm_segments=1'; \
    echo 'apc.shm_size=512M'; \
    echo 'apc.num_files_hint=7000'; \
    echo 'apc.user_entries_hint=4096'; \
    echo 'apc.ttl=7200'; \
    echo 'apc.user_ttl=7200'; \
    echo 'apc.gc_ttl=3600'; \
    echo 'apc.max_file_size=50M'; \
    echo 'apc.stat=1'; \
} > /usr/local/etc/php/conf.d/apcu-recommended.ini

RUN echo 'xdebug.mode=coverage' > /usr/local/etc/php/conf.d/20-xdebug.ini &&  \
      cat /usr/local/etc/php/conf.d/20-xdebug.ini | grep 'xdebug.mode'

RUN echo "memory_limit=-1" > /usr/local/etc/php/conf.d/zz-conf.ini

# memcached
RUN apk add libmemcached-libs zlib \
    && apk add --virtual .memcached-deps $MEMCACHED_DEPS \
    && git clone "https://github.com/php-memcached-dev/php-memcached.git" /usr/src/php/ext/php-memcached \
           && cd /usr/src/php/ext/php-memcached \
           && phpize \
           && docker-php-ext-configure php-memcached --disable-memcached-sasl \
           && docker-php-ext-install php-memcached \
           && cd ../ && rm -rf php-memcached \
           && docker-php-ext-enable memcached \
    && apk del .memcached-deps

WORKDIR /home

RUN rm -rf /var/cache/apk/* && rm -rf /tmp/* && \
    curl --insecure https://getcomposer.org/composer.phar -o /usr/bin/composer && chmod +x /usr/bin/composer

ADD symfony.ini /etc/php8/conf.d/
ADD symfony.ini /etc/php8/cli/conf.d/
ADD xdebug.ini  /etc/php8/conf.d/

ADD symfony.pool.conf /etc/php8/php-fpm.d/

CMD ["php-fpm"]

WORKDIR /var/www/symfony
EXPOSE 9003
