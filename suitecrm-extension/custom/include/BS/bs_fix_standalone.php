<?php
/**
 * Standalone Home dashboard fix — NO entryPoint needed.
 * Upload/copy to public_html/bs_fix.php then open:
 *   https://yoogleconsultancy.in/bs_fix.php
 * Delete after use.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');

header('Content-Type: text/plain; charset=UTF-8');
echo "BS FIX start\n";

$bsFixLogDir = __DIR__ . '/cache';
if (!is_dir($bsFixLogDir)) {
    @mkdir($bsFixLogDir, 0755, true);
}
// IMPORTANT: do not use $log — SuiteCRM sets global $log = LoggerManager
$bs_fix_log_file = $bsFixLogDir . '/bs_fix_standalone.log';
function slog($m)
{
    global $bs_fix_log_file;
    $line = date('c') . ' ' . $m . "\n";
    if (is_string($bs_fix_log_file) && $bs_fix_log_file !== '') {
        @file_put_contents($bs_fix_log_file, $line, FILE_APPEND);
    }
    echo $m . "\n";
}

try {
    if (!defined('sugarEntry')) {
        define('sugarEntry', true);
    }
    require_once 'include/entryPoint.php';
    // Re-assert our log path after SuiteCRM bootstrap clobbers $log
    $bs_fix_log_file = __DIR__ . '/cache/bs_fix_standalone.log';
    slog('bootstrap OK');

    global $current_user, $db;

    // Remove broken registry override
    $legacy = 'custom/include/MVC/Controller/entry_point_registry.php';
    if (is_file($legacy)) {
        rename($legacy, $legacy . '.bak.' . time());
        slog('removed custom entry_point_registry.php');
    }

    // Disable custom dashlets
    foreach ([
        'modules/Home/Dashlets/BS_AdminDashboardDashlet/BS_AdminDashboardDashlet.php',
        'modules/BS_Orders/Dashlets/BS_EmployeeWorkloadDashlet/BS_EmployeeWorkloadDashlet.php',
    ] as $f) {
        if (is_file($f)) {
            rename($f, $f . '.off');
            slog("disabled $f");
        }
    }

    // Install safe retrieve_dash_page override + registry
    $safeSrc = 'custom/include/BS/retrieve_dash_page_safe.php';
    if (!is_file($safeSrc)) {
        slog("MISSING $safeSrc — copy from package first");
    } else {
        slog("safe wrapper present: $safeSrc");
    }

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
        if (strpos($line, 'retrieve_dash_page') !== false || strpos($line, 'bs_operations_board') !== false || strpos($line, 'bs_dash_fix') !== false) {
            $skip = true;
            continue;
        }
        if ($skip) {
            if (trim($line) === ');' || trim($line) === ');?>') {
                $skip = false;
            }
            continue;
        }
        // drop orphan debris
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

    // Also extension source
    if (!is_dir('custom/Extension/application/Ext/EntryPointRegistry')) {
        mkdir('custom/Extension/application/Ext/EntryPointRegistry', 0755, true);
    }
    file_put_contents(
        'custom/Extension/application/Ext/EntryPointRegistry/bs_retrieve_safe.php',
        "<?php\n" . $snippet
    );

    // Reset ALL users' Home prefs (nuclear)
    $db->query("DELETE FROM user_preferences WHERE category = 'Home' AND deleted = 0");
    slog('deleted ALL Home user_preferences');

    if (!empty($current_user->id) && method_exists($current_user, 'resetPreferences')) {
        $current_user->resetPreferences('Home');
        slog('resetPreferences for ' . $current_user->user_name);
    } else {
        slog('not logged in via session — SQL reset still applied');
    }

    // Clear caches
    @unlink('cache/dashlets/dashlets.php');
    foreach (glob('cache/dashlets/*') ?: [] as $f) {
        @unlink($f);
    }
    // controller mapping cache
    if (function_exists('sugar_cache_clear')) {
        // best-effort
    }
    slog('cleared dashlet cache files');

    require_once 'include/Dashlets/DashletCacheBuilder.php';
    $dc = new DashletCacheBuilder();
    $dc->buildCache();
    slog('rebuilt dashlet cache');

    slog('DONE');
    slog('Next: open Home (Ctrl+Shift+R). Then delete this file: bs_fix.php');
    slog('Log also at: cache/bs_fix_standalone.log');
} catch (Throwable $e) {
    slog('FATAL: ' . $e->getMessage());
    slog($e->getFile() . ':' . $e->getLine());
    slog($e->getTraceAsString());
}
