<?php
/**
 * Operations Board — self-contained entry point.
 * DO NOT re-bootstrap entryPoint.php (SuiteCRM already did that).
 * DO NOT use SugarView / module actions (causes "no action by that name: index").
 *
 * URL: index.php?entryPoint=bs_operations_board
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $current_user, $db, $timedate;

if (empty($current_user->id)) {
    sugar_cleanup(true);
    header('Location: index.php?module=Users&action=Login');
    exit;
}

$stats = bs_board_collect_stats();
$recent = bs_board_recent_orders(8);
$who = !empty($current_user->full_name) ? $current_user->full_name : ($current_user->user_name ?? 'User');

header('Content-Type: text/html; charset=UTF-8');
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Operations Board · Yoogle Consultancy</title>
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root{--ink:#122033;--muted:#5b6b7c;--line:#d7e0ea;--accent:#0f766e;--soft:#e7f5f3}
    *{box-sizing:border-box}
    body{margin:0;background:#eef3f7;font-family:"IBM Plex Sans",system-ui,sans-serif;color:var(--ink)}
    .top{padding:14px 20px;background:#0f1c2b;color:#fff;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
    .top strong{letter-spacing:.02em}
    .top nav{display:flex;gap:14px;flex-wrap:wrap;font-size:13px}
    .top a{color:#fff;text-decoration:none}
    .top a.out{color:#9fefdf}
    .wrap{max-width:1180px;margin:18px auto;padding:0 16px 40px}
    .bs-board{background:linear-gradient(165deg,#e8eef4 0%,#f7f9fb 45%,#fff 100%);border:1px solid var(--line);border-radius:14px;padding:28px;box-shadow:0 10px 30px rgba(18,32,51,.06)}
    .hero{display:flex;justify-content:space-between;gap:20px;align-items:flex-end;margin-bottom:22px;flex-wrap:wrap}
    .eyebrow{margin:0 0 6px;font-size:11px;letter-spacing:.16em;text-transform:uppercase;color:var(--accent);font-weight:700}
    h1{margin:0 0 8px;font-size:34px;line-height:1.1;letter-spacing:-.03em}
    .lead{margin:0;max-width:40rem;color:var(--muted);font-size:15px;line-height:1.55}
    .actions{display:flex;gap:8px;flex-wrap:wrap}
    .btn{display:inline-flex;align-items:center;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:#fff;color:var(--ink);text-decoration:none;font-weight:600;font-size:13px}
    .btn.primary{background:var(--accent);border-color:var(--accent);color:#fff}
    .kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:16px}
    .kpi{background:#fff;border:1px solid var(--line);border-radius:12px;padding:16px;text-decoration:none;color:inherit;display:flex;flex-direction:column;gap:8px}
    .kpi span{font-size:12px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.06em}
    .kpi strong{font-size:32px;letter-spacing:-.03em;line-height:1}
    .kpi em{font-style:normal;font-size:12px;color:#7b8b9c}
    .grid{display:grid;grid-template-columns:.9fr 1.4fr;gap:12px}
    .panel{background:#fff;border:1px solid var(--line);border-radius:12px;padding:16px}
    .panel-head{display:flex;justify-content:space-between;align-items:baseline;margin-bottom:12px;gap:10px}
    .panel-head h2{margin:0;font-size:16px}
    .panel-head span,.panel-head a{font-size:12px;color:var(--accent);text-decoration:none;font-weight:600}
    .pipe{list-style:none;margin:0;padding:0}
    .pipe li{padding:10px 0;border-bottom:1px solid #edf1f5}
    .pipe-row{display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px}
    .pipe-row strong{background:var(--soft);color:var(--accent);padding:2px 8px;border-radius:999px;font-size:12px}
    .bar{height:6px;background:#eef2f6;border-radius:99px;overflow:hidden}
    .bar i{display:block;height:100%;background:linear-gradient(90deg,#0f766e,#2dd4bf);border-radius:99px}
    table{width:100%;border-collapse:collapse;font-size:13px}
    th{text-align:left;color:var(--muted);font-size:11px;text-transform:uppercase;letter-spacing:.05em;padding:0 8px 8px 0;border-bottom:1px solid var(--line)}
    td{padding:11px 8px 11px 0;border-bottom:1px solid #edf1f5}
    td a{color:var(--ink);font-weight:700;text-decoration:none}
    .tag{display:inline-block;padding:3px 8px;border-radius:6px;background:#eef2f6;font-size:11px;font-weight:600}
    .empty{color:var(--muted);font-size:13px}
    .quick{display:flex;flex-wrap:wrap;gap:10px;margin-top:16px}
    .quick a{color:var(--accent);font-size:13px;font-weight:600;text-decoration:none;padding:8px 10px;background:#fff;border:1px solid var(--line);border-radius:8px}
    .foot{margin-top:16px;color:#7b8b9c;font-size:12px}
    @media (max-width:1100px){.kpis{grid-template-columns:repeat(2,1fr)}.grid{grid-template-columns:1fr}}
    @media (max-width:640px){.bs-board{padding:16px}h1{font-size:26px}.kpis{grid-template-columns:1fr}}
  </style>
</head>
<body>
  <div class="top">
    <strong>Yoogle Consultancy · CRM</strong>
    <nav>
      <a href="index.php?module=Home&amp;action=index">Home</a>
      <a href="index.php?module=BS_Services&amp;action=index">Services</a>
      <a href="index.php?module=BS_Orders&amp;action=index">Orders</a>
      <a href="index.php?module=BS_Notifications&amp;action=index">Notifications</a>
      <a class="out" href="index.php?module=Users&amp;action=Logout">Logout</a>
    </nav>
  </div>
  <div class="wrap">
    <div class="bs-board">
      <header class="hero">
        <div>
          <p class="eyebrow">Yoogle Consultancy · Business Service CRM</p>
          <h1>Operations Board</h1>
          <p class="lead">Clean live view of today's orders, pipeline health, and team load — built for daily ops.</p>
        </div>
        <div class="actions">
          <a class="btn primary" href="index.php?module=BS_Orders&amp;action=EditView">+ New order</a>
          <a class="btn" href="index.php?module=BS_Services&amp;action=index">Services</a>
          <a class="btn" href="index.php?module=BS_Notifications&amp;action=index">Notifications</a>
        </div>
      </header>

      <section class="kpis">
        <a class="kpi" href="index.php?module=BS_Orders&amp;action=index"><span>Today</span><strong><?php echo (int) $stats['today']; ?></strong><em>New orders today</em></a>
        <a class="kpi" href="index.php?module=BS_Orders&amp;action=index"><span>Pending</span><strong><?php echo (int) $stats['pending']; ?></strong><em>Open in pipeline</em></a>
        <a class="kpi" href="index.php?module=BS_Orders&amp;action=index"><span>Revenue</span><strong>₹<?php echo number_format((float) $stats['month_revenue'], 0); ?></strong><em>Paid this month</em></a>
        <a class="kpi" href="index.php?module=Employees&amp;action=index"><span>Online</span><strong><?php echo (int) $stats['online_employees']; ?></strong><em>Employees available</em></a>
      </section>

      <section class="grid">
        <div class="panel">
          <div class="panel-head"><h2>Pipeline</h2><span>Open work by stage</span></div>
          <ul class="pipe">
<?php
foreach ($stats['pipeline'] as $label => $count) {
    $base = max((int) $stats['pending'], 1);
    $pct = ($label === 'Ready / Delivered') ? 0 : (int) round(($count / $base) * 100);
    echo '<li><div class="pipe-row"><span>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span><strong>' . (int) $count . '</strong></div>';
    echo '<div class="bar"><i style="width:' . min(100, $pct) . '%"></i></div></li>';
}
?>
          </ul>
        </div>
        <div class="panel">
          <div class="panel-head"><h2>Latest orders</h2><a href="index.php?module=BS_Orders&amp;action=index">View all</a></div>
<?php if (empty($recent)) { ?>
          <p class="empty">No orders yet. Create a Service Order to populate this board.</p>
<?php } else { ?>
          <table>
            <thead><tr><th>Order</th><th>Status</th><th>Assignee</th><th>Date</th></tr></thead>
            <tbody>
<?php
    foreach ($recent as $row) {
        $url = 'index.php?module=BS_Orders&action=DetailView&record=' . urlencode($row['id']);
        echo '<tr><td><a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') . '</a></td>';
        echo '<td><span class="tag">' . htmlspecialchars(bs_board_status_label($row['status']), ENT_QUOTES, 'UTF-8') . '</span></td>';
        echo '<td>' . htmlspecialchars($row['assigned'] ?: '—', ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars($row['date'], ENT_QUOTES, 'UTF-8') . '</td></tr>';
    }
?>
            </tbody>
          </table>
<?php } ?>
        </div>
      </section>

      <section class="quick">
        <a href="index.php?module=BS_OrderDocuments&amp;action=index">Documents review</a>
        <a href="index.php?module=BS_Leave&amp;action=index">Leave requests</a>
        <a href="index.php?module=BS_Orders&amp;action=index">All service orders</a>
        <a href="index.php?module=Home&amp;action=index">Classic SuiteCRM home</a>
      </section>
      <footer class="foot">Signed in as <?php echo htmlspecialchars($who, ENT_QUOTES, 'UTF-8'); ?> · Custom Operations Board (0.4.3)</footer>
    </div>
  </div>
</body>
</html>
<?php
sugar_cleanup(true);
exit;

function bs_board_table_exists($table)
{
    global $db;
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
        'payment_pending' => 'Payment pending',
        'on_hold' => 'On hold',
        'cancelled' => 'Cancelled',
    ];
    return $map[$status] ?? $status;
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
    if (!bs_board_table_exists('bs_orders')) {
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

function bs_board_recent_orders($limit = 8)
{
    global $db;
    if (!bs_board_table_exists('bs_orders')) {
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
