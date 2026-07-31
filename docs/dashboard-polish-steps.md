# Dashboard polish 0.3.2 — install steps

404 on CSS URL normal ho sakta hai (hosting `custom/themes` block karti hai).
Is version me CSS **page ke andar inline** load hoti hai — direct URL ki zaroorat nahi.

## 1) Download
https://github.com/premprakash563-ai/premcursor/raw/cursor/suitecrm-business-service-crm-8700/BS_BusinessServiceCRM-0.3.2.zip

## 2) Install
1. Admin → **Module Loader** → Upload zip → **Install**
2. Admin → **Repair** → **Quick Repair and Rebuild**
3. Execute skip (403 avoid)
4. Logout → Login

## 3) File Manager se confirm (optional)
Ye file honi chahiye:
`custom/include/BS/Theme/BS_ThemeHook.php`
`custom/include/BS/Theme/bs-professional.css`
`modules/Home/Dashlets/BS_AdminDashboardDashlet/BS_AdminDashboardDashlet.php`

## 4) Home dashlet
1. **HOME**
2. **Add Dashlets**
3. **Business Service Overview** add karo
4. Extra dashlets hatao

## 5) Look check
- Navbar dark slate
- Buttons teal
- List views clean
- Overview dashlet professional

Hard refresh: `Ctrl+F5`

CSS URL 404 ignore karo — ab zaroori nahi.
