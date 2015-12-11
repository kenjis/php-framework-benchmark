#!/usr/bin/env bash

DIR=`dirname $0`;

echo "## PHP-FPM 5.6.4 with opcode cache";
echo
stack=docker_nginx_php_5_6_4 php $DIR/../../bin/show_results_table.php
echo
echo "## PHP-FPM 7.0.0 with opcode cache";
echo
stack=docker_nginx_php_7_0_0 php $DIR/../../bin/show_results_table.php
echo
echo "## HHVM 3.10.1 (Corresponding roughly to an up-to-date PHP 5.6)";
echo
stack=docker_nginx_hhvm_3_10_1 php $DIR/../../bin/show_results_table.php
echo

exit 0
