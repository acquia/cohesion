#!/usr/bin/env bash


if [[ "ISOLATED_RECOMMENDED" = "$ORCA_JOB" || "DEPRECATED_CODE_SCAN" = "$ORCA_JOB" || "INTEGRATED_DEV" = "$ORCA_JOB" || "ISOLATED_DEV" = "$ORCA_JOB"  || "D9_READINESS" = "$ORCA_JOB" || "INTEGRATED_RECOMMENDED" = "$ORCA_JOB" || "CORE_PREVIOUS" = "$ORCA_JOB" || "CORE_NEXT" = "$ORCA_JOB" ]]; then  # Add the physical dependency to the codebase.
  cd "$TRAVIS_BUILD_DIR/../orca-build"
  composer require drupal/acquia_contenthub:^3.3.0
  composer require drupal/tmgmt:^1.10

  # Install Drupal modules and themes.
  cd docroot
  ../vendor/bin/drush pm:enable -y acquia_contenthub
  ../vendor/bin/drush pm:enable -y tmgmt

  # Backup the fixture state for ORCA's automatic resets between tests.
  ../../orca/bin/orca fixture:backup -f

  # Copy cohesion.services.yml file to enable template storage in database.
  cp modules/contrib/cohesion/tests/cohesion.services.yml modules/contrib/cohesion/cohesion.services.yml

fi
