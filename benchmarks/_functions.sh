benchmark ()
{
    fw="$1"
    url="$2"
    ab_log="output/$fw.ab.log"
    output="output/$fw.output"

    # get rpm
    echo "ab -c 10 -t 3 $url"
    ab -c 10 -t 3 "$url" > "$ab_log"
    rps=`grep "Requests per second:" "$ab_log" | cut -f 7 -d " "`

    # get time
    count=10
    total=0
    for ((i=0; i < $count; i++)); do
        curl "$url" > "$output"
        t=`tail -1 "$output" | cut -f 2 -d ':'`
        total=`php ./benchmarks/sum_ms.php $t $total`
    done
    time=`php ./benchmarks/avg_ms.php $total $count`

    # get memory and file
    memory=`tail -1 "$output" | cut -f 1 -d ':'`
    file=`tail -1 "$output" | cut -f 3 -d ':'`

    echo "$fw: $rps: $memory: $time: $file" >> "$results_file"

    echo "$fw" >> "$check_file"
    grep "Document Length:" "$ab_log" >> "$check_file"
    grep "Failed requests:" "$ab_log" >> "$check_file"
    grep 'Hello World!' "$output" >> "$check_file"
    echo "---" >> "$check_file"

    # check errors
    touch "$error_file"
    error=''
    x=`grep 'Failed requests:        0' "$ab_log"`
    if [ "$x" = "" ]; then
        tmp=`grep "Failed requests:" "$ab_log"`
        error="$error$tmp"
    fi
    x=`grep 'Hello World!' "$output"`
    if [ "$x" = "" ]; then
        tmp=`cat "$output"`
        error="$error$tmp"
    fi
    if [ "$error" != "" ]; then
        echo -e "$fw\n$error" >> "$error_file"
        echo "---" >> "$error_file"
    fi

    echo "$url" >> "$url_file"

    echo
}
