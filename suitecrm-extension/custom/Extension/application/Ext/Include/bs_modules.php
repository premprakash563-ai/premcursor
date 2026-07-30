<?php
/**
 * Register custom modules in SuiteCRM 7.15.1
 * Path: custom/Extension/application/Ext/Include/bs_modules.php
 * After copy: Admin → Repair → Rebuild Extensions + Quick Repair and Rebuild
 */

$moduleList[] = 'BS_Services';
$moduleList[] = 'BS_Orders';
$moduleList[] = 'BS_OrderDocuments';

$beanList['BS_Services'] = 'BS_Services';
$beanList['BS_Orders'] = 'BS_Orders';
$beanList['BS_OrderDocuments'] = 'BS_OrderDocuments';

$beanFiles['BS_Services'] = 'modules/BS_Services/BS_Services.php';
$beanFiles['BS_Orders'] = 'modules/BS_Orders/BS_Orders.php';
$beanFiles['BS_OrderDocuments'] = 'modules/BS_OrderDocuments/BS_OrderDocuments.php';

$modInvisList[] = 'BS_OrderDocuments';
