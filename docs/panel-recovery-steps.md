# Panel broken recovery (Login page unstyled)

## Turant fix — File Manager se

### Step 1: Hamara theme hook band karo
File Manager me ye file **rename** karo:

`custom/Extension/application/Ext/LogicHooks/bs_theme.php`  
→ `bs_theme.php.off`

Aur ye bhi (agar ho):

`custom/application/Ext/LogicHooks/logichooks.ext.php`  
(usme BS theme wali lines baad me repair se rebuild hongi)

Agar ye file dikhe to temporarily rename:
`custom/include/BS/Theme/BS_ThemeHook.php` → `BS_ThemeHook.php.off`

### Step 2: Core theme files check
Folder kholo:

`themes/SuiteP/css/`

Andar ye files honi chahiye:
- `style.css` ya compiled style files
- `bootstrap.css` / theme CSS files

**Agar folder nearly empty hai** (sirf `bs-professional.css`) → theme overwrite ho gaya.

Tab:
1. Hosting backup se `themes/SuiteP` restore karo  
**YA**
2. SuiteCRM 7.15.1 zip download karke usme se `themes/SuiteP` folder upload/replace karo  
   https://github.com/suitecrm/SuiteCRM/releases/tag/v7.15.1

### Step 3: Cache
Delete files inside only:
```
cache/themes/
cache/smarty/templates_c/
```

### Step 4: Browser
- Incognito window
- Login URL: `https://yoogleconsultancy.in/index.php?module=Users&action=Login`
- Login → Admin → Repair → Quick Repair (Execute skip)

### Step 5: Confirm
Login page wapas designed dikhni chahiye.

---

## Baad me (jab panel theek ho)
Naya safe zip (core theme touch nahi karta):
`BS_BusinessServiceCRM-0.3.3.zip`
