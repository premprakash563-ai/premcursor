<?php
/**
 * Full-page Operations Board — visible professional dashboard for client demos.
 * Does not patch SuiteP global CSS (safe on LiteSpeed hosts).
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once 'include/MVC/Controller/SugarController.php';

class BS_DashboardController extends SugarController
{
    public function loadBean()
    {
        $this->bean = null;
    }

    public function action_index()
    {
        $this->view = 'board';
    }
}
