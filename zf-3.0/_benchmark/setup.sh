#!/bin/sh

composer install --no-dev --optimize-autoloader
composer development-disable
chmod o+w data/cache/
