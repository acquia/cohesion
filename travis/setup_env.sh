#!/usr/bin/env bash

# Fail fast when anything goes wrong
set -euo pipefail

./install_yarn.sh
./install_aws.sh
./install_drupal_container.sh
./install_cohesion_module.sh
./build_api_containers.sh
./build_cypress_env.sh
