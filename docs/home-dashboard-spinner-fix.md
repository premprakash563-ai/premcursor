# Home dashboard spinner atka — turant fix

Classic SuiteCRM Home pe dashlets load nahi ho rahe (grey dots).

## Option A — 2 minute UI fix (pehle ye try karo)

1. Home page pe **ACTIONS** (pink button) click
2. Agar dikhe: **Reset Dashboard** / **Reset to default homepage** → click
3. Logout → Login
4. Ctrl+Shift+R (hard refresh)

Agar ACTIONS me Reset na mile → Option B.

## Option B — SSH + phpMyAdmin (reliable)

### 1) SSH
```bash
cd ~/public_html && curl -fsSL https://raw.githubusercontent.com/premprakash563-ai/premcursor/cursor/suitecrm-business-service-crm-8700/scripts/fix-home-dashboard.sh | bash
```

### 2) phpMyAdmin
File banegi: `~/public_html/bs_reset_home_dashlets.sql`  
Usme ye SQL chalao (apna CRM database select karke):

```sql
DELETE FROM user_preferences
WHERE category = 'Home'
  AND deleted = 0;
```

### 3) Browser
Logout → Login → Home  
ACTIONS → **Add Dashlets** se chahiye wale dashlets add karo.

## Client ko kya dikhana hai

Classic Home stock SuiteCRM hai.  
Client demo ke liye **Operations Board** use karo:

```
https://yoogleconsultancy.in/index.php?entryPoint=bs_operations_board
```

(Pehle board deploy confirm karo — alag steps `operations-board-steps.md`)

## Note
Custom `BS_AdminDashboardDashlet` temporarily `.off` ho sakta hai — ye Home AJAX hang rokne ke liye hai.  
Operations Board alag page hai, uspe depend karo.
