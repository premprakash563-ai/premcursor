<?php
/**
 * Inject professional CSS into SuiteCRM UI.
 * Inlines CSS so hosting blocks on /custom/themes/* (404) do not break the look.
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

        $candidates = [
            'custom/themes/SuiteP/css/bs-professional.css',
            'themes/SuiteP/css/bs-professional.css',
            'custom/include/BS/Theme/bs-professional.css',
        ];

        $css = '';
        $used = '';
        foreach ($candidates as $path) {
            if (is_file($path) && is_readable($path)) {
                $css = @file_get_contents($path);
                $used = $path;
                break;
            }
        }

        if ($css === '' || $css === false) {
            // Minimal fallback so UI still improves even if files missing
            $css = $this->fallbackCss();
            $used = 'fallback';
        }

        echo "\n<!-- BS professional theme ({$used}) -->\n<style id=\"bs-professional-theme\">\n"
            . $css
            . "\n</style>\n";
    }

    protected function fallbackCss()
    {
        return <<<'CSS'
@import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&display=swap');
html body{font-family:"IBM Plex Sans",system-ui,sans-serif!important;background:#f3f6f9!important;color:#122033}
.navbar-inverse,#bootstrap-container>.navbar{background:#0f1c2b!important;border:0!important;box-shadow:none!important}
.list.view,table.list.view{border:1px solid #d9e2ec!important;border-radius:8px!important;background:#fff!important;box-shadow:none!important}
.list.view th,table.list.view th{background:#eef2f6!important;color:#5b6b7c!important;font-size:11px!important;text-transform:uppercase!important}
input[type="submit"].button.primary,.btn-primary,input#SAVE,#SAVE_HEADER{background:#0f766e!important;border-color:#0f766e!important;color:#fff!important}
.moduleTitle h2{font-family:"IBM Plex Sans",sans-serif!important;font-weight:700!important;color:#122033!important}
.dashletPanel,.hd.dashletPanel{border:1px solid #d9e2ec!important;border-radius:8px!important;box-shadow:none!important;background:#fff!important}
CSS;
    }
}
