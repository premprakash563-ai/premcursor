<?php
/**
 * BS_Dashboard module — always redirect to Operations Board entry point.
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once 'include/MVC/Controller/SugarController.php';

class BS_DashboardController extends SugarController
{
    public function preProcess()
    {
        // Run before ACL/view — top nav tab must open the board.
        $this->hasAccess = true;
        $this->redirectToBoard();
    }

    public function loadBean()
    {
        $this->bean = null;
    }

    public function action_index()
    {
        $this->redirectToBoard();
    }

    public function action_board()
    {
        $this->redirectToBoard();
    }

    public function action_DetailView()
    {
        $this->redirectToBoard();
    }

    public function action_ListView()
    {
        $this->redirectToBoard();
    }

    public function action_default()
    {
        $this->redirectToBoard();
    }

    protected function redirectToBoard()
    {
        SugarApplication::redirect('index.php?entryPoint=bs_operations_board');
    }
}
