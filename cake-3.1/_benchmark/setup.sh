#!/bin/sh

sudo rm -rf tmp/*
composer install --no-interaction --no-dev --optimize-autoloader
