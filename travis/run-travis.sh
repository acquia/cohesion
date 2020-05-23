#!/usr/bin/env bash

set -e

cd "$(dirname "$0")/.."

export TRAVIS_BUILD_DIR=$(pwd)
export NODE_API_PORT=5000
export SCSS_API_PORT=5001

$TRAVIS_BUILD_DIR/travis/cypress.sh base
