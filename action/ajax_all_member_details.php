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

// Month name variants used in loan_payments.month (numeric / Nov / November)
$monthShort = strtolower(date('M', mktime(0, 0, 0, $month, 1)));
$monthLong  = strtolower(date('F', mktime(0, 0, 0, $month, 1)));

/* =========================================================
   MAIN QUERY
   Fixes vs old version:
   1) RCV prefers principal_portion (not full EMI amount) so Total
      does not double-count INT/PF/Fine
   2) Payments aggregated per member (duplicate payment rows were
      inflating MS / Open MS / GTMS / Total)
   3) All loan payments in the month are SUMmed (old query kept
      only the latest loan_payment id)
   ========================================================= */
$sql = "
SELECT 
    m.id AS member_id,
    m.first_name,
    m.last_name,
    m.mtd_date,
    m.std_date,
    m.monthly_ms,
    m.open_ms AS registration_ms,
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

LEFT JOIN (
    SELECT
        p1.member_id,
        SUM(COALESCE(p1.amount, 0)) AS payment_amount,
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

WHERE (:group_id IS NULL OR m.group_id = :group_id2)
AND (
        :method1 IS NULL
        OR :method2 = ''
        OR p.payment_method = :method3
    )

ORDER BY
    m.monthly_ms ASC,
    m.id ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':pay_year'     => $year,
    ':pay_month'    => $month,
    ':loan_year'    => $year,
    ':loan_month'   => $month,
    ':month_short'  => $monthShort,
    ':month_long'   => $monthLong,
    ':group_id'     => $group,
    ':group_id2'    => $group,
    ':method1'      => $payment_method,
    ':method2'      => $payment_method,
    ':method3'      => $payment_method,
]);

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!$rows) {
    echo json_encode(['data' => []]);
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

/** Normalize loan_payments.month: 11 / "11" / "Nov" / "November" */
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

$results = [];

foreach ($rows as $row) {

    $full_months = monthDiffInRange($row['std_date'], $row['mtd_date'], $month, $year) + 1;
    $expected_amount = $full_months * (float)$row['monthly_ms'];

    $member_id  = $row['member_id'];
    $open_ms    = $row['open_ms'];
    $bal_amount = $row['bal_amount'];

    /* ================= MS CARRY FORWARD ================= */
    if ($open_ms === null || $open_ms === '') {

        $stmtPrev = $pdo->prepare("
            SELECT (open_ms + amount) AS last_total_ms
            FROM payments
            WHERE member_id = ?
            AND (year < ? OR (year = ? AND month < ?))
            ORDER BY year DESC, month DESC
            LIMIT 1
        ");
        $stmtPrev->execute([$member_id, $year, $year, $month]);
        $prev = $stmtPrev->fetch(PDO::FETCH_ASSOC);

        $open_ms = $prev ? $prev['last_total_ms'] : $row['registration_ms'];
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

        // Do not rely on CAST(month AS UNSIGNED) — breaks for "Nov"/"November"
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

    /* ================= FINAL CALCULATIONS ================= */
    $ms_amt     = (float)($row['payment_amount'] ?? 0);
    $loan_amt   = (float)($row['loan_amount'] ?? 0); // RCV = principal
    $int        = (float)($row['interest_portion'] ?? 0);
    $pf         = (float)($row['processing_fee'] ?? 0);
    $fine       = (float)($row['fine'] ?? 0);
    $ln_amt     = (float)($row['ln'] ?? 0);
    $pay_method = $row['payment_method'] ?? 'N/A';

    $unpaid = ($ms_amt <= 0 && $loan_amt <= 0);

    // Opening loan = closing bal + principal recovered - new loan issued
    $open_loan = (float)($bal_amount ?? 0) + $loan_amt - $ln_amt;

    $results[] = [
        'DT_RowClass'     => $unpaid ? 'highlight-red' : '',
        'select'          => '<input type="checkbox" class="row-select">',
        'std'             => date('M-y', strtotime($row['std_date'])),
        'mtd'             => date('M-y', strtotime($row['mtd_date'])),
        'name'            => htmlspecialchars($row['first_name'] . ' ' . $row['last_name']),
        'open_ms'         => round((float)$open_ms, 2),
        'open_loan'       => round($open_loan, 2),
        'ms'              => round($ms_amt, 2),
        'rcv'             => round($loan_amt, 2),
        'int'             => round($int, 2),
        'pf'              => round($pf, 2),
        'fine'            => round($fine, 2),
        'total'           => round($ms_amt + $loan_amt + $int + $pf + $fine, 2),
        'ln'              => round($ln_amt, 2),
        'bal_ln'          => round((float)($bal_amount ?? 0), 2),
        'gtms'            => round((float)$open_ms + $ms_amt, 2),
        'is_unpaid'       => $unpaid,
        'expected_amount' => round((float)$expected_amount, 2),
        'payment_method'  => $pay_method,
    ];
}

echo json_encode(['data' => $results]);
exit;
