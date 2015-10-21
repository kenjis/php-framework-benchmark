#!/bin/sh

# Yii 2.0 requires composer-asset-plugin
composer global require "fxp/composer-asset-plugin:~1.0.3"

composer install --no-dev --optimize-autoloader
chmod o+w assets/ runtime/ web/assets/
