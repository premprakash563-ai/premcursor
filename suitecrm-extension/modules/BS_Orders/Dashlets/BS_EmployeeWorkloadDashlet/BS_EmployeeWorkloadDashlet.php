<?php
/**
 * Admin dashlet: employee workload (today's new enquiries + open projects).
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once 'include/Dashlets/Dashlet.php';

class BS_EmployeeWorkloadDashlet extends Dashlet
{
    public function __construct($id, $def = null)
    {
        parent::__construct($id);
        $this->title = 'Employee Workload';
        $this->isConfigurable = false;
        $this->hasScript = false;
    }

    public function display($text = '')
    {
        global $current_user;
        if (empty($current_user->is_admin)) {
            return parent::display('<em>Admin only</em>');
        }

        if (!file_exists('custom/include/BS/Assignment/BS_AssignmentEngine.php')) {
            return parent::display('<em>Assignment engine not installed</em>');
        }

        require_once 'custom/include/BS/Assignment/BS_AssignmentEngine.php';
        $engine = new BS_AssignmentEngine();
        $rows = $engine->getWorkloadReport();

        $html = '<table class="list view" width="100%" cellspacing="0" cellpadding="0" border="0">';
        $html .= '<tr height="20"><th>Employee</th><th>Availability</th><th>Today New</th><th>Open Projects</th></tr>';
        if (empty($rows)) {
            $html .= '<tr><td colspan="4"><em>No active employees found</em></td></tr>';
        } else {
            foreach ($rows as $r) {
                $name = trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''));
                if ($name === '') {
                    $name = $r['user_name'] ?? $r['id'];
                }
                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars($r['availability'] ?? 'online', ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . (int) ($r['today_new'] ?? 0) . '</td>';
                $html .= '<td>' . (int) ($r['open_projects'] ?? 0) . '</td>';
                $html .= '</tr>';
            }
        }
        $html .= '</table>';
        $html .= '<p style="margin-top:8px;font-size:11px;">Daily new-enquiry cap default: 10 (configurable).</p>';

        return parent::display($html);
    }
}
