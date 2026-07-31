#!/usr/bin/env bash
# Install one-shot dash diagnose+fix entry point, then print URL.
set -euo pipefail
BASE="${1:-$HOME/public_html}"
COMMIT="${BS_FIX_COMMIT:-f01c159}"
# Prefer latest board/fix commits for this file — use branch API fallback
RAW="https://raw.githubusercontent.com/premprakash563-ai/premcursor/cursor/suitecrm-business-service-crm-8700/suitecrm-extension/custom/include/BS/bs_dash_fix.php"
API="https://api.github.com/repos/premprakash563-ai/premcursor/contents/suitecrm-extension/custom/include/BS/bs_dash_fix.php?ref=cursor/suitecrm-business-service-crm-8700"
cd "$BASE"

mkdir -p custom/include/BS
mkdir -p custom/Extension/application/Ext/EntryPointRegistry
mkdir -p custom/application/Ext/EntryPointRegistry

echo "==> Downloading bs_dash_fix.php..."
if ! curl -fsSL "$RAW" -o custom/include/BS/bs_dash_fix.php; then
  curl -fsSL "$API" | php -r '
    $j=json_decode(stream_get_contents(STDIN),true);
    if(empty($j["content"])){fwrite(STDERR,"download failed\n");exit(1);}
    file_put_contents("custom/include/BS/bs_dash_fix.php", base64_decode($j["content"]));
  '
fi

# Register entry point cleanly
python3 - << 'PY'
from pathlib import Path
key = "bs_dash_fix"
snippet = (
    f"$entry_point_registry['{key}'] = array(\n"
    "    'file' => 'custom/include/BS/bs_dash_fix.php',\n"
    "    'auth' => true,\n"
    ");\n"
)
# Extension source
Path("custom/Extension/application/Ext/EntryPointRegistry").mkdir(parents=True, exist_ok=True)
Path("custom/Extension/application/Ext/EntryPointRegistry/bs_dash_fix.php").write_text("<?php\n" + snippet)

ext = Path("custom/application/Ext/EntryPointRegistry/entry_point_registry.ext.php")
text = ext.read_text() if ext.exists() else "<?php\n"
lines=[]; skip=False
for line in text.splitlines():
    if key in line:
        skip=True
        continue
    if skip:
        if line.strip() in (");", ");?>"):
            skip=False
        continue
    lines.append(line)
body="\n".join(lines).strip()
if not body.startswith("<?php"):
    body="<?php\n"+body
body=body.rstrip()+"\n\n"+snippet
ext.parent.mkdir(parents=True, exist_ok=True)
ext.write_text(body)
print("Registered", key)
PY

# Remove dangerous override
if [ -f custom/include/MVC/Controller/entry_point_registry.php ]; then
  mv -f custom/include/MVC/Controller/entry_point_registry.php \
        custom/include/MVC/Controller/entry_point_registry.php.bak.$(date +%s)
  echo "Removed custom entry_point_registry.php override"
fi

# Clear controller cache keys on disk if any
find cache -name '*entry_point*' -delete 2>/dev/null || true
find cache -name '*CONTROLLER*' -delete 2>/dev/null || true
rm -f cache/dashlets/dashlets.php
php -r 'if (function_exists("opcache_reset")) opcache_reset();' 2>/dev/null || true

echo
echo "LOGIN CRM, phir ye kholo:"
echo "https://yoogleconsultancy.in/index.php?entryPoint=bs_dash_fix"
echo
echo "Page pe green/black diagnostic dikhega. Poora text copy karke bhej dena."
echo "Uske baad Home refresh karo."
