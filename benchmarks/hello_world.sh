#!/bin/sh

# exit on failure - use during development
#set -e

cd `dirname $0`
. ./_functions.sh

stack="$1"
bm_name=`basename $0 .sh`

if [ "$stack" = "local" ]; then
  base="http://127.0.0.1/php-framework-benchmark"
fi
if [ "$stack" = "docker_nginx_php_5_6_4" ]; then
  base="http://nginx_php_5_6_4"
fi
if [ "$stack" = "docker_nginx_hhvm_3_10_1" ]; then
  base="http://nginx_hhvm_3_10_1"
fi
if [ "$stack" = "docker_nginx_php_7_0_0" ]; then
  base="http://nginx_php_7_0_0"
fi

output_dir="output/$stack"

results_file="$output_dir/results.$bm_name.log"
check_file="$output_dir/check.$bm_name.log"
error_file="$output_dir/error.$bm_name.log"
url_file="$output_dir/urls.log"

cd ..

mkdir -p "$output_dir"

mv "$results_file" "$results_file.old" || true
mv "$check_file" "$check_file.old" || true
mv "$error_file" "$error_file.old" || true
mv "$url_file" "$url_file.old" || true

for fw in `echo $targets`
do
    if [ -d "$fw" ]; then
        echo "$fw"
        . "$fw/_benchmark/hello_world.sh"
        benchmark "$fw" "$url"
    fi
done

cat "$error_file" || true
