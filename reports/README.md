# Reports

## Category-wise Sale Register

**Paths**

- `sale/category_wise_sale_register.php`
- `reports/category_wise_sale_register.php`

**Offline reference:** Category-wise Sale Register PDF (nested Ist–VIth grouping + subtotals).

### Client gaps addressed

| Gap | Fix |
|---|---|
| Missing filters | Type, Party (+ Acgroup), Supplier (+ Acgroup), Customer, Transport, Ref. No, Sale Gst %, City, State, Representative, Bank / Bank Sub, Wallet / Wallet Sub, Unit, Computer, User, Pur Gst % (+ existing Category/Company/Size/Color/Description/SpInst/Product/Company No/Product Group) |
| Filter search not working | Every multi-select has a Search box (`msFilter`) |
| Grouping incomplete | Full offline option set for Ist–VIth groups (Category, City, Color, CompProdCode, Company, Company No, Description, Party, Product Code, Product Group, Pur Gst %, Purchase Rate, Reference Date/No, Sale Gst %, Sale Rate, Size, Sp Inst1–3, Stock Rate) |
| Missing columns | Central Amt, Local Amt, Exempted Amt, Qty Cont %, Gross Cont % |
| Export only one page | CSV / Excel / PDF pull **all** grouped rows via `?export=csv\|excel\|pdfdata` using session filters |
| Print | Browser Print (any printer) + zoom in/out |

### Query notes (mirrors original .NET SQL)

- Driver: `SBill1` → `Stock` → masters (same join set as offline)
- `UNION ALL`: Sale (`Type.Status=4`) positive + Sale Return (`Status=5`) negated
- **Disc Amt** = `Stock.SDisc_Amt + (Tot_Amt * SBill1.Disc / 100)`
- **Taxable Amt** = `Stock.Tot_Amount`
- **Gross / Tot_Amt** = `Tot_Amt - (Tot_Amt * SBill1.Disc / 100)`
- **CGST / IGST** from `SBill1.TAX_YN` (`N`→CGST, `Y`→IGST); **SGST** = `Stock.SSat_Amt`
- **Central / Local / Exempted Amt & Qty** from **`Stock.Tax_YN`** (`Y` / `N` / `F`)
- Official Ist–VIth grouping: Product Group, Category, Company, Color, Description, Company No
- Qty Cont % / Gross Cont % = row share of report grand totals
- Optional masters (`description`, `subgroup`, `citymaster`, `state`, `representative`, `acgroup`) joined when present

### Dependencies

`../config/database.php`, `../classes/auth.php`, `../includes/header.php`, `sidebar.php`, `footer.php`
