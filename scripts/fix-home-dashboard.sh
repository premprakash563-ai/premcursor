#!/usr/bin/env bash
# Nuclear Home dashboard fix — embeds PHP (no GitHub CDN for bs_fix.php)
set -euo pipefail
BASE="${1:-$HOME/public_html}"
cd "$BASE"

mkdir -p custom/include/BS
mkdir -p custom/Extension/application/Ext/EntryPointRegistry
mkdir -p custom/application/Ext/EntryPointRegistry
mkdir -p cache

echo "==> Writing bs_fix.php (embedded, no download)..."
cat > bs_fix.php << 'PHP'
<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
header('Content-Type: text/plain; charset=UTF-8');
echo "BS FIX start v2\n";

function slog($m)
{
    // Never use global $log — SuiteCRM overwrites it with LoggerManager
    $file = __DIR__ . '/cache/bs_fix_standalone.log';
    if (!is_dir(__DIR__ . '/cache')) {
        @mkdir(__DIR__ . '/cache', 0755, true);
    }
    @file_put_contents($file, date('c') . ' ' . $m . "\n", FILE_APPEND);
    echo $m . "\n";
}

try {
    if (!defined('sugarEntry')) {
        define('sugarEntry', true);
    }
    require_once 'include/entryPoint.php';
    slog('bootstrap OK');

    global $current_user, $db;

    $legacy = 'custom/include/MVC/Controller/entry_point_registry.php';
    if (is_file($legacy)) {
        rename($legacy, $legacy . '.bak.' . time());
        slog('removed custom entry_point_registry.php');
    }

    foreach ([
        'modules/Home/Dashlets/BS_AdminDashboardDashlet/BS_AdminDashboardDashlet.php',
        'modules/BS_Orders/Dashlets/BS_EmployeeWorkloadDashlet/BS_EmployeeWorkloadDashlet.php',
    ] as $f) {
        if (is_file($f)) {
            rename($f, $f . '.off');
            slog("disabled $f");
        }
    }

    $safeSrc = 'custom/include/BS/retrieve_dash_page_safe.php';
    slog(is_file($safeSrc) ? "safe wrapper present: $safeSrc" : "MISSING $safeSrc");

    $extDir = 'custom/application/Ext/EntryPointRegistry';
    if (!is_dir($extDir)) {
        mkdir($extDir, 0755, true);
    }
    $ext = $extDir . '/entry_point_registry.ext.php';
    $snippet = "\$entry_point_registry['retrieve_dash_page'] = array(\n"
        . "    'file' => 'custom/include/BS/retrieve_dash_page_safe.php',\n"
        . "    'auth' => true,\n"
        . ");\n"
        . "\$entry_point_registry['bs_operations_board'] = array(\n"
        . "    'file' => 'custom/include/BS/dashboard_board.php',\n"
        . "    'auth' => true,\n"
        . ");\n";

    $text = is_file($ext) ? file_get_contents($ext) : "<?php\n";
    $lines = [];
    $skip = false;
    foreach (explode("\n", $text) as $line) {
        if (strpos($line, 'retrieve_dash_page') !== false
            || strpos($line, 'bs_operations_board') !== false
            || strpos($line, 'bs_dash_fix') !== false) {
            $skip = true;
            continue;
        }
        if ($skip) {
            if (trim($line) === ');' || trim($line) === ');?>') {
                $skip = false;
            }
            continue;
        }
        if (strpos($line, "dashboard_board.php") !== false && strpos($line, "'file'") !== false) {
            continue;
        }
        $lines[] = $line;
    }
    $body = implode("\n", $lines);
    if (strpos($body, '<?php') === false) {
        $body = "<?php\n" . $body;
    }
    $body = rtrim($body) . "\n\n" . $snippet;
    file_put_contents($ext, $body);
    slog("wrote $ext");

    if (!is_dir('custom/Extension/application/Ext/EntryPointRegistry')) {
        mkdir('custom/Extension/application/Ext/EntryPointRegistry', 0755, true);
    }
    file_put_contents(
        'custom/Extension/application/Ext/EntryPointRegistry/bs_retrieve_safe.php',
        "<?php\n" . $snippet
    );

    $db->query("DELETE FROM user_preferences WHERE category = 'Home' AND deleted = 0");
    slog('deleted ALL Home user_preferences');

    if (!empty($current_user->id) && method_exists($current_user, 'resetPreferences')) {
        $current_user->resetPreferences('Home');
        slog('resetPreferences for ' . $current_user->user_name);
    } else {
        slog('not logged in via session — SQL reset still applied');
    }

    @unlink('cache/dashlets/dashlets.php');
    foreach (glob('cache/dashlets/*') ?: [] as $f) {
        @unlink($f);
    }
    slog('cleared dashlet cache files');

    require_once 'include/Dashlets/DashletCacheBuilder.php';
    $dc = new DashletCacheBuilder();
    $dc->buildCache();
    slog('rebuilt dashlet cache');

    slog('DONE');
    slog('Next: open Home (Ctrl+Shift+R). Then delete bs_fix.php');
} catch (Throwable $e) {
    slog('FATAL: ' . $e->getMessage());
    slog($e->getFile() . ':' . $e->getLine());
    slog($e->getTraceAsString());
}
PHP

echo "==> Writing retrieve_dash_page_safe.php (embedded)..."
cat > custom/include/BS/retrieve_dash_page_safe.php << 'PHP'
<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

function bs_dash_log($msg)
{
    $file = 'cache/bs_retrieve_dash_error.log';
    if (!is_dir('cache')) {
        @mkdir('cache', 0755, true);
    }
    @file_put_contents($file, date('c') . ' ' . $msg . "\n", FILE_APPEND);
}

function bs_disable_custom_dashlets()
{
    foreach ([
        'modules/Home/Dashlets/BS_AdminDashboardDashlet/BS_AdminDashboardDashlet.php',
        'modules/BS_Orders/Dashlets/BS_EmployeeWorkloadDashlet/BS_EmployeeWorkloadDashlet.php',
    ] as $f) {
        if (is_file($f)) {
            @rename($f, $f . '.off');
            bs_dash_log("disabled $f");
        }
    }
}

function bs_reset_home_prefs()
{
    global $current_user, $db;
    if (empty($current_user->id)) {
        return;
    }
    try {
        if (method_exists($current_user, 'resetPreferences')) {
            $current_user->resetPreferences('Home');
        }
    } catch (Throwable $e) {
        bs_dash_log('resetPreferences: ' . $e->getMessage());
    }
    try {
        $uid = $db->quoted($current_user->id);
        $db->query("DELETE FROM user_preferences WHERE assigned_user_id = $uid AND category = 'Home'");
        bs_dash_log("deleted Home prefs for {$current_user->id}");
    } catch (Throwable $e) {
        bs_dash_log('SQL prefs delete: ' . $e->getMessage());
    }
    if (isset($current_user->user_preferences['Home'])) {
        unset($current_user->user_preferences['Home']);
    }
}

function bs_rebuild_dashlet_cache()
{
    @unlink(sugar_cached('dashlets/dashlets.php'));
    require_once 'include/Dashlets/DashletCacheBuilder.php';
    $dc = new DashletCacheBuilder();
    $dc->buildCache();
    bs_dash_log('rebuilt dashlet cache');
}

function bs_run_core_retrieve()
{
    $core = 'include/MySugar/retrieve_dash_page.php';
    if (!is_file($core)) {
        throw new RuntimeException("Missing $core");
    }
    include $core;
}

bs_disable_custom_dashlets();

$legacy = 'custom/include/MVC/Controller/entry_point_registry.php';
if (is_file($legacy)) {
    @rename($legacy, $legacy . '.bak.' . time());
    bs_dash_log('moved legacy entry_point_registry.php');
}

try {
    bs_run_core_retrieve();
} catch (Throwable $e) {
    bs_dash_log('FIRST FAIL: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    bs_dash_log($e->getTraceAsString());
    try {
        bs_reset_home_prefs();
        bs_rebuild_dashlet_cache();
        bs_run_core_retrieve();
        bs_dash_log('RETRY OK');
    } catch (Throwable $e2) {
        bs_dash_log('RETRY FAIL: ' . $e2->getMessage() . ' @ ' . $e2->getFile() . ':' . $e2->getLine());
        header('Content-Type: text/html; charset=UTF-8');
        $msg = htmlspecialchars($e2->getMessage(), ENT_QUOTES, 'UTF-8');
        echo '<div id="pageContainer" class="yui-skin-sam"><div style="padding:24px;font-family:system-ui,sans-serif">';
        echo '<h2>Dashboard temporarily reset</h2>';
        echo '<p>Home dashlets crashed. Use Actions → Add Dashlets, or open Operations Board.</p>';
        echo '<p><a href="index.php?entryPoint=bs_operations_board">Open Operations Board</a></p>';
        echo '<p style="font-size:12px;color:#999">Error: ' . $msg . '</p></div></div>';
        echo '<script>if(typeof(qe_init)!=\'undefined\'){qe_init();}</script>';
    }
}
PHP

# Also sync repo copy path used by package
cp -f bs_fix.php custom/include/BS/bs_fix_standalone.php

echo "==> Patching registry + clearing caches..."
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
    if "dashboard_board.php" in line and "'file'" in line:
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
print("registry OK")
PY

if [ -f custom/include/MVC/Controller/entry_point_registry.php ]; then
  mv -f custom/include/MVC/Controller/entry_point_registry.php \
        custom/include/MVC/Controller/entry_point_registry.php.bak.$(date +%s)
fi

for f in \
  modules/Home/Dashlets/BS_AdminDashboardDashlet/BS_AdminDashboardDashlet.php \
  modules/BS_Orders/Dashlets/BS_EmployeeWorkloadDashlet/BS_EmployeeWorkloadDashlet.php
do
  [ -f "$f" ] && mv -f "$f" "${f}.off" && echo "Disabled $f"
done

rm -f cache/dashlets/dashlets.php
rm -rf cache/dashlets/* 2>/dev/null || true
find cache -name '*CONTROLLER*' -delete 2>/dev/null || true

# Prove new file is on disk
echo "==> Verify bs_fix.php marker:"
grep -n "BS FIX start v2" bs_fix.php || { echo "WRITE FAILED"; exit 1; }
grep -n "Never use global" bs_fix.php || true
php -r 'if (function_exists("opcache_reset")) { opcache_reset(); echo "opcache cleared\n"; }' 2>/dev/null || true

echo
echo "========================================"
echo "OK. Ab browser me kholo:"
echo "https://yoogleconsultancy.in/bs_fix.php"
echo
echo "Pehli line me 'BS FIX start v2' dikhna CHAHIYE."
echo "Purana error aaya to Ctrl+Shift+R / Incognito try karo."
echo "========================================"
