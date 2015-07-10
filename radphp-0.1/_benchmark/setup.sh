#!/bin/sh

COMPOSER_PROCESS_TIMEOUT=3600 composer install --no-dev --optimize-autoloader
