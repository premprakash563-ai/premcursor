<?php
/**
 * Application logic hook — load BS professional theme CSS.
 */

$hook_version = 1;
$hook_array = [];

$hook_array['after_ui_frame'][] = [
    99,
    'BS Professional Theme CSS',
    'custom/include/BS/Theme/BS_ThemeHook.php',
    'BS_ThemeHook',
    'injectCss',
];
