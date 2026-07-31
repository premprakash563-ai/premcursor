#!/usr/bin/env bash
# Fix SuiteCRM Home dashboard stuck on loading spinner.
# Run on server: bash fix-home-dashboard.sh
set -euo pipefail
BASE="${1:-$HOME/public_html}"
cd "$BASE"

echo "==> Checking theme cache (blank/spinner often follows missing style.css)..."
mkdir -p cache/themes/SuiteP/Dawn
if [ ! -s cache/themes/SuiteP/Dawn/style.css ]; then
  if [ -f themes/SuiteP/css/Dawn/style.css ]; then
    cp -f themes/SuiteP/css/Dawn/style.css cache/themes/SuiteP/Dawn/style.css
    echo "Restored Dawn/style.css into cache"
  elif [ -f themes/SuiteP/css/style.css ]; then
    cp -f themes/SuiteP/css/style.css cache/themes/SuiteP/Dawn/style.css
    echo "Restored style.css into cache"
  else
    echo "WARNING: could not find theme style.css source"
  fi
fi
chmod -R 775 cache || true

echo "==> Soft-disabling custom BS Home dashlet (can break AJAX load)..."
if [ -f modules/Home/Dashlets/BS_AdminDashboardDashlet/BS_AdminDashboardDashlet.php ]; then
  mv -f modules/Home/Dashlets/BS_AdminDashboardDashlet/BS_AdminDashboardDashlet.php \
        modules/Home/Dashlets/BS_AdminDashboardDashlet/BS_AdminDashboardDashlet.php.off
  echo "Renamed BS_AdminDashboardDashlet.php -> .off"
fi

echo "==> Writing SQL to reset Home dashlets for all users..."
SQL_FILE="$BASE/bs_reset_home_dashlets.sql"
cat > "$SQL_FILE" << 'EOF'
-- Run in phpMyAdmin (select your SuiteCRM database first)
-- Resets Home page dashlet layout so spinner stops / default dashlets reload

DELETE FROM user_preferences
WHERE category = 'Home'
  AND deleted = 0;

-- Optional: also clear dashlet-related preference blobs
DELETE FROM user_preferences
WHERE category LIKE 'Dashlet%'
  AND deleted = 0;
EOF
echo "SQL written: $SQL_FILE"

echo "==> Clearing compiled templates (safe)..."
rm -rf cache/smarty/templates_c/* 2>/dev/null || true
rm -rf cache/modules/Home/* 2>/dev/null || true

echo
echo "DONE (files)."
echo "Ab phpMyAdmin me ye SQL chalao:"
echo "  $SQL_FILE"
echo
echo "Phir:"
echo "1) Browser Logout / Login"
echo "2) Hard refresh (Ctrl+Shift+R)"
echo "3) Home → ACTIONS → Add Dashlets (agar blank ho)"
echo
echo "Custom board (recommended for client):"
echo "https://yoogleconsultancy.in/index.php?entryPoint=bs_operations_board"
