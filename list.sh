#!/bin/sh

list="
"$(php -r 'require("list.php"); foreach (frameworks() as $fw) {echo $fw."\n";};')"
"
