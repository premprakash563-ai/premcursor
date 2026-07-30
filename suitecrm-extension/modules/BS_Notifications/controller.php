<?php
/**
 * Mark notification read when opened.
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once 'include/MVC/Controller/SugarController.php';

class BS_NotificationsController extends SugarController
{
    public function action_DetailView()
    {
        parent::action_DetailView();
        if (!empty($this->bean) && empty($this->bean->is_read)) {
            $this->bean->is_read = 1;
            $this->bean->save();
        }
    }

    public function action_mark_all_read()
    {
        global $current_user, $db;
        if (empty($current_user->id)) {
            sugar_die('Not authorized');
        }
        $db->query(sprintf(
            "UPDATE bs_notifications SET is_read = 1
             WHERE deleted = 0 AND recipient_type = 'user' AND recipient_id = %s",
            $db->quoted($current_user->id)
        ));
        SugarApplication::appendSuccessMessage('All notifications marked as read.');
        SugarApplication::redirect('index.php?module=BS_Notifications&action=index');
    }
}
