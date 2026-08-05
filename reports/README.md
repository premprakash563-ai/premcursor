# Reports

## Category-wise Sale Register

### Direct live path

```
/selfimage/sale/category-wise-sale-register.php
```

URL:

```
https://liveraho.in/selfimage/sale/category-wise-sale-register.php
```

Repo file (upload this):

```
sale/category-wise-sale-register.php
```

(`sale/category_wise_sale_register.php` is only an alias that includes the hyphen file.)

### Query (.NET parity)

- `SBill1` → `Stock` UNION ALL Status 4 / 5
- Disc / Tot_Amt / Central|Local|Exempted from `Stock.Tax_YN` (`Y`/`N`/`F`)

### Party / Type / Supplier

| Filter | Table | UI |
|---|---|---|
| Type | `type` Status 4,5 | DESCRIPTION checkboxes |
| Party | `acgroup` → `subgroup` | Acgroup dropdown → name checkboxes |
| Supplier | `Product.Code` / `Product.SubCode` (Acgroup_1 / Subgroup_1) | same pattern |

### Dependencies

`../config/database.php`, `../classes/auth.php`, `../includes/header.php`, `sidebar.php`, `footer.php`
