#!/bin/sh

composer install --no-dev --optimize-autoloader
chmod o+w components/demo/cache/ components/demo/logs/
