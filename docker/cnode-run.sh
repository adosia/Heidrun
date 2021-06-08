#!/bin/bash

./entrypoint.sh &
P1=$!

cd cnode-api && php -S 0.0.0.0:10000 index.php &
P2=$!

wait $P1 $P2
