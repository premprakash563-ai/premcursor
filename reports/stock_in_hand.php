<?php
/**
 * Stock In Hand Report
 * Form: MIS-style tabs + dynamic multiselect filters
 * Query: Sale stock R/I as-on-date with optional non-zero balance
 * Path: sale/stock_in_hand.php
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
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

/* ── Helpers (guard against redeclare) ─────────────────────────────────────── */
if (!function_exists('sih_fetchAll')) {
    function sih_fetchAll($db, $sql, $params = array()) {
        try {
            $stmt = $db->prepare($sql);
            if ($stmt === false) return array();
            if ($params) {
                $types = str_repeat('s', count($params));
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : array();
            $stmt->close();
            return $rows;
        } catch (Throwable $e) {
            return array();
        }
    }
}
if (!function_exists('h')) {
    function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('sih_renderMs')) {
    function sih_renderMs($id, $label, $items, $valueKey, $labelKey, $name, $selected, $placeholderAll) {
        $count = count($selected);
        echo '<div class="fg-col-ms">';
        echo '<span class="fl">' . h($label) . '</span>';
        echo '<div class="ms-dropdown" id="' . h($id) . '">';
        echo '<div class="ms-trigger" onclick="toggleMs(\'' . h($id) . '\')" data-placeholder="' . h($placeholderAll) . '">';
        if ($count) {
            $labels = array(); $shown = 0;
            foreach ($items as $it) {
                if (in_array((string)$it[$valueKey], $selected, true)) {
                    $labels[] = (string)$it[$labelKey];
                    if (++$shown >= 2) break;
                }
            }
            echo '<span class="ms-text">' . h(implode(', ', $labels)) . ($count > 2 ? '…' : '') . '</span>';
            echo '<span class="ms-badge">' . $count . '</span>';
        } else {
            echo '<span class="ms-text">' . h($placeholderAll) . '</span>';
        }
        echo '</div><div class="ms-menu" id="' . h($id) . '-menu">';
        echo '<div class="ms-search"><input type="text" placeholder="Search…" oninput="msFilter(\'' . h($id) . '\',this.value)"></div>';
        if (empty($items)) {
            echo '<p style="font-size:10.5px;color:#888;padding:6px 10px;">No options found.</p>';
        }
        foreach ($items as $it) {
            $val = (string)$it[$valueKey];
            $lbl = (string)$it[$labelKey];
            $chk = in_array($val, $selected, true) ? 'checked' : '';
            $uid = $id . '_' . md5($val);
            echo '<div class="ms-item"><input type="checkbox" name="' . h($name) . '[]" value="' . h($val) . '" id="' . h($uid) . '" ' . $chk . '><label for="' . h($uid) . '">' . h($lbl) . '</label></div>';
        }
        echo '<div class="ms-actions">';
        echo '<button type="button" class="btn btn-outline-secondary btn-xs" onclick="msSelectAll(\'' . h($id) . '\',true)">All</button>';
        echo '<button type="button" class="btn btn-outline-secondary btn-xs" onclick="msSelectAll(\'' . h($id) . '\',false)">None</button>';
        echo '<button type="button" class="btn btn-primary btn-xs" onclick="toggleMs(\'' . h($id) . '\')">Done</button>';
        echo '</div></div></div></div>';
    }
}

/* ── Dynamic master lists ──────────────────────────────────────────────────── */
$productGroups = sih_fetchAll($db, "SELECT ProdGroup_Code, ProdGroup_Name FROM productgroup ORDER BY ProdGroup_Name");
$categories    = sih_fetchAll($db, "SELECT C_code AS C_Code, c_name AS C_Name FROM category ORDER BY c_name");
if (empty($categories)) {
    $categories = sih_fetchAll($db, "SELECT C_Code, C_Name FROM Category ORDER BY C_Name");
}
$companies     = sih_fetchAll($db, "SELECT Comp_code AS Comp_Code, comp_name AS Comp_Name FROM compdetail ORDER BY comp_name");
if (empty($companies)) {
    $companies = sih_fetchAll($db, "SELECT Comp_Code, Comp_Name FROM CompDetail ORDER BY Comp_Name");
}
$colors        = sih_fetchAll($db, "SELECT Color_code AS Color_Code, Color_name AS Color_Name FROM color ORDER BY Color_name");
$descriptions  = sih_fetchAll($db, "SELECT Des_Code, Des_Name FROM description ORDER BY Des_Name");
if (empty($descriptions)) {
    $descriptions = sih_fetchAll($db, "SELECT DISTINCT PRODUCT_NAME AS Des_Code, PRODUCT_NAME AS Des_Name FROM product WHERE IFNULL(PRODUCT_NAME,'')<>'' ORDER BY PRODUCT_NAME");
}
$companyNos    = sih_fetchAll($db, "SELECT Comp_No FROM product WHERE IFNULL(Comp_No,'') <> '' GROUP BY Comp_No ORDER BY Comp_No");
$sizes         = sih_fetchAll($db, "SELECT s_code AS S_Code, s_name AS S_Name FROM sizemaster ORDER BY s_name");
$sp1List       = sih_fetchAll($db, "SELECT s_code AS S_Code, s_name AS S_Name FROM specialinst ORDER BY s_name");
$sp2List       = sih_fetchAll($db, "SELECT s_code AS S_Code, s_name AS S_Name FROM specialinst1 ORDER BY s_name");
$sp3List       = sih_fetchAll($db, "SELECT s_code AS S_Code, s_name AS S_Name FROM specialinst2 ORDER BY s_name");
$taxRates      = sih_fetchAll($db, "SELECT DISTINCT IFNULL(Tax,0) AS Tax FROM product ORDER BY Tax");
$productCodes  = sih_fetchAll($db, "SELECT PRODUCT_CODE AS Product_Code FROM product ORDER BY PRODUCT_CODE LIMIT 5000");
$compProdCodes = sih_fetchAll($db, "SELECT DISTINCT CompProdCode FROM product WHERE IFNULL(CompProdCode,'') <> '' ORDER BY CompProdCode LIMIT 5000");
$parties       = $companies; // Party reuses CompDetail until separate master is named
$cities        = sih_fetchAll($db, "SELECT City_Code, City_Name FROM citymaster ORDER BY City_Name");
if (empty($cities)) {
    $cities = sih_fetchAll($db, "SELECT City_Code, City_Name FROM CityMaster ORDER BY City_Name");
}

$groupingFieldOptions = array(
    ''            => '— None —',
    'ProdGroup'   => 'Product Group',
    'Category'    => 'Category',
    'Company'     => 'Company',
    'Color'       => 'Color',
    'Description' => 'Description',
    'CompanyNo'   => 'Company No',
);

/* ── Read filters ──────────────────────────────────────────────────────────── */
$posted = ($_SERVER['REQUEST_METHOD'] === 'POST');
$err = null;
if ($posted && (empty($_POST['csrf_token']) || !hash_equals($csrfToken, $_POST['csrf_token']))) {
    $posted = false;
    $err = 'Form expired, please resubmit.';
}

function sih_pArr($key) {
    return (isset($_POST[$key]) && is_array($_POST[$key])) ? array_map('strval', $_POST[$key]) : array();
}
function sih_pStr($key, $default = '') {
    return isset($_POST[$key]) ? trim((string)$_POST[$key]) : $default;
}

$f = array(
    'as_on_date'     => sih_pStr('as_on_date', date('Y-m-d')),
    'no_of_days'     => sih_pStr('no_of_days'),
    'prod_group'     => sih_pArr('prod_group'),
    'category'       => sih_pArr('category'),
    'company'        => sih_pArr('company'),
    'color'          => sih_pArr('color'),
    'description'    => sih_pArr('description'),
    'size'           => sih_pArr('size'),
    'company_no'     => sih_pArr('company_no'),
    'sp1'            => sih_pArr('sp1'),
    'sp2'            => sih_pArr('sp2'),
    'sp3'            => sih_pArr('sp3'),
    'tax'            => sih_pArr('tax'),
    'party'          => sih_pArr('party'),
    'city'           => sih_pArr('city'),
    'product_code'   => sih_pArr('product_code'),
    'comp_prod_code' => sih_pArr('comp_prod_code'),
    'refno_contains' => sih_pStr('refno_contains'),
    'type_filter'    => sih_pStr('type_filter', 'All'),
    'stock_status'   => sih_pStr('stock_status', 'All'),
    'valuation_req'  => sih_pStr('valuation_req', 'Yes'),
    'valuation_on'   => sih_pStr('valuation_on', 'MRP'),
    'rate_from'      => sih_pStr('rate_from'),
    'rate_to'        => sih_pStr('rate_to'),
    'whatsapp_owner' => !empty($_POST['whatsapp_owner']),
    'cols'           => sih_pArr('cols'),
);

// Grouping levels (same names as MIS: group_1 .. group_6)
$validGroups = array('ProdGroup','Category','Company','Color','Description','CompanyNo','');
$grp = array();
$subTotalReq = array();
for ($i = 1; $i <= 6; $i++) {
    // accept both group_N (MIS) and legacy grpN
    $val = sih_pStr("group_$i", sih_pStr('grp' . $i, ''));
    $grp[$i] = in_array($val, $validGroups, true) ? $val : '';
    $subTotalReq[$i] = !empty($_POST["subtot_$i"]);
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $f['as_on_date'])) {
    $f['as_on_date'] = date('Y-m-d');
}

$defaultCols = array('CompanyNo','Company','Category','Description','Color','Size','Inst3','ProdGroup','Party','RefNo','RefDate');
$activeCols  = $posted && !empty($f['cols']) ? $f['cols'] : $defaultCols;

$colLabels = array(
    'CompanyNo' => 'Company No', 'Company' => 'Company', 'Category' => 'Category',
    'Description' => 'Description', 'Color' => 'Color', 'Size' => 'Size',
    'Inst3' => 'Sp Instruction3', 'ProdGroup' => 'Product Group', 'Party' => 'Party',
    'RefNo' => 'Ref No.', 'RefDate' => 'Ref Date',
);
$colField = array(
    'CompanyNo' => 'Comp_No', 'Company' => 'Comp_Name', 'Category' => 'C_Name',
    'Description' => 'Des_Name', 'Color' => 'Color_Name', 'Size' => 'Size_Name',
    'Inst3' => 'Inst3', 'ProdGroup' => 'ProdGroup_Name', 'Party' => 'Sub_Name',
    'RefNo' => 'RefNo', 'RefDate' => 'RefDate',
);

/* ── Grouping SQL expressions (Grp1..Grp6) ─────────────────────────────────── */
$groupExprMap = array(
    'ProdGroup'   => "UPPER(TRIM(IFNULL(pg.ProdGroup_Name,'')))",
    'Category'    => "UPPER(TRIM(IFNULL(cat.c_name, IFNULL(cat.C_Name,''))))",
    'Company'     => "UPPER(TRIM(IFNULL(cd.comp_name, IFNULL(cd.Comp_Name,''))))",
    'Color'       => "UPPER(TRIM(IFNULL(col.Color_name, IFNULL(col.Color_Name,''))))",
    'Description' => "UPPER(TRIM(IFNULL(p.PRODUCT_NAME,'')))",
    'CompanyNo'   => "UPPER(TRIM(IFNULL(p.comp_no, IFNULL(p.Comp_No,''))))",
);
$grpKeys = array();
for ($i = 1; $i <= 6; $i++) $grpKeys[] = $grp[$i];
$grpSelect = array();
for ($i = 0; $i < 6; $i++) {
    $k = $grpKeys[$i];
    $n = $i + 1;
    if ($k !== '' && isset($groupExprMap[$k])) {
        $grpSelect[] = $groupExprMap[$k] . " AS Grp$n";
    } else {
        $grpSelect[] = "'' AS Grp$n";
    }
}
$grpSelectSql = implode(",\n            ", $grpSelect);

/* ── Report query ──────────────────────────────────────────────────────────── */
$rows = array();
$grandStock = 0.0;
$grandValue = 0.0;

if ($posted) {
    // Rate basis — Sale_Rate is in reference query; other bases fall back if columns exist
    $rateSql = 'IFNULL(p.Sale_Rate,0)';
    if ($f['valuation_req'] === 'No' || $f['valuation_on'] === 'None') {
        $rateSql = '0';
    }

    // Description: PRODUCT_NAME (Desc master may be absent on some DBs)
    $desExpr = "TRIM(IFNULL(p.PRODUCT_NAME,''))";

    $sql = "
        SELECT
            $grpSelectSql,
            TRIM(p.PRODUCT_CODE) AS Product_Code,
            TRIM(IFNULL(p.CompProdCode,'')) AS CompProdCode,
            TRIM(IFNULL(p.comp_no,'')) AS Comp_No,
            TRIM(IFNULL(cd.comp_name,'')) AS Comp_Name,
            TRIM(IFNULL(cat.c_name,'')) AS C_Name,
            $desExpr AS Des_Name,
            TRIM(IFNULL(col.Color_name,'')) AS Color_Name,
            TRIM(IFNULL(sz.s_name,'')) AS Size_Name,
            TRIM(IFNULL(si1.s_name,'')) AS Inst1,
            TRIM(IFNULL(si2.s_name,'')) AS Inst2,
            TRIM(IFNULL(si3.s_name,'')) AS Inst3,
            TRIM(IFNULL(pg.ProdGroup_Name,'')) AS ProdGroup_Name,
            TRIM(IFNULL(sg.Sub_Name,'')) AS Sub_Name,
            TRIM(IFNULL(p.RefNo,'')) AS RefNo,
            p.RefDate AS RefDate,
            ($rateSql) AS Rate,
            IFNULL(SUM(CASE
                WHEN IFNULL(s.ADV_STATUS,'N') = 'N' AND s.Stk_Type = 'R' THEN s.Qty
                WHEN IFNULL(s.ADV_STATUS,'N') = 'N' AND s.Stk_Type = 'I' THEN -s.Qty
            END),0) AS Stock,
            DATEDIFF(?, IFNULL(p.As_On_Date, IFNULL(p.RefDate, s.V_DATE))) AS BarcDays,
            (IFNULL(p.Tax,0) + IFNULL(p.SSat_Per,0)) AS SaleTaxPer
        FROM stock s
        LEFT JOIN type ty ON s.V_TYPE = ty.V_TYPE
        LEFT JOIN product p ON s.PROD_CODE = p.PRODUCT_CODE
        LEFT JOIN subgroup sg ON sg.Subcode = p.Subcode AND sg.GROUP_CODE = p.Code
        LEFT JOIN category cat ON p.CAT_CODE = cat.C_code
        LEFT JOIN compdetail cd ON p.COMP_CODE = cd.Comp_code
        LEFT JOIN sizemaster sz ON p.SIZE_CODE = sz.s_code
        LEFT JOIN color col ON p.COLOR_CODE = col.Color_code
        LEFT JOIN specialinst si1 ON p.INST1_CODE = si1.s_code
        LEFT JOIN specialinst1 si2 ON p.INST2_CODE = si2.s_code
        LEFT JOIN specialinst2 si3 ON p.INST3_CODE = si3.s_code
        LEFT JOIN citymaster cm ON sg.City_Code = cm.City_Code
        LEFT JOIN productgroup pg ON p.ProdGroup_Code = pg.ProdGroup_Code
        WHERE IFNULL(p.PRODUCT_CODE,'') <> ''
          AND s.V_DATE <= ?
          AND s.Stk_Type IN ('R','I')
          AND IFNULL(s.ADV_STATUS,'N') = 'N'
          AND IFNULL(p.STOCK_YN,'Y') = 'Y'
    ";

    // params: BarcDays date, as_on_date
    $params = array($f['as_on_date'], $f['as_on_date']);

    $addIn = function ($column, $values) use (&$sql, &$params) {
        if (empty($values)) return;
        $ph = implode(',', array_fill(0, count($values), '?'));
        $sql .= " AND {$column} IN ($ph)";
        foreach ($values as $v) $params[] = $v;
    };

    $addIn('p.ProdGroup_Code', $f['prod_group']);
    $addIn('p.CAT_CODE',       $f['category']);
    $addIn('p.COMP_CODE',      $f['company']);
    $addIn('p.COLOR_CODE',     $f['color']);
    $addIn('p.PRODUCT_NAME',   $f['description']);
    $addIn('p.SIZE_CODE',      $f['size']);
    $addIn('p.comp_no',        $f['company_no']);
    $addIn('p.INST1_CODE',     $f['sp1']);
    $addIn('p.INST2_CODE',     $f['sp2']);
    $addIn('p.INST3_CODE',     $f['sp3']);
    $addIn('p.Tax',            $f['tax']);
    $addIn('p.COMP_CODE',      $f['party']);
    $addIn('sg.City_Code',     $f['city']);
    $addIn('p.PRODUCT_CODE',   $f['product_code']);
    $addIn('p.CompProdCode',   $f['comp_prod_code']);

    if ($f['refno_contains'] !== '') {
        $sql .= ' AND IFNULL(p.RefNo,\'\') LIKE ?';
        $params[] = '%' . $f['refno_contains'] . '%';
    }

    // Non-zero / positive balance (matches reference subquery filter)
    if ($f['stock_status'] === 'NonZero' || $f['stock_status'] === 'Positive') {
        $sql .= "
          AND ROUND((
                SELECT IFNULL(SUM(sx.Qty),0) FROM stock sx
                WHERE sx.PROD_CODE = p.PRODUCT_CODE AND sx.Stk_Type = 'R'
                  AND IFNULL(sx.ADV_STATUS,'N') = 'N' AND sx.V_DATE <= ?
              ) - (
                SELECT IFNULL(SUM(sx.Qty),0) FROM stock sx
                WHERE sx.PROD_CODE = p.PRODUCT_CODE AND sx.Stk_Type = 'I'
                  AND IFNULL(sx.ADV_STATUS,'N') = 'N' AND sx.V_DATE <= ?
              ), 3) " . ($f['stock_status'] === 'Positive' ? '> 0' : '<> 0');
        $params[] = $f['as_on_date'];
        $params[] = $f['as_on_date'];
    }

    $sql .= "
        GROUP BY
            p.PRODUCT_CODE, p.CompProdCode, p.comp_no, cd.comp_name, cat.c_name,
            p.PRODUCT_NAME, col.Color_name, sz.s_name, si1.s_name, si2.s_name, si3.s_name,
            pg.ProdGroup_Name, sg.Sub_Name, p.RefNo, p.RefDate, p.As_On_Date, p.Tax, p.SSat_Per,
            p.Sale_Rate
    ";

    $havingParts = array();
    if ($f['no_of_days'] !== '' && is_numeric($f['no_of_days'])) {
        $havingParts[] = 'BarcDays >= ?';
        $params[] = (string)(int)$f['no_of_days'];
    }
    if (!empty($havingParts)) {
        $sql .= ' HAVING ' . implode(' AND ', $havingParts);
    }

    $sql .= ' ORDER BY Grp1, Grp2, Grp3, Grp4, Grp5, Grp6, Product_Code';

    try {
        $stmt = $db->prepare($sql);
        if ($stmt === false) {
            throw new RuntimeException($db->error);
        }
        if ($params) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : array();
        $stmt->close();
    } catch (Throwable $e) {
        $err = 'Query failed: ' . $e->getMessage();
        $rows = array();
    }

    // Rate range filter in PHP
    if (!$err && ($f['rate_from'] !== '' || $f['rate_to'] !== '')) {
        $from = $f['rate_from'] !== '' ? (float)$f['rate_from'] : null;
        $to   = $f['rate_to']   !== '' ? (float)$f['rate_to']   : null;
        $filtered = array();
        foreach ($rows as $r) {
            $rate = (float)$r['Rate'];
            if ($from !== null && $rate < $from) continue;
            if ($to !== null && $rate > $to) continue;
            $filtered[] = $r;
        }
        $rows = $filtered;
    }

    foreach ($rows as $r) {
        $grandStock += (float)$r['Stock'];
        $grandValue += (float)$r['Stock'] * (float)$r['Rate'];
    }
}

/* ── Nested grouping tree ──────────────────────────────────────────────────── */
$groupFieldMap = array(
    'ProdGroup'   => 'ProdGroup_Name',
    'Category'    => 'C_Name',
    'Company'     => 'Comp_Name',
    'Color'       => 'Color_Name',
    'Description' => 'Des_Name',
    'CompanyNo'   => 'Comp_No',
);
$activeGroupings = array();
$activeSubtots = array(); // parallel to activeGroupings: whether that level wants subtotal
foreach ($grpKeys as $idx => $g) {
    if ($g !== '' && isset($groupFieldMap[$g])) {
        $activeGroupings[] = $g;
        $origLevel = $idx + 1; // group_1 = index 0
        $activeSubtots[] = !empty($subTotalReq[$origLevel]);
    }
}

if (!function_exists('sih_buildTree')) {
    function sih_buildTree($rows, $groupKeys, $groupFieldMap) {
        if (empty($groupKeys)) return array('__rows__' => $rows);
        $key = $groupFieldMap[$groupKeys[0]];
        $buckets = array();
        foreach ($rows as $r) {
            $label = (isset($r[$key]) && $r[$key] !== '' && $r[$key] !== null) ? $r[$key] : '(Blank)';
            $buckets[$label][] = $r;
        }
        ksort($buckets);
        $tree = array();
        foreach ($buckets as $label => $bucketRows) {
            $tree[$label] = sih_buildTree($bucketRows, array_slice($groupKeys, 1), $groupFieldMap);
        }
        return $tree;
    }
}
if (!function_exists('sih_renderTree')) {
    function sih_renderTree($node, $depth, $activeCols, $colField, $colLabels, $activeSubtots = array()) {
        $stockSum = 0.0; $valueSum = 0.0;
        if (isset($node['__rows__'])) {
            echo '<table class="table table-bordered table-sm sr mb-3"><thead class="table-dark small"><tr>';
            foreach ($activeCols as $c) echo '<th>' . h(isset($colLabels[$c]) ? $colLabels[$c] : $c) . '</th>';
            echo '<th>PCode</th><th>CompProdCode</th><th class="num">Stock</th><th class="num">Rate</th><th class="num">Value</th><th class="num">Days</th><th class="num">Gst%</th></tr></thead><tbody class="small">';
            foreach ($node['__rows__'] as $r) {
                $value = (float)$r['Stock'] * (float)$r['Rate'];
                $stockSum += (float)$r['Stock'];
                $valueSum += $value;
                echo '<tr>';
                foreach ($activeCols as $c) {
                    $field = isset($colField[$c]) ? $colField[$c] : null;
                    echo '<td>' . h($field && isset($r[$field]) ? $r[$field] : '') . '</td>';
                }
                echo '<td>' . h($r['Product_Code']) . '</td>';
                echo '<td>' . h($r['CompProdCode']) . '</td>';
                echo '<td class="num">' . number_format((float)$r['Stock'], 3) . '</td>';
                echo '<td class="num">' . number_format((float)$r['Rate'], 2) . '</td>';
                echo '<td class="num">' . number_format($value, 2) . '</td>';
                echo '<td class="num">' . h(isset($r['BarcDays']) ? $r['BarcDays'] : '') . '</td>';
                echo '<td class="num">' . h($r['SaleTaxPer']) . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
            return array($stockSum, $valueSum);
        }
        foreach ($node as $label => $child) {
            echo '<div class="grp-block" style="margin-left:' . ($depth * 14) . 'px;">';
            echo '<div class="grp-head">' . str_repeat('— ', $depth) . h($label) . '</div>';
            $res = sih_renderTree($child, $depth + 1, $activeCols, $colField, $colLabels, $activeSubtots);
            $s = $res[0]; $v = $res[1];
            $stockSum += $s; $valueSum += $v;
            $isLast = ($depth >= count($activeSubtots) - 1);
            $showSub = $isLast || !empty($activeSubtots[$depth]);
            if ($showSub) {
                echo '<div class="grp-sub">Subtotal — Stock: ' . number_format($s, 3) . ' &nbsp; Value: ' . number_format($v, 2) . '</div>';
            }
            echo '</div>';
        }
        return array($stockSum, $valueSum);
    }
}

$tree = ($posted && !$err) ? sih_buildTree($rows, $activeGroupings, $groupFieldMap) : array();
$jsGroupingOptions = json_encode($groupingFieldOptions, JSON_UNESCAPED_UNICODE);

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
.grp-subtot { display:none; margin-top:3px; align-items:center; gap:4px; }
.grp-subtot.show { display:flex; }
.date-layout { display:flex; gap:12px; flex-wrap:wrap; align-items:flex-start; }
.date-main { flex:1 1 620px; min-width:280px; }
.date-cols { flex:0 0 200px; background:var(--bg-panel); border:1px solid #e5e7eb; border-radius:6px; padding:10px 12px; }
.date-cols .ob-title { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#9ca3af; margin-bottom:8px; }
.wa-row { display:flex; justify-content:flex-end; margin-bottom:8px; }
.wa-row label { font-size:11.5px; display:flex; align-items:center; gap:5px; margin:0; cursor:pointer; }
.opts-row { display:flex; flex-wrap:wrap; gap:10px; align-items:flex-start; }
.opts-block { background:var(--bg-panel); border:1px solid #e5e7eb; border-radius:6px; padding:8px 12px; flex:1 1 220px; }
.opts-block .ob-title { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#9ca3af; margin-bottom:6px; }
.opts-radio-row { display:flex; gap:12px; flex-wrap:wrap; }
.opts-radio-row label, .opts-chk-col label { font-size:11.5px; display:flex; align-items:center; gap:4px; cursor:pointer; margin:0; white-space:nowrap; }
.opts-chk-col { display:flex; flex-direction:column; gap:5px; max-height:160px; overflow-y:auto; }
.rate-pair { display:flex; gap:6px; align-items:center; margin-top:6px; }
.rate-pair input { width:80px; font-size:11.5px; }
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
.sum-card.acc { border-left:3px solid var(--blue); }
.sum-card.grn { border-left:3px solid var(--green); }
.grp-block { margin-top:8px; }
.grp-head { font-weight:700; font-size:12px; background:#eef3ff; padding:4px 8px; border-left:3px solid var(--blue); }
.grp-sub { font-size:11px; color:#333; padding:3px 8px; border-top:1px dashed #bbb; }
table.sr td, table.sr th { font-size:11.5px; white-space:nowrap; }
table.sr td.num, table.sr th.num { text-align:right; font-variant-numeric:tabular-nums; }
.grand-total-bar { margin-top:10px; font-weight:700; font-size:13px; text-align:right; border-top:2px solid #333; padding-top:8px; }
.action-bar { background:#4b5563; color:#fff; border-radius:6px; padding:8px 12px; display:flex; justify-content:space-between; align-items:center; gap:8px; flex-wrap:wrap; }
@media print { .filter-card, .tab-bar, .action-bar, .sidebar, nav, footer { display:none !important; } }
</style>

<div class="container-fluid">
<div class="row">
<?php include '../includes/sidebar.php'; ?>
<div class="col-md-9 col-lg-10 p-4">
<div class="main-content p-3">

<h4 class="mb-3">Stock In Hand
  <small class="text-muted fs-6 fw-normal ms-2">
    <?php if ($posted && !$err): ?>
      As On: <?= h(date('d-M-Y', strtotime($f['as_on_date']))) ?> &nbsp;|&nbsp; <?= number_format(count($rows)) ?> items
    <?php endif; ?>
  </small>
</h4>

<?php if ($err): ?>
  <div class="alert alert-danger"><?= h($err) ?></div>
<?php endif; ?>

<form method="post" id="filterForm">
<input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>">

<!-- Exact tabs from legacy Stock In Hand screen -->
<div class="tab-bar" id="sihTabs">
  <?php
  $tabs = array(
    'date' => 'Date',
    'category' => 'Category',
    'company' => 'Company',
    'size' => 'Size',
    'color' => 'Color',
    'description' => 'Description',
    'sp1' => 'Sp Instruction1',
    'sp2' => 'Sp Instruction2',
    'sp3' => 'Sp Instruction3',
    'compno' => 'Company No.',
    'pcode' => 'Product Code',
    'cpc' => 'CompProdCode',
    'party' => 'Party',
    'refno' => 'Ref. No.',
    'tax' => 'Tax %',
    'city' => 'City',
    'type' => 'Type',
    'prodgroup' => 'Product Group',
  );
  foreach ($tabs as $tid => $tlabel): ?>
    <button type="button" class="tab-btn<?= $tid==='date'?' active':'' ?>" data-tab="<?= h($tid) ?>" onclick="showSihTab('<?= h($tid) ?>')"><?= h($tlabel) ?></button>
  <?php endforeach; ?>
</div>

<!-- ═══════════════ DATE TAB (matches screenshot) ═══════════════ -->
<div class="tab-pane active" id="tab-date">
  <div class="wa-row">
    <label><input type="checkbox" name="whatsapp_owner" value="1" <?= !empty($f['whatsapp_owner'])?'checked':'' ?>> WhatsApp To Owner</label>
  </div>

  <div class="date-layout">
    <div class="date-main">
      <div class="filter-card">
        <div class="fc-title">Date</div>
        <div class="fg-row">
          <div class="fg-col-date">
            <span class="fl">As On Date</span>
            <input type="date" name="as_on_date" value="<?= h($f['as_on_date']) ?>" class="form-control form-control-sm" style="font-size:11.5px;">
          </div>
          <div class="fg-col-date">
            <span class="fl">No. of Days</span>
            <input type="number" name="no_of_days" value="<?= h($f['no_of_days']) ?>" class="form-control form-control-sm" style="font-size:11.5px;" min="0">
          </div>
        </div>
      </div>

      <div class="filter-card">
        <div class="fc-title">Grouping Options</div>
        <div class="grp-grid">
          <?php
          $grpLabels = array('Ist Grouping','IInd Grouping','IIIrd Grouping','IVth Grouping','Vth Grouping','VIth Grouping');
          for ($i = 1; $i <= 6; $i++):
              $nextFilled = ($i < 6) && !empty($grp[$i + 1]);
              $chkLabel   = $grp[$i] !== '' ? (isset($groupingFieldOptions[$grp[$i]]) ? $groupingFieldOptions[$grp[$i]] : $grp[$i]) : '';
          ?>
          <div class="grp-cell">
            <span class="fl"><?= h($grpLabels[$i-1]) ?></span>
            <select name="group_<?= $i ?>" id="group_sel_<?= $i ?>" class="form-select form-select-sm"
                    data-grp-index="<?= $i ?>" onchange="onGroupChange(<?= $i ?>)" style="font-size:11.5px;">
              <?php foreach ($groupingFieldOptions as $ov => $ol): ?>
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
            <div class="ob-title">Stock Valuation Required</div>
            <div class="opts-radio-row">
              <label><input type="radio" name="valuation_req" value="Yes" <?= $f['valuation_req']==='Yes'?'checked':'' ?>> Yes</label>
              <label><input type="radio" name="valuation_req" value="No" <?= $f['valuation_req']==='No'?'checked':'' ?>> No</label>
            </div>
          </div>
          <div class="opts-block">
            <div class="ob-title">Stock Status</div>
            <div class="opts-radio-row">
              <label><input type="radio" name="stock_status" value="All" <?= $f['stock_status']==='All'?'checked':'' ?>> All</label>
              <label><input type="radio" name="stock_status" value="NonZero" <?= $f['stock_status']==='NonZero'?'checked':'' ?>> Without Zero Balance</label>
              <label><input type="radio" name="stock_status" value="Positive" <?= $f['stock_status']==='Positive'?'checked':'' ?>> Only Positive</label>
            </div>
          </div>
          <div class="opts-block">
            <div class="ob-title">Stock Valuation On</div>
            <div class="opts-radio-row">
              <label><input type="radio" name="valuation_on" value="MRP" <?= $f['valuation_on']==='MRP'?'checked':'' ?>> MRP Rate</label>
              <label><input type="radio" name="valuation_on" value="Purchase" <?= $f['valuation_on']==='Purchase'?'checked':'' ?>> Purchase Rate</label>
              <label><input type="radio" name="valuation_on" value="StockRate" <?= $f['valuation_on']==='StockRate'?'checked':'' ?>> Stock Rate</label>
              <label><input type="radio" name="valuation_on" value="None" <?= $f['valuation_on']==='None'?'checked':'' ?>> None</label>
            </div>
            <div class="rate-pair">
              <span style="font-size:11px;">Rate Range From</span>
              <input type="number" step="0.01" name="rate_from" value="<?= h($f['rate_from']) ?>" class="form-control form-control-sm">
              <span style="font-size:11px;">To</span>
              <input type="number" step="0.01" name="rate_to" value="<?= h($f['rate_to']) ?>" class="form-control form-control-sm">
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="date-cols">
      <div class="ob-title">Columns</div>
      <div class="opts-chk-col" style="max-height:none;">
        <?php foreach ($colLabels as $key => $label): ?>
          <label><input type="checkbox" name="cols[]" value="<?= h($key) ?>" <?= in_array($key, $activeCols, true) ? 'checked' : '' ?>> <?= h($label) ?></label>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════ OTHER TABS (one each, like legacy) ═══════════════ -->
<div class="tab-pane" id="tab-category">
  <div class="filter-card"><div class="fc-title">Category</div><div class="fg-row">
    <?php sih_renderMs('ms-cat','Category',$categories,'C_Code','C_Name','category',$f['category'],'All Categories'); ?>
  </div></div>
</div>
<div class="tab-pane" id="tab-company">
  <div class="filter-card"><div class="fc-title">Company</div><div class="fg-row">
    <?php sih_renderMs('ms-cmp','Company',$companies,'Comp_Code','Comp_Name','company',$f['company'],'All Companies'); ?>
  </div></div>
</div>
<div class="tab-pane" id="tab-size">
  <div class="filter-card"><div class="fc-title">Size</div><div class="fg-row">
    <?php sih_renderMs('ms-sz','Size',$sizes,'S_Code','S_Name','size',$f['size'],'All Sizes'); ?>
  </div></div>
</div>
<div class="tab-pane" id="tab-color">
  <div class="filter-card"><div class="fc-title">Color</div><div class="fg-row">
    <?php sih_renderMs('ms-col','Color',$colors,'Color_Code','Color_Name','color',$f['color'],'All Colors'); ?>
  </div></div>
</div>
<div class="tab-pane" id="tab-description">
  <div class="filter-card"><div class="fc-title">Description</div><div class="fg-row">
    <?php sih_renderMs('ms-desc','Description',$descriptions,'Des_Code','Des_Name','description',$f['description'],'All Descriptions'); ?>
  </div></div>
</div>
<div class="tab-pane" id="tab-sp1">
  <div class="filter-card"><div class="fc-title">Sp Instruction1</div><div class="fg-row">
    <?php sih_renderMs('ms-sp1','Sp Instruction1',$sp1List,'S_Code','S_Name','sp1',$f['sp1'],'All'); ?>
  </div></div>
</div>
<div class="tab-pane" id="tab-sp2">
  <div class="filter-card"><div class="fc-title">Sp Instruction2</div><div class="fg-row">
    <?php sih_renderMs('ms-sp2','Sp Instruction2',$sp2List,'S_Code','S_Name','sp2',$f['sp2'],'All'); ?>
  </div></div>
</div>
<div class="tab-pane" id="tab-sp3">
  <div class="filter-card"><div class="fc-title">Sp Instruction3</div><div class="fg-row">
    <?php sih_renderMs('ms-sp3','Sp Instruction3',$sp3List,'S_Code','S_Name','sp3',$f['sp3'],'All'); ?>
  </div></div>
</div>
<div class="tab-pane" id="tab-compno">
  <div class="filter-card"><div class="fc-title">Company No.</div><div class="fg-row">
    <?php sih_renderMs('ms-cno','Company No',$companyNos,'Comp_No','Comp_No','company_no',$f['company_no'],'All'); ?>
  </div></div>
</div>
<div class="tab-pane" id="tab-pcode">
  <div class="filter-card"><div class="fc-title">Product Code</div><div class="fg-row">
    <?php sih_renderMs('ms-pcode','Product Code',$productCodes,'Product_Code','Product_Code','product_code',$f['product_code'],'All Products'); ?>
  </div></div>
</div>
<div class="tab-pane" id="tab-cpc">
  <div class="filter-card"><div class="fc-title">CompProdCode</div><div class="fg-row">
    <?php sih_renderMs('ms-cpc','CompProdCode',$compProdCodes,'CompProdCode','CompProdCode','comp_prod_code',$f['comp_prod_code'],'All'); ?>
  </div></div>
</div>
<div class="tab-pane" id="tab-party">
  <div class="filter-card"><div class="fc-title">Party</div><div class="fg-row">
    <?php sih_renderMs('ms-party','Party',$parties,'Comp_Code','Comp_Name','party',$f['party'],'All'); ?>
  </div></div>
</div>
<div class="tab-pane" id="tab-refno">
  <div class="filter-card"><div class="fc-title">Ref. No.</div><div class="fg-row">
    <div class="fg-col-ms" style="max-width:320px;">
      <span class="fl">Ref. No. contains</span>
      <input type="text" name="refno_contains" value="<?= h($f['refno_contains']) ?>" class="form-control form-control-sm" style="font-size:11.5px;">
    </div>
  </div></div>
</div>
<div class="tab-pane" id="tab-tax">
  <div class="filter-card"><div class="fc-title">Tax %</div><div class="fg-row">
    <?php sih_renderMs('ms-tax','Tax %',$taxRates,'Tax','Tax','tax',$f['tax'],'All'); ?>
  </div></div>
</div>
<div class="tab-pane" id="tab-city">
  <div class="filter-card"><div class="fc-title">City</div><div class="fg-row">
    <?php sih_renderMs('ms-city','City',$cities,'City_Code','City_Name','city',$f['city'],'All'); ?>
  </div></div>
</div>
<div class="tab-pane" id="tab-type">
  <div class="filter-card"><div class="fc-title">Type</div>
    <div class="opts-radio-row">
      <label><input type="radio" name="type_filter" value="All" <?= $f['type_filter']==='All'?'checked':'' ?>> All</label>
      <label><input type="radio" name="type_filter" value="Local" <?= $f['type_filter']==='Local'?'checked':'' ?>> Local</label>
      <label><input type="radio" name="type_filter" value="Central" <?= $f['type_filter']==='Central'?'checked':'' ?>> Central</label>
    </div>
  </div>
</div>
<div class="tab-pane" id="tab-prodgroup">
  <div class="filter-card"><div class="fc-title">Product Group</div><div class="fg-row">
    <?php sih_renderMs('ms-pg','Product Group',$productGroups,'ProdGroup_Code','ProdGroup_Name','prod_group',$f['prod_group'],'All Groups'); ?>
  </div></div>
</div>

<div class="action-bar mb-4">
  <div class="d-flex gap-2">
    <button type="button" class="btn btn-outline-light btn-sm" onclick="sihSaveTemplate()">Save As Template</button>
    <button type="button" class="btn btn-outline-light btn-sm" onclick="sihOpenTemplate()">Open From Template</button>
  </div>
  <div class="d-flex gap-2">
    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="sihReset()">↺ Reset</button>
    <button type="button" class="btn btn-success btn-sm" onclick="sihWhatsApp()">WhatsApp</button>
    <button type="submit" class="btn btn-primary btn-sm px-3">Print</button>
    <a href="../index.php" class="btn btn-dark btn-sm">Exit</a>
  </div>
</div>
</form>

<?php if ($posted && !$err): ?>
<div class="row g-3 mb-3">
  <div class="col"><div class="sum-card acc"><div class="sc-label">Items</div><div class="sc-val"><?= number_format(count($rows)) ?></div></div></div>
  <div class="col"><div class="sum-card acc"><div class="sc-label">Total Stock</div><div class="sc-val"><?= number_format($grandStock, 3) ?></div></div></div>
  <div class="col"><div class="sum-card grn"><div class="sc-label">Total Value</div><div class="sc-val">₹<?= number_format($grandValue, 2) ?></div></div></div>
</div>

<div class="card shadow-sm">
<div class="card-body p-3" id="reportBody">
  <?php if (empty($rows)): ?>
    <p class="text-center py-5 text-muted mb-0">No stock found for the selected filters.</p>
  <?php else: ?>
    <?php sih_renderTree($tree, 0, $activeCols, $colField, $colLabels, $activeSubtots); ?>
    <div class="grand-total-bar">Grand Total — Stock: <?= number_format($grandStock, 3) ?> &nbsp; Value: ₹<?= number_format($grandValue, 2) ?></div>
  <?php endif; ?>
</div>
</div>
<div class="d-flex justify-content-end gap-2 mt-3">
  <button class="btn btn-success btn-sm" onclick="window.print()">🖨 Print</button>
</div>
<?php endif; ?>

</div></div></div></div>

<script>
var SIH_TOTAL = {
  items: <?= (int)count($rows) ?>,
  stock: <?= json_encode(round($grandStock, 3)) ?>,
  value: <?= json_encode(round($grandValue, 2)) ?>,
  asOn: <?= json_encode($f['as_on_date']) ?>
};
var GROUPING_OPTIONS = <?= isset($jsGroupingOptions) ? $jsGroupingOptions : '{}' ?>;

function showSihTab(id) {
  document.querySelectorAll('.tab-pane').forEach(function(p){ p.classList.remove('active'); });
  document.querySelectorAll('.tab-btn').forEach(function(b){ b.classList.remove('active'); });
  var pane = document.getElementById('tab-' + id);
  if (pane) pane.classList.add('active');
  var btn = document.querySelector('.tab-btn[data-tab="'+id+'"]');
  if (btn) btn.classList.add('active');
}

/* MIS-style grouping: cascade clear + "-wise Total?" when next level set */
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
  if (curEl && curEl.value !== '') {
    for (var j = i + 1; j <= 6; j++) {
      var el = document.getElementById('group_sel_' + j);
      if (el && el.value === curEl.value) el.value = '';
    }
  }
  refreshGroupCheckboxes();
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
  var trigger = ms.querySelector('.ms-trigger');
  var textEl  = trigger.querySelector('.ms-text');
  var badgeEl = trigger.querySelector('.ms-badge');
  var placeholder = trigger.getAttribute('data-placeholder') || 'Select…';
  if (checked.length > 0) {
    var labels = Array.from(checked).slice(0,2).map(function(cb){
      return (cb.closest('.ms-item').querySelector('label') || {}).innerText || cb.value;
    });
    if (textEl) textEl.textContent = labels.join(', ') + (checked.length > 2 ? '…' : '');
    if (badgeEl) { badgeEl.textContent = checked.length; badgeEl.style.display = ''; }
    else {
      var b = document.createElement('span');
      b.className = 'ms-badge'; b.textContent = checked.length;
      trigger.appendChild(b);
    }
  } else {
    if (textEl) textEl.textContent = placeholder;
    if (badgeEl) badgeEl.style.display = 'none';
  }
}
document.querySelectorAll('.ms-item input[type=checkbox]').forEach(function(cb){
  cb.addEventListener('change', function(){
    var ms = this.closest('.ms-dropdown');
    if (ms) updateMsTrigger(ms.id);
  });
});

function sihSaveTemplate() {
  var fd = new FormData(document.getElementById('filterForm'));
  var obj = {};
  fd.forEach(function(v,k){
    if (k.endsWith('[]')) {
      k = k.slice(0,-2);
      if (!obj[k]) obj[k] = [];
      obj[k].push(v);
    } else { obj[k] = v; }
  });
  localStorage.setItem('sih_template', JSON.stringify(obj));
  alert('Template saved in this browser.');
}
function sihOpenTemplate() {
  var raw = localStorage.getItem('sih_template');
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
        document.querySelectorAll('[name="'+k+'"]').forEach(function(el){
          if (el.type === 'radio' || el.type === 'checkbox') el.checked = (el.value == val);
          else el.value = val;
        });
      }
    });
    ['ms-cat','ms-cmp','ms-sz','ms-col','ms-desc','ms-pg','ms-cno','ms-sp1','ms-sp2','ms-sp3','ms-party','ms-city','ms-pcode','ms-cpc','ms-tax'].forEach(updateMsTrigger);
    refreshGroupCheckboxes();
    alert('Template loaded. Click Print.');
  } catch (e) { alert('Invalid template.'); }
}
function sihReset() {
  document.getElementById('filterForm').reset();
  for (var i=1;i<=6;i++) {
    var el=document.getElementById('group_sel_'+i); if(el) el.value='';
    var ck=document.getElementById('subtot_'+i); if(ck) ck.checked=false;
  }
  document.querySelectorAll('.ms-item input[type=checkbox]').forEach(function(cb){ cb.checked=false; });
  document.querySelectorAll('[name="cols[]"]').forEach(function(cb){ cb.checked=true; });
  ['ms-cat','ms-cmp','ms-sz','ms-col','ms-desc','ms-pg','ms-cno','ms-sp1','ms-sp2','ms-sp3','ms-party','ms-city','ms-pcode','ms-cpc','ms-tax'].forEach(updateMsTrigger);
  refreshGroupCheckboxes();
}
function sihWhatsApp() {
  var msg = encodeURIComponent('Stock In Hand\nAs On: '+SIH_TOTAL.asOn+'\nItems: '+SIH_TOTAL.items+'\nStock: '+SIH_TOTAL.stock+'\nValue: '+SIH_TOTAL.value);
  window.open('https://wa.me/?text='+msg, '_blank');
}

(function(){ refreshGroupCheckboxes(); })();
</script>

<?php include '../includes/footer.php'; ?>
