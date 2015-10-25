#!/bin/sh

export FLOW_CONTEXT=Production
composer install --no-dev --optimize-autoloader
./flow flow:cache:warmup
sed -i -e "s/{ exit(); }/{ require \$_SERVER['DOCUMENT_ROOT'].'\/php-framework-benchmark\/libs\/output_data.php'; exit(); }/" Packages/Framework/TYPO3.Flow/Classes/TYPO3/Flow/Http/RequestHandler.php
