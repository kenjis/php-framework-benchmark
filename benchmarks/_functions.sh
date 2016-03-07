benchmark ()
{
    fw="$1"
    url="$2"
    ab_log="$output_dir/$fw.ab.log"
    output="$output_dir/$fw.output"
    benchmark_data="$output_dir/$fw.benchmark_data"

    echo "ab -c 10 -t 3 $url"
    ab -c 10 -t 3 "$url" > "$ab_log"
    curl -H 'X-Include-Benchmark-Output-Data: 1' --dump-header "$benchmark_data" "$url" > "$output"

    rps=`grep "Requests per second:" "$ab_log" | cut -f 7 -d " "`
    memory=`grep "X-Benchmark-Output-Data:" "$benchmark_data" | cut -f 2 -d ':' | cut -f 2 -d ' '`
    time=`grep "X-Benchmark-Output-Data:" "$benchmark_data" | cut -f 3 -d ':'`
    file=`grep "X-Benchmark-Output-Data:" "$benchmark_data" | cut -f 4 -d ':'`
    echo "$fw: $rps: $memory: $time: $file" >> "$results_file"

    echo "$fw" >> "$check_file"
    grep "Document Length:" "$ab_log" >> "$check_file"
    grep "Failed requests:" "$ab_log" >> "$check_file"
    grep 'Hello World!' "$output" >> "$check_file"
    grep ':' "$output" >> "$check_file"
    echo "---" >> "$check_file"

    # check errors
    touch "$error_file"
    error=''
    x=`grep 'Failed requests:        0' "$ab_log" || true`
    if [ "$x" = "" ]; then
        tmp=`grep "Failed requests:" "$ab_log"`
        error="$error$tmp"
    fi
    x=`grep 'Hello World!' "$output" || true`
    if [ "$x" = "" ]; then
        tmp=`cat "$output"`
        error="$error$tmp"
    fi
    if [ "$memory" = "" ]; then
        tmp=`cat "$benchmark_data"`
        error="$error$tmp"
    fi
    if [ "$time" = "" ]; then
        tmp=`cat "$benchmark_data"`
        error="$error$tmp"
    fi
    if [ "$file" = "" ]; then
        tmp=`cat "$benchmark_data"`
        error="$error$tmp"
    fi
    if [ "$error" != "" ]; then
        echo -e "$fw\n$error" >> "$error_file"
        echo "---" >> "$error_file"
    fi

    echo "$url" >> "$url_file"

    echo
}
