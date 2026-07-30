<?php
/**
 * MIS (Company-wise Sale Bill Register)
 * Place as: sale/mis.php
 * Query base: Sale (Type.Status=4) UNION ALL Sale Return (Type.Status=5)
 * Disc Amt = Stock.SDisc_Amt + (Stock.Tot_Amt * SBill1.Disc / 100)
 * Gross Amt = Stock.ItemNetAmt
 */

// Temporary: show real PHP error instead of blank HTTP 500 (remove after fix)
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

require_once '../config/database.php';
require_once '../classes/auth.php';

$db   = (new Database())->connect();
$auth = new Auth($db);
if (!$auth->check()) { header('Location: ../login.php'); exit; }

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf_token']) || !hash_equals(isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '', $_POST['csrf_token'])) {
        http_response_code(403); exit('Invalid CSRF token.');
    }
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Avoid "Cannot redeclare" fatals when helpers already exist in includes
if (!function_exists('esc')) {
    function esc($db, $val) {
        return $db->real_escape_string(trim((string)$val));
    }
}
if (!function_exists('h')) {
    function h($val) {
        return htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('safeQuery')) {
    function safeQuery($db, $sql) {
        $r = $db->query($sql);
        if (!$r) return array();
        $rows = array();
        while ($row = $r->fetch_assoc()) $rows[] = $row;
        return $rows;
    }
}
if (!function_exists('multiSelected')) {
    function multiSelected($selected, $value) {
        return in_array($value, $selected, true) ? 'checked' : '';
    }
}
if (!function_exists('fmtNum')) {
    function fmtNum($v, $dec = 2) {
        return number_format((float)$v, $dec);
    }
}
if (!function_exists('asList')) {
    function asList($db, $raw) {
        if (is_array($raw)) {
            $out = array();
            foreach ($raw as $v) {
                $v = trim((string)$v);
                if ($v !== '') $out[] = $v;
            }
            return $out;
        }
        if ($raw !== null && $raw !== '') return array(trim((string)$raw));
        return array();
    }
}
if (!function_exists('inListSql')) {
    function inListSql($db, $vals) {
        $parts = array();
        foreach ($vals as $v) {
            $parts[] = "'" . esc($db, $v) . "'";
        }
        return implode(',', $parts);
    }
}
if (!function_exists('pageUrl')) {
    function pageUrl($pg) {
        return '?' . http_build_query(array_merge($_GET, array('page' => $pg)));
    }
}
if (!function_exists('renderMs')) {
    function renderMs($id, $label, $name, $dd, $valKey, $labelKey, $selected, $allText) {
        ?>
  <div class="fg-col-ms">
    <span class="fl"><?php echo h($label); ?></span>
    <div class="ms-dropdown" id="<?php echo h($id); ?>">
      <div class="ms-trigger" onclick="toggleMs('<?php echo h($id); ?>')">
        <span class="ms-text"><?php
          if (!empty($selected)) {
              echo h(implode(', ', array_slice($selected, 0, 2)) . (count($selected) > 2 ? '…' : ''));
          } else {
              echo h($allText);
          }
        ?></span>
        <?php if (!empty($selected)): ?><span class="ms-badge"><?php echo count($selected); ?></span><?php endif; ?>
      </div>
      <div class="ms-menu" id="<?php echo h($id); ?>-menu">
        <div class="ms-search"><input type="text" placeholder="Search…" oninput="msFilter('<?php echo h($id); ?>',this.value)"></div>
        <?php foreach ($dd as $r):
            $val = (string)(isset($r[$valKey]) ? $r[$valKey] : '');
            $lab = (string)(isset($r[$labelKey]) ? $r[$labelKey] : $val);
            if ($val === '') continue;
            $cid = $id . '_' . md5($val);
        ?>
        <div class="ms-item">
          <input type="checkbox" name="<?php echo h($name); ?>[]" value="<?php echo h($val); ?>" id="<?php echo h($cid); ?>" <?php echo multiSelected($selected, $val); ?>>
          <label for="<?php echo h($cid); ?>"><?php echo h($lab); ?></label>
        </div>
        <?php endforeach; ?>
        <div class="ms-actions">
          <button type="button" class="btn btn-outline-secondary btn-xs" onclick="msSelectAll('<?php echo h($id); ?>',true)">All</button>
          <button type="button" class="btn btn-outline-secondary btn-xs" onclick="msSelectAll('<?php echo h($id); ?>',false)">None</button>
          <button type="button" class="btn btn-primary btn-xs" onclick="toggleMs('<?php echo h($id); ?>')">Done</button>
        </div>
      </div>
    </div>
  </div>
        <?php
    }
}

// ── Filters ───────────────────────────────────────────────────────────────────
$isPost       = ($_SERVER['REQUEST_METHOD'] === 'POST');
$page         = max(1, (int)($_GET['page'] ?? 1));
$rowsPerPage  = 100;
$isPaginating = isset($_GET['page']);

if ($isPost)            { $_SESSION['mis_sbr_filters'] = $_POST; }
elseif (!$isPaginating) { unset($_SESSION['mis_sbr_filters']); }

$filters    = $isPost ? $_POST : ($isPaginating ? ($_SESSION['mis_sbr_filters'] ?? []) : []);
$hasFilters = !empty($filters);
if ($isPost) $page = 1;

// Dates
$from = esc($db, $filters['from_date'] ?? date('Y') . '-04-01');
$to   = esc($db, $filters['to_date']   ?? date('Y-m-d'));
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) $from = date('Y-04-01');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $to))   $to   = date('Y-m-d');

// Radios / flags (Date tab)
$gstnFilter     = esc($db, $filters['gstn_filter']    ?? 'all');   // all | with | without
$typeFilter     = esc($db, $filters['type_filter']    ?? 'all');   // all | local | central | exempted
$payMode        = esc($db, $filters['pay_mode']       ?? 'all');   // all | cash | credit | wallet
$onlySaleReturn = !empty($filters['only_sale_return']);
$onlyDiscount   = !empty($filters['only_discount']);
$withoutAmount  = !empty($filters['without_amount']);
$withoutDiscPct = !empty($filters['without_disc_pct']);
$withoutRep     = !empty($filters['without_representative']);
$whatsappOwner  = !empty($filters['whatsapp_owner']);

// 6 grouping levels — values from reference MIS
$validGroups = ['ProductGroup','Category','Company','Color','Description','CompanyNo',''];
$grp = [];
for ($i = 1; $i <= 6; $i++) {
    $val     = esc($db, $filters["group_$i"] ?? '');
    $grp[$i] = in_array($val, $validGroups, true) ? $val : '';
}
$subTotalReq = [];
for ($i = 1; $i <= 6; $i++) {
    $subTotalReq[$i] = !empty($filters["subtot_$i"]);
}

// Optional dimension filters (dynamic multi-select)
$selProdGroup = asList($db, $filters['sel_prodgroup'] ?? []);
$selCategory  = asList($db, $filters['sel_category']  ?? []);
$selCompany   = asList($db, $filters['sel_company']   ?? []);
$selColor     = asList($db, $filters['sel_color']     ?? []);
$selDesc      = asList($db, $filters['sel_description'] ?? []);
$selCompNo    = asList($db, $filters['sel_compno']    ?? []);
$selRep       = asList($db, $filters['sel_rep']       ?? []);
$selSize      = asList($db, $filters['sel_size']      ?? []);
$selProduct   = asList($db, $filters['sel_product']   ?? []);

// ── Dropdown data (dynamic) ───────────────────────────────────────────────────
$ddProdGroup = safeQuery($db, "SELECT ProdGroup_Name, ProdGroup_Code FROM productgroup ORDER BY ProdGroup_Name");
$ddCategory  = safeQuery($db, "SELECT c_name AS C_Name, C_code AS C_Code FROM category ORDER BY c_name");
$ddCompany   = safeQuery($db, "SELECT comp_name AS Comp_Name, Comp_code AS Comp_Code FROM compdetail ORDER BY comp_name");
$ddColor     = safeQuery($db, "SELECT Color_name AS Color_Name, Color_code AS Color_Code FROM color ORDER BY Color_name");
$ddDesc      = safeQuery($db, "SELECT Des_Name, Des_Code FROM description ORDER BY Des_Name");
// Fallback if description table uses product names only
if (empty($ddDesc)) {
    $ddDesc = safeQuery($db, "SELECT DISTINCT PRODUCT_NAME AS Des_Name, PRODUCT_NAME AS Des_Code FROM product WHERE IFNULL(PRODUCT_NAME,'') <> '' ORDER BY PRODUCT_NAME");
}
$ddCompNo    = safeQuery($db, "SELECT Comp_No FROM product WHERE IFNULL(Comp_No,'') <> '' GROUP BY Comp_No ORDER BY Comp_No");
$ddRep       = safeQuery($db, "SELECT Rp_Name, Rep_Code FROM representative ORDER BY Rp_Name");
$ddSize      = safeQuery($db, "SELECT s_name AS S_Name, s_code AS S_Code FROM sizemaster ORDER BY s_name");
$ddProduct   = safeQuery($db, "SELECT PRODUCT_CODE, PRODUCT_NAME, CompProdCode FROM product ORDER BY PRODUCT_NAME LIMIT 5000");

$groupingOptions = [
    ''             => '— None —',
    'ProductGroup' => 'Product Group',
    'Category'     => 'Category',
    'Company'      => 'Company',
    'Color'        => 'Color',
    'Description'  => 'Description',
    'CompanyNo'    => 'Company No',
];

// Map grouping key → SQL expression (detail query aliases)
$groupExprMap = [
    'ProductGroup' => "UPPER(LTRIM(RTRIM(IFNULL(pg.ProdGroup_Name,''))))",
    'Category'     => "UPPER(LTRIM(RTRIM(IFNULL(c.c_name,''))))",
    'Company'      => "UPPER(LTRIM(RTRIM(IFNULL(cd.comp_name,''))))",
    'Color'        => "UPPER(LTRIM(RTRIM(IFNULL(col.Color_name,''))))",
    'Description'  => "UPPER(LTRIM(RTRIM(IFNULL(COALESCE(d.Des_Name, p.PRODUCT_NAME),''))))",
    'CompanyNo'    => "UPPER(LTRIM(RTRIM(IFNULL(p.comp_no,''))))",
];

$grpSql = [];
for ($i = 1; $i <= 6; $i++) {
    $key = $grp[$i];
    $grpSql[$i] = ($key !== '' && isset($groupExprMap[$key]))
        ? $groupExprMap[$key] . " AS Grp$i"
        : "'' AS Grp$i";
}

// ── WHERE (shared by both UNION legs) ─────────────────────────────────────────
$whereParts = ["st.V_DATE >= '$from'", "st.V_DATE <= '$to'"];

if (!empty($selProdGroup)) {
    $whereParts[] = 'pg.ProdGroup_Name IN (' . inListSql($db, $selProdGroup) . ')';
}
if (!empty($selCategory)) {
    $whereParts[] = 'c.c_name IN (' . inListSql($db, $selCategory) . ')';
}
if (!empty($selCompany)) {
    $whereParts[] = 'cd.comp_name IN (' . inListSql($db, $selCompany) . ')';
}
if (!empty($selColor)) {
    $whereParts[] = 'col.Color_name IN (' . inListSql($db, $selColor) . ')';
}
if (!empty($selDesc)) {
    $whereParts[] = '(COALESCE(d.Des_Name, p.PRODUCT_NAME) IN (' . inListSql($db, $selDesc) . '))';
}
if (!empty($selCompNo)) {
    $whereParts[] = 'p.comp_no IN (' . inListSql($db, $selCompNo) . ')';
}
if (!empty($selRep)) {
    $whereParts[] = 'rp.Rp_Name IN (' . inListSql($db, $selRep) . ')';
}
if (!empty($selSize)) {
    $whereParts[] = 'sm.s_name IN (' . inListSql($db, $selSize) . ')';
}
if (!empty($selProduct)) {
    $whereParts[] = 'p.PRODUCT_CODE IN (' . inListSql($db, $selProduct) . ')';
}

// GSTIN — TAX_YN='Y' treated as With GSTIN / Central style flag (matches existing app)
if ($gstnFilter === 'with')    $whereParts[] = "IFNULL(sb.TAX_YN,'N')='Y'";
if ($gstnFilter === 'without') $whereParts[] = "IFNULL(sb.TAX_YN,'N')<>'Y'";

// Tax type
if ($typeFilter === 'local')    $whereParts[] = "IFNULL(sb.TAX_YN,'N')<>'Y'";
if ($typeFilter === 'central')  $whereParts[] = "IFNULL(sb.TAX_YN,'N')='Y'";
if ($typeFilter === 'exempted') $whereParts[] = 'IFNULL(st.TAX_AMT,0)=0 AND IFNULL(st.SSAT_AMT,0)=0';

// Payment mode (Bank / Wallet codes on SBill1)
// Note: map to your Pay_Mode column if Cash vs Credit need stricter split.
if ($payMode === 'cash') {
    $whereParts[] = "IFNULL(sb.Bank_Code,'')='' AND IFNULL(sb.Wallet_Code,'')='' AND IFNULL(sb.Wallet_SubCode,'')=''";
} elseif ($payMode === 'wallet') {
    $whereParts[] = "(IFNULL(sb.Wallet_Code,'')<>'' OR IFNULL(sb.Wallet_SubCode,'')<>'')";
} elseif ($payMode === 'credit') {
    $whereParts[] = "IFNULL(sb.Bank_Code,'')='' AND IFNULL(sb.Wallet_Code,'')='' AND IFNULL(sb.SubCode,'')<>''";
}

if ($onlyDiscount)   $whereParts[] = '(IFNULL(st.SDISC_AMT,0) > 0 OR IFNULL(sb.Disc,0) > 0 OR IFNULL(st.SDISC,0) > 0)';
if ($withoutAmount)  $whereParts[] = 'IFNULL(st.AMOUNT,0) = 0';
if ($withoutDiscPct) $whereParts[] = 'IFNULL(st.SDISC,0) = 0';
if ($withoutRep)     $whereParts[] = "(IFNULL(rp.Rp_Name,'')='' OR rp.Rp_Name IS NULL)";

$whereCommon = implode(' AND ', $whereParts);

/**
 * Disc Amt formula (same as reference query, simplified algebraically):
 *   Stock.SDisc_Amt + (Stock.Tot_Amt * SBill1.Disc / 100)
 * Gross Amt = Stock.ItemNetAmt
 */
$discAmtExpr = '(IFNULL(st.SDISC_AMT,0) + (IFNULL(st.TOT_AMT, IFNULL(st.TOT_AMOUNT,0)) * IFNULL(sb.Disc,0) / 100))';
$grossExpr   = 'IFNULL(st.ItemNetAmt, IFNULL(st.TOT_AMT, IFNULL(st.TOT_AMOUNT,0)))';

$selectCols = "
    {$grpSql[1]}, {$grpSql[2]}, {$grpSql[3]}, {$grpSql[4]}, {$grpSql[5]}, {$grpSql[6]},
    st.V_TYPE AS V_Type,
    st.V_NO   AS V_No,
    st.V_DATE AS V_Date,
    rp.Rp_Name,
    rp.Rep_Code,
    p.PRODUCT_CODE AS Product_Code,
    p.CompProdCode,
    LTRIM(RTRIM(IFNULL(p.comp_no,''))) AS Comp_No,
    LTRIM(RTRIM(IFNULL(pg.ProdGroup_Name,''))) AS ProdGroup_Name,
    LTRIM(RTRIM(IFNULL(cd.comp_name,''))) AS Comp_Name,
    LTRIM(RTRIM(IFNULL(c.c_name,''))) AS Category,
    LTRIM(RTRIM(IFNULL(COALESCE(d.Des_Name, p.PRODUCT_NAME),''))) AS Des_Name,
    LTRIM(RTRIM(IFNULL(col.Color_name,''))) AS Color_Name,
    LTRIM(RTRIM(IFNULL(sm.s_name,''))) AS Size,
    LTRIM(RTRIM(IFNULL(si1.s_name,''))) AS Inst1,
    LTRIM(RTRIM(IFNULL(si2.s_name,''))) AS Inst2,
    LTRIM(RTRIM(IFNULL(si3.s_name,''))) AS Inst3,
    IFNULL(p.HSN,'' ) AS HSN,
    st.V_TYPE AS TypeName
";

$fromJoins = "
    FROM sbill1 sb
    LEFT JOIN stock st ON st.V_TYPE = sb.V_TYPE AND st.V_NO = sb.V_NO
    LEFT JOIN type ty ON st.V_TYPE = ty.V_TYPE
    LEFT JOIN product p ON st.PROD_CODE = p.PRODUCT_CODE
    LEFT JOIN representative rp ON st.Rep_Code = rp.Rp_Code
    LEFT JOIN productgroup pg ON p.ProdGroup_Code = pg.ProdGroup_Code
    LEFT JOIN compdetail cd ON p.COMP_CODE = cd.Comp_code
    LEFT JOIN category c ON p.CAT_CODE = c.C_code
    LEFT JOIN description d ON p.Desc_Code = d.Des_Code
    LEFT JOIN color col ON p.COLOR_CODE = col.Color_code
    LEFT JOIN sizemaster sm ON p.SIZE_CODE = sm.s_code
    LEFT JOIN specialinst si1 ON p.INST1_CODE = si1.s_code
    LEFT JOIN specialinst1 si2 ON p.INST2_CODE = si2.s_code
    LEFT JOIN specialinst2 si3 ON p.INST3_CODE = si3.s_code
";

// Sale leg (Status=4) — positive; Sale Return leg (Status=5) — negated
$saleSelect = "
    SELECT $selectCols,
        st.QTY AS Qty,
        st.Rate AS Rate,
        st.AMOUNT AS Amount,
        IFNULL(st.SDISC,0) AS SDisc,
        $discAmtExpr AS SDisc_Amt,
        $grossExpr AS GrossAmt,
        4 AS StatusCode
    $fromJoins
    WHERE ty.STATUS = 4 AND $whereCommon
";

$returnSelect = "
    SELECT $selectCols,
        -st.QTY AS Qty,
        st.Rate AS Rate,
        -st.AMOUNT AS Amount,
        IFNULL(st.SDISC,0) AS SDisc,
        -($discAmtExpr) AS SDisc_Amt,
        -($grossExpr) AS GrossAmt,
        5 AS StatusCode
    $fromJoins
    WHERE ty.STATUS = 5 AND $whereCommon
";

if ($onlySaleReturn) {
    $unionSql = $returnSelect;
} else {
    $unionSql = "($saleSelect) UNION ALL ($returnSelect)";
}

$orderBy = 'ORDER BY Grp1, Grp2, Grp3, Grp4, Grp5, Grp6, V_Date, V_Type, V_No, Product_Code';
$detailSql = "SELECT * FROM ($unionSql) AS mis $orderBy";

$rows = [];
$totalRows = 0;
$queryErr = null;
$totals = [
    'Qty' => 0.0, 'Amount' => 0.0, 'SDisc_Amt' => 0.0, 'GrossAmt' => 0.0,
];

if ($hasFilters) {
    $countSql = "SELECT COUNT(*) AS cnt,
        SUM(Qty) AS Qty, SUM(Amount) AS Amount,
        SUM(SDisc_Amt) AS SDisc_Amt, SUM(GrossAmt) AS GrossAmt
        FROM ($unionSql) AS T";
    $tRes = $db->query($countSql);
    if ($tRes) {
        $tRow = $tRes->fetch_assoc();
        $totalRows = (int)($tRow['cnt'] ?? 0);
        foreach ($totals as $k => $_) $totals[$k] = (float)($tRow[$k] ?? 0);
    } else {
        $queryErr = $db->error;
    }

    if (!$queryErr && $totalRows > 0) {
        $offset = ($page - 1) * $rowsPerPage;
        $pRes = $db->query("SELECT * FROM ($unionSql) AS mis $orderBy LIMIT $rowsPerPage OFFSET $offset");
        if ($pRes) {
            while ($r = $pRes->fetch_assoc()) $rows[] = $r;
        } else {
            $queryErr = $db->error;
        }
    }
}

$totalPages = $totalRows > 0 ? (int)ceil($totalRows / $rowsPerPage) : 1;
$page = min($page, max(1, $totalPages));

$jsGroupingOptions = json_encode($groupingOptions, JSON_UNESCAPED_UNICODE);
$jsGrpValues       = json_encode($grp, JSON_UNESCAPED_UNICODE);
$activeGroupLabel  = $grp[1] !== '' ? ($groupingOptions[$grp[1]] ?? $grp[1]) : 'Detail';

include '../includes/header.php';
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

<style>
:root {
  --blue:#1a5cff; --border:#d1d5db; --bg-panel:#f8f9fb;
  --green:#198754; --red:#dc3545;
}
.filter-card { background:#fff; border:1px solid var(--border); border-radius:8px; padding:14px 16px; margin-bottom:14px; box-shadow:0 1px 4px rgba(0,0,0,.06); }
.filter-card .fc-title { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:#6b7280; margin-bottom:10px; padding-bottom:6px; border-bottom:1px solid #f0f0f0; }
.fl { font-size:10.5px; font-weight:600; color:#374151; margin-bottom:3px; display:block; }
.fg-row { display:flex; flex-wrap:wrap; gap:10px; align-items:flex-end; }
.fg-row + .fg-row { margin-top:10px; }
.fg-col-date { flex:0 0 140px; }
.fg-col-ms { flex:1 1 140px; min-width:130px; max-width:210px; }
.grp-grid { display:flex; flex-wrap:wrap; gap:8px; align-items:flex-end; }
.grp-cell { display:flex; flex-direction:column; flex:1 1 130px; min-width:120px; max-width:180px; }
.grp-subtot { display:none; margin-top:3px; align-items:center; gap:4px; }
.grp-subtot.show { display:flex; }
.opts-row { display:flex; flex-wrap:wrap; gap:10px; align-items:flex-start; }
.opts-block { background:var(--bg-panel); border:1px solid #e5e7eb; border-radius:6px; padding:8px 12px; }
.opts-block .ob-title { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#9ca3af; margin-bottom:6px; }
.opts-radio-row { display:flex; gap:12px; flex-wrap:wrap; }
.opts-radio-row label, .opts-chk-col label { font-size:11.5px; display:flex; align-items:center; gap:4px; cursor:pointer; margin:0; white-space:nowrap; }
.opts-chk-col { display:flex; flex-direction:column; gap:5px; }
.ms-dropdown { position:relative; }
.ms-trigger { width:100%; padding:5px 8px; border:1px solid #ced4da; border-radius:4px; background:#fff; font-size:11.5px; cursor:pointer; text-align:left; display:flex; justify-content:space-between; align-items:center; gap:4px; overflow:hidden; }
.ms-trigger:hover { border-color:#86b7fe; }
.ms-trigger .ms-text { overflow:hidden; text-overflow:ellipsis; flex:1; }
.ms-trigger .ms-badge { background:var(--blue); color:#fff; border-radius:10px; font-size:9px; font-weight:700; padding:1px 5px; }
.ms-menu { position:absolute; top:100%; left:0; min-width:100%; width:max-content; max-width:320px; z-index:1050; background:#fff; border:1px solid #ced4da; border-radius:0 0 5px 5px; max-height:220px; overflow-y:auto; display:none; box-shadow:0 6px 16px rgba(0,0,0,.15); }
.ms-menu.show { display:block; }
.ms-search { padding:6px 8px; border-bottom:1px solid #f0f0f0; }
.ms-search input { width:100%; font-size:11px; padding:3px 6px; border:1px solid #dee2e6; border-radius:3px; }
.ms-item { padding:4px 10px; font-size:11.5px; cursor:pointer; display:flex; align-items:center; gap:6px; border-bottom:1px solid #f8f9fa; }
.ms-item:hover { background:#e7f1ff; }
.ms-item input[type=checkbox] { width:13px; height:13px; accent-color:var(--blue); flex-shrink:0; }
.ms-item label { margin:0; cursor:pointer; font-size:11.5px; font-weight:400; flex:1; }
.ms-actions { padding:5px 8px; border-top:2px solid #e9ecef; background:#f8f9fa; display:flex; gap:5px; }
.ms-actions button { font-size:10.5px; padding:2px 8px; }
.tab-bar { display:flex; flex-wrap:wrap; gap:4px; margin-bottom:12px; }
.tab-bar .tab-btn { border:1px solid #ced4da; background:#f3f4f6; color:#374151; font-size:11px; padding:5px 10px; border-radius:4px 4px 0 0; cursor:pointer; }
.tab-bar .tab-btn.active { background:var(--blue); color:#fff; border-color:var(--blue); }
.tab-pane { display:none; }
.tab-pane.active { display:block; }
.sum-card { border-radius:8px; padding:10px 14px; border:1px solid #e0e2e7; background:#fff; }
.sum-card .sc-label { font-size:10px; color:#6b7280; text-transform:uppercase; letter-spacing:.05em; margin-bottom:3px; }
.sum-card .sc-val { font-size:18px; font-weight:700; color:#1a1d27; }
.sum-card .sc-sub { font-size:10px; color:#9ca3af; margin-top:2px; }
.sum-card.acc { border-left:3px solid var(--blue); }
.sum-card.grn { border-left:3px solid var(--green); }
.sum-card.red { border-left:3px solid var(--red); }
table.mis thead th { font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:.03em; white-space:nowrap; }
table.mis td { font-size:11.5px; white-space:nowrap; padding:6px 8px; }
table.mis td.num { text-align:right; font-variant-numeric:tabular-nums; font-family:'Courier New',monospace; font-size:11px; }
table.mis td.neg { color:var(--red); }
tr.group-hdr td { background:#eef3ff; font-weight:700; font-size:11.5px; }
#grandTotalRow td { background:#212529 !important; color:#fff !important; font-weight:700; }
.action-bar { background:#4b5563; color:#fff; border-radius:6px; padding:8px 12px; display:flex; justify-content:space-between; align-items:center; gap:8px; flex-wrap:wrap; }
.action-bar .btn { font-size:12px; }
</style>

<div class="container-fluid">
<div class="row">
<?php include '../includes/sidebar.php'; ?>
<div class="col-md-9 col-lg-10 p-4">
<div class="main-content p-3">

<h4 class="mb-3">MIS (Company-wise Sale Bill Register)
  <small class="text-muted fs-6 fw-normal ms-2">
    <?php if ($hasFilters): ?>
      Period: <?= h($from) ?> – <?= h($to) ?>
      <?php if ($grp[1] !== ''): ?> &nbsp;|&nbsp; Group: <?= h($activeGroupLabel) ?><?php endif; ?>
      &nbsp;|&nbsp; <?= number_format($totalRows) ?> lines
    <?php endif; ?>
  </small>
</h4>

<form method="POST" action="" id="filterForm">
<input type="hidden" name="csrf_token" value="<?= h($csrf_token) ?>">

<!-- Tabs (match legacy MIS shell; Date is primary) -->
<div class="tab-bar" id="misTabs">
  <?php
  $tabs = ['date'=>'Date','type'=>'Type','party'=>'Party','supplier'=>'Supplier','customer'=>'Customer','transport'=>'Transport','category'=>'Category','company'=>'Company','prodgroup'=>'Product Group','color'=>'Color','description'=>'Description','compno'=>'Company No','size'=>'Size','rep'=>'Representative'];
  foreach ($tabs as $tid => $tlabel): ?>
    <button type="button" class="tab-btn<?= $tid==='date'?' active':'' ?>" data-tab="<?= h($tid) ?>" onclick="showMisTab('<?= h($tid) ?>')"><?= h($tlabel) ?></button>
  <?php endforeach; ?>
</div>

<!-- ═══════════════ DATE TAB ═══════════════ -->
<div class="tab-pane active" id="tab-date">
  <div class="filter-card">
    <div class="fc-title">Date &amp; Grouping</div>
    <div class="fg-row">
      <div class="fg-col-date">
        <span class="fl">From Date</span>
        <input type="date" name="from_date" value="<?= h($from) ?>" class="form-control form-control-sm" style="font-size:11.5px;">
      </div>
      <div class="fg-col-date">
        <span class="fl">To Date</span>
        <input type="date" name="to_date" value="<?= h($to) ?>" class="form-control form-control-sm" style="font-size:11.5px;">
      </div>
    </div>

    <div class="grp-grid mt-3">
      <?php
      $grpLabels = ['Ist Grouping','IInd Grouping','IIIrd Grouping','IVth Grouping','Vth Grouping','VIth Grouping'];
      for ($i = 1; $i <= 6; $i++):
          $nextFilled = ($i < 6) && !empty($grp[$i + 1]);
          $chkLabel   = $grp[$i] !== '' ? ($groupingOptions[$grp[$i]] ?? $grp[$i]) : '';
      ?>
      <div class="grp-cell">
        <span class="fl"><?= $grpLabels[$i-1] ?></span>
        <select name="group_<?= $i ?>" id="group_sel_<?= $i ?>" class="form-select form-select-sm"
                data-grp-index="<?= $i ?>" onchange="onGroupChange(<?= $i ?>)" style="font-size:11.5px;">
          <?php foreach ($groupingOptions as $ov => $ol): ?>
            <option value="<?= h($ov) ?>" <?= $grp[$i] === $ov ? 'selected' : '' ?>><?= h($ol) ?></option>
          <?php endforeach; ?>
        </select>
        <div class="grp-subtot<?= $nextFilled ? ' show' : '' ?>" id="grp_chk_col_<?= $i ?>">
          <input type="checkbox" name="subtot_<?= $i ?>" id="subtot_<?= $i ?>" value="1"
                 <?= !empty($subTotalReq[$i]) ? 'checked' : '' ?> style="accent-color:var(--blue);width:12px;height:12px;">
          <label for="subtot_<?= $i ?>" id="subtot_lbl_<?= $i ?>" style="font-size:10px;color:var(--blue);font-weight:600;cursor:pointer;margin:0;">
            <?= h($chkLabel) ?>-wise Total?
          </label>
        </div>
      </div>
      <?php endfor; ?>
    </div>
  </div>

  <div class="filter-card">
    <div class="opts-row">
      <div class="opts-block">
        <div class="ob-title">GSTIN</div>
        <div class="opts-radio-row">
          <?php foreach (['all'=>'All','with'=>'With GSTIN','without'=>'Without GSTIN'] as $v=>$l): ?>
          <label><input type="radio" name="gstn_filter" value="<?= $v ?>" <?= $gstnFilter===$v?'checked':'' ?>><?= $l ?></label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="opts-block">
        <div class="ob-title">Tax Type</div>
        <div class="opts-radio-row">
          <?php foreach (['all'=>'All','local'=>'Local','central'=>'Central','exempted'=>'Exempted'] as $v=>$l): ?>
          <label><input type="radio" name="type_filter" value="<?= $v ?>" <?= $typeFilter===$v?'checked':'' ?>><?= $l ?></label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="opts-block">
        <div class="ob-title">Payment Mode</div>
        <div class="opts-radio-row">
          <?php foreach (['all'=>'All','cash'=>'Cash','credit'=>'Credit','wallet'=>'Wallet'] as $v=>$l): ?>
          <label><input type="radio" name="pay_mode" value="<?= $v ?>" <?= $payMode===$v?'checked':'' ?>><?= $l ?></label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="opts-block">
        <div class="ob-title">Extra Filters</div>
        <div class="opts-chk-col">
          <label><input type="checkbox" name="only_sale_return" value="1" <?= $onlySaleReturn?'checked':'' ?>> Only Sale Return</label>
          <label><input type="checkbox" name="only_discount" value="1" <?= $onlyDiscount?'checked':'' ?>> Only Discount Bill</label>
          <label><input type="checkbox" name="without_amount" value="1" <?= $withoutAmount?'checked':'' ?>> Without Amount</label>
          <label><input type="checkbox" name="without_disc_pct" value="1" <?= $withoutDiscPct?'checked':'' ?>> Without Disc%</label>
          <label><input type="checkbox" name="without_representative" value="1" <?= $withoutRep?'checked':'' ?>> Without Representative</label>
          <label><input type="checkbox" name="whatsapp_owner" value="1" <?= $whatsappOwner?'checked':'' ?>> WhatsApp To Owner</label>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Category / Company / … filter tabs -->
<div class="tab-pane" id="tab-category">
  <div class="filter-card"><div class="fc-title">Category Filter</div><div class="fg-row">
    <?php renderMs('ms-cat','Category','sel_category',$ddCategory,'C_Name','C_Name',$selCategory,'All Categories'); ?>
  </div></div>
</div>
<div class="tab-pane" id="tab-company">
  <div class="filter-card"><div class="fc-title">Company Filter</div><div class="fg-row">
    <?php renderMs('ms-cmp','Company','sel_company',$ddCompany,'Comp_Name','Comp_Name',$selCompany,'All Companies'); ?>
  </div></div>
</div>
<div class="tab-pane" id="tab-prodgroup">
  <div class="filter-card"><div class="fc-title">Product Group Filter</div><div class="fg-row">
    <?php renderMs('ms-pg','Product Group','sel_prodgroup',$ddProdGroup,'ProdGroup_Name','ProdGroup_Name',$selProdGroup,'All Product Groups'); ?>
  </div></div>
</div>
<div class="tab-pane" id="tab-color">
  <div class="filter-card"><div class="fc-title">Color Filter</div><div class="fg-row">
    <?php renderMs('ms-col','Color','sel_color',$ddColor,'Color_Name','Color_Name',$selColor,'All Colors'); ?>
  </div></div>
</div>
<div class="tab-pane" id="tab-description">
  <div class="filter-card"><div class="fc-title">Description Filter</div><div class="fg-row">
    <?php renderMs('ms-desc','Description','sel_description',$ddDesc,'Des_Name','Des_Name',$selDesc,'All Descriptions'); ?>
  </div></div>
</div>
<div class="tab-pane" id="tab-compno">
  <div class="filter-card"><div class="fc-title">Company No Filter</div><div class="fg-row">
    <?php renderMs('ms-cn','Company No','sel_compno',$ddCompNo,'Comp_No','Comp_No',$selCompNo,'All Company No'); ?>
  </div></div>
</div>
<div class="tab-pane" id="tab-size">
  <div class="filter-card"><div class="fc-title">Size Filter</div><div class="fg-row">
    <?php renderMs('ms-sz','Size','sel_size',$ddSize,'S_Name','S_Name',$selSize,'All Sizes'); ?>
  </div></div>
</div>
<div class="tab-pane" id="tab-rep">
  <div class="filter-card"><div class="fc-title">Representative Filter</div><div class="fg-row">
    <?php renderMs('ms-rep','Representative','sel_rep',$ddRep,'Rp_Name','Rp_Name',$selRep,'All Representatives'); ?>
  </div></div>
</div>
<div class="tab-pane" id="tab-type">
  <div class="filter-card"><div class="fc-title">Type</div>
    <p class="small text-muted mb-0">Sale / Sale-Return is controlled by <strong>Only Sale Return</strong> on the Date tab. Detail rows use voucher Type from stock.</p>
  </div>
</div>
<div class="tab-pane" id="tab-party">
  <div class="filter-card"><div class="fc-title">Party / Product</div><div class="fg-row">
    <?php
      $prodDd = array_map(function($r) {
          return [
              'PRODUCT_CODE' => $r['PRODUCT_CODE'],
              'LABEL' => ($r['PRODUCT_CODE'] ?? '') . ' – ' . ($r['PRODUCT_NAME'] ?? ''),
          ];
      }, $ddProduct);
      renderMs('ms-prod','Product','sel_product',$prodDd,'PRODUCT_CODE','LABEL',$selProduct,'All Products');
    ?>
  </div></div>
</div>
<div class="tab-pane" id="tab-supplier">
  <div class="filter-card"><div class="fc-title">Supplier</div><p class="small text-muted mb-0">Reserved — use Company filter for brand/company master.</p></div>
</div>
<div class="tab-pane" id="tab-customer">
  <div class="filter-card"><div class="fc-title">Customer</div><p class="small text-muted mb-0">Reserved for party/customer linkage on SBill1.</p></div>
</div>
<div class="tab-pane" id="tab-transport">
  <div class="filter-card"><div class="fc-title">Transport</div><p class="small text-muted mb-0">Reserved for transport master filter.</p></div>
</div>

<div class="action-bar mb-4">
  <div class="d-flex gap-2">
    <button type="button" class="btn btn-outline-light btn-sm" onclick="saveTemplate()">Save As Template</button>
    <button type="button" class="btn btn-outline-light btn-sm" onclick="openTemplate()">Open From Template</button>
  </div>
  <div class="d-flex gap-2">
    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetAllFilters()">↺ Reset</button>
    <button type="button" class="btn btn-success btn-sm" onclick="whatsAppOwner()" <?= $whatsappOwner?'':'title="Enable WhatsApp To Owner checkbox"' ?>>WhatsApp</button>
    <button type="submit" class="btn btn-primary btn-sm px-3">Print / Run Report</button>
    <a href="../index.php" class="btn btn-dark btn-sm">Exit</a>
  </div>
</div>
</form>

<?php if ($hasFilters): ?>
<div class="row g-3 mb-3">
  <div class="col"><div class="sum-card acc">
    <div class="sc-label">Net Qty</div>
    <div class="sc-val"><?= number_format($totals['Qty'], 3) ?></div>
    <div class="sc-sub">Sale − Return</div>
  </div></div>
  <div class="col"><div class="sum-card acc">
    <div class="sc-label">Amount</div>
    <div class="sc-val">₹<?= number_format($totals['Amount'], 2) ?></div>
    <div class="sc-sub">Stock.Amount</div>
  </div></div>
  <div class="col"><div class="sum-card red">
    <div class="sc-label">Disc Amt</div>
    <div class="sc-val">₹<?= number_format($totals['SDisc_Amt'], 2) ?></div>
    <div class="sc-sub">Item disc + bill disc share</div>
  </div></div>
  <div class="col"><div class="sum-card grn">
    <div class="sc-label">Gross Amt</div>
    <div class="sc-val">₹<?= number_format($totals['GrossAmt'], 2) ?></div>
    <div class="sc-sub">ItemNetAmt</div>
  </div></div>
</div>

<div class="card shadow-sm">
<div class="card-body p-0">
<?php if ($queryErr): ?>
  <div class="alert alert-danger m-3"><strong>Query Error:</strong> <?= h($queryErr) ?>
    <details class="mt-2"><summary>SQL (debug)</summary><pre class="small mb-0" style="white-space:pre-wrap;"><?= h($detailSql) ?></pre></details>
  </div>
<?php elseif ($totalRows === 0): ?>
  <p class="text-center py-5 text-muted mb-0">No records found for the selected filters.</p>
<?php else: ?>

<div class="d-flex justify-content-between align-items-center px-3 pt-3 pb-2 flex-wrap gap-2">
  <span class="small text-muted">
    Showing <strong><?= number_format(($page-1)*$rowsPerPage+1) ?>–<?= number_format(min($page*$rowsPerPage,$totalRows)) ?></strong>
    of <strong><?= number_format($totalRows) ?></strong> lines
    &nbsp;|&nbsp; Page <strong><?= $page ?></strong> of <strong><?= $totalPages ?></strong>
  </span>
  <div class="d-flex align-items-center gap-2">
    <input type="text" id="tableSearch" placeholder="Quick search…" class="form-control form-control-sm" style="width:170px;" oninput="onSearch(this.value)">
    <button class="btn btn-success btn-sm" onclick="doCSV()">⬇ CSV</button>
    <button class="btn btn-danger btn-sm" id="pdfBtn" onclick="doPDF()">⬇ PDF</button>
  </div>
</div>

<div style="overflow-x:auto;">
<table class="table table-bordered table-sm mis mb-0" id="mainTbl">
  <thead class="table-dark small">
    <tr>
      <th>#</th>
      <th>Date</th>
      <th>Type</th>
      <th>VNo</th>
      <th>P. Code</th>
      <th>CompProdCode</th>
      <th>Company No</th>
      <th>Company</th>
      <th>Prod Group</th>
      <th>Category</th>
      <th>Description</th>
      <th>Color</th>
      <th>Size</th>
      <th>Inst1</th>
      <th>Inst2</th>
      <th>HSN</th>
      <th class="text-end">Qty</th>
      <th class="text-end">Rate</th>
      <th class="text-end">Amount</th>
      <th class="text-end">Disc %</th>
      <th class="text-end">Disc Amt</th>
      <th class="text-end">Gross Amt</th>
      <th>Rep Name</th>
    </tr>
  </thead>
  <tbody class="small" id="tBody">
  <?php
  $rn = ($page - 1) * $rowsPerPage + 1;
  $prevGrpKey = null;
  foreach ($rows as $row):
      $grpParts = [];
      for ($gi = 1; $gi <= 6; $gi++) {
          if ($grp[$gi] === '') continue;
          $gv = trim((string)($row["Grp$gi"] ?? ''));
          if ($gv !== '') $grpParts[] = ($groupingOptions[$grp[$gi]] ?? $grp[$gi]) . ': ' . $gv;
      }
      $grpKey = implode(' | ', $grpParts);
      if ($grpKey !== '' && $grpKey !== $prevGrpKey):
          $prevGrpKey = $grpKey;
  ?>
    <tr class="group-hdr"><td colspan="23"><?= h($grpKey) ?></td></tr>
  <?php endif;
      $qty = (float)$row['Qty'];
      $neg = $qty < 0 || (float)$row['Amount'] < 0;
      $cls = $neg ? 'num neg' : 'num';
  ?>
    <tr>
      <td class="text-muted"><?= $rn++ ?></td>
      <td><?= h(date('d-m-Y', strtotime((string)$row['V_Date']))) ?></td>
      <td><?= h((string)($row['V_Type'] ?? '')) ?></td>
      <td><?= h((string)($row['V_No'] ?? '')) ?></td>
      <td><?= h((string)($row['Product_Code'] ?? '')) ?></td>
      <td><?= h((string)($row['CompProdCode'] ?? '')) ?></td>
      <td><?= h((string)($row['Comp_No'] ?? '')) ?></td>
      <td><?= h((string)($row['Comp_Name'] ?? '')) ?></td>
      <td><?= h((string)($row['ProdGroup_Name'] ?? '')) ?></td>
      <td><?= h((string)($row['Category'] ?? '')) ?></td>
      <td><?= h((string)($row['Des_Name'] ?? '')) ?></td>
      <td><?= h((string)($row['Color_Name'] ?? '')) ?></td>
      <td><?= h((string)($row['Size'] ?? '')) ?></td>
      <td><?= h((string)($row['Inst1'] ?? '')) ?></td>
      <td><?= h((string)($row['Inst2'] ?? '')) ?></td>
      <td><?= h((string)($row['HSN'] ?? '')) ?></td>
      <td class="<?= $cls ?>"><?= number_format($qty, 3) ?></td>
      <td class="num"><?= number_format((float)$row['Rate'], 2) ?></td>
      <td class="<?= $cls ?>">₹<?= number_format((float)$row['Amount'], 2) ?></td>
      <td class="num"><?= number_format((float)$row['SDisc'], 2) ?></td>
      <td class="<?= $cls ?>">₹<?= number_format((float)$row['SDisc_Amt'], 2) ?></td>
      <td class="<?= $cls ?>">₹<?= number_format((float)$row['GrossAmt'], 2) ?></td>
      <td><?= h((string)($row['Rp_Name'] ?? '')) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
  <tfoot>
    <tr id="grandTotalRow">
      <td colspan="16"><strong>Grand Total (<?= number_format($totalRows) ?> lines)</strong></td>
      <td class="num"><?= number_format($totals['Qty'], 3) ?></td>
      <td></td>
      <td class="num">₹<?= number_format($totals['Amount'], 2) ?></td>
      <td></td>
      <td class="num">₹<?= number_format($totals['SDisc_Amt'], 2) ?></td>
      <td class="num">₹<?= number_format($totals['GrossAmt'], 2) ?></td>
      <td></td>
    </tr>
  </tfoot>
</table>
</div>

<?php if ($totalPages > 1): ?>
<div class="d-flex justify-content-between align-items-center px-3 py-2 flex-wrap gap-2 border-top">
  <nav>
    <ul class="pagination pagination-sm mb-0">
      <li class="page-item <?= $page<=1?'disabled':'' ?>"><a class="page-link" href="<?= h(pageUrl(1)) ?>">«</a></li>
      <li class="page-item <?= $page<=1?'disabled':'' ?>"><a class="page-link" href="<?= h(pageUrl($page-1)) ?>">‹</a></li>
      <?php
      $s=max(1,$page-2); $e=min($totalPages,$s+4); if ($e-$s<4) $s=max(1,$e-4);
      if ($s>1) echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
      for ($pg=$s;$pg<=$e;$pg++): ?>
        <li class="page-item <?= $pg===$page?'active':'' ?>"><a class="page-link" href="<?= h(pageUrl($pg)) ?>"><?= $pg ?></a></li>
      <?php endfor;
      if ($e<$totalPages) echo '<li class="page-item disabled"><span class="page-link">…</span></li>'; ?>
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
var TOTALS = <?= json_encode([
    'Qty' => round($totals['Qty'], 3),
    'Amount' => round($totals['Amount'], 2),
    'SDisc_Amt' => round($totals['SDisc_Amt'], 2),
    'GrossAmt' => round($totals['GrossAmt'], 2),
    'totalRows' => $totalRows,
], JSON_NUMERIC_CHECK) ?>;
var DATE_FROM = <?= json_encode($from) ?>;
var DATE_TO   = <?= json_encode($to) ?>;
var GROUP_BY  = <?= json_encode($activeGroupLabel) ?>;
var GROUPING_OPTIONS = <?= $jsGroupingOptions ?>;
var WHATSAPP_OWNER = <?= $whatsappOwner ? 'true' : 'false' ?>;

function showMisTab(id) {
    document.querySelectorAll('.tab-pane').forEach(function(p){ p.classList.remove('active'); });
    document.querySelectorAll('.tab-btn').forEach(function(b){ b.classList.remove('active'); });
    var pane = document.getElementById('tab-' + id);
    if (pane) pane.classList.add('active');
    var btn = document.querySelector('.tab-btn[data-tab="'+id+'"]');
    if (btn) btn.classList.add('active');
}

function toggleMs(id) {
    var menu = document.getElementById(id + '-menu');
    if (!menu) return;
    var isOpen = menu.classList.contains('show');
    document.querySelectorAll('.ms-menu.show').forEach(function(m){ m.classList.remove('show'); });
    if (!isOpen) menu.classList.add('show');
}
document.addEventListener('click', function(e) {
    if (!e.target.closest('.ms-dropdown')) {
        document.querySelectorAll('.ms-menu.show').forEach(function(m){ m.classList.remove('show'); });
    }
});
function msSelectAll(id, select) {
    var ms = document.getElementById(id);
    if (!ms) return;
    ms.querySelectorAll('.ms-item:not([style*="display: none"]) input[type=checkbox]').forEach(function(cb){ cb.checked = select; });
    updateMsTrigger(id);
}
function msFilter(id, q) {
    q = (q || '').toLowerCase().trim();
    var ms = document.getElementById(id);
    if (!ms) return;
    ms.querySelectorAll('.ms-item').forEach(function(item) {
        var lbl = (item.querySelector('label') || {}).innerText || '';
        item.style.display = (!q || lbl.toLowerCase().includes(q)) ? '' : 'none';
    });
}
function updateMsTrigger(id) {
    var ms = document.getElementById(id);
    if (!ms) return;
    var checked = ms.querySelectorAll('input[type=checkbox]:checked');
    var textEl  = ms.querySelector('.ms-trigger .ms-text');
    var badgeEl = ms.querySelector('.ms-trigger .ms-badge');
    var placeholders = {
        'ms-cat':'All Categories','ms-cmp':'All Companies','ms-sz':'All Sizes',
        'ms-col':'All Colors','ms-desc':'All Descriptions','ms-pg':'All Product Groups',
        'ms-cn':'All Company No','ms-rep':'All Representatives','ms-prod':'All Products'
    };
    if (checked.length > 0) {
        var labels = Array.from(checked).slice(0,2).map(function(cb){
            return (cb.closest('.ms-item').querySelector('label') || {}).innerText || cb.value;
        });
        if (textEl) textEl.textContent = labels.join(', ') + (checked.length > 2 ? '…' : '');
        if (badgeEl) { badgeEl.textContent = checked.length; badgeEl.style.display = ''; }
        else {
            var b = document.createElement('span');
            b.className = 'ms-badge'; b.textContent = checked.length;
            ms.querySelector('.ms-trigger').appendChild(b);
        }
    } else {
        if (textEl) textEl.textContent = placeholders[id] || 'Select…';
        if (badgeEl) badgeEl.style.display = 'none';
    }
}
document.querySelectorAll('.ms-item input[type=checkbox]').forEach(function(cb){
    cb.addEventListener('change', function(){
        var ms = this.closest('.ms-dropdown');
        if (ms) updateMsTrigger(ms.id);
    });
});

function refreshGroupCheckboxes() {
    for (var i = 1; i <= 6; i++) {
        var chkDiv = document.getElementById('grp_chk_col_' + i);
        if (!chkDiv) continue;
        var nextVal = '';
        if (i < 6) {
            var nextEl = document.getElementById('group_sel_' + (i + 1));
            nextVal = nextEl ? nextEl.value : '';
        }
        var show = (i < 6) && nextVal !== '';
        chkDiv.classList.toggle('show', show);
        if (!show) { var ck = document.getElementById('subtot_' + i); if (ck) ck.checked = false; }
        if (show) {
            var curEl = document.getElementById('group_sel_' + i);
            var curVal = curEl ? curEl.value : '';
            var lbl = curVal !== '' ? (GROUPING_OPTIONS[curVal] || curVal) : '—';
            var lblEl = document.getElementById('subtot_lbl_' + i);
            if (lblEl) lblEl.textContent = lbl + '-wise Total?';
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

function resetAllFilters() {
    var y = new Date().getFullYear();
    document.querySelector('[name=from_date]').value = y + '-04-01';
    document.querySelector('[name=to_date]').value   = new Date().toISOString().slice(0,10);
    for (var i=1;i<=6;i++) {
        var el=document.getElementById('group_sel_'+i);
        if(el) el.value = '';
        var ck=document.getElementById('subtot_'+i); if(ck) ck.checked=false;
    }
    refreshGroupCheckboxes();
    document.querySelector('[name=gstn_filter][value=all]').checked=true;
    document.querySelector('[name=type_filter][value=all]').checked=true;
    document.querySelector('[name=pay_mode][value=all]').checked=true;
    ['only_sale_return','only_discount','without_amount','without_disc_pct','without_representative','whatsapp_owner'].forEach(function(n){
        var el=document.querySelector('[name='+n+']'); if(el) el.checked=false;
    });
    document.querySelectorAll('.ms-item input[type=checkbox]').forEach(function(cb){ cb.checked=false; });
    ['ms-cat','ms-cmp','ms-sz','ms-col','ms-desc','ms-pg','ms-cn','ms-rep','ms-prod'].forEach(updateMsTrigger);
}

function saveTemplate() {
    var fd = new FormData(document.getElementById('filterForm'));
    var obj = {};
    fd.forEach(function(v,k){
        if (k.endsWith('[]')) {
            k = k.slice(0,-2);
            if (!obj[k]) obj[k] = [];
            obj[k].push(v);
        } else {
            obj[k] = v;
        }
    });
    localStorage.setItem('mis_sbr_template', JSON.stringify(obj));
    alert('Template saved in this browser.');
}
function openTemplate() {
    var raw = localStorage.getItem('mis_sbr_template');
    if (!raw) { alert('No saved template found.'); return; }
    try {
        var obj = JSON.parse(raw);
        Object.keys(obj).forEach(function(k){
            if (k === 'csrf_token') return;
            var val = obj[k];
            if (Array.isArray(val)) {
                document.querySelectorAll('[name="'+k+'[]"]').forEach(function(cb){
                    cb.checked = val.indexOf(cb.value) !== -1;
                });
            } else {
                var els = document.querySelectorAll('[name="'+k+'"]');
                els.forEach(function(el){
                    if (el.type === 'radio' || el.type === 'checkbox') {
                        el.checked = (el.value == val) || (el.type==='checkbox' && (val==='1'||val===1||val===true));
                    } else {
                        el.value = val;
                    }
                });
            }
        });
        ['ms-cat','ms-cmp','ms-sz','ms-col','ms-desc','ms-pg','ms-cn','ms-rep','ms-prod'].forEach(updateMsTrigger);
        refreshGroupCheckboxes();
        alert('Template loaded. Click Print / Run Report.');
    } catch (e) {
        alert('Invalid template data.');
    }
}
function whatsAppOwner() {
    if (!WHATSAPP_OWNER && !document.querySelector('[name=whatsapp_owner]')?.checked) {
        alert('Enable “WhatsApp To Owner” then run report.');
        return;
    }
    var msg = encodeURIComponent(
        'MIS Sale Bill Register\nPeriod: '+DATE_FROM+' to '+DATE_TO+
        '\nQty: '+TOTALS.Qty+
        '\nAmount: '+TOTALS.Amount+
        '\nDisc: '+TOTALS.SDisc_Amt+
        '\nGross: '+TOTALS.GrossAmt+
        '\nLines: '+TOTALS.totalRows
    );
    window.open('https://wa.me/?text='+msg, '_blank');
}

function onSearch(q) {
    q = (q||'').toLowerCase().trim();
    document.querySelectorAll('#tBody tr').forEach(function(tr){
        if (tr.classList.contains('group-hdr')) return;
        var t = tr.innerText || '';
        tr.style.display = (!q || t.toLowerCase().includes(q)) ? '' : 'none';
    });
}

function doCSV() {
    if (!TOTALS.totalRows) { alert('Pehle Print / Run Report click karein.'); return; }
    var rows = document.querySelectorAll('#mainTbl tbody tr');
    var csv = ['#,Date,Type,VNo,PCode,CompProdCode,CompanyNo,Company,ProdGroup,Category,Description,Color,Size,Inst1,Inst2,HSN,Qty,Rate,Amount,Disc%,DiscAmt,GrossAmt,Rep'];
    rows.forEach(function(tr){
        if (tr.classList.contains('group-hdr') || tr.style.display==='none') return;
        var cells = tr.querySelectorAll('td');
        if (!cells.length) return;
        csv.push(Array.from(cells).map(function(td){ return '"'+td.innerText.trim().replace(/"/g,'""')+'"'; }).join(','));
    });
    csv.push('"","","","","","","","","","","","","","","","GRAND TOTAL","'+TOTALS.Qty+'","","'+TOTALS.Amount+'","","'+TOTALS.SDisc_Amt+'","'+TOTALS.GrossAmt+'",""');
    var a=document.createElement('a');
    a.href='data:text/csv;charset=utf-8,\uFEFF'+encodeURIComponent(csv.join('\n'));
    a.download='MIS_Sale_Bill_Register_'+DATE_FROM+'_to_'+DATE_TO+'.csv';
    a.click();
}

function doPDF() {
    if (!TOTALS.totalRows) { alert('Pehle Print / Run Report click karein.'); return; }
    var btn=document.getElementById('pdfBtn'); btn.disabled=true; btn.textContent='Generating…';
    var jsPDF=window.jspdf.jsPDF;
    var doc=new jsPDF({orientation:'landscape',unit:'mm',format:'a4'});
    var pw=doc.internal.pageSize.getWidth();
    doc.setFont('helvetica','bold'); doc.setFontSize(13);
    doc.text('MIS (Company-wise Sale Bill Register)', pw/2, 12, {align:'center'});
    doc.setFont('helvetica','normal'); doc.setFontSize(8);
    doc.text('Period: '+DATE_FROM+' to '+DATE_TO+' | Group: '+GROUP_BY+' | Lines: '+TOTALS.totalRows, pw/2, 18, {align:'center'});
    var head=[['Date','Type','VNo','P.Code','Company','Category','Desc','Qty','Rate','Amount','Disc%','DiscAmt','Gross','Rep']];
    var body=[];
    document.querySelectorAll('#mainTbl tbody tr').forEach(function(tr){
        if (tr.style.display==='none' || tr.classList.contains('group-hdr')) return;
        var c=tr.querySelectorAll('td');
        if (c.length < 23) return;
        body.push([c[1].innerText,c[2].innerText,c[3].innerText,c[4].innerText,c[7].innerText,c[9].innerText,c[10].innerText,c[16].innerText,c[17].innerText,c[18].innerText,c[19].innerText,c[20].innerText,c[21].innerText,c[22].innerText]);
    });
    body.push(['','','','','','','GRAND TOTAL', String(TOTALS.Qty), '', String(TOTALS.Amount), '', String(TOTALS.SDisc_Amt), String(TOTALS.GrossAmt), '']);
    doc.autoTable({
        startY:22, head:head, body:body, theme:'grid',
        styles:{fontSize:6.5, cellPadding:1.5},
        headStyles:{fillColor:[26,92,255], textColor:255, fontStyle:'bold'},
        didParseCell:function(d){
            if (d.section==='body' && d.row.index===d.table.body.length-1) {
                d.cell.styles.fillColor=[33,37,41]; d.cell.styles.textColor=255; d.cell.styles.fontStyle='bold';
            }
        }
    });
    doc.save('MIS_Sale_Bill_Register_'+DATE_FROM+'_to_'+DATE_TO+'.pdf');
    btn.disabled=false; btn.textContent='⬇ PDF';
}

(function(){ refreshGroupCheckboxes(); })();
</script>

<?php include '../includes/footer.php'; ?>
