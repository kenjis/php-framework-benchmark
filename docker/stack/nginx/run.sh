#!/bin/bash

# This script is run within the nginx:1.7-based container on start

# Fail on any error
set -o errexit

# Copy nginx configuration from app directory to nginx container

# Remove existing config files
if [ "$(ls /etc/nginx/conf.d/)" ]; then
    rm /etc/nginx/conf.d/*.conf
fi

# Copy custom project include files
if [ "$(ls /stack/nginx/conf.d/)" ]; then
    cp -r /stack/nginx/conf.d/* /etc/nginx/conf.d/
fi
cp /stack/nginx/nginx.conf /etc/nginx/nginx.conf

# Run nginx
nginx -g 'daemon off;'
