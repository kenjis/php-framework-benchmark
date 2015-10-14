#!/bin/sh

sudo rm -rf tmp/*
composer install --no-dev --optimize-autoloader
