#!/usr/bin/env bash

cd "$(dirname "$0")" || exit; source _includes.sh

(
    # Get the Cypress environment ready
    __coh_write_info 'Cypress environment'
    cd $TRAVIS_BUILD_DIR/e2e-tests
    rm -rf ./node_modules
    npm install
    cypress install

    mv ./cypress_jenkins.json ./cypress.json
    mv ./cypress/support/docker-command.js ./cypress/support/vagrant-command.js
    docker cp $TRAVIS_BUILD_DIR/e2e-tests/cypress/fixtures/lightning.info.yml drupal-client:/app/drupal/docroot/modules/contrib/dx8/e2e-tests/cypress/fixtures/lightning.info.yml
    docker cp $TRAVIS_BUILD_DIR/e2e-tests/cypress/fixtures/phpunit.xml drupal-client:/app/drupal/docroot/core/phpunit.xml

    docker exec -i drupal-client bash -c 'chown -R www-data:www-data /app/drupal/docroot'
    docker exec -i drupal-client bash -c 'chmod -R 0777 /app/drupal/docroot/sites/default/files'
    docker exec -i drupal-client bash -c 'echo "\$settings[\"dx8_editable_api_url\"] = TRUE;" >> /app/drupal/docroot/sites/default/settings.php'


    # Copy sql dump file from scenarios
    DUMP_TO_USE=$(cat ../cohesion.info.yml | grep version | grep -oP '[0-9]+.[0-9]+')

    __coh_write_info "Trying to find the dump for version $DUMP_TO_USE"

    aws s3 ls "s3://coh-jenkins-backup/scenarios-db-backup/desktop-dump-${DUMP_TO_USE}.sql"

    if [[ $? = "0" ]]; then
        aws s3 cp "s3://coh-jenkins-backup/scenarios-db-backup/desktop-dump-${DUMP_TO_USE}.sql" ./desktop-scenarios.sql
        aws s3 cp "s3://coh-jenkins-backup/scenarios-db-backup/mobile-dump-${DUMP_TO_USE}.sql" ./mobile-scenarios.sql
    else
        aws s3 cp "s3://coh-jenkins-backup/scenarios-db-backup/desktop-dump-develop.sql" ./desktop-scenarios.sql
        aws s3 cp "s3://coh-jenkins-backup/scenarios-db-backup/mobile-dump-develop.sql" ./mobile-scenarios.sql
    fi

    ls -al

    docker exec -i drupal-client bash -c 'rm -f /app/drupal/docroot/modules/contrib/dx8/e2e-tests/cypress/fixtures/scenarios/scenarios.sql'
    docker exec -i drupal-client bash -c 'mkdir -p /app/drupal/docroot/modules/contrib/dx8/e2e-tests/cypress/fixtures/scenarios'
    docker cp ./desktop-scenarios.sql drupal-client:/app/drupal/docroot/modules/contrib/dx8/e2e-tests/cypress/fixtures/scenarios/desktop-scenarios.sql
    docker cp ./mobile-scenarios.sql drupal-client:/app/drupal/docroot/modules/contrib/dx8/e2e-tests/cypress/fixtures/scenarios/mobile-scenarios.sql
)