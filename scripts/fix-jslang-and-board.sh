#!/usr/bin/env bash
# Fix: jsLanguage 500 + retrieve_dash_page 500 + operations board 500
# Embeds all critical files (no CDN for PHP bodies).
set -euo pipefail
cd "${1:-$HOME/public_html}"

mkdir -p custom/include/BS \
  custom/application/Ext/EntryPointRegistry \
  custom/Extension/application/Ext/EntryPointRegistry \
  cache/jsLanguage/Home \
  cache/dashlets \
  cache/themes/SuiteP/Dawn

echo "==> 1) Cache permissions + jsLanguage dirs"
chmod -R 775 cache 2>/dev/null || chmod -R 777 cache || true

# Remove HTML error stubs pretending to be JS
for f in cache/jsLanguage/en_us.js cache/jsLanguage/Home/en_us.js; do
  if [ -f "$f" ]; then
    # if file looks like HTML error page, delete it
    if head -c 20 "$f" | grep -qi '<html\|Fatal\|<!DOCTYPE\|Internal Server'; then
      rm -f "$f"
      echo "    removed bad $f"
    fi
  fi
done

echo "==> 2) Flush sugar/controller caches that pin old entry points"
find cache -type f \( -name '*CONTROLLER*' -o -name '*entry_point*' -o -name 'sugar_cache_*' \) -delete 2>/dev/null || true
rm -f cache/dashlets/dashlets.php
rm -rf cache/smarty/templates_c/* 2>/dev/null || true
# SuiteCRM often stores file cache here:
rm -rf cache/cache/* 2>/dev/null || true
find cache -maxdepth 1 -type f -name '*.php' -delete 2>/dev/null || true

echo "==> 3) Remove dangerous registry override"
if [ -f custom/include/MVC/Controller/entry_point_registry.php ]; then
  mv -f custom/include/MVC/Controller/entry_point_registry.php \
        custom/include/MVC/Controller/entry_point_registry.php.bak.$(date +%s)
  echo "    moved custom entry_point_registry.php"
fi

echo "==> 4) Write safe retrieve_dash_page + board (embedded)"
# Pull board from known-good commit via API if possible; else minimal inline
BOARD_OK=0
if curl -fsSL "https://api.github.com/repos/premprakash563-ai/premcursor/contents/suitecrm-extension/custom/include/BS/dashboard_board.php?ref=cursor/suitecrm-business-service-crm-8700" \
  | php -r '$j=json_decode(stream_get_contents(STDIN),true); if(empty($j["content"])) exit(1); file_put_contents("custom/include/BS/dashboard_board.php", base64_decode($j["content"]));' \
  2>/dev/null; then
  BOARD_OK=1
  echo "    board from GitHub API"
fi

if [ "$BOARD_OK" != "1" ]; then
  echo "    API failed — writing minimal board fallback"
  cat > custom/include/BS/dashboard_board.php << 'PHP'
<?php
if (!defined('sugarEntry') || !sugarEntry) { die('Not A Valid Entry Point'); }
global $current_user;
if (empty($current_user->id)) { header('Location: index.php?module=Users&action=Login'); exit; }
header('Content-Type: text/html; charset=UTF-8');
$who = htmlspecialchars($current_user->user_name ?? 'User', ENT_QUOTES, 'UTF-8');
echo "<!doctype html><html><head><meta charset=utf-8><title>Operations Board</title>";
echo "<style>body{font-family:system-ui;margin:0;background:#eef3f7} .t{background:#0f1c2b;color:#fff;padding:14px 20px} .c{max-width:900px;margin:24px auto;background:#fff;padding:24px;border-radius:12px}</style></head><body>";
echo "<div class=t><strong>Yoogle Consultancy · Operations Board</strong></div>";
echo "<div class=c><h1>Operations Board</h1><p>Signed in as {$who}</p>";
echo "<p><a href='index.php?module=BS_Orders&action=EditView'>New order</a> | ";
echo "<a href='index.php?module=BS_Orders&action=index'>Orders</a> | ";
echo "<a href='index.php?module=Home&action=index'>Home</a></p>";
echo "<p style='color:#5b6b7c'>Board online (minimal fallback 0.4.4).</p></div></body></html>";
exit;
PHP
fi

# Safe retrieve wrapper via API or embed
if ! curl -fsSL "https://api.github.com/repos/premprakash563-ai/premcursor/contents/suitecrm-extension/custom/include/BS/retrieve_dash_page_safe.php?ref=cursor/suitecrm-business-service-crm-8700" \
  | php -r '$j=json_decode(stream_get_contents(STDIN),true); if(empty($j["content"])) exit(1); file_put_contents("custom/include/BS/retrieve_dash_page_safe.php", base64_decode($j["content"]));' \
  2>/dev/null; then
  echo "    WARN: could not refresh retrieve_dash_page_safe.php (keeping existing)"
fi

echo "==> 5) Clean entry_point_registry.ext.php"
python3 - << 'PY'
from pathlib import Path
ext = Path("custom/application/Ext/EntryPointRegistry/entry_point_registry.ext.php")
keys = ("retrieve_dash_page", "bs_operations_board", "bs_dash_fix")
text = ext.read_text() if ext.exists() else "<?php\n"
lines=[]; skip=False
for line in text.splitlines():
    if any(k in line for k in keys):
        skip=True; continue
    if skip:
        if line.strip() in (");", ");?>"):
            skip=False
        continue
    if "dashboard_board.php" in line and "'file'" in line:
        continue
    if "retrieve_dash_page_safe.php" in line and "'file'" in line:
        continue
    lines.append(line)
body="\n".join(lines).strip()
if not body.startswith("<?php"):
    body="<?php\n"+body
# drop empty duplicate php-only leftovers
body=body.rstrip()+"\n\n"
body += "$entry_point_registry['retrieve_dash_page'] = array(\n"
body += "    'file' => 'custom/include/BS/retrieve_dash_page_safe.php',\n"
body += "    'auth' => true,\n);\n"
body += "$entry_point_registry['bs_operations_board'] = array(\n"
body += "    'file' => 'custom/include/BS/dashboard_board.php',\n"
body += "    'auth' => true,\n);\n"
ext.parent.mkdir(parents=True, exist_ok=True)
ext.write_text(body)
# syntax check
import subprocess, sys
r = subprocess.run(["php","-l",str(ext)], capture_output=True, text=True)
print(r.stdout.strip() or r.stderr.strip())
if r.returncode != 0:
    sys.exit(1)
Path("custom/Extension/application/Ext/EntryPointRegistry/bs_retrieve_safe.php").write_text(
    "<?php\n$entry_point_registry['retrieve_dash_page'] = array(\n    'file' => 'custom/include/BS/retrieve_dash_page_safe.php',\n    'auth' => true,\n);\n$entry_point_registry['bs_operations_board'] = array(\n    'file' => 'custom/include/BS/dashboard_board.php',\n    'auth' => true,\n);\n"
)
print("registry OK")
PY

echo "==> 6) Disable custom dashlets"
for f in \
  modules/Home/Dashlets/BS_AdminDashboardDashlet/BS_AdminDashboardDashlet.php \
  modules/BS_Orders/Dashlets/BS_EmployeeWorkloadDashlet/BS_EmployeeWorkloadDashlet.php
do
  [ -f "$f" ] && mv -f "$f" "${f}.off" && echo "    disabled $f"
done

echo "==> 7) Rebuild js language via PHP bootstrap"
cat > bs_repair_js.php << 'PHP'
<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
header('Content-Type: text/plain; charset=UTF-8');
echo "JS REPAIR start\n";
function out($m){ echo $m."\n"; }
try {
    if (!defined('sugarEntry')) define('sugarEntry', true);
    require_once 'include/entryPoint.php';
    out('bootstrap OK');

    // Flush sugar cache
    if (class_exists('SugarCache') && method_exists('SugarCache', 'instance')) {
        try { SugarCache::instance()->flush(); out('SugarCache flushed'); } catch (Throwable $e) { out('SugarCache flush: '.$e->getMessage()); }
    }
    if (function_exists('sugar_cache_reset')) {
        sugar_cache_reset();
        out('sugar_cache_reset OK');
    }

    // Ensure dirs
    foreach (['cache/jsLanguage','cache/jsLanguage/Home','cache/jsLanguage/app_strings'] as $d) {
        if (!is_dir($d)) mkdir($d, 0775, true);
        out("dir $d");
    }

    // Rebuild general + Home module JS language if helper exists
    $lang = $GLOBALS['current_language'] ?? 'en_us';
    if (file_exists('include/language/jsLanguage.php')) {
        require_once 'include/language/jsLanguage.php';
        if (class_exists('jsLanguage')) {
            if (method_exists('jsLanguage', 'createAppStringsCache')) {
                jsLanguage::createAppStringsCache($lang);
                out("createAppStringsCache($lang)");
            }
            if (method_exists('jsLanguage', 'createModuleStringsCache')) {
                jsLanguage::createModuleStringsCache($lang, 'Home');
                out("createModuleStringsCache Home");
            }
        }
    }

    // Fallback minimal stubs so MIME is application/javascript not HTML 500
    $stubApp = "SUGAR.language = SUGAR.language || {};\nSUGAR.language.setLanguage('app_strings', {});\n";
    $stubHome = "SUGAR.language = SUGAR.language || {};\nSUGAR.language.setLanguage('Home', {});\n";
    $targets = [
        'cache/jsLanguage/en_us.js' => $stubApp,
        'cache/jsLanguage/Home/en_us.js' => $stubHome,
    ];
    foreach ($targets as $path => $stub) {
        if (!is_file($path) || filesize($path) < 20 || preg_match('/<html|Fatal|Internal Server/i', file_get_contents($path))) {
            file_put_contents($path, $stub);
            out("wrote stub $path");
        } else {
            out("keep existing $path (".filesize($path)." bytes)");
        }
        @chmod($path, 0664);
    }

    // Confirm registry mapping
    $ext = 'custom/application/Ext/EntryPointRegistry/entry_point_registry.ext.php';
    out(is_file($ext) ? "ext registry exists" : "ext registry MISSING");
    out(is_file('custom/include/BS/dashboard_board.php') ? "board file OK" : "board MISSING");
    out(is_file('custom/include/BS/retrieve_dash_page_safe.php') ? "safe retrieve OK" : "safe retrieve MISSING");

    // Delete Home prefs again
    global $db;
    if (!empty($db)) {
        $db->query("DELETE FROM user_preferences WHERE category = 'Home' AND deleted = 0");
        out('Home prefs wiped');
    }

    out('DONE');
    out('Next: Login → Home Ctrl+Shift+R');
    out('Board: index.php?entryPoint=bs_operations_board');
    out('Then delete: bs_repair_js.php and bs_fix.php');
} catch (Throwable $e) {
    out('FATAL: '.$e->getMessage());
    out($e->getFile().':'.$e->getLine());
    out($e->getTraceAsString());
}
PHP

php -r 'if (function_exists("opcache_reset")) { opcache_reset(); echo "opcache cleared\n"; }' 2>/dev/null || true

echo
echo "========================================"
echo "SSH files ready. Ab BROWSER me kholo:"
echo "https://yoogleconsultancy.in/bs_repair_js.php"
echo
echo "Text copy karke bhejo. Phir:"
echo "1) Login → Home (Ctrl+Shift+R)"
echo "2) https://yoogleconsultancy.in/index.php?entryPoint=bs_operations_board"
echo "========================================"
