benchmark ()
{
    fw="$1"
    url="$2"
    siege_log="output/$fw.siege.log"
    output="output/$fw.output"

    echo "siege -c 10 -t 3s -r 3 -b -q $url"
    # warm up
    siege -c 10 -t 1s -b -q "$url" 2> /dev/null > /dev/null
    sleep 2s
    siege -c 10 -t 3s -r 3 -b -q "$url" 2> "$siege_log" > /dev/null
    curl "$url" > "$output"

    rps=`grep "Transaction rate:" "$siege_log" | cut -f 2 | xargs | cut -f 1 -d ' '`
    m=`tail -1 "$output" | cut -f 1 -d ':'`
    t=`tail -1 "$output" | cut -f 2 -d ':'`
    echo "$fw: $rps: $m: $t" >> "$results_file"

    echo "$fw" >> "$check_file"
    grep "Document Length:" "$siege_log" >> "$check_file"
    grep "Failed requests:" "$siege_log" >> "$check_file"
    grep 'Hello World!' "$output" >> "$check_file"
    echo "---" >> "$check_file"

    # check errors
    touch "$error_file"
    error=''
    x=`grep 'Failed requests:        0' "$siege_log"`
    if [ "$x" = "" ]; then
        tmp=`grep "Failed requests:" "$siege_log"`
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

    # cool down
    sleep 8s
}
