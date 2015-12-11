#!/usr/bin/env bash

stack=docker_nginx_php_5_6_4 sh benchmark.sh
stack=docker_nginx_php_7_0_0 sh benchmark.sh
stack=docker_nginx_hhvm_3_10_1 sh benchmark.sh

exit 0
