<?php
/**
 * Operations Board — bulletproof entry point (no SugarView action routing).
 * URL: index.php?entryPoint=bs_operations_board
 */

if (!defined('sugarEntry')) {
    define('sugarEntry', true);
}

require_once 'include/entryPoint.php';

global $current_user;
if (empty($current_user->id)) {
    sugar_cleanup(true);
    header('Location: index.php?module=Users&action=Login');
    exit;
}

require_once 'modules/BS_Dashboard/BoardRenderer.php';

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Operations Board · Yoogle Consultancy</title>
  <link rel="stylesheet" href="themes/SuiteP/css/normalize.css">
  <link rel="stylesheet" href="themes/SuiteP/css/bootstrap.min.css">
  <link rel="stylesheet" href="themes/SuiteP/css/fonts.css">
</head>
<body style="margin:0;background:#eef3f7;font-family:Segoe UI,system-ui,sans-serif">
  <div style="padding:14px 20px;background:#0f1c2b;color:#fff;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
    <div style="font-weight:700;letter-spacing:.02em">Yoogle Consultancy · CRM</div>
    <nav style="display:flex;gap:14px;flex-wrap:wrap;font-size:13px">
      <a href="index.php?module=Home&action=index" style="color:#fff;text-decoration:none">Home</a>
      <a href="index.php?module=BS_Services&action=index" style="color:#fff;text-decoration:none">Services</a>
      <a href="index.php?module=BS_Orders&action=index" style="color:#fff;text-decoration:none">Orders</a>
      <a href="index.php?module=BS_Notifications&action=index" style="color:#fff;text-decoration:none">Notifications</a>
      <a href="index.php?module=Users&action=Logout" style="color:#9fefdf;text-decoration:none">Logout</a>
    </nav>
  </div>
  <div style="max-width:1180px;margin:18px auto;padding:0 16px 40px">
<?php
$board = new BS_BoardRenderer();
$board->renderBody();
?>
  </div>
</body>
</html>
<?php
sugar_cleanup(true);
exit;
