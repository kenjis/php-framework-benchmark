#!/bin/sh

base="http://127.0.0.1/php-framework-benchmark"

cd `dirname $0`

if [ $# -eq 0 ]; then
    # include framework list
    . ./list.sh
    export targets="$list"
else
    export targets="${@}"
fi

cd benchmarks

sh hello_world.sh "$base"

php ../bin/show_results_table.php
