#!/usr/bin/env bash
set -ex

if [ "$AUTOMIGRATE" = "true" ]; then
    php artisan migrate --force
    exit 0
else
    echo "Automigrate is disabled. Skipping migration."
fi
