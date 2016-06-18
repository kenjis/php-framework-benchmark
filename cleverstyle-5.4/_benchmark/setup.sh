#!/bin/sh
# Copy installer
cp _benchmark/CleverStyle_Framework_5.4.0+build-2285_Core.phar.php installer.phar.php
# Copy file that will be executed once and will enable Hello module
cp _benchmark/enable_hello_module.php custom/
# Install
php installer.phar.php -sn Benchmark -su http://127.0.0.1 -dh storage/sqlite.db -dn "" -du "" -dp "" -ae admin@test.com -ap 1111 -de SQLite
# Force initial cache filling to avoid any DB access afterwards, this will also enable Hello module
php cli
echo "require \$_SERVER['DOCUMENT_ROOT'].'/php-framework-benchmark/libs/output_data.php';" >> index.php
