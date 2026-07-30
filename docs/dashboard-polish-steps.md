# Dashboard polish — install steps (Git nahi chahiye)

Client request: CRM dashboard professional, simple, clean.

## 1) Zip download
https://github.com/premprakash563-ai/premcursor/raw/cursor/suitecrm-business-service-crm-8700/BS_BusinessServiceCRM-0.3.1.zip

## 2) Install
1. Admin → **Module Loader**
2. Zip upload → **Install**
3. Admin → **Repair** → **Quick Repair and Rebuild**
4. SQL dikhe to **Execute mat dabao** (403 aa sakta hai) — skip
5. Logout → Login

## 3) Cache clear (agar look change na dikhe)
File Manager me delete files inside:
- `cache/themes/`
- `cache/smarty/templates_c/`
- `cache/modules/Home/` (agar ho)

## 4) Home pe naya dashboard lagao
1. Upar **HOME** pe jao
2. **Add Dashlets** / dashboard tools (page top pe)
3. Dashlet list me **Business Service Overview** select karo → Add
4. Optional: **Employee Workload** bhi add karo
5. Purane noisy dashlets (My Activity etc.) hata do — simple rakho

## 5) Result
- Teal + slate clean look (poori CRM UI)
- Home pe: Today / Pending / Month revenue / Online team
- Pipeline + latest orders
- Quick buttons: New order, Services, Notifications, Leave

## Agar CSS na aaye
Seedha check:
`https://yoogleconsultancy.in/custom/themes/SuiteP/css/bs-professional.css`

Agar 404 → Module Loader install incomplete. Dubara Install + Quick Repair.
