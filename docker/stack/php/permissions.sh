#!/usr/bin/env bash

# Show what the script is doing
set -x

# Work around permission errors locally by making sure that "www-data" uses the same uid and gid as the host volume
TARGET_UID=$(stat -c "%u" /public/php-framework-benchmark)
usermod -o -u $TARGET_UID www-data
TARGET_GID=$(stat -c "%g" /public/php-framework-benchmark)
groupmod -o -g $TARGET_GID www-data
