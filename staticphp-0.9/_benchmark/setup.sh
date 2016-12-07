#!/bin/sh

sudo rm -rf Application/Cache/*
composer install --no-interaction --no-dev --optimize-autoloader

chmod 777 -R Application/Cache/
