#!/usr/bin/env bash
make sphinx
while RES=$(inotifywait -e modify doc -r); do
    echo $RES
    make sphinx
done
