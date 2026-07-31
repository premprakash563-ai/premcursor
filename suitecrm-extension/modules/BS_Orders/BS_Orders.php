<?php
/**
 * BS_Orders — customer service orders / projects.
 */

class BS_Orders extends Basic
{
    public $new_schema = true;
    public $module_dir = 'BS_Orders';
    public $object_name = 'BS_Orders';
    public $table_name = 'bs_orders';
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
    public $account_id;
    public $contact_id;
    public $service_id;
    public $status;
    public $payment_status;
    public $amount;
    public $gst_amount;
    public $total_amount;
    public $assigned_at;
    public $is_new_enquiry;
    public $gstin;
    public $pan;
    public $source;
    public $application_json;

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
            $this->name = $this->generateOrderName();
        }
        if ($this->status === '' || $this->status === null) {
            $this->status = 'application_submitted';
        }
        if ($this->payment_status === '' || $this->payment_status === null) {
            $this->payment_status = 'pending';
        }
        if ($this->is_new_enquiry === '' || $this->is_new_enquiry === null) {
            $this->is_new_enquiry = 1;
        }
        return parent::save($check_notify);
    }

    protected function generateOrderName()
    {
        return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid((string) mt_rand(), true)), 0, 6));
    }
}
