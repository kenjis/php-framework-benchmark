#!/bin/sh

export SYMFONY_ENV=prod
composer install --no-dev --optimize-autoloader
php app/console cache:clear --env=prod --no-debug
chmod o+w app/cache/ app/logs/
chmod -R o+w app/cache/*
