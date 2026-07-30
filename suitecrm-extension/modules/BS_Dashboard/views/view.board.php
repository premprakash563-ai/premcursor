<?php
/**
 * Operations Board view — self-contained professional UI.
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once 'include/MVC/View/SugarView.php';

class BS_DashboardViewBoard extends SugarView
{
    public function display()
    {
        global $current_user, $db, $timedate;

        $stats = $this->collectStats();
        $recent = $this->recentOrders(8);

        echo $this->css();
        echo '<div class="bs-board">';
        echo '<header class="bs-board__hero">';
        echo '<div>';
        echo '<p class="bs-board__eyebrow">Yoogle Consultancy · Business Service CRM</p>';
        echo '<h1>Operations Board</h1>';
        echo '<p class="bs-board__lead">Clean live view of today\'s orders, pipeline health, and team load — built for daily ops, not clutter.</p>';
        echo '</div>';
        echo '<div class="bs-board__hero-actions">';
        echo '<a class="bs-btn bs-btn--primary" href="index.php?module=BS_Orders&action=EditView">+ New order</a>';
        echo '<a class="bs-btn" href="index.php?module=BS_Services&action=index">Services</a>';
        echo '<a class="bs-btn" href="index.php?module=BS_Notifications&action=index">Notifications</a>';
        echo '</div></header>';

        echo '<section class="bs-board__kpis">';
        echo $this->kpi('Today', (string) $stats['today'], 'New orders today', 'index.php?module=BS_Orders&action=index');
        echo $this->kpi('Pending', (string) $stats['pending'], 'Open in pipeline', 'index.php?module=BS_Orders&action=index');
        echo $this->kpi('Revenue', '₹' . number_format((float) $stats['month_revenue'], 0), 'Paid this month', 'index.php?module=BS_Orders&action=index');
        echo $this->kpi('Online', (string) $stats['online_employees'], 'Employees available', 'index.php?module=Employees&action=index');
        echo '</section>';

        echo '<section class="bs-board__grid">';
        echo '<div class="bs-panel">';
        echo '<div class="bs-panel__head"><h2>Pipeline</h2><span>Open work by stage</span></div>';
        echo '<ul class="bs-pipe">';
        foreach ($stats['pipeline'] as $label => $count) {
            $pct = $stats['pending'] > 0 ? round(($count / max($stats['pending'], 1)) * 100) : 0;
            if ($label === 'Ready / Delivered') {
                $pct = 0;
            }
            echo '<li>';
            echo '<div class="bs-pipe__row"><span>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span><strong>' . (int) $count . '</strong></div>';
            echo '<div class="bs-pipe__bar"><i style="width:' . (int) min(100, $pct) . '%"></i></div>';
            echo '</li>';
        }
        echo '</ul></div>';

        echo '<div class="bs-panel">';
        echo '<div class="bs-panel__head"><h2>Latest orders</h2><a href="index.php?module=BS_Orders&action=index">View all</a></div>';
        if (empty($recent)) {
            echo '<p class="bs-empty">No orders yet. Create a Service Order to populate this board.</p>';
        } else {
            echo '<table class="bs-table"><thead><tr><th>Order</th><th>Status</th><th>Assignee</th><th>Date</th></tr></thead><tbody>';
            foreach ($recent as $row) {
                $url = 'index.php?module=BS_Orders&action=DetailView&record=' . urlencode($row['id']);
                echo '<tr>';
                echo '<td><a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') . '</a></td>';
                echo '<td><span class="bs-tag">' . htmlspecialchars($this->statusLabel($row['status']), ENT_QUOTES, 'UTF-8') . '</span></td>';
                echo '<td>' . htmlspecialchars($row['assigned'] ?: '—', ENT_QUOTES, 'UTF-8') . '</td>';
                echo '<td>' . htmlspecialchars($row['date'], ENT_QUOTES, 'UTF-8') . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        }
        echo '</div></section>';

        echo '<section class="bs-board__quick">';
        echo '<a href="index.php?module=BS_OrderDocuments&action=index">Documents review</a>';
        echo '<a href="index.php?module=BS_Leave&action=index">Leave requests</a>';
        echo '<a href="index.php?module=BS_Orders&action=index">All service orders</a>';
        echo '<a href="index.php?module=Home&action=index">Classic SuiteCRM home</a>';
        echo '</section>';

        $who = !empty($current_user->full_name) ? $current_user->full_name : ($current_user->user_name ?? 'User');
        echo '<footer class="bs-board__foot">Signed in as ' . htmlspecialchars($who, ENT_QUOTES, 'UTF-8') . ' · Board refreshes on each open</footer>';
        echo '</div>';
    }

    protected function kpi($label, $value, $hint, $href)
    {
        return '<a class="bs-kpi" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">'
            . '<span>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span>'
            . '<strong>' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</strong>'
            . '<em>' . htmlspecialchars($hint, ENT_QUOTES, 'UTF-8') . '</em>'
            . '</a>';
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
            "SELECT COALESCE(SUM(total_amount),0) AS s FROM bs_orders WHERE deleted = 0 AND payment_status = 'paid' AND DATE(date_entered) >= %s",
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
            $r = $db->fetchByAssoc($db->query("SELECT COUNT(*) AS c FROM bs_orders WHERE deleted = 0 AND $where"));
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

    protected function recentOrders($limit = 8)
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

    protected function css()
    {
        return <<<'CSS'
<style>
@import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&display=swap');
.bs-board{--ink:#122033;--muted:#5b6b7c;--line:#d7e0ea;--accent:#0f766e;--soft:#e7f5f3;--bg:#eef3f7;
  font-family:"IBM Plex Sans",system-ui,sans-serif;color:var(--ink);
  background:linear-gradient(165deg,#e8eef4 0%,#f7f9fb 45%,#ffffff 100%);
  border:1px solid var(--line);border-radius:14px;padding:28px;margin:8px 0 24px;box-shadow:0 10px 30px rgba(18,32,51,.06)}
.bs-board__hero{display:flex;justify-content:space-between;gap:20px;align-items:flex-end;margin-bottom:22px;flex-wrap:wrap}
.bs-board__eyebrow{margin:0 0 6px;font-size:11px;letter-spacing:.16em;text-transform:uppercase;color:var(--accent);font-weight:700}
.bs-board h1{margin:0 0 8px;font-size:34px;line-height:1.1;letter-spacing:-.03em}
.bs-board__lead{margin:0;max-width:40rem;color:var(--muted);font-size:15px;line-height:1.55}
.bs-board__hero-actions{display:flex;gap:8px;flex-wrap:wrap}
.bs-btn{display:inline-flex;align-items:center;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:#fff;color:var(--ink);text-decoration:none;font-weight:600;font-size:13px}
.bs-btn--primary{background:var(--accent);border-color:var(--accent);color:#fff}
.bs-board__kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:16px}
.bs-kpi{background:#fff;border:1px solid var(--line);border-radius:12px;padding:16px;text-decoration:none;color:inherit;display:flex;flex-direction:column;gap:8px;transition:transform .15s ease,border-color .15s ease}
.bs-kpi:hover{transform:translateY(-2px);border-color:#9eb1c5}
.bs-kpi span{font-size:12px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.06em}
.bs-kpi strong{font-size:32px;letter-spacing:-.03em;line-height:1}
.bs-kpi em{font-style:normal;font-size:12px;color:#7b8b9c}
.bs-board__grid{display:grid;grid-template-columns:0.9fr 1.4fr;gap:12px}
.bs-panel{background:#fff;border:1px solid var(--line);border-radius:12px;padding:16px}
.bs-panel__head{display:flex;justify-content:space-between;align-items:baseline;margin-bottom:12px;gap:10px}
.bs-panel__head h2{margin:0;font-size:16px}
.bs-panel__head span,.bs-panel__head a{font-size:12px;color:var(--accent);text-decoration:none;font-weight:600}
.bs-pipe{list-style:none;margin:0;padding:0}
.bs-pipe li{padding:10px 0;border-bottom:1px solid #edf1f5}
.bs-pipe li:last-child{border-bottom:0}
.bs-pipe__row{display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px}
.bs-pipe__row strong{background:var(--soft);color:var(--accent);padding:2px 8px;border-radius:999px;font-size:12px}
.bs-pipe__bar{height:6px;background:#eef2f6;border-radius:99px;overflow:hidden}
.bs-pipe__bar i{display:block;height:100%;background:linear-gradient(90deg,#0f766e,#2dd4bf);border-radius:99px}
.bs-table{width:100%;border-collapse:collapse;font-size:13px}
.bs-table th{text-align:left;color:var(--muted);font-size:11px;text-transform:uppercase;letter-spacing:.05em;padding:0 8px 8px 0;border-bottom:1px solid var(--line)}
.bs-table td{padding:11px 8px 11px 0;border-bottom:1px solid #edf1f5}
.bs-table a{color:var(--ink);font-weight:700;text-decoration:none}
.bs-table a:hover{color:var(--accent)}
.bs-tag{display:inline-block;padding:3px 8px;border-radius:6px;background:#eef2f6;font-size:11px;font-weight:600}
.bs-empty{color:var(--muted);font-size:13px}
.bs-board__quick{display:flex;flex-wrap:wrap;gap:10px;margin-top:16px}
.bs-board__quick a{color:var(--accent);font-size:13px;font-weight:600;text-decoration:none;padding:8px 10px;background:#fff;border:1px solid var(--line);border-radius:8px}
.bs-board__foot{margin-top:16px;color:#7b8b9c;font-size:12px}
@media (max-width:1100px){.bs-board__kpis{grid-template-columns:repeat(2,1fr)}.bs-board__grid{grid-template-columns:1fr}}
@media (max-width:640px){.bs-board{padding:16px}.bs-board h1{font-size:26px}.bs-board__kpis{grid-template-columns:1fr}}
</style>
CSS;
    }
}
