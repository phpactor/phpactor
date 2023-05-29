#!/usr/bin/env bash

set -e

composer install --no-dev
mkdir -p build
cd build

wget -O box.phar https://github.com/box-project/box/releases/download/4.3.8/box.phar
php box.phar compile -c ../box.json

cd -

