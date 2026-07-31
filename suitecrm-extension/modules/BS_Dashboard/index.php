<?php
/**
 * Classic fallback — redirect before MVC ACL headaches.
 */
if (!defined('sugarEntry')) {
    define('sugarEntry', true);
}
header('Location: index.php?entryPoint=bs_operations_board');
exit;
