#!/bin/sh

export APP_ENV=prod
export APP_DEBUG=0
composer install --no-dev --optimize-autoloader
php bin/console cache:clear --env=prod --no-debug
php bin/console cache:warmup --env=prod --no-debug
chmod o+w var/cache/ var/log/
chmod -R o+w var/cache/*
