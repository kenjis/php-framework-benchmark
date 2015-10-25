#!/bin/sh

if [ ! `which composer` ]; then
    echo "composer command not found."
    exit 1;
fi

if [ $# -eq 0 ]; then
    # include framework list
    . ./list.sh
    targets="$list"
else
    targets="${@%/}"
fi

for fw in $targets
do
    if [ -d "$fw" ]; then
        echo "***** $fw *****"
        cd "$fw"
        composer update
        cd ..
    fi
done
