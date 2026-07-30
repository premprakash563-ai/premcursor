# Operations Board — fix "There is no action by that name: index"

Woh error **purane board code** se aata hai (SugarView `index` action).
Naya code **alag entryPoint** use karta hai — koi `action=index` nahi.

## Sirf yeh SSH command chalao (public_html pe)

```bash
cd ~/public_html && curl -fsSL https://raw.githubusercontent.com/premprakash563-ai/premcursor/cursor/suitecrm-business-service-crm-8700/scripts/deploy-operations-board.sh | bash
```

## Phir browser me (logged in)

```
https://yoogleconsultancy.in/index.php?entryPoint=bs_operations_board
```

Page ke neeche footer me likha hona chahiye: **Custom Operations Board (0.4.3)**

## Mat kholo (broken / purana)

```
index.php?module=BS_Dashboard&action=index
```

Agar yeh URL se redirect hota hai to theek — warna seedha `entryPoint=bs_operations_board` use karo.

## Confirm files

```bash
head -n 8 ~/public_html/custom/include/BS/dashboard_board.php
grep -n bs_operations_board ~/public_html/custom/application/Ext/EntryPointRegistry/entry_point_registry.ext.php
```

Pehli lines me `self-contained entry point` / `DO NOT use SugarView` dikhna chahiye.
