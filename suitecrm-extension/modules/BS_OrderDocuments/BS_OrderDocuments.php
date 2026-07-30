<?php
/**
 * BS_OrderDocuments — uploaded KYC / application files with review workflow.
 */

class BS_OrderDocuments extends Basic
{
    public $new_schema = true;
    public $module_dir = 'BS_OrderDocuments';
    public $object_name = 'BS_OrderDocuments';
    public $table_name = 'bs_order_documents';
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
    public $doc_code;
    public $filename;
    public $file_path;
    public $mime_type;
    public $status;
    public $reviewer_id;
    public $review_note;
    public $document_id;

    public function bean_implements($interface)
    {
        switch ($interface) {
            case 'ACL':
                return true;
        }
        return false;
    }
}
