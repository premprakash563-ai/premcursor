<?php
/**
 * Register custom modules in SuiteCRM 7.15.1 — Phase 2
 */

$moduleList[] = 'BS_Services';
$moduleList[] = 'BS_Orders';
$moduleList[] = 'BS_OrderDocuments';
$moduleList[] = 'BS_StatusHistory';
$moduleList[] = 'BS_Leave';
$moduleList[] = 'BS_Notifications';

$beanList['BS_Services'] = 'BS_Services';
$beanList['BS_Orders'] = 'BS_Orders';
$beanList['BS_OrderDocuments'] = 'BS_OrderDocuments';
$beanList['BS_StatusHistory'] = 'BS_StatusHistory';
$beanList['BS_Leave'] = 'BS_Leave';
$beanList['BS_Notifications'] = 'BS_Notifications';

$beanFiles['BS_Services'] = 'modules/BS_Services/BS_Services.php';
$beanFiles['BS_Orders'] = 'modules/BS_Orders/BS_Orders.php';
$beanFiles['BS_OrderDocuments'] = 'modules/BS_OrderDocuments/BS_OrderDocuments.php';
$beanFiles['BS_StatusHistory'] = 'modules/BS_StatusHistory/BS_StatusHistory.php';
$beanFiles['BS_Leave'] = 'modules/BS_Leave/BS_Leave.php';
$beanFiles['BS_Notifications'] = 'modules/BS_Notifications/BS_Notifications.php';

$modInvisList[] = 'BS_StatusHistory';
