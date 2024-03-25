#!/usr/bin/env bash

set -euo pipefail

echo "Site Studio rebuild..."
drush --root=/opt/drupal/web cohesion:rebuild -v
