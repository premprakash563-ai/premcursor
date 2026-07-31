<?php
/**
 * Post-install: enable tabs + register Operations Board entry point without Repair.
 */

function bs_enable_module_tabs()
{
    if (!file_exists('modules/MySettings/TabController.php')) {
        return;
    }
    require_once 'modules/MySettings/TabController.php';

    $want = [
        'BS_Dashboard',
        'BS_Services',
        'BS_Orders',
        'BS_OrderDocuments',
        'BS_Leave',
        'BS_Notifications',
    ];
    $tc = new TabController();
    $displayed = $tc->get_system_tabs();
    if (!is_array($displayed)) {
        $displayed = [];
    }
    foreach ($want as $m) {
        if (!in_array($m, $displayed, true)) {
            $displayed[] = $m;
        }
    }
    $tc->set_system_tabs($displayed);
}

function bs_register_operations_board_entrypoint()
{
    $snippet = "<?php\n"
        . "\$entry_point_registry['bs_operations_board'] = array(\n"
        . "    'file' => 'custom/include/BS/dashboard_board.php',\n"
        . "    'auth' => true,\n"
        . ");\n";

    $dirs = [
        'custom/Extension/application/Ext/EntryPointRegistry',
        'custom/application/Ext/EntryPointRegistry',
    ];
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }

    @file_put_contents(
        'custom/Extension/application/Ext/EntryPointRegistry/bs_dashboard.php',
        $snippet
    );
    @file_put_contents(
        'custom/application/Ext/EntryPointRegistry/bs_operations_board.ext.php',
        $snippet
    );

    $ext = 'custom/application/Ext/EntryPointRegistry/entry_point_registry.ext.php';
    $existing = is_file($ext) ? (string) file_get_contents($ext) : "<?php\n";
    $lines = preg_grep('/bs_operations_board/', explode("\n", $existing), PREG_GREP_INVERT);
    $clean = implode("\n", $lines);
    if (strpos($clean, '<?php') === false) {
        $clean = "<?php\n" . $clean;
    }
    $clean = rtrim($clean) . "\n" . $snippet;
    @file_put_contents($ext, $clean);
}

bs_enable_module_tabs();
bs_register_operations_board_entrypoint();
