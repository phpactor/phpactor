#!/usr/bin/env bash
for dotfile in `ls doc/dot`; do
    name=${dotfile%.dot}
    dot doc/dot/$dotfile -Tpng > doc/images/$name.png
done
