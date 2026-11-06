#!/usr/bin/env bash
#
# Download the 8.0.x Inspace UAT database export from Google Drive.
# Output: backup/inspace-uat-8.0.x.sql.gz (relative to repo root).
#
# Usage: .agents/skills/reset-uat-database-inspace/download.sh [output_path]

set -euo pipefail

FILE_ID="1PszpR_3vnUaHzrr5-co1izVuySgmkFNz"
OUT="${1:-backup/inspace-uat-8.0.x.sql.gz}"

mkdir -p "$(dirname "$OUT")"

if command -v gdown >/dev/null 2>&1; then
  echo "Downloading via gdown -> $OUT"
  gdown --fuzzy "$FILE_ID" -O "$OUT"
  echo "Done: $OUT"
  exit 0
fi

echo "gdown not found; attempting curl (may fail on large files needing a confirm token)." >&2
echo "Install gdown for a reliable download: pipx install gdown" >&2

COOKIE="$(mktemp)"
PAGE="$(mktemp)"
trap 'rm -f "$COOKIE" "$PAGE"' EXIT

curl -sL -c "$COOKIE" "https://drive.google.com/uc?export=download&id=${FILE_ID}" -o "$PAGE"
CONFIRM="$(sed -rn 's/.*confirm=([0-9A-Za-z_-]+).*/\1/p' "$PAGE" | head -n1 || true)"

if [ -n "$CONFIRM" ]; then
  curl -Lb "$COOKIE" \
    "https://drive.usercontent.google.com/download?export=download&confirm=${CONFIRM}&id=${FILE_ID}" \
    -o "$OUT"
else
  curl -Lb "$COOKIE" \
    "https://drive.google.com/uc?export=download&id=${FILE_ID}" \
    -o "$OUT"
fi

echo "Done: $OUT"
echo "Verify it is a gzip archive (not an HTML error page): file '$OUT'"
