#!/bin/bash

# This script is run within the php containers on start

# Fail on any error
set -o errexit

# Show what the script is doing
set -x

# Fix permissions
$(dirname $0)/permissions.sh

# HHVM cli
cp $PHP_CONF_DIR/fpm/php.ini /etc/hhvm/php.ini # Note: The HHVM cli is here configured to use default php.ini contents that came with php5-fpm
cat /stack/php/hhvm/php.ini >> /etc/hhvm/php.ini # Default hhvm-specific config used for HHVM cli and HHVM fastcgi server
for configfile in /stack/php/conf.d/*; do
    cat $configfile >> /etc/hhvm/php.ini
done

# HHVM fastcgi server
cp $PHP_CONF_DIR/fpm/php.ini /etc/hhvm/server.ini # Note: The HHVM fastcgi server is here configured to use default php.ini contents that came with php5-fpm
cat /stack/php/hhvm/php.ini >> /etc/hhvm/server.ini # Default hhvm-specific config used for HHVM cli and HHVM fastcgi server
cat /stack/php/hhvm/server.ini >> /etc/hhvm/server.ini # Default hhvm-specific config used for HHVM fastcgi server
for configfile in /stack/php/conf.d/*; do
    cat $configfile >> /etc/hhvm/server.ini
done

# Uncomment in order to use hhvm for cli
/usr/bin/update-alternatives --install /usr/bin/php php /usr/bin/hhvm 60

# Run HHVM in server mode
hhvm -m server -c /etc/hhvm/server.ini -u www-data
