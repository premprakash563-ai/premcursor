<?php
/**
 * BS_StatusHistory — timeline of order status changes.
 */

class BS_StatusHistory extends Basic
{
    public $new_schema = true;
    public $module_dir = 'BS_StatusHistory';
    public $object_name = 'BS_StatusHistory';
    public $table_name = 'bs_status_history';
    public $importable = false;

    public $id;
    public $name;
    public $date_entered;
    public $date_modified;
    public $modified_user_id;
    public $created_by;
    public $description;
    public $deleted;
    public $assigned_user_id;
    public $order_id;
    public $from_status;
    public $to_status;
    public $note;

    public function bean_implements($interface)
    {
        switch ($interface) {
            case 'ACL':
                return true;
        }
        return false;
    }
}
