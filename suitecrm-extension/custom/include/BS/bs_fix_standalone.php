<?php
/**
 * Standalone Home dashboard fix — NO entryPoint needed.
 * Open: https://yoogleconsultancy.in/bs_fix.php
 * Delete after use.
 */

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
