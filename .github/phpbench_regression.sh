#!/usr/bin/env bash
set -e

RETRY_THRESHOLD=${RETRY_THRESHOLD:-5}

echo -e "\n\n"
echo -e "Benchmarking master branch"
echo -e "==========================\n\n"
git fetch origin master
git checkout master
mv composer.lock composer.lock.pr
composer install --quiet
vendor/bin/phpbench run --report=aggregate_compact --progress=travis --retry-threshold=$RETRY_THRESHOLD --tag=master

echo -e "\n\n"
echo -e "Benchmarking GITHUB_REF and comparing to master"
echo -e "==================================================\n\n"
git checkout -
git status
mv composer.lock.pr composer.lock
composer install --quiet
vendor/bin/phpbench run --report=aggregate_compact --progress=travis --retry-threshold=$RETRY_THRESHOLD --uuid=tag:master 
