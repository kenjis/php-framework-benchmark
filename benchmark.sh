#!/bin/sh

base="http://127.0.0.1/php-framework-benchmark"

cd `dirname $0`
cd benchmarks

sh hello_world.sh "$base"
