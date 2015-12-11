#!/usr/bin/env bash

DIR=`dirname $0`;

# Adjust to be able to compare the graphs across the stacks
query_string="max_rps=2800&max_memory=3&max_time=500&max_file=300"

php_5_6_4_url="http://"$(docker-machine ip default)":"$(docker-compose -f $DIR/../docker-compose.yml port nginx_php_5_6_4 80 | sed 's/[0-9.]*://')"/php-framework-benchmark/?$query_string"
php_7_0_0_url="http://"$(docker-machine ip default)":"$(docker-compose -f $DIR/../docker-compose.yml port nginx_php_7_0_0 80 | sed 's/[0-9.]*://')"/php-framework-benchmark/?$query_string"
hhvm_3_10_1_url="http://"$(docker-machine ip default)":"$(docker-compose -f $DIR/../docker-compose.yml port nginx_hhvm_3_10_1 80 | sed 's/[0-9.]*://')"/php-framework-benchmark/?$query_string"

echo "PHP 5.6.4 Stack:";
echo "$php_5_6_4_url"
echo "PHP 7.0.0 Stack:";
echo "$php_7_0_0_url"
echo "HHVM 3.10.1 Stack:";
echo "$hhvm_3_10_1_url"
echo
echo "Run to open the URLs in your browser:"
echo "open '$php_5_6_4_url'"
echo "open '$php_7_0_0_url'"
echo "open '$hhvm_3_10_1_url'"

exit 0