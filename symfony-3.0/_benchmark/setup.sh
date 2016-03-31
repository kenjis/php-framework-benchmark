#!/bin/sh

export SYMFONY_ENV=prod
composer install --no-dev --optimize-autoloader
php bin/console cache:clear --env=prod --no-debug
chmod o+w var/cache/ var/logs/
chmod -R o+w var/cache/*
