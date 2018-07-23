#!/bin/sh

composer install --no-dev --optimize-autoloader
chmod o+w assets/ runtime/ web/assets/
