<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'include/db.php';
require_once 'include/notification_logic.php';

$current_day   = (int) date('j');
// $current_day   = 20;
$current_month = (int) date('n');
$current_year  = (int) date('Y');

// $current_month = 10;
// $current_year  = 2025;

if (isset($_SESSION['user_id'])) {
    // Recalculate pending months with FIFO payment allocation.
    // Example: July/Aug/Sep/Oct due (2000 each), user pays 4000 in Oct -> clears July + Aug only.
    ms_sync_all_notifications($pdo, $current_day, $current_month, $current_year);

    $fetch_sql = "SELECT m.first_name, m.last_name, m.id, m.monthly_ms,
                  COUNT(n.id) as pending_months,
                  GROUP_CONCAT(n.month ORDER BY n.year, n.month SEPARATOR ', ') as month_names,
                  (m.monthly_ms * COUNT(n.id)) as total_pending_amount
                  FROM notifications n
                  JOIN members m ON n.member_id = m.id
                  WHERE n.type = 'ms'
                  GROUP BY m.id
                  HAVING pending_months > 0";

    $stmt = $pdo->query($fetch_sql);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $notif_count = count($notifications);
}
?>
<!doctype html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Prayas Jankalyan Samajik Samiti</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
	<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

	<style>
		.notif-scroll {
			max-height: 450px;
			overflow-y: auto;
			width: 300px;
		}

		.unread-item {
			background-color: #f0f7ff;
			border-bottom: 1px solid #eee;
		}

		.notif-link {
			text-decoration: none;
			color: black;
			display: block;
			padding: 10px;
		}

		.notif-link:hover {
			background-color: #f8f9fa;
		}

		.extra-notif {
			display: none;
		}
	</style>
</head>

<body>
	<nav class="navbar navbar-expand-lg navbar-light bg-white shadow mb-4">
		<div class="container">
			<a class="navbar-brand" href="index.php">
				<img src="./include/prayas-img.png" alt="Prayas Samiti Logo" style="width:50px;">
			</a>

			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
				<span class="navbar-toggler-icon"></span>
			</button>

			<div class="collapse navbar-collapse" id="mainNavbar">
				<ul class="navbar-nav ms-auto align-items-center">
					<?php if (isset($_SESSION['user_id'])): ?>


						<li class="nav-item">
							<span class="nav-link text-success fw-bold">
								👋 Welcome, <?= htmlspecialchars($_SESSION['user_name']); ?>
							</span>
						</li>

						<li class="nav-item"><a class="nav-link" href="add_group.php">Member Groups</a></li>

						<li class="nav-item dropdown">
							<a class="nav-link dropdown-toggle" href="#" id="membersDropdown" role="button" data-bs-toggle="dropdown">
								Members
							</a>
							<ul class="dropdown-menu">
								<li><a class="dropdown-item" href="/prayas">All Members</a></li>
								<li><a class="dropdown-item" href="add_member.php">Add Member</a></li>
								<li><a class="dropdown-item" href="transactions.php">Member Reports</a></li>
								<li><a class="dropdown-item" href="payment_add.php">Add Payments</a></li>
							</ul>
						</li>

						<li class="nav-item"><a class="nav-link" href="transactions.php">All Transactions</a></li>
						<li class="nav-item"><a class="nav-link" href="manage_users.php">Users</a></li>
						<li class="nav-item dropdown me-2">
							<a class="nav-link position-relative" href="#" id="notifBtn" role="button" data-bs-toggle="dropdown" aria-expanded="false">
								<i class="bi bi-bell-fill fs-5"></i>
								<?php if ($notif_count > 0): ?>
									<span id="notif-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
										<?= $notif_count; ?>
									</span>
								<?php endif; ?>
							</a>

							<ul class="dropdown-menu dropdown-menu-end shadow notif-scroll" style="width: 320px;">
								<li class="p-2 d-flex justify-content-between align-items-center border-bottom bg-light">
									<strong>Notifications</strong>
									<?php if ($notif_count > 0): ?>
										<span class="badge bg-soft-danger text-danger" style="font-size: 0.7rem;">
											<?= $notif_count; ?> Pending
										</span>
									<?php endif; ?>
								</li>

								<div id="notif-items-list">
									<?php if ($notif_count == 0): ?>
										<li class="p-3 text-center small text-muted">All payments up to date!</li>
									<?php else: ?>
										<?php foreach ($notifications as $index => $row): ?>
											<div class="unread-item border-bottom <?= ($index >= 5) ? 'extra-notif d-none' : ''; ?>">
												<a href="view_member.php?id=<?= $row['id']; ?>" class="notif-link p-2 d-block text-decoration-none">
													<div class="d-flex justify-content-between align-items-center">
														<small><strong><?= htmlspecialchars($row['first_name'] . " " . $row['last_name']); ?></strong></small>
														<span class="badge bg-danger" style="font-size: 0.55rem;">OVERDUE</span>
													</div>
													<div class="small text-muted" style="font-size: 0.75rem;">
														Pending: <?= $row['pending_months']; ?> Months (<?php
																										$month_numbers = explode(',', $row['month_names']);
																										$formatted_names = [];
																										foreach ($month_numbers as $m_num) {
																											$m_num = trim($m_num);
																											if ($m_num) {
																												$formatted_names[] = DateTime::createFromFormat('!n', $m_num)->format('F');
																											}
																										}
																										echo implode(', ', $formatted_names);
																										?>)
													</div>
													<div class="text-danger fw-bold" style="font-size: 0.85rem;">
														Total: ₹<?= number_format($row['total_pending_amount']); ?>
													</div>
												</a>
											</div>
										<?php endforeach; ?>
									<?php endif; ?>
								</div>

								<?php if ($notif_count > 5): ?>
									<li class="text-center border-top">
										<button class="btn btn-sm text-primary w-100 py-2" id="show-more-notif">
											Show All (<?= $notif_count; ?>)
										</button>
									</li>
								<?php endif; ?>
							</ul>
						</li>
						<li class="nav-item"><a class="nav-link text-danger" href="logout.php">Logout</a></li>

					<?php else: ?>
						<li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
					<?php endif; ?>

				</ul>
			</div>
		</div>
	</nav>


	<script>
		$(document).ready(function() {
			$('#show-more-notif').on('click', function(e) {
				e.preventDefault();
				e.stopPropagation();
				$('.extra-notif').removeClass('d-none').hide().slideDown();
				$(this).parent().hide();
			});
		});
	</script>

	<div class="container">
