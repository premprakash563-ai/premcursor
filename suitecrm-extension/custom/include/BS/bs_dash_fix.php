<?php
/**
 * Temporary diagnostic + Home dashboard reset.
 * Install as entry point, open once while logged in, then DELETE this file.
 *
 * URL: index.php?entryPoint=bs_dash_fix
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

header('Content-Type: text/html; charset=UTF-8');
@ini_set('display_errors', '1');
@ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

global $current_user, $db, $sugar_config;

echo '<h2>BS Dash Fix / Diagnose</h2>';
echo '<pre style="white-space:pre-wrap;font:13px/1.4 monospace;background:#111;color:#0f0;padding:12px">';

if (empty($current_user->id)) {
    echo "NOT LOGGED IN — pehle CRM login karo, phir is URL ko dubara kholo.\n";
    echo '</pre>';
    sugar_cleanup(true);
    exit;
}

echo 'User: ' . $current_user->user_name . " (id={$current_user->id})\n";
echo 'PHP: ' . PHP_VERSION . "\n";

// 1) Remove dangerous override if present
$legacy = 'custom/include/MVC/Controller/entry_point_registry.php';
if (is_file($legacy)) {
    $bak = $legacy . '.bak.' . time();
    @rename($legacy, $bak);
    echo "Moved broken override → $bak\n";
} else {
    echo "OK: no custom entry_point_registry.php override\n";
}

// 2) Disable custom dashlets
$dashlets = [
    'modules/Home/Dashlets/BS_AdminDashboardDashlet/BS_AdminDashboardDashlet.php',
    'modules/BS_Orders/Dashlets/BS_EmployeeWorkloadDashlet/BS_EmployeeWorkloadDashlet.php',
];
foreach ($dashlets as $f) {
    if (is_file($f)) {
        @rename($f, $f . '.off');
        echo "Disabled $f\n";
    }
}

// 3) Clear caches
foreach ([
    'cache/dashlets/dashlets.php',
] as $f) {
    if (is_file($f)) {
        @unlink($f);
        echo "Deleted $f\n";
    }
}
@array_map('unlink', glob('cache/dashlets/*') ?: []);
echo "Cleared cache/dashlets\n";

// 4) Reset THIS user's Home preferences (no phpMyAdmin needed)
try {
    $current_user->resetPreferences('Home');
    // Also wipe DB rows for this user category Home
    $uid = $db->quoted($current_user->id);
    $db->query("DELETE FROM user_preferences WHERE assigned_user_id = $uid AND category = 'Home'");
    echo "Reset Home preferences for current user\n";
} catch (Throwable $e) {
    echo 'Preference reset error: ' . $e->getMessage() . "\n";
}

// 5) Rebuild dashlet cache
try {
    require_once 'include/Dashlets/DashletCacheBuilder.php';
    $dc = new DashletCacheBuilder();
    $dc->buildCache();
    echo "Rebuilt dashlet cache\n";
    if (is_file('cache/dashlets/dashlets.php')) {
        echo "cache/dashlets/dashlets.php exists (" . filesize('cache/dashlets/dashlets.php') . " bytes)\n";
    }
} catch (Throwable $e) {
    echo 'Dashlet cache rebuild FAILED: ' . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}

// 6) Probe retrieve_dash_page — catch fatals too
echo "\n--- Probing retrieve_dash_page ---\n";
register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        echo "\nFATAL: {$err['message']}\nFile: {$err['file']}:{$err['line']}\n";
    }
});

try {
    if (!is_file('include/MySugar/retrieve_dash_page.php')) {
        throw new RuntimeException('Missing include/MySugar/retrieve_dash_page.php');
    }
    if (!is_file('include/MySugar/MySugar.php')) {
        throw new RuntimeException('Missing include/MySugar/MySugar.php');
    }
    require_once 'include/MySugar/MySugar.php';

    $cachefile = sugar_cached('dashlets/dashlets.php');
    if (!is_file($cachefile)) {
        throw new RuntimeException("Dashlet cache missing after rebuild: $cachefile");
    }
    require $cachefile;
    require 'modules/Home/dashlets.php';

    $pages = $current_user->getPreference('pages', 'Home');
    $dashletsPref = $current_user->getPreference('dashlets', 'Home');
    echo 'pages empty? ' . (empty($pages) ? 'yes' : 'no') . "\n";
    echo 'dashlets empty? ' . (empty($dashletsPref) ? 'yes' : 'no') . "\n";

    foreach (['iFrameDashlet', 'SugarFeedDashlet', 'MyContactsDashlet'] as $cls) {
        if (!empty($dashletsFiles[$cls]['file'])) {
            $ok = is_file($dashletsFiles[$cls]['file']) ? 'OK' : 'MISSING';
            echo "dashlet $cls file: {$dashletsFiles[$cls]['file']} [$ok]\n";
        } else {
            echo "dashlet $cls: NOT IN CACHE\n";
        }
    }

    // Actually run core retrieve script and capture output/errors
    $_REQUEST['pageNum'] = '0';
    $_POST['pageNum'] = '0';
    ob_start();
    include 'include/MySugar/retrieve_dash_page.php';
    $buf = ob_get_clean();
    echo 'retrieve_dash_page output bytes: ' . strlen($buf) . "\n";
    if (strlen($buf) > 0) {
        echo "First 300 chars:\n" . substr(strip_tags($buf), 0, 300) . "\n";
    }
    echo "PROBE OK\n";
} catch (Throwable $e) {
    echo "PROBE FAILED: " . $e->getMessage() . "\n";
    echo $e->getFile() . ':' . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}

// 7) Tail suitecrm.log if present
foreach (['suitecrm.log', 'sugarcrm.log'] as $log) {
    if (is_file($log) && is_readable($log)) {
        echo "\n--- Last 40 lines of $log ---\n";
        $lines = @file($log);
        if ($lines) {
            echo htmlspecialchars(implode('', array_slice($lines, -40)), ENT_QUOTES, 'UTF-8');
        }
        break;
    }
}

echo "\nDONE.\n";
echo "1) Ab Home page kholo (Ctrl+Shift+R)\n";
echo "2) Phir ye diagnostic file DELETE karo: custom/include/BS/bs_dash_fix.php\n";
echo "3) Entry registry se bs_dash_fix hata dena\n";
echo '</pre>';
echo '<p><a href="index.php?module=Home&action=index">→ Open Home</a> &nbsp; ';
echo '<a href="index.php?entryPoint=bs_operations_board">→ Operations Board</a></p>';

sugar_cleanup(true);
exit;
