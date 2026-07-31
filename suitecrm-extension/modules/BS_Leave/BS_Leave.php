<?php
/**
 * BS_Leave — employee leave requests (used by auto-assignment skip rules).
 */

class BS_Leave extends Basic
{
    public $new_schema = true;
    public $module_dir = 'BS_Leave';
    public $object_name = 'BS_Leave';
    public $table_name = 'bs_leave';
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
    public $date_start;
    public $date_end;
    public $status;

    public function bean_implements($interface)
    {
        switch ($interface) {
            case 'ACL':
                return true;
        }
        return false;
    }

    public function save($check_notify = false)
    {
        if (empty($this->name)) {
            $this->name = 'Leave ' . ($this->date_start ?: date('Y-m-d'));
        }
        return parent::save($check_notify);
    }
}
