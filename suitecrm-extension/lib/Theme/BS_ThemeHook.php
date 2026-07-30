<?php
/**
 * Inject professional CSS into SuiteCRM UI (SuiteP).
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

        $href = 'custom/themes/SuiteP/css/bs-professional.css';
        if (!file_exists($href) && !is_file($href)) {
            // Allow when cwd differs
            if (!file_exists(getcwd() . '/' . $href)) {
                return;
            }
        }

        $ver = @filemtime($href) ?: time();
        echo "\n<link rel=\"stylesheet\" type=\"text/css\" href=\"{$href}?v={$ver}\">\n";
    }
}
