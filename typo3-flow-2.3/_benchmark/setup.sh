#!/bin/sh

export FLOW_CONTEXT=Production
composer install --no-dev --optimize-autoloader
./flow flow:cache:warmup
sed -i -e "s/{ exit(); }/{ printf(\"\\\n%' 8d:%f\", memory_get_peak_usage(true), microtime(true) - \$_SERVER['REQUEST_TIME_FLOAT']); exit(); }/" Packages/Framework/TYPO3.Flow/Classes/TYPO3/Flow/Http/RequestHandler.php
