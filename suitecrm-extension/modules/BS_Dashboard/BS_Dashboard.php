<?php
/**
 * BS_Dashboard — lightweight SugarBean so ACL/admin tab access works,
 * then controller redirects to entryPoint board.
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once 'data/SugarBean.php';

class BS_Dashboard extends SugarBean
{
    public $new_schema = true;
    public $module_dir = 'BS_Dashboard';
    public $object_name = 'BS_Dashboard';
    public $table_name = 'bs_dashboard';
    public $disable_custom_fields = true;
    public $disable_row_level_security = true;

    public $id;
    public $name;
    public $date_entered;
    public $date_modified;
    public $modified_user_id;
    public $created_by;
    public $description;
    public $deleted;
    public $assigned_user_id;

    public function bean_implements($interface)
    {
        switch ($interface) {
            case 'ACL':
                return true;
        }
        return false;
    }
}
