#!/usr/bin/env bash

cd "$(dirname "$0")" || exit; source _includes.sh

(
    # Prepare the module assets
    __coh_write_info "Running yarn production"
    cd $TRAVIS_BUILD_DIR/apps
    rm -rf ./node_modules
    yarn
    yarn production

    # Create cohesion module folder
    cd $TRAVIS_BUILD_DIR
    mkdir -p drupal/{cohesion,cohesion-theme}

    rsync -a . ./drupal/cohesion --exclude drupal --exclude Jenkinsfile --exclude .git_commit --exclude e2e-tests --exclude themes --exclude .gitignore --exclude .git_previous_commit --exclude .git --exclude cohesion-services --exclude apps --exclude json --exclude modules/cohesion_sync/js/src
    rsync -a ./themes/cohesion_theme/* ./drupal/cohesion-theme/

    # Copy the folder over to the drupal container on docker
    __coh_write_info "Copy the folder over to the drupal container on docker"
    docker cp ./drupal/cohesion drupal-client:/app/drupal/docroot/modules/contrib/dx8
    docker cp ./drupal/cohesion-theme drupal-client:/app/drupal/docroot/themes
    docker cp ./cohesion-services/drupal/drupal/install/dx8 drupal-client:/app/drupal/docroot/profiles/contrib

    docker exec drupal-client bash -c 'mv /app/drupal/docroot/modules/contrib/dx8/composer.dev.json /app/drupal/docroot/modules/contrib/dx8/composer.json'

    # Install the cohesion dependencies
    docker exec -i drupal-client bash -c '/app/install-cohesion-dependencies.sh'

    # Run a drupal Site Install and enable the module
    __coh_write_info "Run a drupal Site Install and enable the module"
    docker exec -i drupal-client bash -c 'cd /app/drupal && ./vendor/bin/drush si lightning --account-name=webadmin --account-pass=webadmin --db-url=mysql://webadmin:webadmin@127.0.0.1/drupal --site-name=Lightning -y'
    docker exec -i drupal-client bash -c 'cd /app/drupal && ./vendor/bin/drush en -y cohesion cohesion_sync cohesion_base_styles cohesion_custom_styles cohesion_elements cohesion_style_helpers cohesion_templates cohesion_website_settings example_element request_data_conditions admin_toolbar admin_toolbar_links_access_filter admin_toolbar_tools'
    docker exec -i drupal-client bash -c 'cd /app/drupal && ./vendor/bin/drush theme:enable -y cohesion_theme'
)