<?php
/**
 * Cleaner Employee Workload dashlet styling (matches dashboard).
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

        $html = '<style>
          .bs-wl{font-family:"IBM Plex Sans",system-ui,sans-serif;color:#122033}
          .bs-wl table{width:100%;border-collapse:collapse;font-size:13px}
          .bs-wl th{text-align:left;color:#5b6b7c;font-size:11px;text-transform:uppercase;letter-spacing:.04em;padding:0 8px 8px 0;border-bottom:1px solid #d9e2ec;font-weight:600}
          .bs-wl td{padding:10px 8px 10px 0;border-bottom:1px solid #edf1f5}
          .bs-wl .pill{display:inline-block;padding:2px 8px;border-radius:4px;background:#e6f4f2;color:#0f766e;font-size:11px;font-weight:600}
          .bs-wl .note{margin:10px 0 0;color:#5b6b7c;font-size:12px}
        </style>';
        $html .= '<div class="bs-wl"><table><thead><tr><th>Employee</th><th>Availability</th><th>Today New</th><th>Open</th></tr></thead><tbody>';
        if (empty($rows)) {
            $html .= '<tr><td colspan="4">No active employees found</td></tr>';
        } else {
            foreach ($rows as $r) {
                $name = trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''));
                if ($name === '') {
                    $name = $r['user_name'] ?? $r['id'];
                }
                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td><span class="pill">' . htmlspecialchars($r['availability'] ?? 'online', ENT_QUOTES, 'UTF-8') . '</span></td>';
                $html .= '<td>' . (int) ($r['today_new'] ?? 0) . '</td>';
                $html .= '<td>' . (int) ($r['open_projects'] ?? 0) . '</td>';
                $html .= '</tr>';
            }
        }
        $html .= '</tbody></table>';
        $html .= '<p class="note">Daily new-enquiry cap default: 10</p></div>';

        return parent::display($html);
    }
}
