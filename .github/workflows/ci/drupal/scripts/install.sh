#!/usr/bin/env bash

set -euo pipefail

DRUPAL_ROOT=/opt/drupal/web

composer install --no-interaction

if [ -d "${DRUPAL_ROOT}/sites/default/files" ]; then
  rm -Rf ${DRUPAL_ROOT}/sites/default/files
fi

echo "Installing Drupal..."
drush --root=${DRUPAL_ROOT} si cohesion_install --db-url=mysql://root:${MARIADB_ROOT_PASSWORD}@database:3306/${MYSQL_DATABASE} --account-name=${DRUPAL_USER} --account-pass=${DRUPAL_PWD} -y -v

echo "\$settings['dx8_editable_api_url'] = TRUE;" >> ${DRUPAL_ROOT}/sites/default/settings.php

echo "Set Site Studio creds..."
drush --root=${DRUPAL_ROOT} cset cohesion.settings api_url ${COH_API_URL} -y

echo "Create a DB dump before import"
drush --root=${DRUPAL_ROOT} sql-dump > /tmp/drupal-bak-clean.sql

echo "Site Studio setup..."
drush --root=${DRUPAL_ROOT} cohesion:import -v

echo "Install original default packages..."
drush --root=${DRUPAL_ROOT} en sitestudio_categories sitestudio_colours -y -v

drush --root=${DRUPAL_ROOT} status
echo "Create a DB dump of the fresh site install"
drush --root=${DRUPAL_ROOT} sql-dump > /tmp/drupal-bak-imported.sql

chown -R www-data:www-data ${DRUPAL_ROOT}/sites

echo "Snapshot the filesystem..."
if [ -d "/tmp/drupal-files-imported" ]; then
  rm -Rf /tmp/drupal-files-imported
fi

# Copy files preserving ownership
cp -rp ${DRUPAL_ROOT}/sites/default/files /tmp/drupal-files-imported

#    drush --root=${DRUPAL_ROOT} en sitestudio_uikit --debug -y -v
#    echo "Create a DB dump with UI kit installed"
#    drush --root=${DRUPAL_ROOT} sql-dump > /tmp/drupal-uikit.sql
