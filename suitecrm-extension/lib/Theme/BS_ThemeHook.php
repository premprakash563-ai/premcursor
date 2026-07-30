<?php
/**
 * Theme hook disabled — do not inject global CSS.
 * Some LiteSpeed/SuiteP setups break when after_ui_frame prints <style>.
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class BS_ThemeHook
{
    public function injectCss($event, $arguments)
    {
        // no-op (safe)
        return;
    }
}
