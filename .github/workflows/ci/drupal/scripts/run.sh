#!/usr/bin/env bash

set -euo pipefail

# Define run values and metrics placeholders.
LOG_FORMAT="Source:$PIPELINE_ENV DRUPAL:$DRUPAL_VERSION PHP:$PHP_VERSION User:%Us System:%Ss Elapsed:%es CPU:%P Status:%x"

timestamp() {
  date +"Timestamp:%Y-%m-%d %T"
}

# Create a unique file for this build/job.
LOGFILE="$(date +"%Y%m%d-%H%M%S")--$BUILD_ID-$BUILD_NUMBER.log"
touch /tmp/$LOGFILE

./wait.sh
/usr/bin/time --format="$(timestamp) Event:install $LOG_FORMAT" --output=/tmp/$LOGFILE --append ./install.sh

cat /tmp/$LOGFILE
