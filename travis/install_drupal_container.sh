#!/usr/bin/env bash

cd "$(dirname "$0")" || exit; source _includes.sh

(
    # Create the drupal environment using docker
    cd $TRAVIS_BUILD_DIR/travis && docker build -t drupal-client .
    docker rm -f drupal-client || echo 'No need to stop here ...'
    docker run -p 80:80 -d --name drupal-client drupal-client:latest
    docker exec -i drupal-client bash -c '/app/container-stable.sh'

    __coh_write_info 'List of docker containers'
    docker ps -a
)