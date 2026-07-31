<?php
/**
 * Force-safe retrieve_dash_page — never 500 the Home dashboard AJAX.
 * Marker in output: BS-SAFE-DASH so Network response can be verified.
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

header('X-BS-Dash: safe-wrapper');

function bs_dash_log($msg)
{
    $file = 'cache/bs_retrieve_dash_error.log';
    if (!is_dir('cache')) {
        @mkdir('cache', 0755, true);
    }
    @file_put_contents($file, date('c') . ' ' . $msg . "\n", FILE_APPEND);
}

function bs_dash_minimal_html($reason = '')
{
    $reason = htmlspecialchars((string) $reason, ENT_QUOTES, 'UTF-8');
    echo '<!-- BS-SAFE-DASH -->';
    echo '<div id="pageNum_0_div">';
    echo '<div style="padding:24px;font-family:system-ui,Segoe UI,sans-serif;max-width:900px">';
    echo '<h2 style="margin:0 0 8px">SuiteCRM Dashboard</h2>';
    echo '<p style="color:#5b6b7c">Classic Home dashlets were reset to stop the loading error.</p>';
    echo '<p><a class="button" style="display:inline-block;padding:10px 14px;background:#0f766e;color:#fff;text-decoration:none;border-radius:8px" href="index.php?entryPoint=bs_operations_board">Open Operations Board</a></p>';
    echo '<p style="margin-top:14px"><input id="add_dashlets" class="button" type="button" value="Add Dashlets" onclick="return (typeof SUGAR!==\'undefined\' && SUGAR.mySugar) ? SUGAR.mySugar.showDashletsDialog() : true;"/></p>';
    if ($reason !== '') {
        echo '<p style="font-size:12px;color:#999;margin-top:18px">BS-SAFE-DASH note: ' . $reason . '</p>';
    }
    echo '</div></div>';
    echo '<script>if(typeof(qe_init)!=\'undefined\'){qe_init();}</script>';
}

bs_dash_log('safe wrapper hit');

// Prefer Ops board over crashing core charts/dashlets while we stabilize Home.
// Attempt core once; on any Throwable OR shutdown fatal, serve minimal HTML.
$bsDashDone = false;

register_shutdown_function(function () {
    global $bsDashDone;
    if (!empty($bsDashDone)) {
        return;
    }
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        bs_dash_log('SHUTDOWN FATAL: ' . $err['message'] . ' @ ' . $err['file'] . ':' . $err['line']);
        // clear any partial output
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }
        if (!headers_sent()) {
            header('Content-Type: text/html; charset=UTF-8');
            header('X-BS-Dash: safe-wrapper-fatal');
        }
        bs_dash_minimal_html($err['message']);
        $bsDashDone = true;
    }
});

try {
    // Ensure custom broken dashlets are not loaded
    foreach ([
        'modules/Home/Dashlets/BS_AdminDashboardDashlet/BS_AdminDashboardDashlet.php',
        'modules/BS_Orders/Dashlets/BS_EmployeeWorkloadDashlet/BS_EmployeeWorkloadDashlet.php',
    ] as $f) {
        if (is_file($f)) {
            @rename($f, $f . '.off');
        }
    }

    // Soft-reset this user's Home prefs if pages look empty/corrupt mid-request
    global $current_user, $db;
    if (!empty($current_user->id) && !empty($db)) {
        $pages = $current_user->getPreference('pages', 'Home');
        $dashlets = $current_user->getPreference('dashlets', 'Home');
        if (!is_array($pages) || !is_array($dashlets)) {
            $uid = $db->quoted($current_user->id);
            $db->query("DELETE FROM user_preferences WHERE assigned_user_id = $uid AND category = 'Home'");
            if (method_exists($current_user, 'resetPreferences')) {
                $current_user->resetPreferences('Home');
            }
            if (isset($current_user->user_preferences['Home'])) {
                unset($current_user->user_preferences['Home']);
            }
            bs_dash_log('reset Home prefs for ' . $current_user->id);
        }
    }

    if (!is_file('include/MySugar/retrieve_dash_page.php')) {
        throw new RuntimeException('Missing core include/MySugar/retrieve_dash_page.php');
    }

    ob_start();
    include 'include/MySugar/retrieve_dash_page.php';
    $out = ob_get_clean();
    $bsDashDone = true;

    if ($out === '' || $out === false) {
        bs_dash_log('core returned empty output');
        bs_dash_minimal_html('empty core output');
    } else {
        echo $out;
        bs_dash_log('core OK bytes=' . strlen($out));
    }
} catch (Throwable $e) {
    bs_dash_log('CATCH: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    while (ob_get_level() > 0) {
        @ob_end_clean();
    }
    $bsDashDone = true;
    bs_dash_minimal_html($e->getMessage());
}
