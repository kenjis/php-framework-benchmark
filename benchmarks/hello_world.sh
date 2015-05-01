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


:<<'#COMMENT'
fw="no-framework"
url="$base/$fw/index.php"
benchmark "$fw" "$url"
#COMMENT

#fw="phalcon-1.3"
#url="$base/$fw/public/index.php?_url=/hello/index"
#benchmark "$fw" "$url"

fw="phalcon-2.0"
url="$base/$fw/public/index.php?_url=/hello/index"
benchmark "$fw" "$url"

fw="ice-1.0"
url="$base/$fw/public/index.php?_url=/hello/index"
benchmark "$fw" "$url"

fw="slim-2.6"
url="$base/$fw/index.php/hello/index"
benchmark "$fw" "$url"

fw="codeigniter-3.0"
url="$base/$fw/index.php/hello/index"
benchmark "$fw" "$url"

fw="lumen-5.0"
url="$base/$fw/public/index.php/hello/index"
benchmark "$fw" "$url"

fw="yii-2.0"
url="$base/$fw/web/index.php?r=hello/index"
benchmark "$fw" "$url"

fw="silex-1.2"
url="$base/$fw/web/index.php/hello/index"
benchmark "$fw" "$url"

fw="cygnite-1.3"
url="$base/$fw/index.php/hello/index"
benchmark "$fw" "$url"

fw="bear-1.0"
url="$base/$fw/var/www/index.php/hello"
benchmark "$fw" "$url"

fw="fuel-1.8-dev"
url="$base/$fw/public/index.php/hello/index"
benchmark "$fw" "$url"

fw="cake-3.0"
url="$base/$fw/index.php/hello/index"
benchmark "$fw" "$url"

fw="aura-2.0"
url="$base/$fw/web/index.php/hello/index"
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

fw="zf-2.4"
url="$base/$fw/public/index.php/application/hello/index"
benchmark "$fw" "$url"

fw="typo3-flow-2.3"
url="$base/$fw/Web/index.php/flow/benchmark"
benchmark "$fw" "$url"

cat "$error_file"
