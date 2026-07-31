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
# Extension source
cat > custom/Extension/application/Ext/EntryPointRegistry/bs_dashboard.php << 'EOF'
<?php
$entry_point_registry['bs_operations_board'] = array(
    'file' => 'custom/include/BS/dashboard_board.php',
    'auth' => true,
);
EOF

# Merged ext file — clean rewrite of our key only (no grep debris)
python3 - << 'PY'
from pathlib import Path
ext = Path("custom/application/Ext/EntryPointRegistry/entry_point_registry.ext.php")
text = ext.read_text() if ext.exists() else "<?php\n"
lines = []
skip_block = False
for line in text.splitlines():
    if "bs_operations_board" in line:
        skip_block = True
        continue
    if skip_block:
        if line.strip() in (");", ");?>"):
            skip_block = False
        continue
    # drop orphaned debris from older grep-based deploys
    s = line.strip()
    if s.startswith("'file' => 'custom/include/BS/dashboard_board.php'"):
        continue
    lines.append(line)
body = "\n".join(lines).strip()
if not body.startswith("<?php"):
    body = "<?php\n" + body
body = body.rstrip() + "\n\n"
body += "$entry_point_registry['bs_operations_board'] = array(\n"
body += "    'file' => 'custom/include/BS/dashboard_board.php',\n"
body += "    'auth' => true,\n"
body += ");\n"
ext.parent.mkdir(parents=True, exist_ok=True)
ext.write_text(body)
print("Wrote", ext)
PY

# NEVER create custom/include/MVC/Controller/entry_point_registry.php
# A bad grep/append there can syntax-break ALL entry points (incl. retrieve_dash_page).
if [ -f custom/include/MVC/Controller/entry_point_registry.php ]; then
  mv -f custom/include/MVC/Controller/entry_point_registry.php \
        custom/include/MVC/Controller/entry_point_registry.php.bak.$(date +%s)
  echo "Removed dangerous custom entry_point_registry.php override"
fi
rm -f custom/application/Ext/EntryPointRegistry/bs_operations_board.ext.php

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
