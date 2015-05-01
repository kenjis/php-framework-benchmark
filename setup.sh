#!/bin/sh

if [ ! `which composer` ]; then
    echo "composer command not found."
    exit 1;
fi

if [ ! `which ab` ]; then
    echo "ab command not found."
    exit 1;
fi

if [ ! `which curl` ]; then
    echo "curl command not found."
    exit 1;
fi

# Yii 2.0 requires composer-asset-plugin
composer global require "fxp/composer-asset-plugin:1.0.0-beta3"

cd yii-2.0
basename `pwd`
composer install --no-dev --optimize-autoloader
chmod o+w assets/ runtime/ web/assets/

cd ../fuel-1.8-dev
basename `pwd`
composer install --prefer-source --no-dev --optimize-autoloader

cd ../laravel-4.2
basename `pwd`
composer install --no-dev --optimize-autoloader
chmod o+w app/storage/*

cd ../cake-3.0
basename `pwd`
sudo rm -rf tmp/*
COMPOSER_PROCESS_TIMEOUT=3600 composer install --no-dev --optimize-autoloader

cd ../symfony-2.5
basename `pwd`
export SYMFONY_ENV=prod
composer install --no-dev --optimize-autoloader
php app/console cache:clear --env=prod --no-debug
chmod o+w app/cache/ app/logs/
chmod -R o+w app/cache/*

cd ../symfony-2.6
basename `pwd`
export SYMFONY_ENV=prod
composer install --no-dev --optimize-autoloader
php app/console cache:clear --env=prod --no-debug
chmod o+w app/cache/ app/logs/
chmod -R o+w app/cache/*

cd ../phalcon-1.3
basename `pwd`
chmod o+w app/cache/

cd ../bear-0.10
basename `pwd`
composer install --no-dev --optimize-autoloader
chmod o+w var/tmp/ var/log/

cd ../bear-1.0
basename `pwd`
composer install --no-dev --optimize-autoloader
chmod o+w var/tmp/ var/log/

cd ../laravel-5.0
basename `pwd`
composer install --no-dev --optimize-autoloader
chmod o+w storage/*
chmod o+w storage/framework/*
php artisan optimize --force
php artisan config:cache
php artisan route:cache

cd ../fuel-2.0-dev
basename `pwd`
composer install --no-dev --optimize-autoloader
chmod o+w components/demo/cache/ components/demo/logs/

cd ../silex-1.2
basename `pwd`
composer install --no-dev --optimize-autoloader

cd ../slim-2.6
basename `pwd`
composer install --no-dev --optimize-autoloader

cd ../zf-2.4
basename `pwd`
composer install --no-dev --optimize-autoloader
chmod o+w data/cache/

cd ../typo3-flow-2.3
basename `pwd`
export FLOW_CONTEXT=Production
composer install --no-dev --optimize-autoloader
./flow flow:cache:warmup
sed -i -e "s/{ exit(); }/{ printf(\"\\\n%' 8d:%f\", memory_get_peak_usage(true), microtime(true) - \$_SERVER['REQUEST_TIME_FLOAT']); exit(); }/" Packages/Framework/TYPO3.Flow/Classes/TYPO3/Flow/Http/RequestHandler.php

cd ../lumen-5.0
basename `pwd`
composer install --no-dev --optimize-autoloader
chmod o+w storage/*
chmod o+w storage/framework/*

cd ../aura-2.0
basename `pwd`
composer install --no-dev --optimize-autoloader
chmod o+w tmp/cache/ tmp/log/

cd ../cygnite-1.3
basename `pwd`
composer install --no-dev --optimize-autoloader
