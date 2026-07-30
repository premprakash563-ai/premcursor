<?php
/**
 * Ensure BS modules appear in system tabs (ALL menu / navbar).
 * Runs after Module Loader install / upgrade.
 */

function bs_enable_module_tabs()
{
    if (!file_exists('modules/MySettings/TabController.php')) {
        return;
    }
    require_once 'modules/MySettings/TabController.php';

    $want = ['BS_Services', 'BS_Orders', 'BS_OrderDocuments'];
    $tc = new TabController();

    // System displayed tabs
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

    // Also remove from hidden if helper exists
    if (method_exists($tc, 'get_hidden_submenus')) {
        // no-op on older helpers
    }
}

bs_enable_module_tabs();
