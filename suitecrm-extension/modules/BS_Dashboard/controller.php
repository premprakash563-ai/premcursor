<?php
/**
 * BS_Dashboard module — redirects to safe entry point board.
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
        SugarApplication::redirect('index.php?entryPoint=bs_operations_board');
    }

    public function action_board()
    {
        SugarApplication::redirect('index.php?entryPoint=bs_operations_board');
    }
}
