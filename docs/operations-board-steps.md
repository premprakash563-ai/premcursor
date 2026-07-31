# Operations Board — fix "There is no action by that name: index"

Woh error **purane board code** se aata hai (SugarView `index` action).
Naya code **alag entryPoint** use karta hai — koi `action=index` nahi.

## Sirf yeh SSH command chalao

```bash
cd ~/public_html && curl -fsSL https://raw.githubusercontent.com/premprakash563-ai/premcursor/e8628df/scripts/deploy-operations-board.sh | bash
```

(Commit SHA use karo — branch URL kabhi purana file cache karke de deta hai.)

## Phir browser me (logged in hona zaroori)

```
https://yoogleconsultancy.in/index.php?entryPoint=bs_operations_board
```

Page ke neeche footer: **Custom Operations Board (0.4.3)**

## Mat kholo (broken)

```
index.php?module=BS_Dashboard&action=index
```

## Confirm

```bash
grep -n "0.4.3" ~/public_html/custom/include/BS/dashboard_board.php
grep -n bs_operations_board ~/public_html/custom/application/Ext/EntryPointRegistry/entry_point_registry.ext.php
```

## Optional: Module Loader zip

https://github.com/premprakash563-ai/premcursor/raw/e8628df/BS_BusinessServiceCRM-0.4.3.zip
