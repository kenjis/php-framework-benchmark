#!/bin/sh

base="http://localhost/php-framework-benchmark"

results="output/results.log"
mv "$results" "$results.old"

benchmark ()
{
    fw="$1"
    url="$2"
    ab_log="output/$fw.ab.log"
    output="output/$fw.output"
    ab -c 10 -n 1000 "$url" > "$ab_log"
    curl "$url" > "$output"
    rps=`grep "Requests per second:" "$ab_log" | cut -f 7 -d " "`
    m=`tail -1 "$output"`
    echo "$fw: $rps: $m" >> "$results"
}

fw="codeigniter-3.0-dev"
url="$base/$fw/index.php/hello/index"
benchmark "$fw" "$url"

fw="yii-2.0"
url="$base/$fw/web/index.php?r=hello/index"
benchmark "$fw" "$url"
