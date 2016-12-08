#!/bin/sh

composer install --no-dev --optimize-autoloader
composer development-enable # @TODO when I disable it, `/hello/index` shows 404. Why?
chmod o+w data/cache/
