#!/bin/sh

if a2query -q -m rewrite; then
	url="$base/$fw/web/hello/index"
else
	url="$base/$fw/web/app.php/hello/index"
fi
