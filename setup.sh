#!/bin/sh

if [ ! `which composer` ]; then
    echo "composer command not found."
    exit 1;
fi

if [ ! `which siege` ]; then
    echo "siege command not found."
    exit 1;
fi

if [ ! `which curl` ]; then
    echo "curl command not found."
    exit 1;
fi

if [ $# -eq 0 ]; then
    # include framework list
    . ./list.sh
    targets="$list"
else
    targets="$@"
fi

for fw in $targets
do
    if [ -d "$fw" ]; then
        echo "$fw"
        cd "$fw"
        . "_benchmark/setup.sh"
        cd ..
    fi
done
