#!/usr/bin/env bash

DIR=`dirname $0`;

echo "PHP 5.6.4 Stack:";
echo "http://"$(docker-machine ip default)":"$(docker-compose -f $DIR/../docker/docker-compose.yml port nginx_php_5_6_4 80 | sed 's/[0-9.]*://')"/php-framework-benchmark/"
echo "PHP 7.0.0 Stack:";
echo "http://"$(docker-machine ip default)":"$(docker-compose -f $DIR/../docker/docker-compose.yml port nginx_php_7_0_0 80 | sed 's/[0-9.]*://')"/php-framework-benchmark/"
echo "HHVM 3.10.1 Stack:";
echo "http://"$(docker-machine ip default)":"$(docker-compose -f $DIR/../docker/docker-compose.yml port nginx_hhvm_3_10_1 80 | sed 's/[0-9.]*://')"/php-framework-benchmark/"

exit 0