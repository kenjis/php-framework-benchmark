#!/bin/sh

cd `dirname $0`
cd ..

# include framework list
. ./list.sh
targets="$list"

echo '['

for fw in $targets
do
    if [ -d "$fw" ]; then
        echo "\t'$fw',"
    fi
done

echo ']'
