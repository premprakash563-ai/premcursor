<?php
/**
 * Operations Board — ultra-safe (0.4.4)
 * Functions first; try/catch; no timedate hard-dep; no re-bootstrap.
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

if (!function_exists('bs_board_table_exists')) {
    function bs_board_table_exists($table)
    {
        global $db;
        if (empty($db)) {
            return false;
        }
        $res = $db->query('SHOW TABLES LIKE ' . $db->quoted($table));
        return (bool) $db->fetchByAssoc($res);
    }

    function bs_board_status_label($status)
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
            'payment_status' => 'Payment',
            'payment_pending' => 'Payment pending',
            'on_hold' => 'On hold',
            'cancelled' => 'Cancelled',
        ];
        return $map[$status] ?? (string) $status;
    }

    function bs_board_today()
    {
        if (!empty($GLOBALS['timedate']) && method_exists($GLOBALS['timedate'], 'nowDbDate')) {
            return $GLOBALS['timedate']->nowDbDate();
        }
        return date('Y-m-d');
    }

    function bs_board_collect_stats()
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
        try {
            if (empty($db) || !bs_board_table_exists('bs_orders')) {
                return $out;
            }
            $today = bs_board_today();
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
        } catch (Throwable $e) {
            // keep zeros
        }
        return $out;
    }

    function bs_board_recent_orders($limit = 8)
    {
        global $db;
        $rows = [];
        try {
            if (empty($db) || !bs_board_table_exists('bs_orders')) {
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
        } catch (Throwable $e) {
            return [];
        }
        return $rows;
    }
}

try {
    global $current_user, $db;

    if (empty($current_user->id)) {
        header('Location: index.php?module=Users&action=Login');
        exit;
    }

    $stats = bs_board_collect_stats();
    $recent = bs_board_recent_orders(8);
    $who = !empty($current_user->full_name) ? $current_user->full_name : ($current_user->user_name ?? 'User');
} catch (Throwable $e) {
    header('Content-Type: text/plain; charset=UTF-8');
    echo "Operations Board error: " . $e->getMessage() . "\n" . $e->getFile() . ':' . $e->getLine();
    exit;
}

header('Content-Type: text/html; charset=UTF-8');
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Operations Board · Yoogle Consultancy</title>
  <style>
    :root{--ink:#122033;--muted:#5b6b7c;--line:#d7e0ea;--accent:#0f766e;--soft:#e7f5f3}
    *{box-sizing:border-box}
    body{margin:0;background:#eef3f7;font-family:system-ui,Segoe UI,sans-serif;color:var(--ink)}
    .top{padding:14px 20px;background:#0f1c2b;color:#fff;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
    .top a{color:#fff;text-decoration:none;font-size:13px;margin-left:12px}
    .wrap{max-width:1180px;margin:18px auto;padding:0 16px 40px}
    .board{background:#fff;border:1px solid var(--line);border-radius:14px;padding:28px}
    .eyebrow{margin:0 0 6px;font-size:11px;letter-spacing:.16em;text-transform:uppercase;color:var(--accent);font-weight:700}
    h1{margin:0 0 8px;font-size:32px}
    .lead{color:var(--muted);margin:0 0 18px}
    .kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px}
    .kpi{border:1px solid var(--line);border-radius:12px;padding:16px;text-decoration:none;color:inherit}
    .kpi span{display:block;font-size:12px;color:var(--muted)}
    .kpi strong{display:block;font-size:28px;margin:6px 0}
    .kpi em{font-style:normal;font-size:12px;color:#7b8b9c}
    .grid{display:grid;grid-template-columns:.9fr 1.4fr;gap:12px}
    .panel{border:1px solid var(--line);border-radius:12px;padding:16px}
    .panel h2{margin:0 0 10px;font-size:16px}
    .pipe{list-style:none;margin:0;padding:0}
    .pipe li{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #edf1f5;font-size:13px}
    table{width:100%;border-collapse:collapse;font-size:13px}
    th,td{text-align:left;padding:8px 6px;border-bottom:1px solid #edf1f5}
    .btn{display:inline-block;padding:10px 14px;border-radius:8px;background:var(--accent);color:#fff;text-decoration:none;font-weight:600;font-size:13px;margin-right:8px}
    .foot{margin-top:16px;color:#7b8b9c;font-size:12px}
    @media(max-width:900px){.kpis,.grid{grid-template-columns:1fr}}
  </style>
</head>
<body>
  <div class="top">
    <strong>Yoogle Consultancy · CRM</strong>
    <nav>
      <a href="index.php?module=Home&amp;action=index">Home</a>
      <a href="index.php?module=BS_Orders&amp;action=index">Orders</a>
      <a href="index.php?module=BS_Services&amp;action=index">Services</a>
      <a href="index.php?module=Users&amp;action=Logout">Logout</a>
    </nav>
  </div>
  <div class="wrap"><div class="board">
    <p class="eyebrow">Yoogle Consultancy · Business Service CRM</p>
    <h1>Operations Board</h1>
    <p class="lead">Daily ops view — orders, pipeline, team load.</p>
    <p><a class="btn" href="index.php?module=BS_Orders&amp;action=EditView">+ New order</a></p>
    <div class="kpis">
      <a class="kpi" href="index.php?module=BS_Orders&amp;action=index"><span>Today</span><strong><?php echo (int)$stats['today']; ?></strong><em>New orders</em></a>
      <a class="kpi" href="index.php?module=BS_Orders&amp;action=index"><span>Pending</span><strong><?php echo (int)$stats['pending']; ?></strong><em>Open pipeline</em></a>
      <a class="kpi" href="index.php?module=BS_Orders&amp;action=index"><span>Revenue</span><strong>₹<?php echo number_format((float)$stats['month_revenue'],0); ?></strong><em>Paid this month</em></a>
      <a class="kpi" href="index.php?module=Employees&amp;action=index"><span>Online</span><strong><?php echo (int)$stats['online_employees']; ?></strong><em>Employees</em></a>
    </div>
    <div class="grid">
      <div class="panel">
        <h2>Pipeline</h2>
        <ul class="pipe">
        <?php foreach ($stats['pipeline'] as $label => $count) {
            echo '<li><span>'.htmlspecialchars($label,ENT_QUOTES,'UTF-8').'</span><strong>'.(int)$count.'</strong></li>';
        } ?>
        </ul>
      </div>
      <div class="panel">
        <h2>Latest orders</h2>
        <?php if (empty($recent)) { echo '<p class="lead">No orders yet.</p>'; } else { ?>
        <table><thead><tr><th>Order</th><th>Status</th><th>Assignee</th><th>Date</th></tr></thead><tbody>
        <?php foreach ($recent as $row) {
            $url = 'index.php?module=BS_Orders&action=DetailView&record='.urlencode($row['id']);
            echo '<tr><td><a href="'.htmlspecialchars($url,ENT_QUOTES,'UTF-8').'">'.htmlspecialchars($row['name'],ENT_QUOTES,'UTF-8').'</a></td>';
            echo '<td>'.htmlspecialchars(bs_board_status_label($row['status']),ENT_QUOTES,'UTF-8').'</td>';
            echo '<td>'.htmlspecialchars($row['assigned']?:'—',ENT_QUOTES,'UTF-8').'</td>';
            echo '<td>'.htmlspecialchars($row['date'],ENT_QUOTES,'UTF-8').'</td></tr>';
        } ?>
        </tbody></table>
        <?php } ?>
      </div>
    </div>
    <p class="foot">Signed in as <?php echo htmlspecialchars($who,ENT_QUOTES,'UTF-8'); ?> · Operations Board 0.4.4</p>
  </div></div>
</body>
</html>
<?php
if (!isset($GLOBALS['log']) || !is_object($GLOBALS['log']) || !method_exists($GLOBALS['log'], 'debug')) {
    if (class_exists('LoggerManager')) {
        $GLOBALS['log'] = LoggerManager::getLogger('SugarCRM');
    }
}
if (function_exists('sugar_cleanup')) {
    sugar_cleanup(true);
}
exit;
