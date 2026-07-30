<?php
/**
 * Professional Business Service CRM home dashboard (clean KPI overview).
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once 'include/Dashlets/Dashlet.php';

class BS_AdminDashboardDashlet extends Dashlet
{
    public function __construct($id, $def = null)
    {
        parent::__construct($id);
        $this->title = 'Business Service Overview';
        $this->isConfigurable = false;
        $this->hasScript = false;
    }

    public function display($text = '')
    {
        $stats = $this->collectStats();
        $recent = $this->recentOrders(6);

        $html = $this->styles();
        $html .= '<div class="bs-dash">';
        $html .= '<div class="bs-dash__intro">';
        $html .= '<p class="bs-dash__eyebrow">Operations</p>';
        $html .= '<h2 class="bs-dash__title">Business Service Overview</h2>';
        $html .= '<p class="bs-dash__sub">Today\'s workload, pipeline health, and latest orders — keep this board clean and act from here.</p>';
        $html .= '</div>';

        $html .= '<div class="bs-dash__kpis">';
        $html .= $this->kpi('Today', (string) $stats['today'], 'New orders', 'index.php?module=BS_Orders&action=index');
        $html .= $this->kpi('Pending', (string) $stats['pending'], 'In progress', 'index.php?module=BS_Orders&action=index');
        $html .= $this->kpi('Month', $this->money($stats['month_revenue']), 'Revenue (paid)', 'index.php?module=BS_Orders&action=index');
        $html .= $this->kpi('Team', (string) $stats['online_employees'], 'Online employees', 'index.php?module=Employees&action=index');
        $html .= '</div>';

        $html .= '<div class="bs-dash__split">';
        $html .= '<section class="bs-dash__panel">';
        $html .= '<div class="bs-dash__panel-head"><h3>Pipeline</h3><span>Open orders by stage</span></div>';
        $html .= '<ul class="bs-dash__pipeline">';
        foreach ($stats['pipeline'] as $label => $count) {
            $html .= '<li><span>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span><strong>' . (int) $count . '</strong></li>';
        }
        $html .= '</ul></section>';

        $html .= '<section class="bs-dash__panel">';
        $html .= '<div class="bs-dash__panel-head"><h3>Latest orders</h3>';
        $html .= '<a class="bs-dash__link" href="index.php?module=BS_Orders&action=index">View all</a></div>';
        if (empty($recent)) {
            $html .= '<p class="bs-dash__empty">No orders yet. Create a Service Order to populate this list.</p>';
        } else {
            $html .= '<table class="bs-dash__table"><thead><tr><th>Order</th><th>Status</th><th>Assigned</th><th>Date</th></tr></thead><tbody>';
            foreach ($recent as $row) {
                $url = 'index.php?module=BS_Orders&action=DetailView&record=' . urlencode($row['id']);
                $html .= '<tr>';
                $html .= '<td><a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') . '</a></td>';
                $html .= '<td><span class="bs-dash__tag">' . htmlspecialchars($this->statusLabel($row['status']), ENT_QUOTES, 'UTF-8') . '</span></td>';
                $html .= '<td>' . htmlspecialchars($row['assigned'] ?: '—', ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars($row['date'], ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';
        }
        $html .= '</section></div>';

        $html .= '<div class="bs-dash__actions">';
        $html .= '<a class="bs-dash__btn bs-dash__btn--primary" href="index.php?module=BS_Orders&action=EditView">New order</a>';
        $html .= '<a class="bs-dash__btn" href="index.php?module=BS_Services&action=index">Services</a>';
        $html .= '<a class="bs-dash__btn" href="index.php?module=BS_Notifications&action=index">Notifications</a>';
        $html .= '<a class="bs-dash__btn" href="index.php?module=BS_Leave&action=index">Leave</a>';
        $html .= '</div>';

        $html .= '</div>';
        return parent::display($html);
    }

    protected function kpi($label, $value, $hint, $href)
    {
        return '<a class="bs-dash__kpi" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">'
            . '<span class="bs-dash__kpi-label">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span>'
            . '<span class="bs-dash__kpi-value">' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</span>'
            . '<span class="bs-dash__kpi-hint">' . htmlspecialchars($hint, ENT_QUOTES, 'UTF-8') . '</span>'
            . '</a>';
    }

    protected function money($amount)
    {
        return '₹' . number_format((float) $amount, 0);
    }

    protected function statusLabel($status)
    {
        $map = [
            'application_submitted' => 'Submitted',
            'documents_received' => 'Docs received',
            'under_verification' => 'Verification',
            'processing' => 'Processing',
            'department_submission' => 'Dept submission',
            'approval_pending' => 'Approval',
            'completed' => 'Completed',
            'certificate_ready' => 'Certificate',
            'delivered' => 'Delivered',
            'payment_pending' => 'Payment pending',
            'on_hold' => 'On hold',
            'cancelled' => 'Cancelled',
        ];
        return $map[$status] ?? $status;
    }

    protected function collectStats()
    {
        global $db;
        $out = [
            'today' => 0,
            'pending' => 0,
            'month_revenue' => 0,
            'online_employees' => 0,
            'pipeline' => [
                'Submitted' => 0,
                'Verification' => 0,
                'Processing' => 0,
                'Approval' => 0,
                'Ready / Delivered' => 0,
            ],
        ];

        if (!$this->tableExists('bs_orders')) {
            return $out;
        }

        $today = $GLOBALS['timedate']->nowDbDate();
        $row = $db->fetchByAssoc($db->query(
            'SELECT COUNT(*) AS c FROM bs_orders WHERE deleted = 0 AND DATE(date_entered) = ' . $db->quoted($today)
        ));
        $out['today'] = (int) ($row['c'] ?? 0);

        $row = $db->fetchByAssoc($db->query(
            "SELECT COUNT(*) AS c FROM bs_orders WHERE deleted = 0 AND status NOT IN ('delivered','cancelled','completed')"
        ));
        $out['pending'] = (int) ($row['c'] ?? 0);

        $monthStart = date('Y-m-01');
        $row = $db->fetchByAssoc($db->query(sprintf(
            "SELECT COALESCE(SUM(total_amount),0) AS s FROM bs_orders
             WHERE deleted = 0 AND payment_status = 'paid' AND DATE(date_entered) >= %s",
            $db->quoted($monthStart)
        )));
        $out['month_revenue'] = (float) ($row['s'] ?? 0);

        $mapSql = [
            'Submitted' => "status IN ('application_submitted','payment_pending','documents_received')",
            'Verification' => "status = 'under_verification'",
            'Processing' => "status IN ('processing','department_submission')",
            'Approval' => "status = 'approval_pending'",
            'Ready / Delivered' => "status IN ('completed','certificate_ready','delivered')",
        ];
        foreach ($mapSql as $label => $where) {
            $r = $db->fetchByAssoc($db->query(
                "SELECT COUNT(*) AS c FROM bs_orders WHERE deleted = 0 AND $where"
            ));
            $out['pipeline'][$label] = (int) ($r['c'] ?? 0);
        }

        $r = $db->fetchByAssoc($db->query(
            "SELECT COUNT(*) AS c FROM users u
             LEFT JOIN users_cstm uc ON uc.id_c = u.id
             WHERE u.deleted = 0 AND u.status = 'Active' AND u.employee_status = 'Active'
               AND COALESCE(uc.availability_c, 'online') = 'online'
               AND (u.is_admin = 0 OR u.is_admin IS NULL)"
        ));
        $out['online_employees'] = (int) ($r['c'] ?? 0);

        return $out;
    }

    protected function recentOrders($limit = 6)
    {
        global $db;
        if (!$this->tableExists('bs_orders')) {
            return [];
        }
        $limit = (int) $limit;
        $sql = "SELECT o.id, o.name, o.status, o.date_entered,
                       CONCAT(COALESCE(u.first_name,''),' ',COALESCE(u.last_name,'')) AS assigned
                FROM bs_orders o
                LEFT JOIN users u ON u.id = o.assigned_user_id
                WHERE o.deleted = 0
                ORDER BY o.date_entered DESC
                LIMIT {$limit}";
        $rows = [];
        $res = $db->query($sql);
        while ($row = $db->fetchByAssoc($res)) {
            $rows[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'status' => $row['status'],
                'assigned' => trim($row['assigned']),
                'date' => !empty($row['date_entered']) ? substr($row['date_entered'], 0, 10) : '',
            ];
        }
        return $rows;
    }

    protected function tableExists($table)
    {
        global $db;
        $res = $db->query('SHOW TABLES LIKE ' . $db->quoted($table));
        return (bool) $db->fetchByAssoc($res);
    }

    protected function styles()
    {
        return <<<'CSS'
<style>
@import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&display=swap');
.bs-dash{
  --ink:#122033; --muted:#5b6b7c; --line:#d9e2ec; --surface:#f5f7fa; --panel:#ffffff;
  --accent:#0f766e; --accent-soft:#e6f4f2; --shadow:0 1px 0 rgba(18,32,51,.04);
  font-family:"IBM Plex Sans", system-ui, sans-serif; color:var(--ink);
  background:linear-gradient(180deg,#eef3f7 0%, #f7f9fb 42%, #ffffff 100%);
  border:1px solid var(--line); border-radius:10px; padding:22px 22px 18px; box-shadow:var(--shadow);
}
.bs-dash__eyebrow{margin:0 0 4px; font-size:11px; letter-spacing:.14em; text-transform:uppercase; color:var(--accent); font-weight:600;}
.bs-dash__title{margin:0 0 6px; font-size:26px; line-height:1.15; font-weight:700; letter-spacing:-.02em;}
.bs-dash__sub{margin:0 0 18px; max-width:54rem; color:var(--muted); font-size:14px; line-height:1.5;}
.bs-dash__kpis{display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; margin-bottom:16px;}
.bs-dash__kpi{display:flex; flex-direction:column; gap:6px; text-decoration:none; color:inherit;
  background:var(--panel); border:1px solid var(--line); border-radius:8px; padding:14px 14px 12px; transition:border-color .15s ease, transform .15s ease;}
.bs-dash__kpi:hover{border-color:#9fb3c8; transform:translateY(-1px);}
.bs-dash__kpi-label{font-size:12px; color:var(--muted); font-weight:500;}
.bs-dash__kpi-value{font-size:28px; font-weight:700; letter-spacing:-.03em; line-height:1;}
.bs-dash__kpi-hint{font-size:12px; color:#7b8b9c;}
.bs-dash__split{display:grid; grid-template-columns:0.9fr 1.4fr; gap:12px;}
.bs-dash__panel{background:var(--panel); border:1px solid var(--line); border-radius:8px; padding:14px;}
.bs-dash__panel-head{display:flex; align-items:baseline; justify-content:space-between; gap:10px; margin-bottom:10px;}
.bs-dash__panel-head h3{margin:0; font-size:15px; font-weight:600;}
.bs-dash__panel-head span,.bs-dash__link{font-size:12px; color:var(--accent); text-decoration:none; font-weight:500;}
.bs-dash__pipeline{list-style:none; margin:0; padding:0;}
.bs-dash__pipeline li{display:flex; justify-content:space-between; align-items:center; padding:9px 0; border-bottom:1px solid #edf1f5; font-size:13px;}
.bs-dash__pipeline li:last-child{border-bottom:0;}
.bs-dash__pipeline strong{font-variant-numeric:tabular-nums; background:var(--accent-soft); color:var(--accent); padding:2px 8px; border-radius:4px; font-size:12px;}
.bs-dash__table{width:100%; border-collapse:collapse; font-size:13px;}
.bs-dash__table th{text-align:left; color:var(--muted); font-weight:500; font-size:11px; letter-spacing:.04em; text-transform:uppercase; padding:0 8px 8px 0; border-bottom:1px solid var(--line);}
.bs-dash__table td{padding:10px 8px 10px 0; border-bottom:1px solid #edf1f5; vertical-align:middle;}
.bs-dash__table a{color:var(--ink); text-decoration:none; font-weight:600;}
.bs-dash__table a:hover{color:var(--accent);}
.bs-dash__tag{display:inline-block; padding:2px 8px; border-radius:4px; background:#eef2f6; color:#334e68; font-size:11px; font-weight:500;}
.bs-dash__empty{margin:8px 0 0; color:var(--muted); font-size:13px;}
.bs-dash__actions{display:flex; flex-wrap:wrap; gap:8px; margin-top:14px;}
.bs-dash__btn{display:inline-flex; align-items:center; justify-content:center; padding:8px 14px; border-radius:6px; border:1px solid var(--line);
  background:#fff; color:var(--ink); text-decoration:none; font-size:13px; font-weight:550;}
.bs-dash__btn:hover{border-color:#9fb3c8;}
.bs-dash__btn--primary{background:var(--accent); border-color:var(--accent); color:#fff;}
.bs-dash__btn--primary:hover{filter:brightness(.96); border-color:var(--accent);}
@media (max-width: 1100px){
  .bs-dash__kpis{grid-template-columns:repeat(2,minmax(0,1fr));}
  .bs-dash__split{grid-template-columns:1fr;}
}
@media (max-width: 640px){
  .bs-dash{padding:16px;}
  .bs-dash__kpis{grid-template-columns:1fr;}
  .bs-dash__title{font-size:22px;}
}
</style>
CSS;
    }
}
