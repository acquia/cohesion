#!/usr/bin/env bash

# This script should only be run on Travis CI.
[[ "$TRAVIS" ]] || exit 0

cd "$(dirname "$0")" || exit; source _includes.sh

# Install AWS CLI
# This script requires 2 environment variables to be set on Travis
# https://docs.travis-ci.com/user/environment-variables#encrypting-environment-variables
pyenv global 3.7.1
pip install -U pip
pip install awscli

__coh_write_info 'AWS CLI Version'
aws --version

mkdir -p ~/.aws

cat > ~/.aws/credentials << EOL
[default]
aws_access_key_id = ${COH_AWS_ACCESS_KEY_ID}
aws_secret_access_key = ${COH_AWS_SECRET_ACCESS_KEY}
EOL

cat > ~/.aws/config << EOL
[default]
output = json
region = eu-west-1
EOL
