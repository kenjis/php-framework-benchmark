#!/bin/sh

composer install --no-dev --optimize-autoloader
chmod o+w storage/*
chmod o+w storage/framework/*
php artisan optimize --force
php artisan config:cache
php artisan route:cache
