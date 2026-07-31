# Home dashboard: retrieve_dash_page → 500

## Fix (SSH)

```bash
cd ~/public_html && curl -fsSL https://raw.githubusercontent.com/premprakash563-ai/premcursor/cursor/suitecrm-business-service-crm-8700/scripts/fix-home-dashboard.sh | bash
```

## Phir browser me kholo (entryPoint nahi)

```
https://yoogleconsultancy.in/bs_fix.php
```

Plain text page aayegi. **Poora text copy** karke bhejo.

## Phir

1. Home → Ctrl+Shift+R  
2. Spinner hatna chahiye  
3. Delete: `rm ~/public_html/bs_fix.php`

## Backup SQL (phpMyAdmin)

```sql
DELETE FROM user_preferences WHERE category = 'Home' AND deleted = 0;
```

## Kya fix karti hai

- `retrieve_dash_page` ko safe wrapper se override (PHP Error catch + auto reset)
- Custom BS dashlets disable
- Broken registry override hataati hai
- Home prefs wipe + dashlet cache rebuild
