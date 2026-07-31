#!/usr/bin/env bash
# Writes bs_repair_js.php + board + safe retrieve + registry (fully offline bodies via GitHub API only for board if needed)
set -euo pipefail
cd ~/public_html

mkdir -p custom/include/BS cache/jsLanguage/Home \
  custom/application/Ext/EntryPointRegistry \
  custom/Extension/application/Ext/EntryPointRegistry
chmod -R 775 cache 2>/dev/null || chmod -R 777 cache || true

echo "==> Writing bs_repair_js.php"
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

    if (class_exists('SugarCache') && method_exists('SugarCache', 'instance')) {
        try { SugarCache::instance()->flush(); out('SugarCache flushed'); } catch (Throwable $e) { out('SugarCache flush: '.$e->getMessage()); }
    }
    if (function_exists('sugar_cache_reset')) { sugar_cache_reset(); out('sugar_cache_reset OK'); }

    foreach (['cache/jsLanguage','cache/jsLanguage/Home'] as $d) {
        if (!is_dir($d)) mkdir($d, 0775, true);
        out("dir $d");
    }

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

    $stubApp = "SUGAR.language = SUGAR.language || {};\nSUGAR.language.setLanguage('app_strings', {});\n";
    $stubHome = "SUGAR.language = SUGAR.language || {};\nSUGAR.language.setLanguage('Home', {});\n";
    foreach ([
        'cache/jsLanguage/en_us.js' => $stubApp,
        'cache/jsLanguage/Home/en_us.js' => $stubHome,
    ] as $path => $stub) {
        $bad = !is_file($path) || filesize($path) < 20;
        if (!$bad) {
            $c = @file_get_contents($path);
            if ($c !== false && preg_match('/<html|Fatal|Internal Server/i', $c)) $bad = true;
        }
        if ($bad) { file_put_contents($path, $stub); out("wrote stub $path"); }
        else { out("keep existing $path (".filesize($path)." bytes)"); }
        @chmod($path, 0664);
    }

    global $db;
    if (!empty($db)) {
        $db->query("DELETE FROM user_preferences WHERE category = 'Home' AND deleted = 0");
        out('Home prefs wiped');
    }

    out(is_file('custom/include/BS/dashboard_board.php') ? 'board file OK' : 'board MISSING');
    out(is_file('custom/include/BS/retrieve_dash_page_safe.php') ? 'safe retrieve OK' : 'safe retrieve MISSING');
    out('DONE');
} catch (Throwable $e) {
    out('FATAL: '.$e->getMessage());
    out($e->getFile().':'.$e->getLine());
    out($e->getTraceAsString());
}
PHP

echo "==> Writing board + safe retrieve via GitHub API"
fetch_api() {
  local path="$1" dest="$2"
  curl -fsSL "https://api.github.com/repos/premprakash563-ai/premcursor/contents/${path}?ref=b599b02" \
    | php -r '$j=json_decode(stream_get_contents(STDIN),true); if(empty($j["content"])){fwrite(STDERR,"fail\n"); exit(1);} file_put_contents($argv[1], base64_decode($j["content"]));' "$dest"
}

fetch_api "suitecrm-extension/custom/include/BS/dashboard_board.php" "custom/include/BS/dashboard_board.php"
fetch_api "suitecrm-extension/custom/include/BS/retrieve_dash_page_safe.php" "custom/include/BS/retrieve_dash_page_safe.php"

echo "==> Registry"
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
    if "dashboard_board.php" in line and "'file'" in line: continue
    if "retrieve_dash_page_safe.php" in line and "'file'" in line: continue
    lines.append(line)
body="\n".join(lines).strip()
if not body.startswith("<?php"): body="<?php\n"+body
body=body.rstrip()+"\n\n"
body += "$entry_point_registry['retrieve_dash_page'] = array(\n    'file' => 'custom/include/BS/retrieve_dash_page_safe.php',\n    'auth' => true,\n);\n"
body += "$entry_point_registry['bs_operations_board'] = array(\n    'file' => 'custom/include/BS/dashboard_board.php',\n    'auth' => true,\n);\n"
ext.write_text(body)
print("registry ok")
PY

if [ -f custom/include/MVC/Controller/entry_point_registry.php ]; then
  mv -f custom/include/MVC/Controller/entry_point_registry.php \
        custom/include/MVC/Controller/entry_point_registry.php.bak.$(date +%s)
fi

for f in \
  modules/Home/Dashlets/BS_AdminDashboardDashlet/BS_AdminDashboardDashlet.php \
  modules/BS_Orders/Dashlets/BS_EmployeeWorkloadDashlet/BS_EmployeeWorkloadDashlet.php
do
  [ -f "$f" ] && mv -f "$f" "${f}.off" && echo "disabled $f"
done

find cache -type f \( -name '*CONTROLLER*' -o -name '*entry_point*' -o -name 'sugar_cache_*' \) -delete 2>/dev/null || true
rm -f cache/dashlets/dashlets.php
php -r 'if (function_exists("opcache_reset")) opcache_reset();' 2>/dev/null || true

ls -la bs_repair_js.php
grep -n "JS REPAIR start" bs_repair_js.php
echo
echo "OK. Ab browser: https://yoogleconsultancy.in/bs_repair_js.php"
