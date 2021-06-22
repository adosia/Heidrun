#!/bin/bash

echo "Starting heidrun cron runner..."

while [ true ]
do
  php application/artisan schedule:run --verbose --no-interaction &
  sleep 60
done
