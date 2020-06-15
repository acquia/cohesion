#!/usr/bin/env bash

# This script should only be run on Travis CI.
[[ "$TRAVIS" ]] || exit 0

cd "$(dirname "$0")" || exit; source _includes.sh

# Install the right version of yarn
__coh_write_info "Installing Yarn"
curl -o- -L https://yarnpkg.com/install.sh | bash -s -- --version 1.22.0
export PATH="$HOME/.yarn/bin:$PATH"
