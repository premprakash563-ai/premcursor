# retrieve_dash_page 500 — exact error nikaalo

Console me sirf 500 dikhta hai. Exact PHP error chahiye.

## 1) SSH (ek command)

```bash
cd ~/public_html && curl -fsSL https://raw.githubusercontent.com/premprakash563-ai/premcursor/cursor/suitecrm-business-service-crm-8700/scripts/install-dash-fix.sh | bash
```

## 2) Browser (logged in)

```
https://yoogleconsultancy.in/index.php?entryPoint=bs_dash_fix
```

Black/green page aayegi — **poora text copy** karke bhejo.

Ye page:
- broken registry override hataati hai
- custom dashlets disable
- Home prefs reset (isi user ke)
- dashlet cache rebuild
- crash reason print karti hai

## 3) Phir Home

Ctrl+Shift+R → spinner hatna chahiye.

## 4) Cleanup (baad me)

```bash
rm -f ~/public_html/custom/include/BS/bs_dash_fix.php
rm -f ~/public_html/custom/Extension/application/Ext/EntryPointRegistry/bs_dash_fix.php
```
