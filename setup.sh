#!/bin/sh

if [ ! `which composer` ]; then
    echo "composer command not found."
    exit 1;
fi

# Yii 2.0 requires composer-asset-plugin
composer global require "fxp/composer-asset-plugin:1.0.0-beta3"

cd yii-2.0
composer install

cd ../fuel-1.8-dev
composer install --prefer-source

cd ../laravel-4.2
composer install
