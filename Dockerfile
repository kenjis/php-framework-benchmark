FROM php:7.1.9-apache

ARG GITHUB_OAUTH_TOKEN

ENV COMPOSER_NO_INTERACTION=1 \
    COMPOSER_ALLOW_SUPERUSER=1 \
    PATH=/root/.composer/vendor/bin:$PATH

RUN apt-get -qy update && \
    apt-get -qy install \
        git \
        zlib1g-dev \
        libicu-dev \
    && \
    apt-get -qy clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN docker-php-ext-configure \
        intl \
    && \
    docker-php-ext-install -j$(nproc) \
        zip \
        intl

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    composer config -g github-oauth.github.com ${GITHUB_OAUTH_TOKEN} && \
    composer global require hirak/prestissimo

WORKDIR /var/www/html/php-framework-benchmark
