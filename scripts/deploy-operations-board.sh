#!/usr/bin/env bash
# Force-fix Operations Board on SuiteCRM server (run inside public_html or pass path).
# Avoids SugarView "There is no action by that name: index".
set -euo pipefail
BASE="${1:-$HOME/public_html}"
# Pin to commit SHA — raw.githubusercontent.com branch URLs can CDN-cache old files
COMMIT="${BS_BOARD_COMMIT:-4e348ee}"
REPO_RAW="https://raw.githubusercontent.com/premprakash563-ai/premcursor/${COMMIT}/suitecrm-extension"
API_FILE="https://api.github.com/repos/premprakash563-ai/premcursor/contents/suitecrm-extension/custom/include/BS/dashboard_board.php?ref=${COMMIT}"
cd "$BASE"

mkdir -p custom/include/BS
mkdir -p custom/Extension/application/Ext/EntryPointRegistry
mkdir -p custom/application/Ext/EntryPointRegistry
mkdir -p custom/include/MVC/Controller
mkdir -p modules/BS_Dashboard

echo "==> Downloading board PHP (self-contained, commit ${COMMIT})..."
if ! curl -fsSL "$REPO_RAW/custom/include/BS/dashboard_board.php" -o custom/include/BS/dashboard_board.php; then
  echo "raw CDN failed, trying GitHub API..."
  curl -fsSL "$API_FILE" | php -r '
    $j = json_decode(stream_get_contents(STDIN), true);
    if (empty($j["content"])) { fwrite(STDERR, "API download failed\n"); exit(1); }
    file_put_contents("custom/include/BS/dashboard_board.php", base64_decode($j["content"]));
  '
fi

# Keep renderer/controller in sync (optional; board no longer depends on them)
curl -fsSL "$REPO_RAW/modules/BS_Dashboard/BoardRenderer.php" -o modules/BS_Dashboard/BoardRenderer.php || true
curl -fsSL "$REPO_RAW/modules/BS_Dashboard/controller.php" -o modules/BS_Dashboard/controller.php || true

echo "==> Registering entry point (Repair NOT required)..."
REG_SNIPPET="<?php
\$entry_point_registry['bs_operations_board'] = array(
    'file' => 'custom/include/BS/dashboard_board.php',
    'auth' => true,
);
"

# Extension source (for future Quick Repair)
printf '%s\n' "$REG_SNIPPET" > custom/Extension/application/Ext/EntryPointRegistry/bs_dashboard.php

# Merged ext file SuiteCRM actually loads
EXT_FILE="custom/application/Ext/EntryPointRegistry/entry_point_registry.ext.php"
mkdir -p "$(dirname "$EXT_FILE")"
if [ -f "$EXT_FILE" ]; then
  # Remove any previous bs_operations_board block, then append fresh
  grep -v "bs_operations_board" "$EXT_FILE" > "${EXT_FILE}.tmp" || true
  mv "${EXT_FILE}.tmp" "$EXT_FILE"
  printf '%s\n' "$REG_SNIPPET" >> "$EXT_FILE"
else
  printf '%s\n' "$REG_SNIPPET" > "$EXT_FILE"
fi

# Also write standalone .ext.php (some hosts load all files in folder)
printf '%s\n' "$REG_SNIPPET" > custom/application/Ext/EntryPointRegistry/bs_operations_board.ext.php

# Legacy fallback registry (loaded by older Sugar paths)
LEGACY="custom/include/MVC/Controller/entry_point_registry.php"
if [ -f include/MVC/Controller/entry_point_registry.php ]; then
  cp -f include/MVC/Controller/entry_point_registry.php "$LEGACY"
  # Strip previous custom lines then append
  grep -v "bs_operations_board" "$LEGACY" > "${LEGACY}.tmp" || true
  mv "${LEGACY}.tmp" "$LEGACY"
  printf '%s\n' "$REG_SNIPPET" >> "$LEGACY"
fi

# Verify board file has the fix marker (must be 0.4.3 self-contained)
if ! grep -q "Custom Operations Board (0.4.3)" custom/include/BS/dashboard_board.php; then
  echo "ERROR: board file is NOT 0.4.3. Aborting."
  echo "First lines:"
  head -n 12 custom/include/BS/dashboard_board.php
  exit 1
fi
if grep -q "require_once 'include/entryPoint.php'" custom/include/BS/dashboard_board.php; then
  echo "ERROR: old bootstrap still present. Aborting."
  exit 1
fi
echo "OK: board file is self-contained 0.4.3 (no SugarView)"

# Theme cache safety
if [ ! -s cache/themes/SuiteP/Dawn/style.css ]; then
  mkdir -p cache/themes/SuiteP/Dawn
  chmod -R 775 cache || true
  if [ -f themes/SuiteP/css/Dawn/style.css ]; then
    cp -f themes/SuiteP/css/Dawn/style.css cache/themes/SuiteP/Dawn/style.css
  fi
fi

# Clear PHP opcache if CLI available
php -r 'if (function_exists("opcache_reset")) { opcache_reset(); echo "opcache cleared\n"; }' 2>/dev/null || true

echo
echo "DONE. Login CRM, then open:"
echo "https://yoogleconsultancy.in/index.php?entryPoint=bs_operations_board"
echo
echo "Footer pe 'Custom Operations Board (0.4.3)' dikhna chahiye."
echo "Agar purana error aaye: Ctrl+Shift+R hard refresh, phir Logout/Login."
