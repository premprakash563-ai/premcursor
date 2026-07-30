<?php
/**
 * BS_Dashboard — full-page professional operations board (no global theme CSS).
 */

class BS_Dashboard
{
    public $module_dir = 'BS_Dashboard';
    public $object_name = 'BS_Dashboard';
    public $table_name = 'bs_dashboard';
    public $new_schema = true;
    public $disable_custom_fields = true;

    public function bean_implements($interface)
    {
        return false;
    }
}
