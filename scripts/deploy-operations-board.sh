#!/usr/bin/env bash
# Emergency deploy Operations Board on SuiteCRM server (run inside public_html)
set -euo pipefail
BASE="${1:-$HOME/public_html}"
REPO_RAW="https://raw.githubusercontent.com/premprakash563-ai/premcursor/cursor/suitecrm-business-service-crm-8700/suitecrm-extension"
cd "$BASE"

mkdir -p custom/include/BS
mkdir -p modules/BS_Dashboard
mkdir -p custom/application/Ext/EntryPointRegistry
mkdir -p custom/Extension/application/Ext/EntryPointRegistry

curl -fsSL "$REPO_RAW/custom/include/BS/dashboard_board.php" -o custom/include/BS/dashboard_board.php
curl -fsSL "$REPO_RAW/modules/BS_Dashboard/BoardRenderer.php" -o modules/BS_Dashboard/BoardRenderer.php

# Direct registry (no Quick Repair needed)
cat > custom/application/Ext/EntryPointRegistry/bs_operations_board.ext.php << 'EOF'
<?php
$entry_point_registry['bs_operations_board'] = array(
    'file' => 'custom/include/BS/dashboard_board.php',
    'auth' => true,
);
EOF

cp custom/application/Ext/EntryPointRegistry/bs_operations_board.ext.php \
   custom/Extension/application/Ext/EntryPointRegistry/bs_dashboard.php

# Ensure theme cache style exists (avoid blank CRM)
if [ ! -s cache/themes/SuiteP/Dawn/style.css ]; then
  mkdir -p cache/themes/SuiteP/Dawn
  chmod -R 775 cache || true
  if [ -f themes/SuiteP/css/Dawn/style.css ]; then
    cp -f themes/SuiteP/css/Dawn/style.css cache/themes/SuiteP/Dawn/style.css
  else
    cat themes/SuiteP/css/normalize.css themes/SuiteP/css/bootstrap.min.css themes/SuiteP/css/fonts.css themes/SuiteP/css/grid.css > cache/themes/SuiteP/Dawn/style.css || true
  fi
fi

echo "OK. Open while logged in:"
echo "https://yoogleconsultancy.in/index.php?entryPoint=bs_operations_board"
