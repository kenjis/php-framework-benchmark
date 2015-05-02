#!/bin/sh

composer install --no-dev --optimize-autoloader
chmod o+w tmp/cache/ tmp/log/
