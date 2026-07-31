#!/usr/bin/env bash
# Nuclear Home dashboard fix for retrieve_dash_page HTTP 500
# Embeds files (no CDN dependency for critical PHP).
set -euo pipefail
BASE="${1:-$HOME/public_html}"
COMMIT="${BS_FIX_COMMIT:-0f8c7c8}"
cd "$BASE"

mkdir -p custom/include/BS
mkdir -p custom/Extension/application/Ext/EntryPointRegistry
mkdir -p custom/application/Ext/EntryPointRegistry
mkdir -p cache

echo "==> Fetch safe wrapper + standalone fixer..."
# Try commit-pinned raw; fallback to API
fetch() {
  local rel="$1" dest="$2"
  local url="https://raw.githubusercontent.com/premprakash563-ai/premcursor/cursor/suitecrm-business-service-crm-8700/${rel}"
  if curl -fsSL "$url" -o "$dest"; then
    return 0
  fi
  local api="https://api.github.com/repos/premprakash563-ai/premcursor/contents/${rel}?ref=cursor/suitecrm-business-service-crm-8700"
  curl -fsSL "$api" | php -r '
    $j=json_decode(stream_get_contents(STDIN),true);
    if(empty($j["content"])){fwrite(STDERR,"fail\n");exit(1);}
    file_put_contents($argv[1], base64_decode($j["content"]));
  ' "$dest"
}

fetch "suitecrm-extension/custom/include/BS/retrieve_dash_page_safe.php" "custom/include/BS/retrieve_dash_page_safe.php"
fetch "suitecrm-extension/custom/include/BS/bs_fix_standalone.php" "bs_fix.php"

# Also try board file if missing
if [ ! -f custom/include/BS/dashboard_board.php ]; then
  fetch "suitecrm-extension/custom/include/BS/dashboard_board.php" "custom/include/BS/dashboard_board.php" || true
fi

echo "==> Patch entry_point_registry.ext.php (override retrieve_dash_page)"
python3 - << 'PY'
from pathlib import Path
ext = Path("custom/application/Ext/EntryPointRegistry/entry_point_registry.ext.php")
keys = ("retrieve_dash_page", "bs_operations_board", "bs_dash_fix")
text = ext.read_text() if ext.exists() else "<?php\n"
lines=[]; skip=False
for line in text.splitlines():
    if any(k in line for k in keys):
        skip=True
        continue
    if skip:
        if line.strip() in (");", ");?>"):
            skip=False
        continue
    s=line.strip()
    if s.startswith("'file' => 'custom/include/BS/"):
        continue
    lines.append(line)
body="\n".join(lines).strip()
if not body.startswith("<?php"):
    body="<?php\n"+body
body=body.rstrip()+"\n\n"
body += "$entry_point_registry['retrieve_dash_page'] = array(\n"
body += "    'file' => 'custom/include/BS/retrieve_dash_page_safe.php',\n"
body += "    'auth' => true,\n"
body += ");\n"
body += "$entry_point_registry['bs_operations_board'] = array(\n"
body += "    'file' => 'custom/include/BS/dashboard_board.php',\n"
body += "    'auth' => true,\n"
body += ");\n"
ext.parent.mkdir(parents=True, exist_ok=True)
ext.write_text(body)
Path("custom/Extension/application/Ext/EntryPointRegistry/bs_retrieve_safe.php").write_text(
    "<?php\n"+body.split("<?php",1)[-1] if "retrieve_dash_page" in body else "<?php\n"
)
print("OK registry")
PY

# Remove dangerous override
if [ -f custom/include/MVC/Controller/entry_point_registry.php ]; then
  mv -f custom/include/MVC/Controller/entry_point_registry.php \
        custom/include/MVC/Controller/entry_point_registry.php.bak.$(date +%s)
  echo "Removed broken custom entry_point_registry.php"
fi

# Disable custom dashlets
for f in \
  modules/Home/Dashlets/BS_AdminDashboardDashlet/BS_AdminDashboardDashlet.php \
  modules/BS_Orders/Dashlets/BS_EmployeeWorkloadDashlet/BS_EmployeeWorkloadDashlet.php
do
  [ -f "$f" ] && mv -f "$f" "${f}.off" && echo "Disabled $f"
done

# Clear caches
rm -f cache/dashlets/dashlets.php
rm -rf cache/dashlets/* 2>/dev/null || true
find cache -name '*CONTROLLER*' -delete 2>/dev/null || true
find cache -name '*entry_point*' -delete 2>/dev/null || true

# SQL file
cat > bs_reset_home_dashlets.sql << 'EOF'
DELETE FROM user_preferences WHERE category = 'Home' AND deleted = 0;
EOF

php -r 'if (function_exists("opcache_reset")) opcache_reset();' 2>/dev/null || true

echo
echo "========================================"
echo "AB YE 2 CHEEZ KARO:"
echo
echo "A) Browser (login optional):"
echo "   https://yoogleconsultancy.in/bs_fix.php"
echo "   Plain text dikhega — copy karke bhej dena."
echo
echo "B) phpMyAdmin SQL (agar A me SQL fail ho):"
echo "   DELETE FROM user_preferences WHERE category = 'Home' AND deleted = 0;"
echo
echo "Phir Home: Ctrl+Shift+R"
echo "Baad me delete: rm ~/public_html/bs_fix.php"
echo "========================================"
