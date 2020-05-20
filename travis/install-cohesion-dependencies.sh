#!/bin/bash

cd /app/drupal

cat ./docroot/modules/contrib/dx8/composer.json | jq -r '.require | to_entries[] | "\(.key):\(.value)"' | xargs -L 1 composer require --no-update --no-scripts -n

composer require --no-update --no-scripts -n drush/drush
COMPOSER_MEMORY_LIMIT=-1 composer update --with-all-dependencies -n

ln -sf /app/drupal/vendor/bin/drush /usr/local/bin
ln -sf /app/drupal/vendor/bin/phpunit /usr/local/bin

drush --version
drush status

mkdir -p /app/drupal/docroot/modules/contrib/dx8/e2e-tests/cypress/fixtures

# Update settings.php
echo '$settings["http_client_config"]["timeout"] = 60;' >> /app/drupal/docroot/sites/default/default.settings.php