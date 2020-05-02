#!/usr/bin/env bash
make html
while RES=$(inotifywait -e modify doc -r); do
    echo $RES
    make html
done
