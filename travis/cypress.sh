#!/usr/bin/env bash

cd "$(dirname "$0")" || exit; source _includes.sh

./setup_env.sh

# Run the cypress stuff
(
    cd $TRAVIS_BUILD_DIR/e2e-tests
    if [[ "$TRAVIS" ]]; then
        NODE_API_CONTAINER_IP=$(docker inspect cohesion-node-api -f "{{.NetworkSettings.IPAddress}}")
    else
        NODE_API_CONTAINER_IP='192.168.77.77'
    fi

    __coh_write_info 'Is Node API accessible for Cypress?'
    curl ${NODE_API_CONTAINER_IP}:${NODE_API_PORT-3000} || exit 1


    if [[ $1 = "base" ]]; then
      CYPRESS_containerName=drupal-client CYPRESS_baseUrl=http://127.0.0.1 CYPRESS_apiURL=http://${NODE_API_CONTAINER_IP}:${NODE_API_PORT-3000} node runtests.js setup
    else
      CYPRESS_containerName=drupal-client CYPRESS_baseUrl=http://127.0.0.1 CYPRESS_apiURL=http://${NODE_API_CONTAINER_IP}:${NODE_API_PORT-3000} yarn setup
    fi

    if [[ $? -ne 0 ]]; then
        exit 50
    fi

    CYPRESS_containerName=drupal-client CYPRESS_baseUrl=http://127.0.0.1 CYPRESS_apiURL=http://${NODE_API_CONTAINER_IP}:${NODE_API_PORT-3000} node runtests.js $1


    if [[ $? -ne 0 ]]; then
        exit 50
    fi

    if [[ "$TRAVIS" ]]; then
        if [[ ! -z "$(ls -A ./cypress/videos)" ]]; then
            aws s3 cp ./cypress/videos s3://cypress-screenshots/builds/${TRAVIS_BRANCH}/${TRAVIS_BUILD_NUMBER} --recursive
        fi
        if [[ ! -z "$(ls -A ./cypress/screenshots)" ]]; then
            aws s3 cp ./cypress/screenshots s3://cypress-screenshots/builds/${TRAVIS_BRANCH}/${TRAVIS_BUILD_NUMBER} --recursive
            exit 50
        fi
    fi
)
