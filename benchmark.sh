#!/bin/sh

# exit on failure
set -e

if [ "$stack" = "" ]; then
  stack="local"
fi

cd `dirname $0`

if [ $# -eq 0 ]; then
    # include framework list
    . ./list.sh
    export targets="$list"
else
    export targets="${@%/}"
fi

cd benchmarks

sh hello_world.sh "$stack"

php ../bin/show_results_table.php
