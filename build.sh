#!/usr/bin/env bash
# build.sh
#
# Self-contained build script for the Cohesion repo. This script can be called
# from any directory and will build CI assets.
#
# Usage:
#   ./build.sh

set -euo pipefail

# Determine the directory of this script and set REPO_ROOT to the repo root.
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR" && pwd)"
export REPO_ROOT

# Helper function for logging.
log() {
  echo "[build.sh] $*"
}

# Check for required commands.
command -v npm >/dev/null 2>&1 || { echo >&2 "npm is required but not installed. Aborting."; exit 1; }
command -v node >/dev/null 2>&1 || { echo >&2 "node is required but not installed. Aborting."; exit 1; }

# Fail fast when the runtime does not satisfy package engine constraints.
NODE_MAJOR="$(node -p "process.versions.node.split('.')[0]")"
if [[ "$NODE_MAJOR" -lt 24 ]]; then
  echo >&2 "Node >=24.0.0 is required. Detected: $(node -v)"
  exit 1
fi

# --- CI asset build ---
log "Installing npm dependencies in repo root (npm ci)..."
cd "$REPO_ROOT"
npm ci --no-audit --no-fund

log "Building assets in repo root..."
npm run compile:scss

log "Installing npm dependencies in /apps (npm ci)..."
cd "$REPO_ROOT/apps"
npm ci --no-audit --no-fund

log "Building production assets in /apps..."
npm run production


log "Asset build complete."
