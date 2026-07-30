<?php
/**
 * BS_Notifications — in-app notification center.
 */

class BS_Notifications extends Basic
{
    public $new_schema = true;
    public $module_dir = 'BS_Notifications';
    public $object_name = 'BS_Notifications';
    public $table_name = 'bs_notifications';
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
    public $recipient_type;
    public $recipient_id;
    public $event_code;
    public $message;
    public $payload_json;
    public $is_read;
    public $related_order_id;

    public function bean_implements($interface)
    {
        switch ($interface) {
            case 'ACL':
                return true;
        }
        return false;
    }
}
