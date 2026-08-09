<?php
session_start();
include '../include/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['data' => []]);
    exit;
}

$month = (int)$_POST['month'];   // eg: 11
$year  = (int)$_POST['year'];    // eg: 2025
$group = !empty($_POST['group']) ? (int)$_POST['group'] : null;
$payment_method = !empty($_POST['payment_method']) ? $_POST['payment_method'] : null;

$monthShort = strtolower(date('M', mktime(0, 0, 0, $month, 1)));
$monthLong  = strtolower(date('F', mktime(0, 0, 0, $month, 1)));

/**
 * GTMS bug (All Groups vs single group):
 * - All Groups pe pehle `group_id IS NULL` / invalid group wale members bhi aa jaate the
 *   jo kisi bhi group filter me nahi dikhte → GTMS sum inflate
 * - Isliye All Groups = sirf un members ka sum jo valid `groups` table me hain
 *   (same set jo group-wise filters cover karte hain)
 */
$params = [
    ':pay_year'    => $year,
    ':pay_month'   => $month,
    ':loan_year'   => $year,
    ':loan_month'  => $month,
    ':month_short' => $monthShort,
    ':month_long'  => $monthLong,
];

$groupSql = "";
if ($group !== null) {
    // Specific group
    $groupSql = " AND m.group_id = :group_id ";
    $params[':group_id'] = $group;
} else {
    // All Groups → only members that belong to an existing group
    // (NULL / deleted-group orphans exclude — tabhi All == sum of each group)
    $groupSql = " AND m.group_id IS NOT NULL
                  AND EXISTS (SELECT 1 FROM `groups` g WHERE g.id = m.group_id) ";
}

$methodSql = "";
if ($payment_method !== null && $payment_method !== '') {
    $methodSql = " AND p.payment_method = :method ";
    $params[':method'] = $payment_method;
}

$sql = "
SELECT 
    m.id AS member_id,
    m.first_name,
    m.last_name,
    m.mtd_date,
    m.std_date,
    m.monthly_ms,
    m.open_ms AS registration_ms,
    m.group_id,
    g.name AS group_name,
    p.payment_amount,
    p.open_ms,
    p.payment_method,
    COALESCE(lp.loan_amount, 0) AS loan_amount,
    COALESCE(lp.interest_portion, 0) AS interest_portion,
    COALESCE(lp.processing_fee, 0) AS processing_fee,
    COALESCE(lp.fine, 0) AS fine,
    COALESCE(lp.ln, 0) AS ln,
    lp.bal_amount
FROM members m
LEFT JOIN `groups` g ON g.id = m.group_id

LEFT JOIN (
    SELECT
        p1.member_id,
        SUM(COALESCE(p1.amount, 0)) AS payment_amount,
        /* true opening = earliest payment row of this month */
        SUBSTRING_INDEX(
            GROUP_CONCAT(p1.open_ms ORDER BY p1.id ASC SEPARATOR '||'),
            '||', 1
        ) AS open_ms,
        SUBSTRING_INDEX(
            GROUP_CONCAT(p1.method ORDER BY p1.id DESC SEPARATOR '||'),
            '||', 1
        ) AS payment_method
    FROM payments p1
    WHERE p1.year = :pay_year
      AND p1.month = :pay_month
    GROUP BY p1.member_id
) p ON p.member_id = m.id

LEFT JOIN (
    SELECT
        l.member_id,
        SUM(
            CASE
                WHEN lp.principal_portion IS NOT NULL THEN lp.principal_portion
                ELSE COALESCE(lp.amount, 0)
            END
        ) AS loan_amount,
        SUM(COALESCE(lp.interest_portion, 0)) AS interest_portion,
        SUM(COALESCE(lp.processing_fee, 0)) AS processing_fee,
        SUM(COALESCE(lp.fine, 0)) AS fine,
        SUM(COALESCE(lp.ln, 0)) AS ln,
        SUBSTRING_INDEX(
            GROUP_CONCAT(lp.bal_amount ORDER BY lp.id DESC SEPARATOR '||'),
            '||', 1
        ) AS bal_amount
    FROM loan_payments lp
    JOIN loans l ON l.id = lp.loan_id
    WHERE CAST(lp.year AS UNSIGNED) = :loan_year
      AND (
            CAST(lp.month AS UNSIGNED) = :loan_month
            OR LOWER(lp.month) IN (:month_short, :month_long)
          )
    GROUP BY l.member_id
) lp ON lp.member_id = m.id

WHERE 1=1
{$groupSql}
{$methodSql}

ORDER BY
    m.monthly_ms ASC,
    m.id ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!$rows) {
    echo json_encode(['data' => [], 'totals' => ['gtms' => 0]]);
    exit;
}

function monthDiffInRange(
    string $startDate,
    string $endDate,
    int $month,
    int $year
): int {
    $start = new DateTime($startDate);
    $end   = new DateTime($endDate);

    $given = DateTime::createFromFormat(
        '!d-m-Y',
        sprintf('01-%02d-%04d', $month, $year)
    );

    if ($given < $start) {
        $given = clone $start;
    }
    if ($given > $end) {
        $given = clone $end;
    }

    return ((int)$given->format('Y') * 12 + (int)$given->format('m'))
        - ((int)$start->format('Y') * 12 + (int)$start->format('m'));
}

function loanMonthSortKey($monthValue): int
{
    if ($monthValue === null || $monthValue === '') {
        return 0;
    }
    if (is_numeric($monthValue)) {
        return (int)$monthValue;
    }
    $ts = strtotime('1 ' . $monthValue);
    return $ts === false ? 0 : (int)date('n', $ts);
}

/**
 * Previous month closing MS:
 * opening of that month (earliest row) + SUM(all amounts that month)
 * (LIMIT 1 on a single payment row was wrong when multiple payments exist)
 */
function getPreviousClosingMs(PDO $pdo, int $memberId, int $year, int $month, $registrationMs): float
{
    $stmtLast = $pdo->prepare("
        SELECT year, month
        FROM payments
        WHERE member_id = ?
          AND (year < ? OR (year = ? AND month < ?))
        ORDER BY year DESC, month DESC
        LIMIT 1
    ");
    $stmtLast->execute([$memberId, $year, $year, $month]);
    $last = $stmtLast->fetch(PDO::FETCH_ASSOC);

    if (!$last) {
        return (float)$registrationMs;
    }

    $py = (int)$last['year'];
    $pm = (int)$last['month'];

    $stmtClose = $pdo->prepare("
        SELECT
            (
                SELECT open_ms
                FROM payments
                WHERE member_id = ? AND year = ? AND month = ?
                ORDER BY id ASC
                LIMIT 1
            ) AS open_ms,
            (
                SELECT COALESCE(SUM(amount), 0)
                FROM payments
                WHERE member_id = ? AND year = ? AND month = ?
            ) AS paid
    ");
    $stmtClose->execute([$memberId, $py, $pm, $memberId, $py, $pm]);
    $close = $stmtClose->fetch(PDO::FETCH_ASSOC);

    if (!$close || $close['open_ms'] === null || $close['open_ms'] === '') {
        return (float)$registrationMs;
    }

    return (float)$close['open_ms'] + (float)$close['paid'];
}

$results = [];
$sumGtms = 0.0;
$sumMs = 0.0;
$sumOpenMs = 0.0;
$byGroup = []; // group_id => [name, gtms, members, open_ms, ms]

foreach ($rows as $row) {

    $full_months = monthDiffInRange($row['std_date'], $row['mtd_date'], $month, $year) + 1;
    $expected_amount = $full_months * (float)$row['monthly_ms'];

    $member_id  = (int)$row['member_id'];
    $open_ms    = $row['open_ms'];
    $bal_amount = $row['bal_amount'];

    /* ================= MS CARRY FORWARD ================= */
    if ($open_ms === null || $open_ms === '') {
        $open_ms = getPreviousClosingMs(
            $pdo,
            $member_id,
            $year,
            $month,
            $row['registration_ms']
        );
    }

    /* ================= LOAN BALANCE CARRY FORWARD ================= */
    if ($bal_amount === null) {

        $currentYear  = (int) date('Y');
        $currentMonth = (int) date('n');
        $givenYear    = (int) $year;
        $givenMonth   = (int) $month;

        $isFuture = ($givenYear > $currentYear)
            || ($givenYear === $currentYear && $givenMonth > $currentMonth);

        $calcYear  = $isFuture ? $currentYear : $givenYear;
        $calcMonth = $isFuture ? $currentMonth : $givenMonth;

        $stmtLoanBal = $pdo->prepare("
            SELECT lp.bal_amount, lp.year, lp.month, lp.id
            FROM loan_payments lp
            JOIN loans l ON lp.loan_id = l.id
            WHERE l.member_id = ?
              AND CAST(lp.year AS UNSIGNED) <= ?
            ORDER BY CAST(lp.year AS UNSIGNED) DESC, lp.id DESC
            LIMIT 50
        ");
        $stmtLoanBal->execute([$member_id, $calcYear]);
        $loanRows = $stmtLoanBal->fetchAll(PDO::FETCH_ASSOC);

        $best = null;
        foreach ($loanRows as $lr) {
            $y = (int)$lr['year'];
            $m = loanMonthSortKey($lr['month']);
            if ($y > $calcYear || ($y === $calcYear && $m > $calcMonth)) {
                continue;
            }
            if (
                $best === null
                || $y > $best['_y']
                || ($y === $best['_y'] && $m > $best['_m'])
                || ($y === $best['_y'] && $m === $best['_m'] && (int)$lr['id'] > (int)$best['id'])
            ) {
                $best = $lr;
                $best['_y'] = $y;
                $best['_m'] = $m;
            }
        }

        if ($best) {
            $bal_amount = $best['bal_amount'];
        } else {
            $stmtTotal = $pdo->prepare("
                SELECT SUM(principal) AS total_p
                FROM loans
                WHERE member_id = ? AND status = 'pending'
            ");
            $stmtTotal->execute([$member_id]);
            $resTotal = $stmtTotal->fetch(PDO::FETCH_ASSOC);
            $bal_amount = $resTotal['total_p'] ?? 0;
        }
    }

    $ms_amt     = (float)($row['payment_amount'] ?? 0);
    $loan_amt   = (float)($row['loan_amount'] ?? 0);
    $int        = (float)($row['interest_portion'] ?? 0);
    $pf         = (float)($row['processing_fee'] ?? 0);
    $fine       = (float)($row['fine'] ?? 0);
    $ln_amt     = (float)($row['ln'] ?? 0);
    $pay_method = $row['payment_method'] ?? 'N/A';
    $open_ms_f  = (float)$open_ms;
    $gtms       = round($open_ms_f + $ms_amt, 2);

    $unpaid = ($ms_amt <= 0 && $loan_amt <= 0);
    $open_loan = (float)($bal_amount ?? 0) + $loan_amt - $ln_amt;

    $sumGtms += $gtms;
    $sumMs += $ms_amt;
    $sumOpenMs += $open_ms_f;

    $gid = $row['group_id'] !== null && $row['group_id'] !== '' ? (int)$row['group_id'] : 0;
    $gname = $row['group_name'] ?: '(No Group)';
    if (!isset($byGroup[$gid])) {
        $byGroup[$gid] = [
            'group_id' => $gid,
            'group_name' => $gname,
            'members' => 0,
            'open_ms' => 0.0,
            'ms' => 0.0,
            'gtms' => 0.0,
        ];
    }
    $byGroup[$gid]['members']++;
    $byGroup[$gid]['open_ms'] += $open_ms_f;
    $byGroup[$gid]['ms'] += $ms_amt;
    $byGroup[$gid]['gtms'] += $gtms;

    $results[] = [
        'DT_RowClass'     => $unpaid ? 'highlight-red' : '',
        'select'          => '<input type="checkbox" class="row-select">',
        'std'             => date('M-y', strtotime($row['std_date'])),
        'mtd'             => date('M-y', strtotime($row['mtd_date'])),
        'name'            => htmlspecialchars($row['first_name'] . ' ' . $row['last_name']),
        'group_id'        => $gid,
        'group_name'      => htmlspecialchars($gname),
        'open_ms'         => round($open_ms_f, 2),
        'open_loan'       => round($open_loan, 2),
        'ms'              => round($ms_amt, 2),
        'rcv'             => round($loan_amt, 2),
        'int'             => round($int, 2),
        'pf'              => round($pf, 2),
        'fine'            => round($fine, 2),
        'total'           => round($ms_amt + $loan_amt + $int + $pf + $fine, 2),
        'ln'              => round($ln_amt, 2),
        'bal_ln'          => round((float)($bal_amount ?? 0), 2),
        'gtms'            => $gtms,
        'is_unpaid'       => $unpaid,
        'expected_amount' => round((float)$expected_amount, 2),
        'payment_method'  => $pay_method,
    ];
}

// Round group breakdown for JSON
$byGroupOut = [];
foreach ($byGroup as $g) {
    $byGroupOut[] = [
        'group_id'   => $g['group_id'],
        'group_name' => $g['group_name'],
        'members'    => $g['members'],
        'open_ms'    => round($g['open_ms'], 2),
        'ms'         => round($g['ms'], 2),
        'gtms'       => round($g['gtms'], 2),
    ];
}
usort($byGroupOut, function ($a, $b) {
    return strcmp($a['group_name'], $b['group_name']);
});

echo json_encode([
    'data' => $results,
    'totals' => [
        'open_ms'       => round($sumOpenMs, 2),
        'ms'            => round($sumMs, 2),
        'gtms'          => round($sumGtms, 2),
        'member_count'  => count($results),
        'group_count'   => count($byGroupOut),
    ],
    'by_group' => $byGroupOut,
]);
exit;
