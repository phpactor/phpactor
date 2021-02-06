#!/usr/bin/env bash
set -e

RETRY_THRESHOLD=${RETRY_THRESHOLD:-5}

echo -e "\n\n"
echo -e "Benchmarking master branch"
echo -e "==========================\n\n"
git fetch origin master  &> /dev/null
git checkout master  &> /dev/null
mv composer.lock composer.lock.pr
composer install --quiet
vendor/bin/phpbench run --report=aggregate --progress=travis --retry-threshold=$RETRY_THRESHOLD --tag=master

echo -e "\n\n"
echo -e "Benchmarking GITHUB_REF and comparing to master"
echo -e "==================================================\n\n"
git checkout - &> /dev/null
mv composer.lock.pr composer.lock
composer install --quiet
vendor/bin/phpbench run --report=aggregate --progress=travis --retry-threshold=$RETRY_THRESHOLD --ref=master 
