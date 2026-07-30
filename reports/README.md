# MIS (Company-wise Sale Bill Register)

Path: `reports/mis_sale_bill_register.php`

## Form (legacy MIS Date tab)

- From / To Date
- Ist–VIth Grouping: Product Group, Category, Company, Color, Description, Company No
- GSTIN: All / With / Without
- Tax Type: All / Local / Central / Exempted
- Payment Mode: All / Cash / Credit / Wallet
- Flags: Only Sale Return, Only Discount Bill, Without Amount, Without Disc%, Without Representative, WhatsApp To Owner
- Dynamic multi-select tabs: Category, Company, Product Group, Color, Description, Company No, Size, Representative, Product

## Calculation (from reference query)

- **Sale** (`Type.Status = 4`): positive Qty / Amount / Disc / Gross
- **Sale Return** (`Type.Status = 5`): negated via `UNION ALL`
- **Disc Amt** = `Stock.SDisc_Amt + (Stock.Tot_Amt * SBill1.Disc / 100)`
- **Gross Amt** = `Stock.ItemNetAmt`

## Dependencies

Uses existing app includes:

- `../config/database.php`
- `../classes/auth.php`
- `../includes/header.php`, `sidebar.php`, `footer.php`

## Stock In Hand

Path: `sale/stock_in_hand.php` (copy also in `reports/`)

- MIS-style tab form (Date, Category, Company, Product Group, Color, Description, Company No, Size, Sp Inst, Party, City, Product, Tax, Columns)
- Dynamic multiselect filters from DB
- Query: Stock R/I as-on-date, optional non-zero balance subquery, BarcDays, Ist–VIth grouping
