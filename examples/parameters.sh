#!/bin/bash

if [ "$1" != "" ]; then
    echo "Building parameters for" $1

    git clone https://github.com/rozehnal/environment-parameters-test
    cd environment-parameters-test
    composer install
    composer run-script build --no-interaction -- --env=$1
    cp -r ./build/* ../app/config
    cd ..
    rm -rf ./environment-parameters-test

else
    echo "You must specify the environment"
fi