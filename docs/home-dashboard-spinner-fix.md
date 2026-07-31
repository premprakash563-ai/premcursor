# Home spinner + console: retrieve_dash_page → 500

Matlab dashboard AJAX crash ho rahi hai.

## Turant fix (SSH)

```bash
cd ~/public_html && curl -fsSL https://raw.githubusercontent.com/premprakash563-ai/premcursor/cursor/suitecrm-business-service-crm-8700/scripts/fix-home-dashboard.sh | bash
```

## Phir phpMyAdmin SQL

```sql
DELETE FROM user_preferences
WHERE category = 'Home'
  AND deleted = 0;
```

## Browser

1. Logout → Login  
2. Ctrl+Shift+R  
3. F12 → Network → `retrieve_dash_page` ab **200** hona chahiye (500 nahi)

## Ye script kya karti hai

- Broken `custom/include/MVC/Controller/entry_point_registry.php` hataati hai  
- Entry point registry clean rewrite  
- Custom BS dashlets temporarily disable  
- Dashlet/cache clear  
- Home prefs reset SQL generate

## Client demo

Classic Home stock SuiteCRM hai.  
Custom look ke liye Operations Board:

```
https://yoogleconsultancy.in/index.php?entryPoint=bs_operations_board
```
