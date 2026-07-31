# Phase 2 install steps (SuiteCRM 7.15.1) — Git nahi chahiye

## 1) Naya zip download
https://github.com/premprakash563-ai/premcursor/raw/cursor/suitecrm-business-service-crm-8700/BS_BusinessServiceCRM-0.3.0.zip

## 2) Module Loader
1. Admin → **Module Loader** (Upgrade Wizard mat kholo)
2. Zip upload → **Install**
3. Admin → Repair → **Rebuild Extensions**
4. Admin → Repair → **Quick Repair and Rebuild** → SQL aaye to **Execute**

## 3) Naye modules menu me lao
Admin → **Display Modules and Subpanels** → enable:
- **Leave Requests**
- **Notifications**

(Services / Service Orders pehle se enabled honge)

## 4) Employee availability field (Studio)
1. Admin → **Studio** → **Users** → **Layouts** → **Edit View**
2. Field list se add karo:
   - **Availability** (`availability_c`) — online / offline / on_leave
   - optional: Max Enquiries Override
3. Save & Deploy

Employee ko **Offline** / **On Leave** set karo to auto-assign skip karega.

## 5) Schedulers (auto reminders)
1. Admin → **Schedulers**
2. Naye jobs dikhne chahiye:
   - **BS Document Reminders**
   - **BS Idle Order Escalation**
3. Dono ko **Active** karo, interval = every hour / daily as needed
4. Cron already SuiteCRM pe chal raha hona chahiye

## 6) Test
1. Naya **Service Order** banao (unassigned) → employee assign + **Notifications** me entry
2. Employee → **Leave Requests** → approved leave banao → us employee ko naya order assign nahi hona chahiye
3. Order document Reject/Re-upload → notification
4. ALL → **Notifications** list check

## Direct links (agar menu hide ho)
- Leave: `/index.php?module=BS_Leave&action=index`
- Notifications: `/index.php?module=BS_Notifications&action=index`
