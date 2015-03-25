#!/bin/sh

cd `dirname $0`
. ./_functions.sh

base="$1"
bm_name=`basename $0 .sh`

cd ..
results="output/results.$bm_name.log"
mv "$results" "$results.old"


fw="phalcon-1.3"
url="$base/$fw/public/index.php?_url=/hello/index"
benchmark "$fw" "$url"

fw="codeigniter-3.0-dev"
url="$base/$fw/index.php/hello/index"
benchmark "$fw" "$url"

fw="yii-2.0"
url="$base/$fw/web/index.php?r=hello/index"
benchmark "$fw" "$url"

fw="fuel-1.8-dev"
url="$base/$fw/public/index.php/hello/index"
benchmark "$fw" "$url"

fw="silex-1.2"
url="$base/$fw/web/"
benchmark "$fw" "$url"

fw="bear-1.0"
url="$base/$fw/var/www/index.php/hello"
benchmark "$fw" "$url"

fw="cake-3.0"
url="$base/$fw/index.php/hello/index"
benchmark "$fw" "$url"

#fw="bear-0.10"
#url="$base/$fw/var/www/index.php/hello"
#benchmark "$fw" "$url"

#fw="symfony-2.5"
#url="$base/$fw/web/app.php/hello/index"
#benchmark "$fw" "$url"

fw="symfony-2.6"
url="$base/$fw/web/app.php/hello/index"
benchmark "$fw" "$url"

#fw="laravel-4.2"
#url="$base/$fw/public/index.php/hello/index"
#benchmark "$fw" "$url"

fw="laravel-5.0"
url="$base/$fw/public/index.php/hello/index"
benchmark "$fw" "$url"

#fw="fuel-2.0-dev"
#url="$base/$fw/public/index.php/hello/index"
#benchmark "$fw" "$url"
