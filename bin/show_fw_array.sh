#!/bin/sh

cd `dirname $0`
cd ..

php -r 'require("list.php"); var_export(frameworks());'
