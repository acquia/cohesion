#!/usr/bin/env bash

cd "$(dirname "$0")" || exit; source _includes.sh

(
    # Build the Node API + SCSS API
    __coh_write_info "Building the containers for the API and SCSS"
    cd $TRAVIS_BUILD_DIR/cohesion-services/scss-gateway
    docker build --build-arg NODE_ENVIRONMENT=prod --build-arg NODE_ENV=production -t cohesion-node-scss .
    docker rm -f cohesion-node-scss || echo 'No need to stop here ...'
    docker run -d --name cohesion-node-scss -m 512M -p ${SCSS_API_PORT-3001}:3000 cohesion-node-scss:latest
    docker exec cohesion-node-scss bash -c 'cd /node && yarn install'
    docker exec cohesion-node-scss bash -c 'printenv'
    docker restart cohesion-node-scss
    if [[ "$TRAVIS" ]]; then
        NODE_API_CONTAINER_IP="$(docker inspect cohesion-node-scss -f "{{.NetworkSettings.IPAddress}}"):3000"
    else
        NODE_API_CONTAINER_IP="192.168.77.77:${SCSS_API_PORT-3001}"
    fi

    cd $TRAVIS_BUILD_DIR/cohesion-services/dx8-gateway && docker build --build-arg NODE_ENVIRONMENT=prod --build-arg NODE_ENV=production -t cohesion-node-api .
    docker rm -f cohesion-node-api || echo 'No need to stop here ...'
    docker run -d --name cohesion-node-api -m 512M -p ${NODE_API_PORT-3000}:3000 -e SASS_SERVICE_URL=http://$NODE_API_CONTAINER_IP cohesion-node-api:latest
    docker exec cohesion-node-api bash -c 'cd /php && composer install'
    docker exec cohesion-node-api bash -c 'cd /node && yarn install'
    docker exec cohesion-node-api bash -c 'printenv'
    docker restart cohesion-node-api

    sleep 5

    __coh_write_info 'List of docker containers'
    docker ps -a

    # Node API
    __coh_write_info 'Is Node API accessible?'
    curl 127.0.0.1:${NODE_API_PORT-3000} || exit 1
    # SCSS API
    __coh_write_info 'Is Node SCSS accessible?'
    curl 127.0.0.1:${SCSS_API_PORT-3001} || exit 1
)