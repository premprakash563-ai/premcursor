#!/usr/bin/env bash
# Fix: Home dashboard spinner — console shows retrieve_dash_page → HTTP 500
# Run: bash fix-home-dashboard.sh
set -euo pipefail
BASE="${1:-$HOME/public_html}"
cd "$BASE"

echo "==> 1) Remove broken custom entry_point_registry override (common 500 cause)"
if [ -f custom/include/MVC/Controller/entry_point_registry.php ]; then
  mv -f custom/include/MVC/Controller/entry_point_registry.php \
        custom/include/MVC/Controller/entry_point_registry.php.bak.$(date +%s)
  echo "    Moved custom entry_point_registry.php out of the way"
fi

echo "==> 2) Rewrite clean EntryPointRegistry ext (only bs_operations_board add)"
mkdir -p custom/Extension/application/Ext/EntryPointRegistry
mkdir -p custom/application/Ext/EntryPointRegistry

# Extension source
cat > custom/Extension/application/Ext/EntryPointRegistry/bs_dashboard.php << 'EOF'
<?php
$entry_point_registry['bs_operations_board'] = array(
    'file' => 'custom/include/BS/dashboard_board.php',
    'auth' => true,
);
EOF

# Merged file SuiteCRM loads — KEEP other entries if present, strip broken leftovers
EXT="custom/application/Ext/EntryPointRegistry/entry_point_registry.ext.php"
python3 - << 'PY'
from pathlib import Path
ext = Path("custom/application/Ext/EntryPointRegistry/entry_point_registry.ext.php")
text = ext.read_text() if ext.exists() else "<?php\n"
# Drop any prior bs_operations_board related debris / duplicate php opens after first
lines = []
skip_block = False
for line in text.splitlines():
    if "bs_operations_board" in line:
        skip_block = True
        continue
    if skip_block:
        # skip until closing ); of the array assignment
        if line.strip() == ");" or line.strip() == ");?>":
            skip_block = False
        continue
    lines.append(line)
# Ensure starts with <?php
body = "\n".join(lines).strip()
if not body.startswith("<?php"):
    body = "<?php\n" + body
# Remove orphaned auth/file lines that can remain from bad greps
clean = []
for line in body.splitlines():
    s = line.strip()
    if s.startswith("'file' => 'custom/include/BS/dashboard_board.php'"):
        continue
    if s == "'auth' => true," and clean and "dashboard_board" in "\n".join(clean[-3:]):
        continue
    clean.append(line)
body = "\n".join(clean).rstrip() + "\n"
body += "\n$entry_point_registry['bs_operations_board'] = array(\n"
body += "    'file' => 'custom/include/BS/dashboard_board.php',\n"
body += "    'auth' => true,\n"
body += ");\n"
ext.write_text(body)
print("    Wrote", ext)
PY

# Remove stray per-file ext that is not the merged name (harmless but tidy)
rm -f custom/application/Ext/EntryPointRegistry/bs_operations_board.ext.php

echo "==> 3) Disable custom Home dashlets (prefs may still reference them)"
for f in \
  modules/Home/Dashlets/BS_AdminDashboardDashlet/BS_AdminDashboardDashlet.php \
  modules/BS_Orders/Dashlets/BS_EmployeeWorkloadDashlet/BS_EmployeeWorkloadDashlet.php
do
  if [ -f "$f" ]; then
    mv -f "$f" "${f}.off"
    echo "    Disabled $f"
  fi
done

echo "==> 4) Clear dashlet + controller caches"
rm -f cache/dashlets/dashlets.php
rm -rf cache/dashlets/* 2>/dev/null || true
rm -rf cache/smarty/templates_c/* 2>/dev/null || true
rm -rf cache/modules/Home/* 2>/dev/null || true
# Sugar file cache may hold CONTROLLER_entry_point_registry_*
find cache -name '*entry_point*' -delete 2>/dev/null || true
find cache -name '*CONTROLLER*' -delete 2>/dev/null || true

echo "==> 5) Theme style.css safety"
mkdir -p cache/themes/SuiteP/Dawn
if [ ! -s cache/themes/SuiteP/Dawn/style.css ]; then
  if [ -f themes/SuiteP/css/Dawn/style.css ]; then
    cp -f themes/SuiteP/css/Dawn/style.css cache/themes/SuiteP/Dawn/style.css
  fi
fi
chmod -R 775 cache || true

echo "==> 6) Write SQL to reset Home dash prefs"
SQL_FILE="$BASE/bs_reset_home_dashlets.sql"
cat > "$SQL_FILE" << 'EOF'
-- phpMyAdmin: select SuiteCRM DB, then run this
DELETE FROM user_preferences
WHERE category = 'Home'
  AND deleted = 0;
EOF
echo "    $SQL_FILE"

php -r 'if (function_exists("opcache_reset")) { opcache_reset(); echo "opcache cleared\n"; }' 2>/dev/null || true

echo
echo "============================================"
echo "FILES DONE. Ab ZAROORI:"
echo "1) phpMyAdmin me SQL chalao: bs_reset_home_dashlets.sql"
echo "2) Logout / Login"
echo "3) Home hard refresh (Ctrl+Shift+R)"
echo "4) Console me retrieve_dash_page ab 200 hona chahiye"
echo
echo "Client board (alag page):"
echo "https://yoogleconsultancy.in/index.php?entryPoint=bs_operations_board"
echo "============================================"
