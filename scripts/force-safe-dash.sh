#!/usr/bin/env bash
# Fix $log LoggerManager clash + force safe retrieve_dash_page
set -euo pipefail
cd ~/public_html

mkdir -p custom/include/BS custom/include/MVC/Controller \
  custom/application/Ext/EntryPointRegistry cache

echo "==> Safe retrieve wrapper (restores LoggerManager if \$log was overwritten)"
cat > custom/include/BS/retrieve_dash_page_safe.php << 'PHP'
<?php
if (!defined('sugarEntry') || !sugarEntry) { die('Not A Valid Entry Point'); }

// CRITICAL: some custom code sets $log to a string path and breaks sugar_cleanup()
if (!isset($GLOBALS['log']) || !is_object($GLOBALS['log']) || !method_exists($GLOBALS['log'], 'debug')) {
    if (class_exists('LoggerManager')) {
        $GLOBALS['log'] = LoggerManager::getLogger('SugarCRM');
    }
}

header('X-BS-Dash: safe-wrapper');

function bs_dash_log($msg) {
    $file = 'cache/bs_retrieve_dash_error.log';
    if (!is_dir('cache')) @mkdir('cache', 0755, true);
    @file_put_contents($file, date('c').' '.$msg."\n", FILE_APPEND);
}

function bs_dash_restore_logger() {
    if (!isset($GLOBALS['log']) || !is_object($GLOBALS['log']) || !method_exists($GLOBALS['log'], 'debug')) {
        if (class_exists('LoggerManager')) {
            $GLOBALS['log'] = LoggerManager::getLogger('SugarCRM');
            bs_dash_log('restored LoggerManager');
        }
    }
}

function bs_dash_minimal_html($reason = '') {
    bs_dash_restore_logger();
    $reason = htmlspecialchars((string)$reason, ENT_QUOTES, 'UTF-8');
    echo '<!-- BS-SAFE-DASH -->';
    echo '<div id="pageNum_0_div"><div style="padding:24px;font-family:system-ui,sans-serif;max-width:900px">';
    echo '<h2 style="margin:0 0 8px">SuiteCRM Dashboard</h2>';
    echo '<p style="color:#5b6b7c">Classic Home dashlets were reset to stop the loading error.</p>';
    echo '<p><a style="display:inline-block;padding:10px 14px;background:#0f766e;color:#fff;text-decoration:none;border-radius:8px" href="index.php?entryPoint=bs_operations_board">Open Operations Board</a></p>';
    echo '<p style="margin-top:14px"><input id="add_dashlets" class="button" type="button" value="Add Dashlets" onclick="return (typeof SUGAR!==\'undefined\' && SUGAR.mySugar) ? SUGAR.mySugar.showDashletsDialog() : true;"/></p>';
    if ($reason !== '') echo '<p style="font-size:12px;color:#999;margin-top:18px">BS-SAFE-DASH note: '.$reason.'</p>';
    echo '</div></div>';
    echo '<script>if(typeof(qe_init)!=\'undefined\'){qe_init();}</script>';
}

bs_dash_log('safe wrapper hit');
$bsDashDone = false;

register_shutdown_function(function () {
    global $bsDashDone;
    bs_dash_restore_logger();
    if (!empty($bsDashDone)) return;
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        bs_dash_log('SHUTDOWN FATAL: '.$err['message'].' @ '.$err['file'].':'.$err['line']);
        while (ob_get_level() > 0) @ob_end_clean();
        if (!headers_sent()) { header('Content-Type: text/html; charset=UTF-8'); header('X-BS-Dash: safe-wrapper-fatal'); }
        bs_dash_minimal_html($err['message']);
        $bsDashDone = true;
    }
});

try {
    foreach ([
        'modules/Home/Dashlets/BS_AdminDashboardDashlet/BS_AdminDashboardDashlet.php',
        'modules/BS_Orders/Dashlets/BS_EmployeeWorkloadDashlet/BS_EmployeeWorkloadDashlet.php',
    ] as $f) { if (is_file($f)) @rename($f, $f.'.off'); }

    global $current_user, $db;
    if (!empty($current_user->id) && !empty($db)) {
        $pages = $current_user->getPreference('pages', 'Home');
        $dashlets = $current_user->getPreference('dashlets', 'Home');
        if (!is_array($pages) || !is_array($dashlets)) {
            $uid = $db->quoted($current_user->id);
            $db->query("DELETE FROM user_preferences WHERE assigned_user_id = $uid AND category = 'Home'");
            if (method_exists($current_user, 'resetPreferences')) $current_user->resetPreferences('Home');
            if (isset($current_user->user_preferences['Home'])) unset($current_user->user_preferences['Home']);
            bs_dash_log('reset Home prefs for '.$current_user->id);
        }
    }

    if (!is_file('include/MySugar/retrieve_dash_page.php')) {
        throw new RuntimeException('Missing core retrieve_dash_page.php');
    }

    ob_start();
    include 'include/MySugar/retrieve_dash_page.php';
    $out = ob_get_clean();
    bs_dash_restore_logger();
    $bsDashDone = true;

    if ($out === '' || $out === false) {
        bs_dash_log('core empty');
        bs_dash_minimal_html('empty core output');
    } else {
        echo $out;
        bs_dash_log('core OK bytes='.strlen($out));
    }
} catch (Throwable $e) {
    bs_dash_log('CATCH: '.$e->getMessage().' @ '.$e->getFile().':'.$e->getLine());
    while (ob_get_level() > 0) @ob_end_clean();
    bs_dash_restore_logger();
    $bsDashDone = true;
    bs_dash_minimal_html($e->getMessage());
}
PHP

echo "==> Additive registry override"
cat > custom/include/MVC/Controller/entry_point_registry.php << 'PHP'
<?php
$entry_point_registry['retrieve_dash_page'] = array(
    'file' => 'custom/include/BS/retrieve_dash_page_safe.php',
    'auth' => true,
);
$entry_point_registry['bs_operations_board'] = array(
    'file' => 'custom/include/BS/dashboard_board.php',
    'auth' => true,
);
PHP

cat > custom/application/Ext/EntryPointRegistry/entry_point_registry.ext.php << 'PHP'
<?php
$entry_point_registry['retrieve_dash_page'] = array(
    'file' => 'custom/include/BS/retrieve_dash_page_safe.php',
    'auth' => true,
);
$entry_point_registry['bs_operations_board'] = array(
    'file' => 'custom/include/BS/dashboard_board.php',
    'auth' => true,
);
PHP

# Fix board sugar_cleanup logger too
if [ -f custom/include/BS/dashboard_board.php ]; then
  # prepend restore if not present
  if ! grep -q 'bs_dash_restore_logger\|LoggerManager::getLogger' custom/include/BS/dashboard_board.php; then
    echo "board present"
  fi
fi

# Ensure board file exists
if [ ! -f custom/include/BS/dashboard_board.php ]; then
  curl -fsSL "https://api.github.com/repos/premprakash563-ai/premcursor/contents/suitecrm-extension/custom/include/BS/dashboard_board.php?ref=b599b02" \
    | php -r '$j=json_decode(stream_get_contents(STDIN),true); if(empty($j["content"])) exit(1); file_put_contents("custom/include/BS/dashboard_board.php", base64_decode($j["content"]));'
fi

# Patch dashboard_board to restore logger before sugar_cleanup
python3 - << 'PY'
from pathlib import Path
p = Path('custom/include/BS/dashboard_board.php')
t = p.read_text()
needle = "if (function_exists('sugar_cleanup')) {\n    sugar_cleanup(true);\n}"
fix = """if (!isset($GLOBALS['log']) || !is_object($GLOBALS['log']) || !method_exists($GLOBALS['log'], 'debug')) {
    if (class_exists('LoggerManager')) { $GLOBALS['log'] = LoggerManager::getLogger('SugarCRM'); }
}
if (function_exists('sugar_cleanup')) {
    sugar_cleanup(true);
}"""
if "LoggerManager::getLogger" not in t and needle in t:
    p.write_text(t.replace(needle, fix))
    print('board logger restore patched')
elif "LoggerManager::getLogger" in t:
    print('board already has logger restore')
else:
    # older board with just sugar_cleanup(true); exit;
    t2 = t.replace("sugar_cleanup(true);\nexit;", """if (!isset($GLOBALS['log']) || !is_object($GLOBALS['log']) || !method_exists($GLOBALS['log'], 'debug')) {
    if (class_exists('LoggerManager')) { $GLOBALS['log'] = LoggerManager::getLogger('SugarCRM'); }
}
sugar_cleanup(true);
exit;""")
    if t2 != t:
        p.write_text(t2)
        print('board logger restore patched (legacy)')
    else:
        print('board patch skipped')
PY

echo "==> Fixed probe (never uses \$log variable)"
cat > bs_dash_probe.php << 'PHP'
<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
header('Content-Type: text/plain; charset=UTF-8');
echo "DASH PROBE v3\n";
if (!defined('sugarEntry')) define('sugarEntry', true);
require_once 'include/entryPoint.php';
echo "bootstrap OK\n";
echo "log type: " . (isset($GLOBALS['log']) ? gettype($GLOBALS['log']) : 'unset') . "\n";

$custom = 'custom/include/MVC/Controller/entry_point_registry.php';
$safe = 'custom/include/BS/retrieve_dash_page_safe.php';
echo (is_file($custom) ? 'custom registry OK' : 'custom registry MISSING') . "\n";
echo (is_file($safe) ? 'safe file OK' : 'safe file MISSING') . "\n";

// Do NOT use variable name $log — it breaks SuiteCRM cleanup
$bs_log_path = 'cache/bs_retrieve_dash_error.log';
if (is_file($custom)) {
    include $custom;
    echo isset($entry_point_registry['retrieve_dash_page']['file'])
        ? ('mapped to ' . $entry_point_registry['retrieve_dash_page']['file'] . "\n")
        : "retrieve_dash_page NOT mapped\n";
}

if (class_exists('SugarCache') && method_exists('SugarCache', 'instance')) {
    try { SugarCache::instance()->flush(); echo "SugarCache flushed\n"; } catch (Throwable $e) { echo $e->getMessage()."\n"; }
}

echo is_file($bs_log_path) ? ("--- log ---\n" . file_get_contents($bs_log_path) . "\n") : "no log yet\n";

// Restore logger before shutdown cleanup
if (!isset($GLOBALS['log']) || !is_object($GLOBALS['log']) || !method_exists($GLOBALS['log'], 'debug')) {
    if (class_exists('LoggerManager')) {
        $GLOBALS['log'] = LoggerManager::getLogger('SugarCRM');
        echo "logger restored\n";
    }
}
echo "log type final: " . (isset($GLOBALS['log']) ? gettype($GLOBALS['log']) : 'unset') . "\n";
echo "DONE\n";
PHP

find cache -type f \( -name '*CONTROLLER*' -o -name '*entry_point*' -o -name 'sugar_cache_*' \) -delete 2>/dev/null || true
php -r 'if (function_exists("opcache_reset")) opcache_reset();' 2>/dev/null || true

echo
echo "OK."
echo "1) https://yoogleconsultancy.in/bs_dash_probe.php  (DASH PROBE v3 + no fatal)"
echo "2) Login → Home Ctrl+Shift+R"
echo "3) Network retrieve_dash_page → 200 / Response has BS-SAFE-DASH or dashlets"
echo "4) Board: index.php?entryPoint=bs_operations_board"
