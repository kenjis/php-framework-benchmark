#!/bin/sh

composer install --no-dev --optimize-autoloader
chmod o+w app/storage/*
