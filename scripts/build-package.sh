#!/usr/bin/env bash
# Build Module Loader zip for SuiteCRM 7.15.1
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
OUT="${1:-$ROOT/BS_BusinessServiceCRM-0.4.2.zip}"
cd "$ROOT/suitecrm-extension"
rm -f "$OUT"
zip -r "$OUT" \
  manifest.php \
  README.md \
  modules \
  lib \
  custom \
  install \
  scripts \
  -x '*.DS_Store*'
echo "Built: $OUT"
