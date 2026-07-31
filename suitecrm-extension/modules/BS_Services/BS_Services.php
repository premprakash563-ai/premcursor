<?php
/**
 * BS_Services — business compliance / registration services catalog.
 */

class BS_Services extends Basic
{
    public $new_schema = true;
    public $module_dir = 'BS_Services';
    public $object_name = 'BS_Services';
    public $table_name = 'bs_services';
    public $importable = true;

    public $id;
    public $name;
    public $date_entered;
    public $date_modified;
    public $modified_user_id;
    public $created_by;
    public $description;
    public $deleted;
    public $assigned_user_id;
    public $price;
    public $gst_percent;
    public $delivery_days;
    public $category;
    public $status;

    public function bean_implements($interface)
    {
        switch ($interface) {
            case 'ACL':
                return true;
        }
        return false;
    }
}
