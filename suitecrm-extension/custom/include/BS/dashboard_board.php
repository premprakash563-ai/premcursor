<?php
/**
 * Operations Board entry point — uses SugarView chrome (no broken $app->header).
 * URL: index.php?entryPoint=bs_operations_board
 */

if (!defined('sugarEntry')) {
    define('sugarEntry', true);
}

require_once 'include/entryPoint.php';

if (empty($current_user->id)) {
    sugar_cleanup(true);
    header('Location: index.php?module=Users&action=Login');
    exit;
}

require_once 'include/MVC/View/SugarView.php';
require_once 'modules/BS_Dashboard/BoardRenderer.php';

class BS_OperationsBoardView extends SugarView
{
    public function display()
    {
        $board = new BS_BoardRenderer();
        $board->renderBody();
    }
}

$view = new BS_OperationsBoardView();
$view->init('Home', 'index');
$view->process();
sugar_cleanup(true);
