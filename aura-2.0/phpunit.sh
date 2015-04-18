php -S localhost:8080 -t ./web/ &
PID=$!
phpunit
STATUS=$?
kill $PID
exit $STATUS
