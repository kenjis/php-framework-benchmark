#!/bin/sh

base="http://localhost/php-framework-benchmark"

cd `dirname $0`
cd benchmarks

sh hello_world.sh "$base"
