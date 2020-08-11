#!/usr/bin/env bash

cd "$(dirname "$0")" || exit; source _includes.sh

./setup_env.sh

# Run the linters
__coh_write_info 'Run the linters'
docker exec cohesion-node-api bash -c 'cd /php && composer run-script test'
docker exec cohesion-node-api bash -c 'cd /node && yarn run test'
docker exec cohesion-node-api bash -c 'cd /node && yarn run lint'
docker exec cohesion-node-scss bash -c 'cd /node && yarn run test'
docker exec cohesion-node-scss bash -c 'cd /node && yarn run lint'


__coh_write_info 'Run phpunit'
docker exec drupal-client bash -c 'export SYMFONY_DEPRECATIONS_HELPER=disabled && cd /app/drupal/ && ./vendor/bin/phpunit -c ./docroot/core/phpunit.xml.dist --testsuite=unit --group Cohesion'

__coh_write_info 'Run phpunit on kernel'
docker exec drupal-client bash -c 'export SYMFONY_DEPRECATIONS_HELPER=disabled && cd /app/drupal/docroot/core && ../../vendor/bin/phpunit --testsuite=kernel --group=Cohesion'

__coh_write_info 'Run drupal-check'
docker exec drupal-client bash -c 'cd /app/drupal/docroot/modules/contrib/dx8 && /home/application/tools/drupal-check/vendor/bin/drupal-check *.module *.install *.php ./src ./modules'

