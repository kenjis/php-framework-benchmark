#!/bin/sh

cd `dirname $0`
. ./_functions.sh

base="$1"
bm_name=`basename $0 .sh`

results_file="output/results.$bm_name.log"
check_file="output/check.$bm_name.log"
error_file="output/error.$bm_name.log"
url_file="output/urls.log"

cd ..

mv "$results_file" "$results_file.old"
mv "$check_file" "$check_file.old"
mv "$error_file" "$error_file.old"
mv "$url_file" "$url_file.old"

for fw in `echo $targets`
do
    if [ -d "$fw" ]; then
        echo "$fw"
        . "$fw/_benchmark/hello_world.sh"
        benchmark "$fw" "$url"
    fi
done

cat "$error_file"
