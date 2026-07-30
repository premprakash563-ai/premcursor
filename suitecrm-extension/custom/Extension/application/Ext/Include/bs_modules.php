<?php
/**
 * Register custom modules in SuiteCRM 7.15.1
 */

$moduleList[] = 'BS_Services';
$moduleList[] = 'BS_Orders';
$moduleList[] = 'BS_OrderDocuments';
$moduleList[] = 'BS_StatusHistory';

$beanList['BS_Services'] = 'BS_Services';
$beanList['BS_Orders'] = 'BS_Orders';
$beanList['BS_OrderDocuments'] = 'BS_OrderDocuments';
$beanList['BS_StatusHistory'] = 'BS_StatusHistory';

$beanFiles['BS_Services'] = 'modules/BS_Services/BS_Services.php';
$beanFiles['BS_Orders'] = 'modules/BS_Orders/BS_Orders.php';
$beanFiles['BS_OrderDocuments'] = 'modules/BS_OrderDocuments/BS_OrderDocuments.php';
$beanFiles['BS_StatusHistory'] = 'modules/BS_StatusHistory/BS_StatusHistory.php';

// Keep history out of main tab; documents visible for employees
$modInvisList[] = 'BS_StatusHistory';
