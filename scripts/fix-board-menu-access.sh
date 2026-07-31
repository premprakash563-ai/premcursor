#!/usr/bin/env bash
# Fix "You do not have access to this area" on Operations Board top tab
set -euo pipefail
cd ~/public_html

mkdir -p modules/BS_Dashboard

echo "==> Writing BS_Dashboard redirect controller + index + bean"
cat > modules/BS_Dashboard/index.php << 'PHP'
<?php
if (!defined('sugarEntry')) {
    define('sugarEntry', true);
}
header('Location: index.php?entryPoint=bs_operations_board');
exit;
PHP

cat > modules/BS_Dashboard/controller.php << 'PHP'
<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
require_once 'include/MVC/Controller/SugarController.php';
class BS_DashboardController extends SugarController
{
    public function preProcess()
    {
        $this->hasAccess = true;
        SugarApplication::redirect('index.php?entryPoint=bs_operations_board');
    }
    public function loadBean() { $this->bean = null; }
    public function action_index() { SugarApplication::redirect('index.php?entryPoint=bs_operations_board'); }
    public function action_default() { SugarApplication::redirect('index.php?entryPoint=bs_operations_board'); }
}
PHP

# Fetch proper bean from API if possible; else minimal SugarBean
if curl -fsSL "https://api.github.com/repos/premprakash563-ai/premcursor/contents/suitecrm-extension/modules/BS_Dashboard/BS_Dashboard.php?ref=cursor/suitecrm-business-service-crm-8700" \
  | php -r '$j=json_decode(stream_get_contents(STDIN),true); if(empty($j["content"])) exit(1); file_put_contents("modules/BS_Dashboard/BS_Dashboard.php", base64_decode($j["content"]));' \
  2>/dev/null; then
  echo "bean from API"
else
cat > modules/BS_Dashboard/BS_Dashboard.php << 'PHP'
<?php
if (!defined('sugarEntry') || !sugarEntry) { die('Not A Valid Entry Point'); }
require_once 'data/SugarBean.php';
class BS_Dashboard extends SugarBean {
    public $new_schema = true;
    public $module_dir = 'BS_Dashboard';
    public $object_name = 'BS_Dashboard';
    public $table_name = 'bs_dashboard';
    public $disable_row_level_security = true;
    public function bean_implements($interface) {
        return $interface === 'ACL';
    }
}
PHP
fi

# Ensure entry point still registered
mkdir -p custom/include/MVC/Controller custom/include/BS
if [ ! -f custom/include/MVC/Controller/entry_point_registry.php ]; then
cat > custom/include/MVC/Controller/entry_point_registry.php << 'PHP'
<?php
$entry_point_registry['retrieve_dash_page'] = array(
    'file' => 'custom/include/BS/retrieve_dash_page_safe.php',
    'auth' => true,
);
$entry_point_registry['bs_operations_board'] = array(
    'file' => 'custom/include/BS/dashboard_board.php',
    'auth' => true,
);
PHP
else
  # ensure board mapping present
  if ! grep -q "bs_operations_board" custom/include/MVC/Controller/entry_point_registry.php; then
    cat >> custom/include/MVC/Controller/entry_point_registry.php << 'PHP'
$entry_point_registry['bs_operations_board'] = array(
    'file' => 'custom/include/BS/dashboard_board.php',
    'auth' => true,
);
PHP
  fi
fi

php -r 'if (function_exists("opcache_reset")) opcache_reset();' 2>/dev/null || true

echo
echo "OK. Ab Operations Board MENU click karo — board khulna chahiye."
echo "Direct URL bhi chalega:"
echo "https://yoogleconsultancy.in/index.php?entryPoint=bs_operations_board"
