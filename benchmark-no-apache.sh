#!/bin/sh

#base="http://127.0.0.1/php-framework-benchmark"
base="http://localhost:8888/php-framework-benchmark"

cd `dirname $0`

php -S localhost:8888 -t .. > /dev/null 2>&1 &
PHP_PID=$!
sleep 2;

if [ $# -eq 0 ]; then
    # include framework list
    . ./list.sh
    export targets="$list"
else
    export targets="${@%/}"
fi

cd benchmarks

sh hello_world.sh "$base"

php ../bin/show_results_table.php
kill $PHP_PID
