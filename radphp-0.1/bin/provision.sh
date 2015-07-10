#!/usr/bin/env bash

echo 'DROP DATABASE IF EXISTS radphp_db;' | sudo -u postgres psql
echo "DROP ROLE IF EXISTS radphp_user;" | sudo -u postgres psql
echo "CREATE USER radphp_user WITH PASSWORD 'rad123';" | sudo -u postgres psql
echo "CREATE DATABASE radphp_db;" | sudo -u postgres psql
echo "GRANT ALL PRIVILEGES ON DATABASE radphp_db to radphp_user;" | sudo -u postgres psql
