#!/bin/sh

cd `dirname $0`
. _functions.sh

base="$1"
bm_name=`basename $0 .sh`

cd ..
results="output/results.$bm_name.log"
mv "$results" "$results.old"


fw="codeigniter-3.0-dev"
url="$base/$fw/index.php/hello/index"
benchmark "$fw" "$url"

fw="yii-2.0"
url="$base/$fw/web/index.php?r=hello/index"
benchmark "$fw" "$url"
