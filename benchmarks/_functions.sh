benchmark ()
{
    fw="$1"
    url="$2"
    ab_log="output/$fw.ab.log"
    output="output/$fw.output"

    echo "ab -c 10 -t 3 $url"
    ab -c 10 -t 3 "$url" > "$ab_log"
    curl "$url" > "$output"
    rps=`grep "Requests per second:" "$ab_log" | cut -f 7 -d " "`
    m=`tail -1 "$output"`
    echo "$fw: $rps: $m" >> "$results"
    echo
}
