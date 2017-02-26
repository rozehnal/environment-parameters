#!/bin/bash

#1. add buildpack to your app https://github.com/weibeld/heroku-buildpack-run
#2. create CONFIGURATION_REPO env variable with https://username:pasword@repo

url=$(cat "$ENV_DIR/CONFIGURATION_REPO")
git clone "$url"|sed -r 's/[ ]+/_/g'
cd peakclimber-com-configuration #modify
pwd
composer install
composer run-script build --no-interaction -- --env=heroku
cp -r ./build/* ../app/config
cd ..
rm -rf ./peakclimber-com-configuration #modify