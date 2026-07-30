<?php
/**
 * Safer theme inject — only after login, never breaks Login CSS stack.
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class BS_ThemeHook
{
    public function injectCss($event, $arguments)
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;

        // Never touch Login / entry pages — broken theme stack risk
        $action = isset($_REQUEST['action']) ? strtolower((string) $_REQUEST['action']) : '';
        $module = isset($_REQUEST['module']) ? strtolower((string) $_REQUEST['module']) : '';
        if ($module === 'users' && in_array($action, ['login', 'authenticate', 'logout'], true)) {
            return;
        }
        if (empty($GLOBALS['current_user']->id)) {
            return;
        }

        $candidates = [
            'custom/include/BS/Theme/bs-professional.css',
            'custom/themes/SuiteP/css/bs-professional.css',
        ];

        $css = '';
        foreach ($candidates as $path) {
            if (is_file($path) && is_readable($path)) {
                $css = @file_get_contents($path);
                break;
            }
        }
        if ($css === '' || $css === false) {
            return;
        }

        echo "\n<style id=\"bs-professional-theme\">\n{$css}\n</style>\n";
    }
}
