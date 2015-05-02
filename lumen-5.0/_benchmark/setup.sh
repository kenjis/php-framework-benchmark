#!/bin/sh

composer install --no-dev --optimize-autoloader
chmod o+w storage/*
chmod o+w storage/framework/*
