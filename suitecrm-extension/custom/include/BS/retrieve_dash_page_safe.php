<?php
/**
 * Safe override for SuiteCRM retrieve_dash_page.
 * Catches PHP 8+ Errors (core only catches Exception) and recovers Home prefs.
 */

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
    $files = [
        'modules/Home/Dashlets/BS_AdminDashboardDashlet/BS_AdminDashboardDashlet.php',
        'modules/BS_Orders/Dashlets/BS_EmployeeWorkloadDashlet/BS_EmployeeWorkloadDashlet.php',
    ];
    foreach ($files as $f) {
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
    // Clear in-memory prefs so retrieve rebuilds defaults
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

// Remove dangerous full registry override if present
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

    // Recover once: reset prefs + rebuild cache + retry
    try {
        bs_reset_home_prefs();
        bs_rebuild_dashlet_cache();
        bs_run_core_retrieve();
        bs_dash_log('RETRY OK');
    } catch (Throwable $e2) {
        bs_dash_log('RETRY FAIL: ' . $e2->getMessage() . ' @ ' . $e2->getFile() . ':' . $e2->getLine());
        bs_dash_log($e2->getTraceAsString());

        // Last resort: return minimal page so Home spinner stops
        header('Content-Type: text/html; charset=UTF-8');
        $msg = htmlspecialchars($e2->getMessage(), ENT_QUOTES, 'UTF-8');
        echo '<div id="pageContainer" class="yui-skin-sam">';
        echo '<div style="padding:24px;font-family:system-ui,sans-serif">';
        echo '<h2 style="margin:0 0 8px">Dashboard temporarily reset</h2>';
        echo '<p style="color:#5b6b7c">Home dashlets crashed. Prefs were cleared. Use Actions → Add Dashlets, or open Operations Board.</p>';
        echo '<p><a href="index.php?entryPoint=bs_operations_board">Open Operations Board</a></p>';
        echo '<p style="font-size:12px;color:#999">Error: ' . $msg . '</p>';
        echo '<p style="font-size:12px;color:#999">Log: cache/bs_retrieve_dash_error.log</p>';
        echo '</div></div>';
        echo '<script>if(typeof(qe_init)!=\'undefined\'){qe_init();}</script>';
    }
}
