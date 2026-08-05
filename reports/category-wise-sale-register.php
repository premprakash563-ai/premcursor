<?php
/**
 * CSR_BUILD_20260805_D46DAF4 — if this string is missing on server, old file is still deployed.
 *
 * Category-wise Sale Register
 *
 * LIVE PATH (direct):
 *   /selfimage/sale/category-wise-sale-register.php
 *   https://liveraho.in/selfimage/sale/category-wise-sale-register.php
 *
 * Repo file:
 *   sale/category-wise-sale-register.php
 *
 * Query engine mirrors original .NET Category-wise Sale Register:
 *   UNION ALL Sale (Type.Status=4) + Sale Return (Status=5, negated)
 *   Disc / Tot_Amt / Central|Local|Exempted from Stock.Tax_YN + SBill1.Disc
 * UI: multi filters, grouping, Cont%, full-dataset export & print.
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

require_once '../config/database.php';
require_once '../classes/auth.php';

$db   = (new Database())->connect();
$auth = new Auth($db);
if (!$auth->check()) { header('Location: ../login.php'); exit; }

if (session_status() === PHP_SESSION_NONE) session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        http_response_code(403); exit('Invalid CSRF token.');
    }
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

if (!function_exists('esc')) {
    function esc($db, $val) { return $db->real_escape_string(trim((string)$val)); }
}
if (!function_exists('h')) {
    function h($val) { return htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8'); }
}
// Always define — do not skip if another include already declared safeQuery
if (!function_exists('csrSafeQuery')) {
    function csrSafeQuery($db, $sql) {
        try {
            $r = $db->query($sql);
            if (!$r) return [];
            $rows = [];
            while ($row = $r->fetch_assoc()) $rows[] = $row;
            return $rows;
        } catch (Throwable $e) {
            return [];
        }
    }
}
// Back-compat alias only if not already defined elsewhere
if (!function_exists('safeQuery')) {
    function safeQuery($db, $sql) { return csrSafeQuery($db, $sql); }
}
if (!function_exists('tableExists')) {
    function tableExists($db, $name) {
        static $cache = [];
        $key = strtolower($name);
        if (isset($cache[$key])) return $cache[$key];
        try {
            $n = $db->real_escape_string($name);
            $r = $db->query("SHOW TABLES LIKE '$n'");
            $cache[$key] = ($r && $r->num_rows > 0);
        } catch (Throwable $e) {
            $cache[$key] = false;
        }
        return $cache[$key];
    }
}
if (!function_exists('columnExists')) {
    function columnExists($db, $table, $col) {
        static $cache = [];
        $key = strtolower($table.'.'.$col);
        if (isset($cache[$key])) return $cache[$key];
        try {
            $t = $db->real_escape_string($table);
            $c = $db->real_escape_string($col);
            $r = $db->query("SHOW COLUMNS FROM `$t` LIKE '$c'");
            $cache[$key] = ($r && $r->num_rows > 0);
        } catch (Throwable $e) {
            $cache[$key] = false;
        }
        return $cache[$key];
    }
}
if (!function_exists('firstExistingCol')) {
    function firstExistingCol($db, $table, $candidates) {
        foreach ($candidates as $col) {
            if (columnExists($db, $table, $col)) return $col;
        }
        return null;
    }
}
if (!function_exists('asList')) {
    function asList($raw) {
        if (is_array($raw)) {
            $out = [];
            foreach ($raw as $v) { $v = trim((string)$v); if ($v !== '') $out[] = $v; }
            return $out;
        }
        if ($raw !== null && $raw !== '') return [trim((string)$raw)];
        return [];
    }
}
if (!function_exists('inListSql')) {
    function inListSql($db, $vals) {
        $parts = [];
        foreach ($vals as $v) $parts[] = "'" . esc($db, $v) . "'";
        return implode(',', $parts);
    }
}
if (!function_exists('multiSelected')) {
    function multiSelected($selected, $value) {
        return in_array($value, $selected, true) ? 'checked' : '';
    }
}
if (!function_exists('fmtNum')) {
    function fmtNum($v, $dec = 2) { return number_format((float)$v, $dec); }
}
if (!function_exists('pageUrl')) {
    function pageUrl($pg) {
        return '?' . http_build_query(array_merge($_GET, ['page' => $pg]));
    }
}
if (!function_exists('pct')) {
    function pct($part, $whole) {
        $w = (float)$whole;
        if (abs($w) < 0.0000001) return 0.0;
        return round(((float)$part / $w) * 100, 2);
    }
}


// ── AJAX: load Subgroup checkboxes for Party / Supplier Acgroup ───────────────
if (isset($_GET['ajax']) && $_GET['ajax'] === 'subgroups') {
    header('Content-Type: application/json; charset=utf-8');
    $code = esc($db, $_GET['code'] ?? '');
    $out = [];
    if ($code !== '') {
        $rows = csrSafeQuery($db, "SELECT SUBCODE, SUB_NAME FROM subgroup WHERE GROUP_CODE='$code' ORDER BY SUB_NAME");
        if (empty($rows)) {
            $rows = csrSafeQuery($db, "SELECT SubCode AS SUBCODE, Sub_Name AS SUB_NAME FROM subgroup WHERE Group_Code='$code' ORDER BY Sub_Name");
        }
        foreach ($rows as $r) {
            $out[] = [
                'SUBCODE'  => (string)($r['SUBCODE'] ?? $r['SubCode'] ?? ''),
                'SUB_NAME' => (string)($r['SUB_NAME'] ?? $r['Sub_Name'] ?? ''),
            ];
        }
    }
    echo json_encode($out, JSON_UNESCAPED_UNICODE);
    exit;
}

// ── Filters capture ───────────────────────────────────────────────────────────
$isPost       = ($_SERVER['REQUEST_METHOD'] === 'POST');
$page         = max(1, (int)($_GET['page'] ?? 1));
$rowsPerPage  = 50;
$isPaginating = isset($_GET['page']);
$exportMode   = strtolower(trim((string)($_GET['export'] ?? $_POST['export'] ?? '')));

if ($isPost && $exportMode === '') { $_SESSION['csr_filters'] = $_POST; }
elseif (!$isPaginating && $exportMode === '' && !$isPost) { unset($_SESSION['csr_filters']); }

$filters    = $isPost ? $_POST : (($_SESSION['csr_filters'] ?? []));
$hasFilters = !empty($filters);
if ($isPost && $exportMode === '') $page = 1;

// Dates
$from = esc($db, $filters['from_date'] ?? ((date('Y') - 1) . '-04-01'));
$to   = esc($db, $filters['to_date']   ?? date('Y-m-d'));
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) $from = date('Y-04-01');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $to))   $to   = date('Y-m-d');

$groupFullName  = !empty($filters['group_full_name']);
$gstnFilter     = esc($db, $filters['gstn_filter']   ?? 'all');
$typeFilter     = esc($db, $filters['type_filter']   ?? 'all'); // local/central/exempted tax type
$onlySaleReturn = !empty($filters['only_sale_return']);
$onlyDiscount   = !empty($filters['only_discount']);
$whatsappOwner  = !empty($filters['whatsapp_owner']);
$saleRateFrom   = esc($db, $filters['sale_rate_from'] ?? '');
$saleRateTo     = esc($db, $filters['sale_rate_to']   ?? '');

// ── Grouping (offline option set) ─────────────────────────────────────────────
$groupingOptions = [
    ''              => '— None —',
    // Official .NET Ist–VIth list
    'ProductGroup'  => 'Product Group',
    'Category'      => 'Category',
    'Company'       => 'Company',
    'Color'         => 'Color',
    'Description'   => 'Description',
    'CompanyNo'     => 'Company No',
    // Extended (same join set as .NET query)
    'City'          => 'City',
    'CompProdCode'  => 'Comp.Prod.Code',
    'Party'         => 'Party',
    'ProductCode'   => 'Product Code',
    'PurGst'        => 'Pur Gst %',
    'PurchaseRate'  => 'Purchase Rate',
    'ReferenceDate' => 'Reference Date',
    'ReferenceNo'   => 'Reference No',
    'SaleGst'       => 'Sale Gst %',
    'SaleRate'      => 'Sale Rate',
    'Size'          => 'Size',
    'SpInst1'       => 'Sp Inst1',
    'SpInst2'       => 'Sp Inst2',
    'SpInst3'       => 'Sp Inst3',
    'StockRate'     => 'Stock Rate',
];
$validGroups = array_keys($groupingOptions);

$grp = [];
for ($i = 1; $i <= 6; $i++) {
    $val = esc($db, $filters["group_$i"] ?? ($i === 1 ? 'Category' : ''));
    $grp[$i] = in_array($val, $validGroups, true) ? $val : '';
}
$primaryGroup = $grp[1] ?: 'Category';
$subTotalReq = [];
for ($i = 1; $i <= 6; $i++) $subTotalReq[$i] = !empty($filters["subtot_$i"]);

// ── Tab filter values ─────────────────────────────────────────────────────────
if (!function_exists('takeMode')) {
function takeMode($filters, $key, $default = 'all') {
    $v = trim((string)($filters[$key] ?? $default));
    return $v === 'select' ? 'select' : 'all';
}
}

$tabDate       = 'select';
$tabType       = takeMode($filters, 'tab_type_mode');
$selType       = asList($filters['sel_type'] ?? $filters['type_list'] ?? []);
$tabParty      = takeMode($filters, 'tab_party_mode');
// Party = Acgroup (SBill1.Code) + Subgroup checkboxes (SBill1.SubCode) — same as live app
$selPartyAcg   = esc($db, $filters['sel_party_acgroup'] ?? $filters['party_code'] ?? '');
$selPartySubs  = asList($filters['sel_party_sub'] ?? $filters['party_sub_codes'] ?? []);
$selParty      = $selPartySubs; // back-compat for tab badge
$tabSupplier   = takeMode($filters, 'tab_supplier_mode');
// Supplier = Product Acgroup (Product.Code / Acgroup_1) + Subgroup_1 (Product.SubCode)
$selSuppAcg    = esc($db, $filters['sel_supplier_acgroup'] ?? '');
$selSuppSubs   = asList($filters['sel_supplier_sub'] ?? []);
$selSupplier   = $selSuppSubs;
$tabCustomer   = takeMode($filters, 'tab_customer_mode');
$selCustomer   = asList($filters['sel_customer'] ?? []);
$tabTransport  = takeMode($filters, 'tab_transport_mode');
$selTransport  = asList($filters['sel_transport'] ?? []);
$tabRefNo      = takeMode($filters, 'tab_refno_mode');
$selRefNo      = asList($filters['sel_refno'] ?? []);
$tabSaleGst    = takeMode($filters, 'tab_salegst_mode');
$selSaleGst    = asList($filters['sel_salegst'] ?? []);
$tabCity       = takeMode($filters, 'tab_city_mode');
$selCity       = asList($filters['sel_city'] ?? []);
$tabState      = takeMode($filters, 'tab_state_mode');
$selState      = asList($filters['sel_state'] ?? []);
$tabRep        = takeMode($filters, 'tab_rep_mode');
$selRep        = asList($filters['sel_rep'] ?? []);
$tabBank       = takeMode($filters, 'tab_bank_mode');
$selBankAc     = asList($filters['sel_bank'] ?? []);
$selBankSub    = asList($filters['sel_bank_sub'] ?? []);
$tabWallet     = takeMode($filters, 'tab_wallet_mode');
$selWalletAc   = asList($filters['sel_wallet'] ?? []);
$selWalletSub  = asList($filters['sel_wallet_sub'] ?? []);
$tabUnit       = takeMode($filters, 'tab_unit_mode');
$selUnit       = asList($filters['sel_unit'] ?? []);
$tabComputer   = takeMode($filters, 'tab_computer_mode');
$selComputer   = asList($filters['sel_computer'] ?? []);
$tabUser       = takeMode($filters, 'tab_user_mode');
$selUser       = asList($filters['sel_user'] ?? []);
$tabPurGst     = takeMode($filters, 'tab_purgst_mode');
$selPurGst     = asList($filters['sel_purgst'] ?? []);

$tabCategory   = takeMode($filters, 'tab_category_mode');
$selCategory   = asList($filters['sel_category'] ?? []);
$tabCompany    = takeMode($filters, 'tab_company_mode');
$selCompany    = asList($filters['sel_company'] ?? []);
$tabSize       = takeMode($filters, 'tab_size_mode');
$selSizeCat    = esc($db, $filters['sel_size_category'] ?? '');
$selSize       = asList($filters['sel_size'] ?? []);
$tabColor      = takeMode($filters, 'tab_color_mode');
$selColor      = asList($filters['sel_color'] ?? []);
$tabDesc       = takeMode($filters, 'tab_desc_mode');
$selDesc       = asList($filters['sel_description'] ?? []);
$tabSpInst1    = takeMode($filters, 'tab_spinst1_mode');
$selSpInst1Cat = esc($db, $filters['sel_spinst1_cat'] ?? '');
$selSpInst1    = asList($filters['sel_spinst1'] ?? []);
$tabSpInst2    = takeMode($filters, 'tab_spinst2_mode');
$selSpInst2Cat = esc($db, $filters['sel_spinst2_cat'] ?? '');
$selSpInst2    = asList($filters['sel_spinst2'] ?? []);
$tabSpInst3    = takeMode($filters, 'tab_spinst3_mode');
$selSpInst3Cat = esc($db, $filters['sel_spinst3_cat'] ?? '');
$selSpInst3    = asList($filters['sel_spinst3'] ?? []);
$tabProduct    = takeMode($filters, 'tab_product_mode');
$selProduct    = asList($filters['sel_product'] ?? []);
$tabCompNo     = takeMode($filters, 'tab_compno_mode');
$selCompNo     = asList($filters['sel_compno'] ?? []);
$tabProdGroup  = takeMode($filters, 'tab_prodgroup_mode');
$selProdGroup  = asList($filters['sel_prodgroup'] ?? []);

// ── Dropdown data (defensive) ─────────────────────────────────────────────────
$ddCategory    = csrSafeQuery($db, "SELECT C_code, c_name FROM category ORDER BY c_name");
$ddCompany     = csrSafeQuery($db, "SELECT Comp_code, comp_name FROM compdetail ORDER BY comp_name");
$ddSizeAll     = csrSafeQuery($db, "SELECT s.s_code, s.s_name, s.cat_code FROM sizemaster s ORDER BY s.s_name");
$ddSizeCats    = csrSafeQuery($db, "SELECT DISTINCT c.C_code, c.c_name FROM category c JOIN sizemaster s ON s.cat_code = c.C_code ORDER BY c.c_name");
$ddColor       = csrSafeQuery($db, "SELECT Color_code, Color_name FROM color ORDER BY Color_name");
$ddDescription = csrSafeQuery($db, "SELECT Des_Name, Des_Code FROM description ORDER BY Des_Name");
if (empty($ddDescription)) {
    $ddDescription = csrSafeQuery($db, "SELECT DISTINCT PRODUCT_NAME AS Des_Name, PRODUCT_NAME AS Des_Code FROM product WHERE IFNULL(PRODUCT_NAME,'') <> '' ORDER BY PRODUCT_NAME");
}
$ddSpInst1All  = csrSafeQuery($db, "SELECT i.s_code, i.s_name, i.cat_code FROM specialinst i ORDER BY i.s_name");
$ddSpInst1Cats = csrSafeQuery($db, "SELECT DISTINCT c.C_code, c.c_name FROM category c JOIN specialinst i ON i.cat_code = c.C_code ORDER BY c.c_name");
$ddSpInst2All  = csrSafeQuery($db, "SELECT i.s_code, i.s_name, i.cat_code FROM specialinst1 i ORDER BY i.s_name");
$ddSpInst2Cats = csrSafeQuery($db, "SELECT DISTINCT c.C_code, c.c_name FROM category c JOIN specialinst1 i ON i.cat_code = c.C_code ORDER BY c.c_name");
$ddSpInst3All  = csrSafeQuery($db, "SELECT i.s_code, i.s_name, i.cat_code FROM specialinst2 i ORDER BY i.s_name");
$ddSpInst3Cats = csrSafeQuery($db, "SELECT DISTINCT c.C_code, c.c_name FROM category c JOIN specialinst2 i ON i.cat_code = c.C_code ORDER BY c.c_name");
$ddProduct     = csrSafeQuery($db, "SELECT PRODUCT_CODE, PRODUCT_NAME, CONCAT(PRODUCT_CODE, ' - ', IFNULL(PRODUCT_NAME,'')) AS display FROM product ORDER BY PRODUCT_NAME LIMIT 8000");
$ddCompNo      = csrSafeQuery($db, "SELECT Comp_No AS comp_no FROM product WHERE IFNULL(Comp_No,'') <> '' GROUP BY Comp_No ORDER BY Comp_No");
if (empty($ddCompNo)) {
    $ddCompNo = csrSafeQuery($db, "SELECT DISTINCT comp_no FROM product WHERE IFNULL(comp_no,'') <> '' ORDER BY comp_no");
}
$ddProdGroup   = csrSafeQuery($db, "SELECT ProdGroup_Code, ProdGroup_Name FROM productgroup ORDER BY ProdGroup_Name");

// Type — live app query (Status 4 sale / 5 sale return)
$ddType = csrSafeQuery($db, "SELECT V_TYPE, MAX(DESCRIPTION) AS DESCRIPTION FROM type WHERE Status IN (4,5) GROUP BY V_TYPE ORDER BY DESCRIPTION");
if (empty($ddType)) {
    $ddType = csrSafeQuery($db, "SELECT V_TYPE, MAX(IFNULL(DESCRIPTION, IFNULL(V_Name, V_TYPE))) AS DESCRIPTION FROM type WHERE STATUS IN (4,5) GROUP BY V_TYPE ORDER BY DESCRIPTION");
}

// Party list = Acgroup (CODE, NAME) — then Subgroup by GROUP_CODE
$ddAcgroup = csrSafeQuery($db, "SELECT CODE, NAME FROM acgroup ORDER BY NAME");
if (empty($ddAcgroup)) {
    $ddAcgroup = csrSafeQuery($db, "SELECT Code AS CODE, IFNULL(Name, Code) AS NAME FROM acgroup ORDER BY NAME");
}
// normalize keys Code/Name for templates
foreach ($ddAcgroup as &$ag) {
    if (!isset($ag['CODE']) && isset($ag['Code'])) $ag['CODE'] = $ag['Code'];
    if (!isset($ag['NAME']) && isset($ag['Name'])) $ag['NAME'] = $ag['Name'];
}
unset($ag);

// Preload party subgroups when an Acgroup is already selected
$ddPartySubs = [];
if ($selPartyAcg !== '') {
    $ddPartySubs = csrSafeQuery($db, "SELECT SUBCODE, SUB_NAME FROM subgroup WHERE GROUP_CODE='$selPartyAcg' ORDER BY SUB_NAME");
    if (empty($ddPartySubs)) {
        $ddPartySubs = csrSafeQuery($db, "SELECT SubCode AS SUBCODE, Sub_Name AS SUB_NAME FROM subgroup WHERE Group_Code='$selPartyAcg' ORDER BY Sub_Name");
    }
}
$ddSuppSubs = [];
if ($selSuppAcg !== '') {
    $ddSuppSubs = csrSafeQuery($db, "SELECT SUBCODE, SUB_NAME FROM subgroup WHERE GROUP_CODE='$selSuppAcg' ORDER BY SUB_NAME");
    if (empty($ddSuppSubs)) {
        $ddSuppSubs = csrSafeQuery($db, "SELECT SubCode AS SUBCODE, Sub_Name AS SUB_NAME FROM subgroup WHERE Group_Code='$selSuppAcg' ORDER BY Sub_Name");
    }
}

// Customer still lists all subgroup names (bill parties)
$ddCustomer = csrSafeQuery($db, "SELECT DISTINCT SubCode, Sub_Name, Group_Code FROM subgroup WHERE IFNULL(Sub_Name,'') <> '' ORDER BY Sub_Name LIMIT 8000");

// Transport — probe real columns (Trans_Name may not exist on this DB)
$ddTransport = [];
if (tableExists($db, 'transport')) {
    $tCol = firstExistingCol($db, 'transport', ['Trans_Name', 'Transport', 'T_Name', 'Name', 'TRANSPORT']);
    if ($tCol) $ddTransport = csrSafeQuery($db, "SELECT DISTINCT `$tCol` AS name FROM transport WHERE IFNULL(`$tCol`,'') <> '' ORDER BY name");
}
if (empty($ddTransport)) {
    $tCol = firstExistingCol($db, 'sbill1', ['Transport', 'Trans_Name', 'TRANSPORT', 'TransName']);
    if ($tCol) $ddTransport = csrSafeQuery($db, "SELECT DISTINCT IFNULL(`$tCol`,'') AS name FROM sbill1 WHERE IFNULL(`$tCol`,'') <> '' ORDER BY name");
}

// Ref No
$ddRefNo = [];
$refCol = firstExistingCol($db, 'sbill1', ['Ref_No', 'RefNo', 'REF_NO', 'ReferenceNo']);
if ($refCol) {
    $ddRefNo = csrSafeQuery($db, "SELECT DISTINCT IFNULL(`$refCol`,'') AS refno FROM sbill1 WHERE IFNULL(`$refCol`,'') <> '' ORDER BY refno LIMIT 5000");
}
if (empty($ddRefNo)) {
    $pref = firstExistingCol($db, 'product', ['RefNo', 'Ref_No', 'REF_NO']);
    if ($pref) $ddRefNo = csrSafeQuery($db, "SELECT DISTINCT IFNULL(`$pref`,'') AS refno FROM product WHERE IFNULL(`$pref`,'') <> '' ORDER BY refno LIMIT 5000");
}

$ddSaleGst = [];
$ssatSel = columnExists($db, 'product', 'SSat_Per') ? 'IFNULL(SSat_Per,0)' : '0';
$ddSaleGst = csrSafeQuery($db, "SELECT DISTINCT ROUND(IFNULL(Tax,0)+$ssatSel,2) AS gst FROM product ORDER BY gst");
$purExpr = 'IFNULL(Tax,0)';
if (columnExists($db, 'product', 'PTax')) $purExpr = 'IFNULL(PTax, IFNULL(Tax,0))';
elseif (columnExists($db, 'product', 'Pur_Tax')) $purExpr = 'IFNULL(Pur_Tax, IFNULL(Tax,0))';
$ddPurGst = csrSafeQuery($db, "SELECT DISTINCT ROUND($purExpr,2) AS gst FROM product ORDER BY gst");

$ddCity = csrSafeQuery($db, "SELECT City_Code, City_Name FROM citymaster ORDER BY City_Name");
if (empty($ddCity)) $ddCity = csrSafeQuery($db, "SELECT City_Code, City_Name FROM CityMaster ORDER BY City_Name");

$ddState = csrSafeQuery($db, "SELECT State_Code, IFNULL(State_Name, State_Code) AS State_Name FROM state ORDER BY State_Name");
if (empty($ddState)) $ddState = csrSafeQuery($db, "SELECT State_Code, IFNULL(State_Name, State_Code) AS State_Name FROM State ORDER BY State_Name");

$ddRep = csrSafeQuery($db, "SELECT Rp_Code, Rp_Name FROM representative ORDER BY Rp_Name");
if (empty($ddRep)) $ddRep = csrSafeQuery($db, "SELECT Rep_Code AS Rp_Code, Rp_Name FROM representative ORDER BY Rp_Name");

$ddBank = csrSafeQuery($db, "SELECT DISTINCT SubCode, Sub_Name FROM subgroup WHERE IFNULL(Sub_Name,'') <> '' ORDER BY Sub_Name LIMIT 5000");
$ddWallet = $ddBank;

$ddUnit = [];
if (tableExists($db, 'unitmaster')) {
    $ddUnit = csrSafeQuery($db, "SELECT Unit_Code, Unit_Name FROM unitmaster ORDER BY Unit_Name");
}
if (empty($ddUnit)) {
    $uCol = firstExistingCol($db, 'product', ['Unit', 'Unit_Code', 'UNIT']);
    if ($uCol) $ddUnit = csrSafeQuery($db, "SELECT DISTINCT IFNULL(`$uCol`,'') AS Unit_Code, IFNULL(`$uCol`,'') AS Unit_Name FROM product WHERE IFNULL(`$uCol`,'') <> '' ORDER BY Unit_Name");
}

// Computer / User — only real sbill1 columns
$ddComputer = [];
$compCol = firstExistingCol($db, 'sbill1', ['Computer', 'Comp_Name', 'Machine', 'COMPUTER']);
if ($compCol) {
    $ddComputer = csrSafeQuery($db, "SELECT DISTINCT IFNULL(`$compCol`,'') AS name FROM sbill1 WHERE IFNULL(`$compCol`,'') <> '' ORDER BY name");
}
$ddUser = [];
$userCol = firstExistingCol($db, 'sbill1', ['UserName', 'User_Name', 'User', 'USER_NAME']);
if ($userCol) {
    $ddUser = csrSafeQuery($db, "SELECT DISTINCT IFNULL(`$userCol`,'') AS name FROM sbill1 WHERE IFNULL(`$userCol`,'') <> '' ORDER BY name");
}
if (empty($ddUser) && tableExists($db, 'users')) {
    $uc = firstExistingCol($db, 'users', ['UserName', 'User_Name', 'NAME', 'name']);
    if ($uc) $ddUser = csrSafeQuery($db, "SELECT `$uc` AS name FROM users ORDER BY name");
}

// ── Group column map (.NET UPPER/LTRIM/RTRIM style) ─────────────────────────
// Official Ist–VIth values: Product Group, Category, Company, Color, Description, Company No
// Extra keys kept for client filter/grouping requests; expressions use same joins as .NET.
$norm = function ($expr) {
    return "UPPER(LTRIM(RTRIM(IFNULL($expr,''))))";
};

// Column helpers — only reference columns that exist (MySQL errors inside IFNULL too)
$pickCol = function ($table, $alias, $candidates, $fallbackSql = '0') use ($db) {
    foreach ($candidates as $col) {
        if (columnExists($db, $table, $col)) return "$alias.`$col`";
    }
    return $fallbackSql;
};
$totAmtExpr    = 'IFNULL(' . $pickCol('stock', 'st', ['Tot_Amt', 'TOT_AMT', 'TOT_AMOUNT', 'Tot_Amount'], '0') . ',0)';
$totAmountExpr = 'IFNULL(' . $pickCol('stock', 'st', ['Tot_Amount', 'TOT_AMOUNT', 'Tot_Amt', 'TOT_AMT'], '0') . ',0)';
$sdiscExpr     = 'IFNULL(' . $pickCol('stock', 'st', ['SDisc_Amt', 'SDISC_AMT', 'SDiscAmt'], '0') . ',0)';
$billDiscExpr  = columnExists($db, 'sbill1', 'Disc') ? 'IFNULL(sb.Disc,0)' : '0';
$taxAmtExpr    = 'IFNULL(' . $pickCol('stock', 'st', ['Tax_Amt', 'TAX_AMT', 'TaxAmt'], '0') . ',0)';
$ssatExpr      = 'IFNULL(' . $pickCol('stock', 'st', ['SSat_Amt', 'SSAT_AMT', 'SSatAmt'], '0') . ',0)';
$stockTaxYnCol = firstExistingCol($db, 'stock', ['Tax_YN', 'TAX_YN']);
$stockTaxYn    = $stockTaxYnCol ? "IFNULL(st.`$stockTaxYnCol`,'N')" : "'N'";
$billTaxYn     = columnExists($db, 'sbill1', 'TAX_YN') ? "IFNULL(sb.TAX_YN,'N')" : "'N'";
// Net after bill-header discount (matches .NET Tot_Amt formula)
$netTotExpr   = "($totAmtExpr - (($totAmtExpr * $billDiscExpr) / 100))";
// Disc amt = line disc + bill disc portion (matches .NET SDisc_Amt algebra)
$discAmtExpr  = "($sdiscExpr + ($totAmtExpr * $billDiscExpr / 100))";

$refNoExpr = '\'\'';
foreach ([['sbill1','sb',['Ref_No','RefNo','REF_NO']], ['product','p',['RefNo','Ref_No','REF_NO']]] as $spec) {
    $c = firstExistingCol($db, $spec[0], $spec[2]);
    if ($c) { $refNoExpr = "IFNULL({$spec[1]}.`$c`,'')"; break; }
}
$refDateParts = [];
foreach ([['sbill1','sb',['Ref_Date','RefDate']], ['product','p',['RefDate','Ref_Date']]] as $spec) {
    $c = firstExistingCol($db, $spec[0], $spec[2]);
    if ($c) $refDateParts[] = "{$spec[1]}.`$c`";
}
if (count($refDateParts) === 1) $refDateExpr = "IFNULL(DATE_FORMAT({$refDateParts[0]}, '%Y-%m-%d'),'')";
elseif (count($refDateParts) >= 2) $refDateExpr = "IFNULL(DATE_FORMAT(IFNULL({$refDateParts[0]}, {$refDateParts[1]}), '%Y-%m-%d'),'')";
else $refDateExpr = "''";

$prateExpr = 'IFNULL(' . $pickCol('product', 'p', ['PRate', 'Pur_Rate', 'PurchaseRate'], '0') . ',0)';
$srateExpr = 'IFNULL(' . $pickCol('product', 'p', ['SRate', 'Stock_Rate', 'StockRate'], '0') . ',0)';

$groupColMap = [
    'ProductGroup'  => $norm('pg.ProdGroup_Name'),
    'Category'      => $norm('c.c_name'),
    'Company'       => $norm('cd.comp_name'),
    'Color'         => $norm('col.Color_name'),
    'Description'   => $norm("IFNULL(des.Des_Name, p.PRODUCT_NAME)"),
    'CompanyNo'     => $norm('p.comp_no'),
    'City'          => $norm('cm.City_Name'),
    'CompProdCode'  => $norm('p.CompProdCode'),
    'Party'         => $norm('party.Sub_Name'),
    'ProductCode'   => $norm('p.PRODUCT_CODE'),
    'PurGst'        => "CAST(ROUND(" . (
        columnExists($db, 'product', 'PTax') ? 'IFNULL(p.PTax, IFNULL(p.Tax,0))' :
        (columnExists($db, 'product', 'Pur_Tax') ? 'IFNULL(p.Pur_Tax, IFNULL(p.Tax,0))' : 'IFNULL(p.Tax,0)')
    ) . ",2) AS CHAR)",
    'PurchaseRate'  => "CAST(ROUND($prateExpr,2) AS CHAR)",
    'ReferenceDate' => $refDateExpr,
    'ReferenceNo'   => $refNoExpr,
    'SaleGst'       => "CAST(ROUND(IFNULL(p.Tax,0)+" . (columnExists($db, 'product', 'SSat_Per') ? 'IFNULL(p.SSat_Per,0)' : '0') . ",2) AS CHAR)",
    'SaleRate'      => "CAST(ROUND(IFNULL(st.AMOUNT/NULLIF(st.QTY,0),0),2) AS CHAR)",
    'Size'          => $norm('sm.s_name'),
    'SpInst1'       => $norm('si1.s_name'),
    'SpInst2'       => $norm('si2.s_name'),
    'SpInst3'       => $norm('si3.s_name'),
    'StockRate'     => "CAST(ROUND($srateExpr,2) AS CHAR)",
];

// Always expose Grp1..Grp6 like .NET (empty string when unused)
$grpSqlParts = [];
$grpByParts  = [];
$activeGroups = [];
for ($i = 1; $i <= 6; $i++) {
    $key = $grp[$i];
    if ($key !== '' && isset($groupColMap[$key])) {
        $expr = $groupColMap[$key];
        $grpSqlParts[] = "$expr AS g" . ($i - 1);
        $grpByParts[]  = $expr;
        $activeGroups[] = ['level' => $i, 'name' => $key, 'col' => $expr, 'label' => $groupingOptions[$key]];
    } else {
        $grpSqlParts[] = "'' AS g" . ($i - 1);
    }
}
if (empty($activeGroups)) {
    $expr = $groupColMap['Category'];
    $grpSqlParts[0] = "$expr AS g0";
    $grpByParts = [$expr];
    $activeGroups[] = ['level' => 1, 'name' => 'Category', 'col' => $expr, 'label' => 'Category'];
}
$grpSelectSql = implode(",\n            ", $grpSqlParts);
$groupCols    = $grpByParts; // only non-empty for GROUP BY
$groupBySql   = implode(', ', $groupCols);
$numGroupLevels = count($activeGroups);
$orderBySql   = implode(', ', array_map(function ($i) { return 'g' . $i; }, range(0, 5)));

// ── WHERE (shared; Type.Status applied per UNION leg) ─────────────────────────
$whereParts = ["st.V_DATE >= '$from'", "st.V_DATE <= '$to'"];

if ($tabType === 'select' && !empty($selType)) {
    $whereParts[] = 'st.V_TYPE IN (' . inListSql($db, $selType) . ')';
}
// Party on bill: SBill1.Code = Acgroup, SBill1.SubCode = Subgroup
if ($tabParty === 'select') {
    if ($selPartyAcg !== '') $whereParts[] = "sb.Code = '$selPartyAcg'";
    if (!empty($selPartySubs)) $whereParts[] = 'sb.SubCode IN (' . inListSql($db, $selPartySubs) . ')';
}
// Supplier from product account: Product.Code = Acgroup_1, Product.SubCode = Subgroup_1 (.NET query)
if ($tabSupplier === 'select') {
    if ($selSuppAcg !== '') $whereParts[] = "IFNULL(p.Code, IFNULL(p.GROUP_CODE,'')) = '$selSuppAcg'";
    if (!empty($selSuppSubs)) $whereParts[] = "IFNULL(p.Subcode, IFNULL(p.SubCode,'')) IN (" . inListSql($db, $selSuppSubs) . ")";
}
if ($tabCustomer === 'select' && !empty($selCustomer)) {
    $whereParts[] = 'party.Sub_Name IN (' . inListSql($db, $selCustomer) . ')';
}
if ($tabTransport === 'select' && !empty($selTransport)) {
    $tCol = firstExistingCol($db, 'sbill1', ['Transport', 'Trans_Name', 'TRANSPORT', 'TransName']);
    if ($tCol) $whereParts[] = "IFNULL(sb.`$tCol`,'') IN (" . inListSql($db, $selTransport) . ")";
}
if ($tabRefNo === 'select' && !empty($selRefNo)) {
    $refCol = firstExistingCol($db, 'sbill1', ['Ref_No', 'RefNo', 'REF_NO', 'ReferenceNo']);
    $pref = firstExistingCol($db, 'product', ['RefNo', 'Ref_No', 'REF_NO']);
    if ($refCol) $whereParts[] = "IFNULL(sb.`$refCol`,'') IN (" . inListSql($db, $selRefNo) . ")";
    elseif ($pref) $whereParts[] = "IFNULL(p.`$pref`,'') IN (" . inListSql($db, $selRefNo) . ")";
}
if ($tabSaleGst === 'select' && !empty($selSaleGst)) {
    $ssat = columnExists($db, 'product', 'SSat_Per') ? 'IFNULL(p.SSat_Per,0)' : '0';
    $whereParts[] = "CAST(ROUND(IFNULL(p.Tax,0)+$ssat,2) AS CHAR) IN (" . inListSql($db, $selSaleGst) . ")";
}
if ($tabCity === 'select' && !empty($selCity)) {
    $whereParts[] = 'cm.City_Name IN (' . inListSql($db, $selCity) . ')';
}
if ($tabState === 'select' && !empty($selState)) {
    $whereParts[] = 'IFNULL(stt.State_Name, stt.State_Code) IN (' . inListSql($db, $selState) . ')';
}
if ($tabRep === 'select' && !empty($selRep)) {
    $whereParts[] = 'rp.Rp_Name IN (' . inListSql($db, $selRep) . ')';
}
if ($tabBank === 'select') {
    if (!empty($selBankAc)) $whereParts[] = 'sb.Bank_Code IN (' . inListSql($db, $selBankAc) . ')';
    if (!empty($selBankSub)) $whereParts[] = 'bank.Sub_Name IN (' . inListSql($db, $selBankSub) . ')';
}
if ($tabWallet === 'select') {
    if (!empty($selWalletAc)) $whereParts[] = 'sb.Wallet_Code IN (' . inListSql($db, $selWalletAc) . ')';
    if (!empty($selWalletSub)) $whereParts[] = 'wallet.Sub_Name IN (' . inListSql($db, $selWalletSub) . ')';
}
if ($tabUnit === 'select' && !empty($selUnit)) {
    $whereParts[] = "IFNULL(p.Unit, IFNULL(p.Unit_Code,'')) IN (" . inListSql($db, $selUnit) . ")";
}
if ($tabComputer === 'select' && !empty($selComputer)) {
    $compExpr = null;
    foreach (['Computer','Comp_Name','Machine'] as $col) {
        if (columnExists($db, 'sbill1', $col)) { $compExpr = "IFNULL(sb.`$col`,'')"; break; }
    }
    if ($compExpr) $whereParts[] = "$compExpr IN (" . inListSql($db, $selComputer) . ")";
}
if ($tabUser === 'select' && !empty($selUser)) {
    $userExpr = null;
    foreach (['UserName','User_Name','User'] as $col) {
        if (columnExists($db, 'sbill1', $col)) { $userExpr = "IFNULL(sb.`$col`,'')"; break; }
    }
    if ($userExpr) $whereParts[] = "$userExpr IN (" . inListSql($db, $selUser) . ")";
}
if ($tabPurGst === 'select' && !empty($selPurGst)) {
    if (columnExists($db, 'product', 'PTax')) {
        $purWhere = 'IFNULL(p.PTax, IFNULL(p.Tax,0))';
    } elseif (columnExists($db, 'product', 'Pur_Tax')) {
        $purWhere = 'IFNULL(p.Pur_Tax, IFNULL(p.Tax,0))';
    } else {
        $purWhere = 'IFNULL(p.Tax,0)';
    }
    $whereParts[] = "CAST(ROUND($purWhere,2) AS CHAR) IN (" . inListSql($db, $selPurGst) . ")";
}
if ($tabCategory === 'select' && !empty($selCategory)) {
    $whereParts[] = 'c.c_name IN (' . inListSql($db, $selCategory) . ')';
}
if ($tabCompany === 'select' && !empty($selCompany)) {
    $whereParts[] = 'cd.comp_name IN (' . inListSql($db, $selCompany) . ')';
}
if ($tabSize === 'select') {
    if (!empty($selSize)) $whereParts[] = 'sm.s_name IN (' . inListSql($db, $selSize) . ')';
    elseif ($selSizeCat !== '') $whereParts[] = "sm.cat_code = '$selSizeCat'";
}
if ($tabColor === 'select' && !empty($selColor)) {
    $whereParts[] = 'col.Color_name IN (' . inListSql($db, $selColor) . ')';
}
if ($tabDesc === 'select' && !empty($selDesc)) {
    $whereParts[] = "IFNULL(des.Des_Name, p.PRODUCT_NAME) IN (" . inListSql($db, $selDesc) . ")";
}
if ($tabSpInst1 === 'select') {
    if (!empty($selSpInst1)) $whereParts[] = 'si1.s_name IN (' . inListSql($db, $selSpInst1) . ')';
    elseif ($selSpInst1Cat !== '') $whereParts[] = "si1.cat_code = '$selSpInst1Cat'";
}
if ($tabSpInst2 === 'select') {
    if (!empty($selSpInst2)) $whereParts[] = 'si2.s_name IN (' . inListSql($db, $selSpInst2) . ')';
    elseif ($selSpInst2Cat !== '') $whereParts[] = "si2.cat_code = '$selSpInst2Cat'";
}
if ($tabSpInst3 === 'select') {
    if (!empty($selSpInst3)) $whereParts[] = 'si3.s_name IN (' . inListSql($db, $selSpInst3) . ')';
    elseif ($selSpInst3Cat !== '') $whereParts[] = "si3.cat_code = '$selSpInst3Cat'";
}
if ($tabProduct === 'select' && !empty($selProduct)) {
    $whereParts[] = 'p.PRODUCT_CODE IN (' . inListSql($db, $selProduct) . ')';
}
if ($tabCompNo === 'select' && !empty($selCompNo)) {
    $whereParts[] = 'p.comp_no IN (' . inListSql($db, $selCompNo) . ')';
}
if ($tabProdGroup === 'select' && !empty($selProdGroup)) {
    $whereParts[] = 'pg.ProdGroup_Name IN (' . inListSql($db, $selProdGroup) . ')';
}

// GSTIN uses SBill1.TAX_YN (bill header) — same as .NET CGST/IGST split source
if ($gstnFilter === 'with')    $whereParts[] = "$billTaxYn='Y'";
if ($gstnFilter === 'without') $whereParts[] = "$billTaxYn<>'Y'";
// Tax Type All/Local/Central/Exempted uses Stock.Tax_YN (Y/N/F) like Central/Local/Exempted Amt
if ($typeFilter === 'local')    $whereParts[] = "$stockTaxYn='N'";
if ($typeFilter === 'central')  $whereParts[] = "$stockTaxYn='Y'";
if ($typeFilter === 'exempted') $whereParts[] = "$stockTaxYn='F'";
if ($onlyDiscount)   $whereParts[] = "($discAmtExpr) > 0";
if ($saleRateFrom !== '') $whereParts[] = "(st.AMOUNT/NULLIF(st.QTY,0)) >= '$saleRateFrom'";
if ($saleRateTo   !== '') $whereParts[] = "(st.AMOUNT/NULLIF(st.QTY,0)) <= '$saleRateTo'";

$whereCommon = implode(' AND ', $whereParts);

// ── Aggregates (.NET formulas; sign applied per UNION leg) ─────────────────────
$AGG_POS = "
            IFNULL(SUM(st.QTY),0) AS Qty,
            IFNULL(SUM(st.AMOUNT),0) AS Amount,
            IFNULL(SUM($discAmtExpr),0) AS DiscAmt,
            IFNULL(SUM($totAmountExpr),0) AS TaxableAmt,
            IFNULL(SUM($netTotExpr),0) AS GrossAmt,
            IFNULL(SUM(CASE WHEN $billTaxYn = 'N' THEN $taxAmtExpr ELSE 0 END),0) AS CGSTAmt,
            IFNULL(SUM($ssatExpr),0) AS SGSTAmt,
            IFNULL(SUM(CASE WHEN $billTaxYn = 'Y' THEN $taxAmtExpr ELSE 0 END),0) AS IGSTAmt,
            IFNULL(SUM(CASE WHEN $stockTaxYn = 'Y' THEN ($netTotExpr) ELSE 0 END),0) AS CentralAmt,
            IFNULL(SUM(CASE WHEN $stockTaxYn = 'N' THEN ($netTotExpr) ELSE 0 END),0) AS LocalAmt,
            IFNULL(SUM(CASE WHEN $stockTaxYn = 'F' THEN ($netTotExpr) ELSE 0 END),0) AS ExemptedAmt,
            IFNULL(SUM(CASE WHEN $stockTaxYn = 'Y' THEN st.QTY ELSE 0 END),0) AS CentralQty,
            IFNULL(SUM(CASE WHEN $stockTaxYn = 'N' THEN st.QTY ELSE 0 END),0) AS LocalQty,
            IFNULL(SUM(CASE WHEN $stockTaxYn = 'F' THEN st.QTY ELSE 0 END),0) AS ExemptedQty";

$AGG_NEG = "
            -IFNULL(SUM(st.QTY),0) AS Qty,
            -IFNULL(SUM(st.AMOUNT),0) AS Amount,
            -IFNULL(SUM($discAmtExpr),0) AS DiscAmt,
            -IFNULL(SUM($totAmountExpr),0) AS TaxableAmt,
            -IFNULL(SUM($netTotExpr),0) AS GrossAmt,
            -IFNULL(SUM(CASE WHEN $billTaxYn = 'N' THEN $taxAmtExpr ELSE 0 END),0) AS CGSTAmt,
            -IFNULL(SUM($ssatExpr),0) AS SGSTAmt,
            -IFNULL(SUM(CASE WHEN $billTaxYn = 'Y' THEN $taxAmtExpr ELSE 0 END),0) AS IGSTAmt,
            -IFNULL(SUM(CASE WHEN $stockTaxYn = 'Y' THEN ($netTotExpr) ELSE 0 END),0) AS CentralAmt,
            -IFNULL(SUM(CASE WHEN $stockTaxYn = 'N' THEN ($netTotExpr) ELSE 0 END),0) AS LocalAmt,
            -IFNULL(SUM(CASE WHEN $stockTaxYn = 'F' THEN ($netTotExpr) ELSE 0 END),0) AS ExemptedAmt,
            -IFNULL(SUM(CASE WHEN $stockTaxYn = 'Y' THEN st.QTY ELSE 0 END),0) AS CentralQty,
            -IFNULL(SUM(CASE WHEN $stockTaxYn = 'N' THEN st.QTY ELSE 0 END),0) AS LocalQty,
            -IFNULL(SUM(CASE WHEN $stockTaxYn = 'F' THEN st.QTY ELSE 0 END),0) AS ExemptedQty";

// Joins mirror .NET query (SBill1 → Stock → masters)
$FROM_JOINS = "
        FROM sbill1 sb
        LEFT JOIN stock st ON st.V_TYPE = sb.V_TYPE AND st.V_NO = sb.V_NO
        LEFT JOIN type ty ON st.V_TYPE = ty.V_TYPE
        LEFT JOIN product p ON st.PROD_CODE = p.PRODUCT_CODE
        LEFT JOIN productgroup pg ON p.ProdGroup_Code = pg.ProdGroup_Code
        LEFT JOIN compdetail cd ON p.COMP_CODE = cd.Comp_code
        LEFT JOIN category c ON p.CAT_CODE = c.C_code
        LEFT JOIN color col ON p.COLOR_CODE = col.Color_code
        LEFT JOIN sizemaster sm ON p.SIZE_CODE = sm.s_code
        LEFT JOIN specialinst si1 ON p.INST1_CODE = si1.s_code
        LEFT JOIN specialinst1 si2 ON p.INST2_CODE = si2.s_code
        LEFT JOIN specialinst2 si3 ON p.INST3_CODE = si3.s_code";

if (tableExists($db, 'description')) {
    $descCol = columnExists($db, 'product', 'Desc_Code') ? 'Desc_Code' : (columnExists($db, 'product', 'DESC_CODE') ? 'DESC_CODE' : '');
    if ($descCol !== '') {
        $FROM_JOINS .= "\n        LEFT JOIN description des ON p.`$descCol` = des.Des_Code";
    } else {
        $FROM_JOINS .= "\n        LEFT JOIN (SELECT '' AS Des_Code, '' AS Des_Name) des ON 1=0";
    }
} else {
    $FROM_JOINS .= "\n        LEFT JOIN (SELECT '' AS Des_Code, '' AS Des_Name) des ON 1=0";
}
if (tableExists($db, 'acgroup')) {
    $FROM_JOINS .= "\n        LEFT JOIN acgroup ag ON sb.Code = ag.Code";
    $FROM_JOINS .= "\n        LEFT JOIN acgroup ag1 ON p.Code = ag1.Code";
}
if (tableExists($db, 'subgroup')) {
    $FROM_JOINS .= "
        LEFT JOIN subgroup party ON sb.Code = party.Group_Code AND sb.SubCode = party.SubCode
        LEFT JOIN subgroup supp ON supp.SubCode = p.Subcode AND supp.Group_Code = p.Code
        LEFT JOIN subgroup bank ON sb.Bank_Code = bank.SubCode
        LEFT JOIN subgroup wallet ON sb.Wallet_Code = wallet.Group_Code AND sb.Wallet_SubCode = wallet.SubCode";
} else {
    $FROM_JOINS .= "
        LEFT JOIN (SELECT '' AS Group_Code, '' AS SubCode, '' AS Sub_Name, '' AS City_Code) party ON 1=0
        LEFT JOIN (SELECT '' AS Group_Code, '' AS SubCode, '' AS Sub_Name, '' AS City_Code) supp ON 1=0
        LEFT JOIN (SELECT '' AS SubCode, '' AS Sub_Name) bank ON 1=0
        LEFT JOIN (SELECT '' AS Group_Code, '' AS SubCode, '' AS Sub_Name) wallet ON 1=0";
}
// City via Product's Subgroup_1 (matches .NET CityMaster join)
if (tableExists($db, 'citymaster')) {
    $FROM_JOINS .= "\n        LEFT JOIN citymaster cm ON cm.City_Code = supp.City_Code";
} elseif (tableExists($db, 'CityMaster')) {
    $FROM_JOINS .= "\n        LEFT JOIN CityMaster cm ON cm.City_Code = supp.City_Code";
} else {
    $FROM_JOINS .= "\n        LEFT JOIN (SELECT '' AS City_Code, '' AS City_Name, '' AS State_Code) cm ON 1=0";
}
if (tableExists($db, 'state')) {
    $FROM_JOINS .= "\n        LEFT JOIN state stt ON stt.State_Code = cm.State_Code";
} elseif (tableExists($db, 'State')) {
    $FROM_JOINS .= "\n        LEFT JOIN State stt ON stt.State_Code = cm.State_Code";
} else {
    $FROM_JOINS .= "\n        LEFT JOIN (SELECT '' AS State_Code, '' AS State_Name) stt ON 1=0";
}
if (tableExists($db, 'representative')) {
    $FROM_JOINS .= "\n        LEFT JOIN representative rp ON st.Rep_Code = rp.Rp_Code";
} else {
    $FROM_JOINS .= "\n        LEFT JOIN (SELECT '' AS Rp_Code, '' AS Rp_Name) rp ON 1=0";
}

/**
 * Build one UNION leg (.NET Status=4 positive / Status=5 negative).
 */
if (!function_exists('buildLegSql')) {
function buildLegSql($status, $agg, $grpSelectSql, $groupBySql, $whereCommon, $fromJoins) {
    return "
        SELECT
            $grpSelectSql,
            $agg
        $fromJoins
        WHERE ty.STATUS = $status AND $whereCommon
        GROUP BY $groupBySql";
}
}

if (!function_exists('buildMainSql')) {
function buildMainSql($onlySaleReturn, $grpSelectSql, $groupBySql, $orderBySql, $AGG_POS, $AGG_NEG, $whereCommon, $FROM_JOINS) {
    $sale = buildLegSql(4, $AGG_POS, $grpSelectSql, $groupBySql, $whereCommon, $FROM_JOINS);
    $ret  = buildLegSql(5, $AGG_NEG, $grpSelectSql, $groupBySql, $whereCommon, $FROM_JOINS);
    if ($onlySaleReturn) {
        $union = $ret;
    } else {
        $union = "($sale) UNION ALL ($ret)";
    }
    // Re-aggregate Grp buckets across UNION (same as summing both legs)
    $gCols = [];
    for ($i = 0; $i < 6; $i++) $gCols[] = "g$i";
    $gSelect = implode(', ', $gCols);
    $metrics = 'Qty,Amount,DiscAmt,TaxableAmt,GrossAmt,CGSTAmt,SGSTAmt,IGSTAmt,CentralAmt,LocalAmt,ExemptedAmt,CentralQty,LocalQty,ExemptedQty';
    $sums = implode(', ', array_map(function ($m) { return "SUM($m) AS $m"; }, explode(',', $metrics)));
    return "
        SELECT $gSelect, $sums
        FROM ($union) AS u
        GROUP BY $gSelect
        ORDER BY $orderBySql ASC";
}
}

/**
 * $nActive = how many leading *active* groups to subtotal (1..numGroupLevels-1).
 * Uses original Ist–VIth slots (g0..g5) so keys align with main query rows.
 */
if (!function_exists('buildSubtotalSql')) {
function buildSubtotalSql($onlySaleReturn, $activeGroups, $nActive, $AGG_POS, $AGG_NEG, $whereCommon, $FROM_JOINS) {
    $cols = [];
    $groupBy = [];
    $outAliases = [];
    for ($i = 0; $i < $nActive; $i++) {
        $slot = (int)$activeGroups[$i]['level'] - 1; // 0..5
        $expr = $activeGroups[$i]['col'];
        $cols[] = "$expr AS g$slot";
        $groupBy[] = $expr;
        $outAliases[] = "g$slot";
    }
    $selectCols = implode(', ', $cols);
    $groupBySql = implode(', ', $groupBy);
    $sale = "
        SELECT $selectCols, $AGG_POS
        $FROM_JOINS
        WHERE ty.STATUS = 4 AND $whereCommon
        GROUP BY $groupBySql";
    $ret = "
        SELECT $selectCols, $AGG_NEG
        $FROM_JOINS
        WHERE ty.STATUS = 5 AND $whereCommon
        GROUP BY $groupBySql";
    $union = $onlySaleReturn ? $ret : "($sale) UNION ALL ($ret)";
    $gSelect = implode(', ', $outAliases);
    $metrics = ['Qty','Amount','DiscAmt','TaxableAmt','GrossAmt','CGSTAmt','SGSTAmt','IGSTAmt','CentralAmt','LocalAmt','ExemptedAmt','CentralQty','LocalQty','ExemptedQty'];
    $sums = implode(', ', array_map(function ($m) { return "SUM($m) AS $m"; }, $metrics));
    return "
        SELECT $gSelect, $sums
        FROM ($union) AS u
        GROUP BY $gSelect";
}
}

$metricKeys = ['Qty','Amount','DiscAmt','TaxableAmt','GrossAmt','CGSTAmt','SGSTAmt','IGSTAmt','CentralAmt','LocalAmt','ExemptedAmt','CentralQty','LocalQty','ExemptedQty'];
$rows = []; $totalRows = 0; $queryErr = null;
$totals = array_fill_keys($metricKeys, 0.0);
$subtotals = [];
$subtotalLevelsToCompute = [];

$mainSql = '';
if ($hasFilters) {
    $mainSql = buildMainSql($onlySaleReturn, $grpSelectSql, $groupBySql, $orderBySql, $AGG_POS, $AGG_NEG, $whereCommon, $FROM_JOINS);
    $sumCols = implode(', ', array_map(function ($k) { return "SUM($k) AS $k"; }, $metricKeys));
    $tRes = @$db->query("SELECT COUNT(*) AS cnt, $sumCols FROM ($mainSql) AS T");
    if ($tRes) {
        $tRow = $tRes->fetch_assoc();
        $totalRows = (int)($tRow['cnt'] ?? 0);
        foreach ($totals as $k => $_) $totals[$k] = (float)($tRow[$k] ?? 0);
    } else {
        $queryErr = $db->error;
    }

    $wantAll = in_array($exportMode, ['csv','excel','xlsx','json','pdfdata'], true);
    if (!$queryErr && $totalRows > 0) {
        if ($wantAll) {
            $pRes = @$db->query("SELECT * FROM ($mainSql) AS PD");
        } else {
            $offset = ($page - 1) * $rowsPerPage;
            $pRes = @$db->query("SELECT * FROM ($mainSql) AS PD LIMIT $rowsPerPage OFFSET $offset");
        }
        if ($pRes) { while ($r = $pRes->fetch_assoc()) $rows[] = $r; }
        else { $queryErr = $db->error; }
    }

    if (!$queryErr && $totalRows > 0 && $numGroupLevels > 1) {
        for ($i = 0; $i < $numGroupLevels - 1; $i++) {
            $origLevel = $activeGroups[$i]['level'];
            if (!empty($subTotalReq[$origLevel])) $subtotalLevelsToCompute[] = $i + 1;
        }
    }
    foreach ($subtotalLevelsToCompute as $n) {
        $sql = buildSubtotalSql($onlySaleReturn, $activeGroups, $n, $AGG_POS, $AGG_NEG, $whereCommon, $FROM_JOINS);
        $res = @$db->query($sql);
        if ($res) {
            $map = [];
            while ($r = $res->fetch_assoc()) {
                $keyParts = [];
                for ($i = 0; $i < $n; $i++) {
                    $slot = (int)$activeGroups[$i]['level'] - 1;
                    $keyParts[] = (string)($r["g$slot"] ?? '');
                }
                $map[implode("\x1F", $keyParts)] = $r;
            }
            $subtotals[$n] = $map;
        }
    }
}

// Enrich contribution %
foreach ($rows as &$rr) {
    $rr['QtyContPct']   = pct($rr['Qty'] ?? 0, $totals['Qty']);
    $rr['GrossContPct'] = pct($rr['GrossAmt'] ?? $rr['TaxableAmt'] ?? 0, $totals['GrossAmt'] ?: $totals['TaxableAmt']);
}
unset($rr);

$totalPages = $totalRows > 0 ? (int)ceil($totalRows / $rowsPerPage) : 1;
$page = min($page, max(1, $totalPages));

// ── Export handlers (full dataset) ────────────────────────────────────────────
if (!function_exists('rowMetrics')) {
function rowMetrics($r) {
    $taxable = (float)($r['TaxableAmt'] ?? 0);
    $cgst = (float)($r['CGSTAmt'] ?? 0);
    $sgst = (float)($r['SGSTAmt'] ?? 0);
    $igst = (float)($r['IGSTAmt'] ?? 0);
    return [
        'Qty' => (float)($r['Qty'] ?? 0),
        'Amount' => (float)($r['Amount'] ?? 0),
        'DiscAmt' => (float)($r['DiscAmt'] ?? 0),
        'TaxableAmt' => $taxable,
        'GrossAmt' => (float)($r['GrossAmt'] ?? $taxable),
        'CGSTAmt' => $cgst,
        'SGSTAmt' => $sgst,
        'IGSTAmt' => $igst,
        'TotalAmt' => $taxable + $cgst + $sgst + $igst,
        'CentralAmt' => (float)($r['CentralAmt'] ?? 0),
        'LocalAmt' => (float)($r['LocalAmt'] ?? 0),
        'ExemptedAmt' => (float)($r['ExemptedAmt'] ?? 0),
        'QtyContPct' => (float)($r['QtyContPct'] ?? 0),
        'GrossContPct' => (float)($r['GrossContPct'] ?? 0),
        'CentralQty' => (float)($r['CentralQty'] ?? 0),
        'LocalQty' => (float)($r['LocalQty'] ?? 0),
        'ExemptedQty' => (float)($r['ExemptedQty'] ?? 0),
    ];
}
}

if ($exportMode && $hasFilters && !$queryErr) {
    $groupHeaders = array_map(function ($g) { return $g['label']; }, $activeGroups);
    $metricHeaders = ['Qty','Amount','Disc Amt','Taxable Amt','Gross Amt','CGST','SGST','IGST','Total Amt','Central Amt','Local Amt','Exempted Amt','Qty Cont%','Gross Cont%','Central Qty','Local Qty','Exempted Qty'];

    if ($exportMode === 'json' || $exportMode === 'pdfdata') {
        header('Content-Type: application/json; charset=utf-8');
        $payload = [];
        foreach ($rows as $idx => $r) {
            $gvals = [];
            for ($i = 0; $i < $numGroupLevels; $i++) {
                $slot = (int)$activeGroups[$i]['level'] - 1;
                $gvals[] = (string)($r['g' . $slot] ?? '');
            }
            $payload[] = array_merge(['groups' => $gvals], rowMetrics($r));
        }
        // Include subtotals for PDF
        $subOut = [];
        foreach ($subtotals as $n => $map) {
            foreach ($map as $key => $sr) {
                $parts = explode("\x1F", $key);
                $subOut[] = [
                    'level' => $n,
                    'label' => ($activeGroups[$n-1]['label'] ?? '') . '-wise Total',
                    'groups' => $parts,
                    'metrics' => rowMetrics($sr + [
                        'QtyContPct' => pct($sr['Qty'] ?? 0, $totals['Qty']),
                        'GrossContPct' => pct($sr['GrossAmt'] ?? 0, $totals['GrossAmt'] ?: $totals['TaxableAmt']),
                    ]),
                ];
            }
        }
        echo json_encode([
            'from' => $from, 'to' => $to,
            'groups' => $groupHeaders,
            'rows' => $payload,
            'subtotals' => $subOut,
            'subtotalLevels' => $subtotalLevelsToCompute,
            'totals' => rowMetrics($totals + [
                'QtyContPct' => 100,
                'GrossContPct' => 100,
            ]),
            'totalRows' => $totalRows,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($exportMode === 'csv' || $exportMode === 'excel' || $exportMode === 'xlsx') {
        $filename = 'Category_Wise_Sale_' . $from . '_to_' . $to . ($exportMode === 'csv' ? '.csv' : '.xls');
        header('Content-Type: ' . ($exportMode === 'csv' ? 'text/csv' : 'application/vnd.ms-excel') . '; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo "\xEF\xBB\xBF";
        $out = fopen('php://output', 'w');
        fputcsv($out, array_merge(['#'], $groupHeaders, $metricHeaders));
        $rn = 1;
        foreach ($rows as $r) {
            $gvals = [];
            for ($i = 0; $i < $numGroupLevels; $i++) {
                $slot = (int)$activeGroups[$i]['level'] - 1;
                $gvals[] = (string)($r['g' . $slot] ?? '');
            }
            $m = rowMetrics($r);
            fputcsv($out, array_merge([$rn++], $gvals, [
                $m['Qty'], $m['Amount'], $m['DiscAmt'], $m['TaxableAmt'], $m['GrossAmt'],
                $m['CGSTAmt'], $m['SGSTAmt'], $m['IGSTAmt'], $m['TotalAmt'],
                $m['CentralAmt'], $m['LocalAmt'], $m['ExemptedAmt'],
                $m['QtyContPct'], $m['GrossContPct'],
                $m['CentralQty'], $m['LocalQty'], $m['ExemptedQty'],
            ]));
        }
        $tm = rowMetrics($totals + ['QtyContPct'=>100,'GrossContPct'=>100]);
        fputcsv($out, array_merge([''], array_fill(0, max(0,$numGroupLevels-1), ''), ['GRAND TOTAL'], [
            $tm['Qty'], $tm['Amount'], $tm['DiscAmt'], $tm['TaxableAmt'], $tm['GrossAmt'],
            $tm['CGSTAmt'], $tm['SGSTAmt'], $tm['IGSTAmt'], $tm['TotalAmt'],
            $tm['CentralAmt'], $tm['LocalAmt'], $tm['ExemptedAmt'],
            100, 100, $tm['CentralQty'], $tm['LocalQty'], $tm['ExemptedQty'],
        ]));
        fclose($out);
        exit;
    }
}

// Helper to render multiselect
if (!function_exists('renderMs')) {
function renderMs($id, $name, $dd, $valKey, $labelKey, $selected, $placeholder, $extraAttr = '') {
    $count = count($selected);
    echo '<div class="ms-dropdown" id="' . h($id) . '">';
    echo '<div class="ms-trigger" onclick="toggleMs(\'' . h($id) . '\')">';
    echo '<span class="ms-text">' . ($count ? h($count . ' selected') : h($placeholder)) . '</span>';
    echo '<span class="ms-count">' . ($count ?: '') . '</span></div>';
    echo '<div class="ms-menu" id="' . h($id) . '-menu">';
    echo '<div class="ms-search"><input type="text" placeholder="Search…" oninput="msFilter(\'' . h($id) . '\', this.value)" onclick="event.stopPropagation()"></div>';
    foreach ($dd as $r) {
        $val = (string)($r[$valKey] ?? '');
        $lab = (string)($r[$labelKey] ?? $val);
        if ($val === '' && $lab === '') continue;
        $use = $val !== '' ? $val : $lab;
        $cid = $id . '_' . md5($use);
        $data = '';
        if ($extraAttr && isset($r[$extraAttr])) $data = ' data-cat="' . h((string)$r[$extraAttr]) . '"';
        echo '<div class="ms-item"' . $data . '><input type="checkbox" name="' . h($name) . '[]" value="' . h($use) . '" id="' . h($cid) . '" ' . multiSelected($selected, $use) . '>';
        echo '<label for="' . h($cid) . '">' . h($lab) . '</label></div>';
    }
    echo '<div class="ms-actions"><button type="button" class="btn btn-outline-secondary btn-xs" onclick="msSelectAll(\'' . h($id) . '\',true)">All</button>';
    echo '<button type="button" class="btn btn-outline-secondary btn-xs" onclick="msSelectAll(\'' . h($id) . '\',false)">None</button>';
    echo '<button type="button" class="btn btn-primary btn-xs" onclick="toggleMs(\'' . h($id) . '\')">Done</button></div>';
    echo '</div></div>';
}
}

include '../includes/header.php';
?>
<!-- CSR_BUILD_20260805_D46DAF4 -->


<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<style>
:root { --tab-active:#1a5cff; --tab-border:#d1d5db; }
.filter-tabs { border-bottom:2px solid var(--tab-border); display:flex; flex-wrap:wrap; gap:2px; }
.filter-tabs .nav-link { font-size:11px; font-weight:600; padding:6px 10px; border-radius:6px 6px 0 0; border:1px solid transparent; color:#6b7280; background:#f3f4f6; margin-bottom:-2px; }
.filter-tabs .nav-link:hover { background:#e5e7eb; color:#111; }
.filter-tabs .nav-link.active { background:#fff; border-color:var(--tab-border) var(--tab-border) #fff; color:var(--tab-active); }
.filter-tabs .nav-link.has-filter { color:#d97706 !important; }
.filter-tabs .nav-link.has-filter::after { content:' ●'; font-size:8px; }
.tab-content>.tab-pane { padding:14px; border:1px solid var(--tab-border); border-top:none; background:#fff; border-radius:0 0 6px 6px; }
.tab-radio-row { display:flex; align-items:center; gap:18px; margin-bottom:10px; flex-wrap:wrap; }
.tab-radio-row label { font-size:13px; cursor:pointer; display:flex; align-items:center; gap:5px; margin:0; font-weight:600; }
.select-panel { display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end; }
.select-panel label { font-size:11px; font-weight:600; color:#374151; margin-bottom:3px; display:block; }
.ms-dropdown { position:relative; min-width:220px; max-width:340px; }
.ms-trigger { width:100%; padding:6px 10px; border:1px solid #ced4da; border-radius:4px; background:#fff; font-size:12px; cursor:pointer; text-align:left; display:flex; justify-content:space-between; align-items:center; }
.ms-trigger .ms-count { color:#0d6efd; font-weight:600; }
.ms-menu { position:absolute; top:100%; left:0; right:0; z-index:1000; background:#fff; border:1px solid #ced4da; border-radius:0 0 4px 4px; max-height:280px; overflow-y:auto; display:none; box-shadow:0 4px 12px rgba(0,0,0,.15); }
.ms-menu.show { display:block; }
.ms-search { padding:6px 8px; border-bottom:1px solid #e5e7eb; position:sticky; top:0; background:#fff; z-index:1; }
.ms-search input { width:100%; font-size:12px; padding:4px 8px; border:1px solid #ced4da; border-radius:4px; }
.ms-item { padding:5px 10px; font-size:12px; cursor:pointer; display:flex; align-items:center; gap:6px; border-bottom:1px solid #f0f0f0; }
.ms-item:hover { background:#e7f1ff; }
.ms-item label { margin:0; cursor:pointer; font-size:12px; font-weight:400; flex:1; }
.ms-actions { padding:6px 10px; border-top:2px solid #e9ecef; background:#f8f9fa; display:flex; gap:6px; position:sticky; bottom:0; }
.ms-actions button { font-size:11px; padding:2px 8px; }
.grp-chk-col { display:none; }
.grp-chk-col.show { display:flex; align-items:center; }
.grp-chk-col label { font-size:11.5px; font-weight:600; color:#0d6efd; display:flex; align-items:center; gap:4px; cursor:pointer; margin:0; }
.sum-card { border-radius:8px; padding:10px 14px; border:1px solid #e0e2e7; background:#fff; }
.sum-card .sc-label { font-size:10px; color:#6b7280; text-transform:uppercase; letter-spacing:.05em; margin-bottom:3px; }
.sum-card .sc-val { font-size:16px; font-weight:700; color:#1a1d27; }
.sum-card.acc { border-left:3px solid #0d6efd; }
.sum-card.grn { border-left:3px solid #198754; }
.sum-card.red { border-left:3px solid #dc3545; }
table.sr thead th { font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:.03em; white-space:nowrap; }
table.sr td { font-size:11.5px; white-space:nowrap; padding:6px 8px; }
table.sr td.num { text-align:right; font-variant-numeric:tabular-nums; font-family:'Courier New',monospace; font-size:11px; }
table.sr td.grp { font-weight:600; }
table.sr tfoot td { font-weight:700; font-size:11.5px; background:#f8f9fa; }
tr.subtotal-row td { background:#eef3ff; font-weight:700; font-size:11px; border-top:1px solid #c8d6ff; }
tr.subtotal-row td.lvl-1 { background:#dbe6ff; }
tr.subtotal-row td.lvl-2 { background:#e6edff; }
tr.subtotal-row td.lvl-3 { background:#f0f4ff; }
#grandTotalRow td { background:#212529 !important; color:#fff !important; font-weight:700; }
.zoom-bar { display:flex; align-items:center; gap:8px; }
.sub-cb-list { max-height:220px; overflow-y:auto; border:1px solid #e5e7eb; border-radius:6px; padding:8px 10px; background:#fafbfc; min-width:240px; flex:1; }
.sub-cb-list label { display:flex; align-items:center; gap:6px; font-size:12px; font-weight:500; margin:0 0 4px; cursor:pointer; }
.sub-cb-list input[type=checkbox] { width:14px; height:14px; accent-color:#0d6efd; }
.sub-cb-empty { font-size:12px; color:#9ca3af; padding:6px 0; }
@media print {
  .no-print, .filter-tabs, #filterForm, .sidebar, nav, .btn, .pagination, #tableSearch { display:none !important; }
  .col-md-9, .col-lg-10 { width:100% !important; max-width:100% !important; flex:0 0 100%; }
  table.sr { font-size:9px; }
}
</style>

<div class="container-fluid">
<div class="row">
<?php include '../includes/sidebar.php'; ?>
<div class="col-md-9 col-lg-10 p-4">
<div class="main-content p-3">

<h4 class="mb-3">Category-wise Sale Register
  <small class="text-muted fs-6 fw-normal ms-2">
    <?php if ($hasFilters): ?>
      Period: <?= h($from) ?> – <?= h($to) ?> &nbsp;|&nbsp; Group: <?= h($primaryGroup) ?> &nbsp;|&nbsp; <?= number_format($totalRows) ?> groups
    <?php endif; ?>
  </small>
</h4>

<form method="POST" action="" id="filterForm" class="card shadow-sm border-0 mb-4 no-print">
<div class="card-body bg-white">
<input type="hidden" name="csrf_token" value="<?= h($csrf_token) ?>">

<div class="mb-3">
  <div class="fw-semibold small mb-2">Filter By:</div>
  <ul class="nav filter-tabs" id="filterTabsList">
<?php
$tabsMeta = [
  'tab-date' => ['label'=>'Date Range','mode'=>'select','val'=>$from.' to '.$to],
  'tab-type' => ['label'=>'Type','mode'=>$tabType,'val'=>implode(', ',$selType)],
  'tab-party' => ['label'=>'Party','mode'=>$tabParty,'val'=>($selPartyAcg!==''?$selPartyAcg.(!empty($selPartySubs)?' ('.count($selPartySubs).')':''):'')],
  'tab-supplier' => ['label'=>'Supplier','mode'=>$tabSupplier,'val'=>($selSuppAcg!==''?$selSuppAcg.(!empty($selSuppSubs)?' ('.count($selSuppSubs).')':''):'')],
  'tab-customer' => ['label'=>'Customer','mode'=>$tabCustomer,'val'=>implode(', ',$selCustomer)],
  'tab-transport' => ['label'=>'Transport','mode'=>$tabTransport,'val'=>implode(', ',$selTransport)],
  'tab-refno' => ['label'=>'Ref. No','mode'=>$tabRefNo,'val'=>implode(', ',$selRefNo)],
  'tab-salegst' => ['label'=>'Sale Gst %','mode'=>$tabSaleGst,'val'=>implode(', ',$selSaleGst)],
  'tab-city' => ['label'=>'City','mode'=>$tabCity,'val'=>implode(', ',$selCity)],
  'tab-state' => ['label'=>'State','mode'=>$tabState,'val'=>implode(', ',$selState)],
  'tab-rep' => ['label'=>'Representative','mode'=>$tabRep,'val'=>implode(', ',$selRep)],
  'tab-bank' => ['label'=>'Bank','mode'=>$tabBank,'val'=>implode(', ', array_filter(array_merge($selBankAc,$selBankSub)))],
  'tab-wallet' => ['label'=>'Wallet','mode'=>$tabWallet,'val'=>implode(', ', array_filter(array_merge($selWalletAc,$selWalletSub)))],
  'tab-unit' => ['label'=>'Unit','mode'=>$tabUnit,'val'=>implode(', ',$selUnit)],
  'tab-computer' => ['label'=>'Computer','mode'=>$tabComputer,'val'=>implode(', ',$selComputer)],
  'tab-user' => ['label'=>'User','mode'=>$tabUser,'val'=>implode(', ',$selUser)],
  'tab-purgst' => ['label'=>'Pur Gst %','mode'=>$tabPurGst,'val'=>implode(', ',$selPurGst)],
  'tab-category' => ['label'=>'Category','mode'=>$tabCategory,'val'=>implode(', ',$selCategory)],
  'tab-company' => ['label'=>'Company','mode'=>$tabCompany,'val'=>implode(', ',$selCompany)],
  'tab-size' => ['label'=>'Size','mode'=>$tabSize,'val'=>implode(', ',$selSize)],
  'tab-color' => ['label'=>'Color','mode'=>$tabColor,'val'=>implode(', ',$selColor)],
  'tab-desc' => ['label'=>'Description','mode'=>$tabDesc,'val'=>implode(', ',$selDesc)],
  'tab-spinst1' => ['label'=>'Sp Inst-1','mode'=>$tabSpInst1,'val'=>implode(', ',$selSpInst1)],
  'tab-spinst2' => ['label'=>'Sp Inst-2','mode'=>$tabSpInst2,'val'=>implode(', ',$selSpInst2)],
  'tab-spinst3' => ['label'=>'Sp Inst-3','mode'=>$tabSpInst3,'val'=>implode(', ',$selSpInst3)],
  'tab-product' => ['label'=>'Product','mode'=>$tabProduct,'val'=>implode(', ',$selProduct)],
  'tab-compno' => ['label'=>'Company No','mode'=>$tabCompNo,'val'=>implode(', ',$selCompNo)],
  'tab-prodgroup' => ['label'=>'Product Group','mode'=>$tabProdGroup,'val'=>implode(', ',$selProdGroup)],
];
$firstTab = true;
foreach ($tabsMeta as $tid => $tdata):
  $hasFilter = ($tdata['mode'] === 'select' && $tdata['val'] !== '');
  $cls = ($firstTab ? 'active ' : '') . ($hasFilter ? 'has-filter' : '');
  $firstTab = false;
?>
    <li class="nav-item">
      <button class="nav-link <?= $cls ?>" type="button" data-bs-toggle="tab" data-bs-target="#<?= $tid ?>"><?= h($tdata['label']) ?></button>
    </li>
<?php endforeach; ?>
  </ul>

  <div class="tab-content" id="filterTabsContent">

    <div class="tab-pane fade show active" id="tab-date">
      <div class="row g-3 mb-3">
        <div class="col-md-3">
          <label class="form-label small fw-semibold">From Date</label>
          <input type="date" name="from_date" value="<?= h($from) ?>" class="form-control form-control-sm">
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-semibold">To Date</label>
          <input type="date" name="to_date" value="<?= h($to) ?>" class="form-control form-control-sm">
        </div>
      </div>
      <div class="row g-3">
        <div class="col-lg-6">
          <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-light fw-semibold">Grouping Options (Ist–VIth)</div>
            <div class="card-body">
              <?php
              $grpLabels = ['Ist','IInd','IIIrd','IVth','Vth','VIth'];
              for ($i = 1; $i <= 6; $i++):
                $nextFilled = ($i < 6) && !empty($grp[$i + 1]);
                $chkLabel = $grp[$i] !== '' ? ($groupingOptions[$grp[$i]] ?? $grp[$i]) : '';
              ?>
              <div class="row align-items-center mb-2" data-grp-row="<?= $i ?>">
                <div class="col-3"><label class="small fw-semibold mb-0"><?= $grpLabels[$i-1] ?> Group</label></div>
                <div class="col-4">
                  <select name="group_<?= $i ?>" id="group_sel_<?= $i ?>" class="form-select form-select-sm" onchange="onGroupChange(<?= $i ?>)">
                    <?php foreach ($groupingOptions as $ov => $ol): ?>
                      <option value="<?= h($ov) ?>" <?= $grp[$i] === $ov ? 'selected' : '' ?>><?= h($ol) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-5 grp-chk-col<?= $nextFilled ? ' show' : '' ?>" id="grp_chk_col_<?= $i ?>">
                  <label>
                    <input type="checkbox" name="subtot_<?= $i ?>" id="subtot_<?= $i ?>" value="1" <?= !empty($subTotalReq[$i]) ? 'checked' : '' ?>>
                    <span id="subtot_lbl_<?= $i ?>"><?= h($chkLabel) ?>-wise Total Required ?</span>
                  </label>
                </div>
              </div>
              <?php endfor; ?>
            </div>
          </div>
        </div>
        <div class="col-lg-3">
          <div class="card shadow-sm border-0 h-100"><div class="card-body d-flex flex-column gap-3">
            <div>
              <div class="fw-semibold small mb-2">GSTIN Filter</div>
              <div class="d-flex flex-wrap gap-3">
                <?php foreach (['all'=>'All','with'=>'With GSTIN','without'=>'Without GSTIN'] as $v=>$l): ?>
                <label class="small"><input type="radio" name="gstn_filter" value="<?= $v ?>" <?= $gstnFilter===$v?'checked':'' ?>> <?= $l ?></label>
                <?php endforeach; ?>
              </div>
            </div>
            <div>
              <div class="fw-semibold small mb-2">Tax Type</div>
              <div class="d-flex flex-wrap gap-3">
                <?php foreach (['all'=>'All','local'=>'Local','central'=>'Central','exempted'=>'Exempted'] as $v=>$l): ?>
                <label class="small"><input type="radio" name="type_filter" value="<?= $v ?>" <?= $typeFilter===$v?'checked':'' ?>> <?= $l ?></label>
                <?php endforeach; ?>
              </div>
            </div>
            <div>
              <div class="fw-semibold small mb-2">Sale Rate</div>
              <div class="row g-2">
                <div class="col-6"><input type="number" name="sale_rate_from" value="<?= h($saleRateFrom) ?>" placeholder="From" class="form-control form-control-sm" step="0.01"></div>
                <div class="col-6"><input type="number" name="sale_rate_to" value="<?= h($saleRateTo) ?>" placeholder="To" class="form-control form-control-sm" step="0.01"></div>
              </div>
            </div>
          </div></div>
        </div>
        <div class="col-lg-3">
          <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-light fw-semibold">Additional Options</div>
            <div class="card-body d-flex flex-column gap-3">
              <div class="form-check"><input class="form-check-input" type="checkbox" name="group_full_name" id="chkFullName" value="1" <?= $groupFullName?'checked':'' ?>><label class="form-check-label small" for="chkFullName">Groups Full Name Required</label></div>
              <div class="form-check"><input class="form-check-input" type="checkbox" name="only_sale_return" id="chkSaleReturn" value="1" <?= $onlySaleReturn?'checked':'' ?>><label class="form-check-label small" for="chkSaleReturn">Only Sale Return</label></div>
              <div class="form-check"><input class="form-check-input" type="checkbox" name="only_discount" id="chkDiscount" value="1" <?= $onlyDiscount?'checked':'' ?>><label class="form-check-label small" for="chkDiscount">Only Discount Bill</label></div>
              <div class="form-check"><input class="form-check-input" type="checkbox" name="whatsapp_owner" id="chkWhatsapp" value="1" <?= $whatsappOwner?'checked':'' ?>><label class="form-check-label small" for="chkWhatsapp">WhatsApp To Owner</label></div>
            </div>
          </div>
        </div>
      </div>
    </div>


<?php
// Generic All/Select multiselect tab renderer
if (!function_exists('renderFilterTab')) {
function renderFilterTab($id, $modeName, $mode, $msId, $selName, $dd, $valKey, $labelKey, $selected, $allLabel, $selectLabel, $placeholder, $extraHtml = '') {
    $isSelect = ($mode === 'select');
?>
    <div class="tab-pane fade" id="<?= h($id) ?>">
      <div class="tab-radio-row">
        <label><input type="radio" name="<?= h($modeName) ?>" value="all" <?= !$isSelect?'checked':'' ?> onchange="togglePanel('<?= h($id) ?>-panel', false)"> All</label>
        <label><input type="radio" name="<?= h($modeName) ?>" value="select" <?= $isSelect?'checked':'' ?> onchange="togglePanel('<?= h($id) ?>-panel', true)"> <?= h($selectLabel) ?></label>
      </div>
      <div id="<?= h($id) ?>-panel" style="<?= $isSelect?'':'display:none' ?>">
        <div class="select-panel">
          <?= $extraHtml ?>
          <?php renderMs($msId, $selName, $dd, $valKey, $labelKey, $selected, $placeholder); ?>
        </div>
      </div>
    </div>
<?php
}
}
?>

<?php
// ── Type (Voucher) — All / Select with DESCRIPTION checkboxes ───────────────
?>
    <div class="tab-pane fade" id="tab-type">
      <div class="tab-radio-row">
        <label><input type="radio" name="tab_type_mode" value="all" <?= $tabType!=='select'?'checked':'' ?> onchange="togglePanel('tab-type-panel', false)"> All</label>
        <label><input type="radio" name="tab_type_mode" value="select" <?= $tabType==='select'?'checked':'' ?> onchange="togglePanel('tab-type-panel', true)"> Select Type</label>
      </div>
      <div id="tab-type-panel" style="<?= $tabType==='select'?'':'display:none' ?>">
        <div class="sub-cb-list" id="typeList">
          <?php if (empty($ddType)): ?>
            <div class="sub-cb-empty">No sale / sale-return types found.</div>
          <?php else: foreach ($ddType as $t):
            $tv = (string)($t['V_TYPE'] ?? '');
            $tl = (string)($t['DESCRIPTION'] ?? $tv);
            if ($tv === '') continue;
          ?>
            <label>
              <input type="checkbox" name="sel_type[]" value="<?= h($tv) ?>" <?= multiSelected($selType, $tv) ?>>
              <?= h($tl) ?>
            </label>
          <?php endforeach; endif; ?>
        </div>
      </div>
    </div>

<?php
// ── Party — Acgroup dropdown, then Subgroup name checkboxes ─────────────────
?>
    <div class="tab-pane fade" id="tab-party">
      <div class="tab-radio-row">
        <label><input type="radio" name="tab_party_mode" value="all" <?= $tabParty!=='select'?'checked':'' ?> onchange="togglePanel('tab-party-panel', false)"> All</label>
        <label><input type="radio" name="tab_party_mode" value="select" <?= $tabParty==='select'?'checked':'' ?> onchange="togglePanel('tab-party-panel', true)"> Select Party</label>
      </div>
      <div id="tab-party-panel" style="<?= $tabParty==='select'?'':'display:none' ?>">
        <div class="select-panel" style="align-items:flex-start;">
          <div style="min-width:220px;">
            <label>Select Acgroup / Party</label>
            <select name="sel_party_acgroup" id="partySelect" class="form-select form-select-sm mb-1"
                    onchange="loadSubgroups('partySelect','partySubList','sel_party_sub[]')">
              <option value="">— Select Party —</option>
              <?php foreach ($ddAcgroup as $r):
                $code = (string)($r['CODE'] ?? $r['Code'] ?? '');
                $name = (string)($r['NAME'] ?? $r['Name'] ?? $code);
              ?>
                <option value="<?= h($code) ?>" <?= $selPartyAcg===$code?'selected':'' ?>><?= h($name) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="sub-cb-list" id="partySubList">
            <?php if ($selPartyAcg === ''): ?>
              <div class="sub-cb-empty">Pehle upar se Party (Acgroup) select karein — names yahan checkbox me aayenge.</div>
            <?php elseif (empty($ddPartySubs)): ?>
              <div class="sub-cb-empty">Is Acgroup me koi Subgroup nahi mila.</div>
            <?php else: foreach ($ddPartySubs as $sb):
              $sc = (string)($sb['SUBCODE'] ?? '');
              $sn = (string)($sb['SUB_NAME'] ?? $sc);
              if ($sc === '') continue;
            ?>
              <label>
                <input type="checkbox" name="sel_party_sub[]" value="<?= h($sc) ?>" <?= multiSelected($selPartySubs, $sc) ?>>
                <?= h($sn) ?>
              </label>
            <?php endforeach; endif; ?>
          </div>
        </div>
      </div>
    </div>

<?php
// ── Supplier — Product.Code (Acgroup_1) + Product.SubCode (Subgroup_1) ───────
?>
    <div class="tab-pane fade" id="tab-supplier">
      <div class="tab-radio-row">
        <label><input type="radio" name="tab_supplier_mode" value="all" <?= $tabSupplier!=='select'?'checked':'' ?> onchange="togglePanel('tab-supplier-panel', false)"> All</label>
        <label><input type="radio" name="tab_supplier_mode" value="select" <?= $tabSupplier==='select'?'checked':'' ?> onchange="togglePanel('tab-supplier-panel', true)"> Select Supplier</label>
      </div>
      <div id="tab-supplier-panel" style="<?= $tabSupplier==='select'?'':'display:none' ?>">
        <div class="select-panel" style="align-items:flex-start;">
          <div style="min-width:220px;">
            <label>Select Acgroup (Supplier)</label>
            <select name="sel_supplier_acgroup" id="suppSelect" class="form-select form-select-sm mb-1"
                    onchange="loadSubgroups('suppSelect','suppSubList','sel_supplier_sub[]')">
              <option value="">— Select Acgroup —</option>
              <?php foreach ($ddAcgroup as $r):
                $code = (string)($r['CODE'] ?? $r['Code'] ?? '');
                $name = (string)($r['NAME'] ?? $r['Name'] ?? $code);
              ?>
                <option value="<?= h($code) ?>" <?= $selSuppAcg===$code?'selected':'' ?>><?= h($name) ?></option>
              <?php endforeach; ?>
            </select>
            <div class="small text-muted mt-1">.NET: Product.Code → Acgroup_1, Product.SubCode → Subgroup_1</div>
          </div>
          <div class="sub-cb-list" id="suppSubList">
            <?php if ($selSuppAcg === ''): ?>
              <div class="sub-cb-empty">Pehle Acgroup select karein — supplier names checkbox me aayenge.</div>
            <?php elseif (empty($ddSuppSubs)): ?>
              <div class="sub-cb-empty">Is Acgroup me koi Subgroup nahi mila.</div>
            <?php else: foreach ($ddSuppSubs as $sb):
              $sc = (string)($sb['SUBCODE'] ?? '');
              $sn = (string)($sb['SUB_NAME'] ?? $sc);
              if ($sc === '') continue;
            ?>
              <label>
                <input type="checkbox" name="sel_supplier_sub[]" value="<?= h($sc) ?>" <?= multiSelected($selSuppSubs, $sc) ?>>
                <?= h($sn) ?>
              </label>
            <?php endforeach; endif; ?>
          </div>
        </div>
      </div>
    </div>

<?php
renderFilterTab('tab-customer','tab_customer_mode',$tabCustomer,'ms-cust','sel_customer',$ddCustomer,'Sub_Name','Sub_Name',$selCustomer,'All Customers','Select Customer','— Select Customers —');
renderFilterTab('tab-transport','tab_transport_mode',$tabTransport,'ms-trans','sel_transport',$ddTransport,'name','name',$selTransport,'All Transport','Select Transport','— Select Transport —');
renderFilterTab('tab-refno','tab_refno_mode',$tabRefNo,'ms-refno','sel_refno',$ddRefNo,'refno','refno',$selRefNo,'All Ref No','Select Ref No','— Select Ref No —');
renderFilterTab('tab-salegst','tab_salegst_mode',$tabSaleGst,'ms-salegst','sel_salegst',$ddSaleGst,'gst','gst',$selSaleGst,'All Sale Gst %','Select Sale Gst %','— Select Sale Gst % —');
renderFilterTab('tab-city','tab_city_mode',$tabCity,'ms-city','sel_city',$ddCity,'City_Name','City_Name',$selCity,'All Cities','Select City','— Select Cities —');
renderFilterTab('tab-state','tab_state_mode',$tabState,'ms-state','sel_state',$ddState,'State_Name','State_Name',$selState,'All States','Select State','— Select States —');
renderFilterTab('tab-rep','tab_rep_mode',$tabRep,'ms-rep','sel_rep',$ddRep,'Rp_Name','Rp_Name',$selRep,'All Representatives','Select Representative','— Select Representative —');
?>

    <div class="tab-pane fade" id="tab-bank">
      <div class="tab-radio-row">
        <label><input type="radio" name="tab_bank_mode" value="all" <?= $tabBank!=='select'?'checked':'' ?> onchange="togglePanel('tab-bank-panel', false)"> All</label>
        <label><input type="radio" name="tab_bank_mode" value="select" <?= $tabBank==='select'?'checked':'' ?> onchange="togglePanel('tab-bank-panel', true)"> Select Bank AC</label>
      </div>
      <div id="tab-bank-panel" style="<?= $tabBank==='select'?'':'display:none' ?>">
        <div class="select-panel">
          <?php renderMs('ms-bank','sel_bank',$ddBank,'SubCode','Sub_Name',$selBankAc,'— Select Bank AC —'); ?>
          <?php renderMs('ms-banksub','sel_bank_sub',$ddBank,'Sub_Name','Sub_Name',$selBankSub,'— Select Bank Sub Account —'); ?>
        </div>
      </div>
    </div>

    <div class="tab-pane fade" id="tab-wallet">
      <div class="tab-radio-row">
        <label><input type="radio" name="tab_wallet_mode" value="all" <?= $tabWallet!=='select'?'checked':'' ?> onchange="togglePanel('tab-wallet-panel', false)"> All</label>
        <label><input type="radio" name="tab_wallet_mode" value="select" <?= $tabWallet==='select'?'checked':'' ?> onchange="togglePanel('tab-wallet-panel', true)"> Select Wallet AC</label>
      </div>
      <div id="tab-wallet-panel" style="<?= $tabWallet==='select'?'':'display:none' ?>">
        <div class="select-panel">
          <?php renderMs('ms-wallet','sel_wallet',$ddWallet,'SubCode','Sub_Name',$selWalletAc,'— Select Wallet AC —'); ?>
          <?php renderMs('ms-walletsub','sel_wallet_sub',$ddWallet,'Sub_Name','Sub_Name',$selWalletSub,'— Select Wallet Sub Account —'); ?>
        </div>
      </div>
    </div>

<?php
renderFilterTab('tab-unit','tab_unit_mode',$tabUnit,'ms-unit','sel_unit',$ddUnit,'Unit_Name','Unit_Name',$selUnit,'All Units','Select Unit','— Select Units —');
renderFilterTab('tab-computer','tab_computer_mode',$tabComputer,'ms-comptr','sel_computer',$ddComputer,'name','name',$selComputer,'All Computers','Select Computer','— Select Computers —');
renderFilterTab('tab-user','tab_user_mode',$tabUser,'ms-user','sel_user',$ddUser,'name','name',$selUser,'All Users','Select User','— Select Users —');
renderFilterTab('tab-purgst','tab_purgst_mode',$tabPurGst,'ms-purgst','sel_purgst',$ddPurGst,'gst','gst',$selPurGst,'All Pur Gst %','Select Pur Gst %','— Select Pur Gst % —');
renderFilterTab('tab-category','tab_category_mode',$tabCategory,'ms-cat','sel_category',$ddCategory,'c_name','c_name',$selCategory,'All Categories','Select Category','— Select Categories —');
renderFilterTab('tab-company','tab_company_mode',$tabCompany,'ms-cmp','sel_company',$ddCompany,'comp_name','comp_name',$selCompany,'All Companies','Select Company','— Select Companies —');
?>

    <div class="tab-pane fade" id="tab-size">
      <div class="tab-radio-row">
        <label><input type="radio" name="tab_size_mode" value="all" <?= $tabSize!=='select'?'checked':'' ?> onchange="togglePanel('tab-size-panel', false)"> All</label>
        <label><input type="radio" name="tab_size_mode" value="select" <?= $tabSize==='select'?'checked':'' ?> onchange="togglePanel('tab-size-panel', true)"> Select Size</label>
      </div>
      <div id="tab-size-panel" style="<?= $tabSize==='select'?'':'display:none' ?>">
        <div class="select-panel">
          <div>
            <label>By Category (optional)</label>
            <select name="sel_size_category" id="sz-cat-dd" class="form-select form-select-sm" onchange="filterByAcgroup('ms-sz', this.value)">
              <option value="">— All Categories —</option>
              <?php foreach ($ddSizeCats as $r): ?>
                <option value="<?= h($r['C_code']) ?>" <?= $selSizeCat===(string)$r['C_code']?'selected':'' ?>><?= h($r['c_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="ms-dropdown" id="ms-sz">
            <div class="ms-trigger" onclick="toggleMs('ms-sz')">
              <span class="ms-text"><?= !empty($selSize)?count($selSize).' selected':'— Select Sizes —' ?></span>
              <span class="ms-count"><?= !empty($selSize)?count($selSize):'' ?></span>
            </div>
            <div class="ms-menu" id="ms-sz-menu">
              <div class="ms-search"><input type="text" placeholder="Search…" oninput="msFilter('ms-sz', this.value)" onclick="event.stopPropagation()"></div>
              <?php foreach ($ddSizeAll as $r): ?>
              <div class="ms-item" data-cat="<?= h($r['cat_code']) ?>">
                <input type="checkbox" name="sel_size[]" value="<?= h($r['s_name']) ?>" id="sz_<?= h($r['s_code']) ?>" <?= multiSelected($selSize,$r['s_name']) ?>>
                <label for="sz_<?= h($r['s_code']) ?>"><?= h($r['s_name']) ?></label>
              </div>
              <?php endforeach; ?>
              <div class="ms-actions">
                <button type="button" class="btn btn-outline-secondary btn-xs" onclick="msSelectAll('ms-sz',true)">All</button>
                <button type="button" class="btn btn-outline-secondary btn-xs" onclick="msSelectAll('ms-sz',false)">None</button>
                <button type="button" class="btn btn-primary btn-xs" onclick="toggleMs('ms-sz')">Done</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

<?php
renderFilterTab('tab-color','tab_color_mode',$tabColor,'ms-col','sel_color',$ddColor,'Color_name','Color_name',$selColor,'All Colors','Select Color','— Select Colors —');
renderFilterTab('tab-desc','tab_desc_mode',$tabDesc,'ms-desc','sel_description',$ddDescription,'Des_Name','Des_Name',$selDesc,'All Descriptions','Select Description','— Select Descriptions —');
?>



<?php
// Sp Inst tabs with category cascade
if (!function_exists('renderSpInstTab')) {
function renderSpInstTab($id, $modeName, $mode, $msId, $selName, $ddAll, $ddCats, $selCatName, $selCat, $selected, $label) {
?>
    <div class="tab-pane fade" id="<?= h($id) ?>">
      <div class="tab-radio-row">
        <label><input type="radio" name="<?= h($modeName) ?>" value="all" <?= $mode!=='select'?'checked':'' ?> onchange="togglePanel('<?= h($id) ?>-panel', false)"> All</label>
        <label><input type="radio" name="<?= h($modeName) ?>" value="select" <?= $mode==='select'?'checked':'' ?> onchange="togglePanel('<?= h($id) ?>-panel', true)"> Select <?= h($label) ?></label>
      </div>
      <div id="<?= h($id) ?>-panel" style="<?= $mode==='select'?'':'display:none' ?>">
        <div class="select-panel">
          <div>
            <label>By Category (optional)</label>
            <select name="<?= h($selCatName) ?>" class="form-select form-select-sm" onchange="filterByAcgroup('<?= h($msId) ?>', this.value)">
              <option value="">— All Categories —</option>
              <?php foreach ($ddCats as $r): ?>
                <option value="<?= h($r['C_code']) ?>" <?= $selCat===(string)$r['C_code']?'selected':'' ?>><?= h($r['c_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="ms-dropdown" id="<?= h($msId) ?>">
            <div class="ms-trigger" onclick="toggleMs('<?= h($msId) ?>')">
              <span class="ms-text"><?= !empty($selected)?count($selected).' selected':'— Select '.$label.' —' ?></span>
              <span class="ms-count"><?= !empty($selected)?count($selected):'' ?></span>
            </div>
            <div class="ms-menu" id="<?= h($msId) ?>-menu">
              <div class="ms-search"><input type="text" placeholder="Search…" oninput="msFilter('<?= h($msId) ?>', this.value)" onclick="event.stopPropagation()"></div>
              <?php foreach ($ddAll as $r): ?>
              <div class="ms-item" data-cat="<?= h($r['cat_code']) ?>">
                <input type="checkbox" name="<?= h($selName) ?>[]" value="<?= h($r['s_name']) ?>" id="<?= h($msId.'_'.$r['s_code']) ?>" <?= multiSelected($selected,$r['s_name']) ?>>
                <label for="<?= h($msId.'_'.$r['s_code']) ?>"><?= h($r['s_name']) ?></label>
              </div>
              <?php endforeach; ?>
              <div class="ms-actions">
                <button type="button" class="btn btn-outline-secondary btn-xs" onclick="msSelectAll('<?= h($msId) ?>',true)">All</button>
                <button type="button" class="btn btn-outline-secondary btn-xs" onclick="msSelectAll('<?= h($msId) ?>',false)">None</button>
                <button type="button" class="btn btn-primary btn-xs" onclick="toggleMs('<?= h($msId) ?>')">Done</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
<?php
}
}
renderSpInstTab('tab-spinst1','tab_spinst1_mode',$tabSpInst1,'ms-sp1','sel_spinst1',$ddSpInst1All,$ddSpInst1Cats,'sel_spinst1_cat',$selSpInst1Cat,$selSpInst1,'SpInst1');
renderSpInstTab('tab-spinst2','tab_spinst2_mode',$tabSpInst2,'ms-sp2','sel_spinst2',$ddSpInst2All,$ddSpInst2Cats,'sel_spinst2_cat',$selSpInst2Cat,$selSpInst2,'SpInst2');
renderSpInstTab('tab-spinst3','tab_spinst3_mode',$tabSpInst3,'ms-sp3','sel_spinst3',$ddSpInst3All,$ddSpInst3Cats,'sel_spinst3_cat',$selSpInst3Cat,$selSpInst3,'SpInst3');

renderFilterTab('tab-product','tab_product_mode',$tabProduct,'ms-prod','sel_product',$ddProduct,'PRODUCT_CODE','display',$selProduct,'All Products','Select Product','— Select Products —');
renderFilterTab('tab-compno','tab_compno_mode',$tabCompNo,'ms-cn','sel_compno',$ddCompNo,'comp_no','comp_no',$selCompNo,'All Company No','Select Company No','— Select Company No —');
renderFilterTab('tab-prodgroup','tab_prodgroup_mode',$tabProdGroup,'ms-pg','sel_prodgroup',$ddProdGroup,'ProdGroup_Name','ProdGroup_Name',$selProdGroup,'All Product Groups','Select Product Group','— Select Product Groups —');
?>

  </div><!-- /tab-content -->
</div>

<div class="text-end mt-3">
  <button type="button" class="btn btn-outline-secondary btn-sm me-2" onclick="resetAllFilters()">↺ Reset</button>
  <button type="submit" class="btn btn-primary px-4">🔍 Search / Run Report</button>
</div>
</div>
</form>

<!-- RESULTS -->
<?php if ($hasFilters): ?>
<div class="row g-3 mb-3 no-print">
  <div class="col"><div class="sum-card acc"><div class="sc-label">Total Qty</div><div class="sc-val"><?= number_format($totals['Qty'],2) ?></div></div></div>
  <div class="col"><div class="sum-card acc"><div class="sc-label">Gross Amt</div><div class="sc-val">₹<?= number_format($totals['GrossAmt']/1000,0) ?>K</div></div></div>
  <div class="col"><div class="sum-card grn"><div class="sc-label">Taxable Amt</div><div class="sc-val">₹<?= number_format($totals['TaxableAmt']/1000,0) ?>K</div></div></div>
  <div class="col"><div class="sum-card grn"><div class="sc-label">Central Amt</div><div class="sc-val">₹<?= number_format($totals['CentralAmt']/1000,0) ?>K</div></div></div>
  <div class="col"><div class="sum-card"><div class="sc-label">Local Amt</div><div class="sc-val">₹<?= number_format($totals['LocalAmt']/1000,0) ?>K</div></div></div>
  <div class="col"><div class="sum-card red"><div class="sc-label">Disc Amt</div><div class="sc-val">₹<?= number_format($totals['DiscAmt']/1000,0) ?>K</div></div></div>
</div>

<div class="card shadow-sm">
<div class="card-body p-0">
<?php if ($queryErr): ?>
  <div class="alert alert-danger m-3"><strong>Query Error:</strong> <?= h($queryErr) ?></div>
<?php elseif ($totalRows === 0): ?>
  <p class="text-center py-5 text-muted mb-0">No records found for the selected filters.</p>
<?php else: ?>

<div class="d-flex justify-content-between align-items-center px-3 pt-3 pb-2 flex-wrap gap-2 no-print">
  <span class="small text-muted">
    Showing <strong><?= number_format(($page-1)*$rowsPerPage+1) ?>–<?= number_format(min($page*$rowsPerPage,$totalRows)) ?></strong>
    of <strong><?= number_format($totalRows) ?></strong> groups
  </span>
  <div class="d-flex align-items-center gap-2 flex-wrap">
    <div class="zoom-bar">
      <button type="button" class="btn btn-outline-secondary btn-sm" onclick="zoomReport(-0.1)">−</button>
      <span class="small" id="zoomLbl">100%</span>
      <button type="button" class="btn btn-outline-secondary btn-sm" onclick="zoomReport(0.1)">+</button>
    </div>
    <input type="text" id="tableSearch" placeholder="Quick search…" class="form-control form-control-sm" style="width:160px;" oninput="onSearch(this.value)">
    <button class="btn btn-success btn-sm" onclick="doExport('csv')">⬇ CSV</button>
    <button class="btn btn-success btn-sm" onclick="doExport('excel')">⬇ Excel</button>
    <button class="btn btn-danger btn-sm" id="pdfBtn" onclick="doPDF()">⬇ PDF</button>
    <button class="btn btn-dark btn-sm" onclick="window.print()">🖨 Print</button>
  </div>
</div>

<div id="reportZoomWrap" style="overflow-x:auto;">
<table class="table table-bordered table-sm sr mb-0" id="mainTbl">
  <thead class="table-dark small">
    <tr>
      <th>#</th>
      <?php foreach ($activeGroups as $g): ?><th><?= h($g['label']) ?></th><?php endforeach; ?>
      <th class="text-end">Qty</th>
      <th class="text-end">Amount</th>
      <th class="text-end">Disc Amt</th>
      <th class="text-end">Taxable Amt</th>
      <th class="text-end">Gross Amt</th>
      <th class="text-end">CGST</th>
      <th class="text-end">SGST</th>
      <th class="text-end">IGST</th>
      <th class="text-end">Total Amt</th>
      <th class="text-end">Central Amt</th>
      <th class="text-end">Local Amt</th>
      <th class="text-end">Exempted Amt</th>
      <th class="text-end">Qty Cont%</th>
      <th class="text-end">Gross Cont%</th>
      <th class="text-end">Central Qty</th>
      <th class="text-end">Local Qty</th>
      <th class="text-end">Exempt Qty</th>
    </tr>
  </thead>
  <tbody class="small" id="tBody">
<?php
$rn = ($page - 1) * $rowsPerPage + 1;
$prevKeys = array_fill(0, $numGroupLevels, null);
$colspanGroups = $numGroupLevels;

if (!function_exists('emitSubtotalRow')) {
function emitSubtotalRow($n, $key, $sr, $activeGroups, $totals) {
    $lvlLabel = $activeGroups[$n-1]['label'] ?? '';
    $parts = explode("\x1F", $key);
    $name = end($parts);
    $m = rowMetrics($sr + [
        'QtyContPct' => pct($sr['Qty'] ?? 0, $totals['Qty']),
        'GrossContPct' => pct($sr['GrossAmt'] ?? 0, $totals['GrossAmt'] ?: $totals['TaxableAmt']),
    ]);
    $pad = $n; // visual indent via colspan first cells
    ?>
    <tr class="subtotal-row">
      <td colspan="<?= $pad + 1 ?>" class="lvl-<?= (int)$n ?>"><?= h($lvlLabel) ?>-wise Total : <?= h($name) ?></td>
      <?php for ($k = $pad; $k < count($activeGroups); $k++): ?><td class="lvl-<?= (int)$n ?>"></td><?php endfor; ?>
      <td class="num lvl-<?= (int)$n ?>"><?= fmtNum($m['Qty']) ?></td>
      <td class="num lvl-<?= (int)$n ?>">₹<?= fmtNum($m['Amount']) ?></td>
      <td class="num lvl-<?= (int)$n ?>">₹<?= fmtNum($m['DiscAmt']) ?></td>
      <td class="num lvl-<?= (int)$n ?>">₹<?= fmtNum($m['TaxableAmt']) ?></td>
      <td class="num lvl-<?= (int)$n ?>">₹<?= fmtNum($m['GrossAmt']) ?></td>
      <td class="num lvl-<?= (int)$n ?>">₹<?= fmtNum($m['CGSTAmt']) ?></td>
      <td class="num lvl-<?= (int)$n ?>">₹<?= fmtNum($m['SGSTAmt']) ?></td>
      <td class="num lvl-<?= (int)$n ?>">₹<?= fmtNum($m['IGSTAmt']) ?></td>
      <td class="num lvl-<?= (int)$n ?>">₹<?= fmtNum($m['TotalAmt']) ?></td>
      <td class="num lvl-<?= (int)$n ?>">₹<?= fmtNum($m['CentralAmt']) ?></td>
      <td class="num lvl-<?= (int)$n ?>">₹<?= fmtNum($m['LocalAmt']) ?></td>
      <td class="num lvl-<?= (int)$n ?>">₹<?= fmtNum($m['ExemptedAmt']) ?></td>
      <td class="num lvl-<?= (int)$n ?>"><?= fmtNum($m['QtyContPct']) ?></td>
      <td class="num lvl-<?= (int)$n ?>"><?= fmtNum($m['GrossContPct']) ?></td>
      <td class="num lvl-<?= (int)$n ?>"><?= number_format($m['CentralQty']) ?></td>
      <td class="num lvl-<?= (int)$n ?>"><?= number_format($m['LocalQty']) ?></td>
      <td class="num lvl-<?= (int)$n ?>"><?= number_format($m['ExemptedQty']) ?></td>
    </tr>
    <?php
}
}

foreach ($rows as $idx => $row):
    $curKeys = [];
    for ($i = 0; $i < $numGroupLevels; $i++) {
        $slot = (int)$activeGroups[$i]['level'] - 1;
        $curKeys[$i] = (string)($row['g' . $slot] ?? '');
    }

    if ($idx > 0) {
        // Emit subtotals from deepest to shallowest when keys change
        $levels = $subtotalLevelsToCompute;
        rsort($levels);
        foreach ($levels as $n) {
            $changed = false;
            for ($i = 0; $i < $n; $i++) {
                if (($prevKeys[$i] ?? null) !== ($curKeys[$i] ?? null)) { $changed = true; break; }
            }
            if ($changed) {
                $keyParts = [];
                for ($i = 0; $i < $n; $i++) $keyParts[] = (string)($prevKeys[$i] ?? '');
                $key = implode("\x1F", $keyParts);
                if (isset($subtotals[$n][$key])) {
                    emitSubtotalRow($n, $key, $subtotals[$n][$key], $activeGroups, $totals);
                }
            }
        }
    }
    $prevKeys = $curKeys;
    $m = rowMetrics($row);
?>
    <tr>
      <td class="text-muted"><?= $rn++ ?></td>
      <?php for ($i = 0; $i < $numGroupLevels; $i++): ?>
        <td class="grp"><?= h($curKeys[$i]) ?></td>
      <?php endfor; ?>
      <td class="num"><?= fmtNum($m['Qty']) ?></td>
      <td class="num">₹<?= fmtNum($m['Amount']) ?></td>
      <td class="num text-danger">₹<?= fmtNum($m['DiscAmt']) ?></td>
      <td class="num">₹<?= fmtNum($m['TaxableAmt']) ?></td>
      <td class="num">₹<?= fmtNum($m['GrossAmt']) ?></td>
      <td class="num">₹<?= fmtNum($m['CGSTAmt']) ?></td>
      <td class="num">₹<?= fmtNum($m['SGSTAmt']) ?></td>
      <td class="num">₹<?= fmtNum($m['IGSTAmt']) ?></td>
      <td class="num fw-bold">₹<?= fmtNum($m['TotalAmt']) ?></td>
      <td class="num">₹<?= fmtNum($m['CentralAmt']) ?></td>
      <td class="num">₹<?= fmtNum($m['LocalAmt']) ?></td>
      <td class="num">₹<?= fmtNum($m['ExemptedAmt']) ?></td>
      <td class="num"><?= fmtNum($m['QtyContPct']) ?></td>
      <td class="num"><?= fmtNum($m['GrossContPct']) ?></td>
      <td class="num"><?= number_format($m['CentralQty']) ?></td>
      <td class="num"><?= number_format($m['LocalQty']) ?></td>
      <td class="num"><?= number_format($m['ExemptedQty']) ?></td>
    </tr>
<?php endforeach;

// trailing subtotals
if (!empty($rows)) {
    $levels = $subtotalLevelsToCompute;
    rsort($levels);
    foreach ($levels as $n) {
        $keyParts = [];
        for ($i = 0; $i < $n; $i++) $keyParts[] = (string)($prevKeys[$i] ?? '');
        $key = implode("\x1F", $keyParts);
        if (isset($subtotals[$n][$key])) {
            emitSubtotalRow($n, $key, $subtotals[$n][$key], $activeGroups, $totals);
        }
    }
}
$tm = rowMetrics($totals + ['QtyContPct'=>100,'GrossContPct'=>100]);
?>
  </tbody>
  <tfoot>
    <tr id="grandTotalRow">
      <td colspan="<?= 1 + $numGroupLevels ?>"><strong>Grand Total (<?= number_format($totalRows) ?> groups)</strong></td>
      <td class="num"><?= fmtNum($tm['Qty']) ?></td>
      <td class="num">₹<?= fmtNum($tm['Amount']) ?></td>
      <td class="num">₹<?= fmtNum($tm['DiscAmt']) ?></td>
      <td class="num">₹<?= fmtNum($tm['TaxableAmt']) ?></td>
      <td class="num">₹<?= fmtNum($tm['GrossAmt']) ?></td>
      <td class="num">₹<?= fmtNum($tm['CGSTAmt']) ?></td>
      <td class="num">₹<?= fmtNum($tm['SGSTAmt']) ?></td>
      <td class="num">₹<?= fmtNum($tm['IGSTAmt']) ?></td>
      <td class="num">₹<?= fmtNum($tm['TotalAmt']) ?></td>
      <td class="num">₹<?= fmtNum($tm['CentralAmt']) ?></td>
      <td class="num">₹<?= fmtNum($tm['LocalAmt']) ?></td>
      <td class="num">₹<?= fmtNum($tm['ExemptedAmt']) ?></td>
      <td class="num">100.00</td>
      <td class="num">100.00</td>
      <td class="num"><?= number_format($tm['CentralQty']) ?></td>
      <td class="num"><?= number_format($tm['LocalQty']) ?></td>
      <td class="num"><?= number_format($tm['ExemptedQty']) ?></td>
    </tr>
  </tfoot>
</table>
</div>

<?php if ($totalPages > 1): ?>
<div class="d-flex justify-content-between align-items-center px-3 py-2 flex-wrap gap-2 border-top no-print">
  <nav>
    <ul class="pagination pagination-sm mb-0">
      <li class="page-item <?= $page<=1?'disabled':'' ?>"><a class="page-link" href="<?= h(pageUrl(1)) ?>">«</a></li>
      <li class="page-item <?= $page<=1?'disabled':'' ?>"><a class="page-link" href="<?= h(pageUrl($page-1)) ?>">‹</a></li>
      <?php
      $s=max(1,$page-2); $e=min($totalPages,$s+4); if ($e-$s<4) $s=max(1,$e-4);
      for ($pg=$s;$pg<=$e;$pg++): ?>
        <li class="page-item <?= $pg===$page?'active':'' ?>"><a class="page-link" href="<?= h(pageUrl($pg)) ?>"><?= $pg ?></a></li>
      <?php endfor; ?>
      <li class="page-item <?= $page>=$totalPages?'disabled':'' ?>"><a class="page-link" href="<?= h(pageUrl($page+1)) ?>">›</a></li>
      <li class="page-item <?= $page>=$totalPages?'disabled':'' ?>"><a class="page-link" href="<?= h(pageUrl($totalPages)) ?>">»</a></li>
    </ul>
  </nav>
</div>
<?php endif; ?>

<?php endif; ?>
</div></div>
<?php endif; ?>

</div></div></div></div>


<script>
var TOTALS = <?= json_encode(array_merge(
  array_map(function($v){ return round((float)$v, 2); }, $totals),
  ['TotAmt'=>round($totals['TaxableAmt']+$totals['CGSTAmt']+$totals['SGSTAmt']+$totals['IGSTAmt'],2), 'totalRows'=>$totalRows]
), JSON_NUMERIC_CHECK) ?>;
var DATE_FROM = <?= json_encode($from) ?>;
var DATE_TO   = <?= json_encode($to) ?>;
var GROUP_BY  = <?= json_encode($primaryGroup) ?>;
var GROUP_LABELS = <?= json_encode(array_map(function ($g) { return $g['label']; }, $activeGroups), JSON_UNESCAPED_UNICODE) ?>;
var GROUPING_OPTIONS = <?= json_encode($groupingOptions, JSON_UNESCAPED_UNICODE) ?>;
var HAS_FILTERS = <?= $hasFilters ? 'true' : 'false' ?>;
var reportZoom = 1;


function loadSubgroups(selectId, listId, checkboxName) {
  var sel = document.getElementById(selectId);
  var list = document.getElementById(listId);
  if (!sel || !list) return;
  var code = sel.value || '';
  if (!code) {
    list.innerHTML = '<div class="sub-cb-empty">Pehle Acgroup select karein — names yahan checkbox me aayenge.</div>';
    return;
  }
  list.innerHTML = '<div class="sub-cb-empty">Loading…</div>';
  fetch('?ajax=subgroups&code=' + encodeURIComponent(code))
    .then(function(r){ return r.json(); })
    .then(function(rows){
      if (!rows || !rows.length) {
        list.innerHTML = '<div class="sub-cb-empty">Is Acgroup me koi Subgroup nahi mila.</div>';
        return;
      }
      var html = '';
      rows.forEach(function(row){
        var sc = row.SUBCODE || '';
        var sn = row.SUB_NAME || sc;
        if (!sc) return;
        var id = listId + '_' + sc;
        html += '<label><input type="checkbox" name="'+checkboxName+'" value="'+sc.replace(/"/g,'&quot;')+'" id="'+id+'"> '+
                (sn.replace(/</g,'&lt;'))+'</label>';
      });
      list.innerHTML = html || '<div class="sub-cb-empty">Is Acgroup me koi Subgroup nahi mila.</div>';
    })
    .catch(function(){
      list.innerHTML = '<div class="sub-cb-empty">Load failed.</div>';
    });
}

function togglePanel(id, show) {
  var el = document.getElementById(id);
  if (el) el.style.display = show ? '' : 'none';
}
function refreshGroupCheckboxes() {
  for (var i = 1; i <= 6; i++) {
    var chkCol = document.getElementById('grp_chk_col_' + i);
    if (!chkCol) continue;
    var nextVal = '';
    if (i < 6) {
      var nextEl = document.getElementById('group_sel_' + (i + 1));
      nextVal = nextEl ? nextEl.value : '';
    }
    var shouldShow = (i < 6) && nextVal !== '';
    if (shouldShow) {
      chkCol.classList.add('show');
      var curEl = document.getElementById('group_sel_' + i);
      var curVal = curEl ? curEl.value : '';
      var lbl = curVal !== '' ? (GROUPING_OPTIONS[curVal] || curVal) : '';
      var lblEl = document.getElementById('subtot_lbl_' + i);
      if (lblEl) lblEl.textContent = (lbl !== '' ? lbl : '—') + '-wise Total Required ?';
    } else {
      chkCol.classList.remove('show');
      var chkEl = document.getElementById('subtot_' + i);
      if (chkEl) chkEl.checked = false;
    }
  }
}
function onGroupChange(i) {
  var curEl = document.getElementById('group_sel_' + i);
  if (curEl && curEl.value === '') {
    for (var j = i + 1; j <= 6; j++) {
      var el = document.getElementById('group_sel_' + j);
      if (el) el.value = '';
      var ck = document.getElementById('subtot_' + j);
      if (ck) ck.checked = false;
    }
  }
  refreshGroupCheckboxes();
}
function toggleMs(id) {
  var menu = document.getElementById(id + '-menu');
  if (!menu) return;
  var isOpen = menu.classList.contains('show');
  document.querySelectorAll('.ms-menu.show').forEach(function(m){ if (m !== menu) m.classList.remove('show'); });
  menu.classList.toggle('show', !isOpen);
}
document.addEventListener('click', function(e) {
  if (!e.target.closest('.ms-dropdown')) {
    document.querySelectorAll('.ms-menu.show').forEach(function(m){ m.classList.remove('show'); });
  }
});
function msSelectAll(id, select) {
  var ms = document.getElementById(id);
  if (!ms) return;
  ms.querySelectorAll('.ms-item').forEach(function(item){
    if (item.style.display === 'none') return;
    var cb = item.querySelector('input[type=checkbox]');
    if (cb) cb.checked = select;
  });
  updateMsTrigger(id);
}
function msFilter(id, q) {
  q = (q || '').toLowerCase().trim();
  var ms = document.getElementById(id);
  if (!ms) return;
  ms.querySelectorAll('.ms-item').forEach(function(item){
    // respect acgroup/category hide
    if (item.dataset._acgHidden === '1') { item.style.display = 'none'; return; }
    var lbl = (item.querySelector('label') || {}).innerText || '';
    item.style.display = (!q || lbl.toLowerCase().includes(q)) ? '' : 'none';
  });
}
function filterByAcgroup(msId, catCode) {
  var ms = document.getElementById(msId);
  if (!ms) return;
  ms.querySelectorAll('.ms-item').forEach(function(item){
    var itemCat = item.getAttribute('data-cat') || '';
    var show = !catCode || String(itemCat) === String(catCode);
    item.dataset._acgHidden = show ? '0' : '1';
    item.style.display = show ? '' : 'none';
    if (!show) {
      var cb = item.querySelector('input[type=checkbox]');
      if (cb) cb.checked = false;
    }
  });
  // re-apply search box if any
  var search = ms.querySelector('.ms-search input');
  if (search && search.value) msFilter(msId, search.value);
  updateMsTrigger(msId);
}
function updateMsTrigger(id) {
  var ms = document.getElementById(id);
  if (!ms) return;
  var checkboxes = ms.querySelectorAll('input[type=checkbox]:checked');
  var trigger = ms.querySelector('.ms-trigger .ms-text');
  var count = ms.querySelector('.ms-trigger .ms-count');
  if (checkboxes.length > 0) {
    if (trigger) trigger.textContent = checkboxes.length + ' selected';
    if (count) count.textContent = checkboxes.length;
  } else {
    if (trigger) trigger.textContent = trigger.textContent.indexOf('Select') >= 0 ? trigger.textContent.replace(/^\d+ selected/, '— Select') : '— Select —';
    // keep placeholder-ish
    if (count) count.textContent = '';
  }
}
document.querySelectorAll('.ms-item input[type=checkbox]').forEach(function(cb){
  cb.addEventListener('change', function(){
    var ms = this.closest('.ms-dropdown');
    if (ms) updateMsTrigger(ms.id);
  });
});

(function init(){
  var szCat = document.getElementById('sz-cat-dd');
  if (szCat && szCat.value) filterByAcgroup('ms-sz', szCat.value);
  document.querySelectorAll('.ms-dropdown').forEach(function(ms){ updateMsTrigger(ms.id); });
  refreshGroupCheckboxes();
})();

function onSearch(q) {
  q = (q || '').toLowerCase().trim();
  document.querySelectorAll('#tBody tr').forEach(function(tr){
    if (tr.classList.contains('subtotal-row')) return;
    var t = tr.innerText || '';
    tr.style.display = (!q || t.toLowerCase().includes(q)) ? '' : 'none';
  });
}
function zoomReport(delta) {
  reportZoom = Math.min(1.6, Math.max(0.6, reportZoom + delta));
  var el = document.getElementById('reportZoomWrap');
  if (el) el.style.zoom = reportZoom;
  var lbl = document.getElementById('zoomLbl');
  if (lbl) lbl.textContent = Math.round(reportZoom * 100) + '%';
}
function resetAllFilters() {
  document.querySelector('[name=from_date]').value = (new Date().getFullYear()-1)+'-04-01';
  document.querySelector('[name=to_date]').value = new Date().toISOString().slice(0,10);
  for (var i=1;i<=6;i++) {
    var el=document.getElementById('group_sel_'+i);
    if (el) el.value = i===1 ? 'Category' : '';
    var ck=document.getElementById('subtot_'+i);
    if (ck) ck.checked = false;
  }
  refreshGroupCheckboxes();
  document.querySelector('[name=gstn_filter][value=all]').checked = true;
  document.querySelector('[name=type_filter][value=all]').checked = true;
  ['group_full_name','only_sale_return','only_discount','whatsapp_owner'].forEach(function(n){
    var el=document.querySelector('[name='+n+']'); if(el) el.checked=false;
  });
  document.querySelector('[name=sale_rate_from]').value='';
  document.querySelector('[name=sale_rate_to]').value='';
  document.querySelectorAll('input[type=radio][value=all]').forEach(function(el){
    if (el.name && el.name.indexOf('tab_')===0) {
      el.checked = true;
      el.dispatchEvent(new Event('change'));
    }
  });
  document.querySelectorAll('.ms-item input[type=checkbox]').forEach(function(cb){ cb.checked=false; });
  document.querySelectorAll('.ms-dropdown').forEach(function(ms){ updateMsTrigger(ms.id); });
  document.querySelectorAll('.filter-tabs .nav-link').forEach(function(btn){ btn.classList.remove('has-filter'); });
}

function doExport(mode) {
  if (!HAS_FILTERS || !TOTALS.totalRows) { alert('Pehle Search / Run Report click karein.'); return; }
  // Full dataset export via server (all pages)
  window.location.href = '?export=' + encodeURIComponent(mode);
}

async function fetchAllRows() {
  var res = await fetch('?export=pdfdata');
  if (!res.ok) throw new Error('Export failed');
  return res.json();
}

async function doPDF() {
  if (!HAS_FILTERS || !TOTALS.totalRows) { alert('Pehle Search / Run Report click karein.'); return; }
  var btn = document.getElementById('pdfBtn');
  btn.disabled = true; btn.textContent = 'Generating…';
  try {
    var data = await fetchAllRows();
    var jsPDF = window.jspdf.jsPDF;
    var doc = new jsPDF({ orientation:'landscape', unit:'mm', format:'a4' });
    var pw = doc.internal.pageSize.getWidth();
    doc.setFont('helvetica','bold'); doc.setFontSize(13);
    doc.text('Category-wise Sale Register', pw/2, 12, {align:'center'});
    doc.setFont('helvetica','normal'); doc.setFontSize(8);
    doc.text('Period: '+data.from+' to '+data.to+' | Groups: '+(data.groups||[]).join(' > ')+' | Total: '+data.totalRows, pw/2, 18, {align:'center'});
    doc.text('Generated: '+new Date().toLocaleString('en-IN')+' | Full export (all pages)', pw/2, 23, {align:'center'});

    var head = [['#'].concat(data.groups || GROUP_LABELS).concat(['Qty','Amount','Disc','Taxable','Gross','CGST','SGST','IGST','Total','Central Amt','Local Amt','Exempt Amt','Qty%','Gross%','C.Qty','L.Qty','E.Qty'])];
    var body = [];
    (data.rows || []).forEach(function(r, idx){
      body.push([idx+1].concat(r.groups || []).concat([
        r.Qty, r.Amount, r.DiscAmt, r.TaxableAmt, r.GrossAmt,
        r.CGSTAmt, r.SGSTAmt, r.IGSTAmt, r.TotalAmt,
        r.CentralAmt, r.LocalAmt, r.ExemptedAmt,
        r.QtyContPct, r.GrossContPct,
        r.CentralQty, r.LocalQty, r.ExemptedQty
      ]));
    });
    var t = data.totals || {};
    body.push([''].concat(Array((data.groups||[]).length).fill('')).map(function(v,i){ return i===0?'GRAND TOTAL':v; }).concat([
      t.Qty, t.Amount, t.DiscAmt, t.TaxableAmt, t.GrossAmt,
      t.CGSTAmt, t.SGSTAmt, t.IGSTAmt, t.TotalAmt,
      t.CentralAmt, t.LocalAmt, t.ExemptedAmt,
      100, 100, t.CentralQty, t.LocalQty, t.ExemptedQty
    ]));

    doc.autoTable({
      startY: 26,
      head: head,
      body: body,
      theme: 'grid',
      styles: { fontSize: 6, cellPadding: 1.2 },
      headStyles: { fillColor:[26,92,255], textColor:255, fontStyle:'bold' },
      alternateRowStyles: { fillColor:[247,248,252] },
      didParseCell: function(d){
        if (d.section==='body' && d.row.index === d.table.body.length-1) {
          d.cell.styles.fillColor=[33,37,41];
          d.cell.styles.textColor=255;
          d.cell.styles.fontStyle='bold';
        }
      },
      didDrawPage: function(d){
        var ph = doc.internal.pageSize.getHeight();
        doc.setFontSize(7);
        doc.text('Page '+doc.internal.getCurrentPageInfo().pageNumber+' of '+doc.internal.getNumberOfPages(), pw-12, ph-5, {align:'right'});
        doc.text('All '+data.totalRows+' groups included.', d.settings.margin.left, ph-5);
      }
    });
    doc.save('Category_Wise_Sale_'+DATE_FROM+'_to_'+DATE_TO+'.pdf');
  } catch (e) {
    alert('PDF export failed: ' + (e.message || e));
  }
  btn.disabled = false; btn.textContent = '⬇ PDF';
}
</script>

<?php include '../includes/footer.php'; ?>
