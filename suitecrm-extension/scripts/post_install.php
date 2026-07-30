<?php
/**
 * Ensure BS modules appear in system tabs (ALL menu / navbar).
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

bs_enable_module_tabs();
