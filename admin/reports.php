<?php
$required_role = 'admin';
require_once __DIR__ . '/../includes/auth_check.php';

// Fetch Request Counts by Status
$stats_status = $conn->query("
    SELECT Status, COUNT(*) as cnt 
    FROM ServiceRequest 
    GROUP BY Status
");
$status_counts = [
    'Pending' => 0,
    'Assigned' => 0,
    'In Progress' => 0,
    'Completed' => 0,
    'Cancelled' => 0
];
while ($row = $stats_status->fetch_assoc()) {
    $status_counts[$row['Status']] = $row['cnt'];
}

// Total Booking Earnings (Sum of completed service request category prices)
$earnings_res = $conn->query("
    SELECT SUM(sc.Price) as total 
    FROM ServiceRequest sr
    JOIN ServiceCategory sc ON sr.ServiceID = sc.ServiceID
    WHERE sr.Status = 'Completed'
");
$total_earnings = $earnings_res->fetch_assoc()['total'] ?: 0.00;

// Average platform rating
$rating_res = $conn->query("SELECT AVG(Rating) as avg_r FROM Review");
$avg_rating = $rating_res->fetch_assoc()['avg_r'] ?: 0.0;

// Latest 5 Customer Reviews
$latest_reviews = $conn->query("
    SELECT r.Rating, r.Comment, r.ReviewDate, c.Name as CustomerName, e.Name as TechnicianName
    FROM Review r
    JOIN Customer c ON r.CustomerID = c.CustomerID
    JOIN Employee e ON r.EmployeeID = e.EmployeeID
    ORDER BY r.ReviewDate DESC
    LIMIT 5
");

// Total registered counts
$cust_cnt = $conn->query("SELECT COUNT(*) as cnt FROM Customer")->fetch_assoc()['cnt'];
$tech_cnt = $conn->query("SELECT COUNT(*) as cnt FROM Employee")->fetch_assoc()['cnt'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Insights - HSMS Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .progress-bar-container {
            background: #e2e8f0;
            border-radius: 8px;
            height: 10px;
            overflow: hidden;
            width: 100%;
            margin-top: 6px;
        }
        .progress-bar {
            background: var(--primary);
            height: 100%;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="page-container">
        <h2>Reports & Analytics</h2>
        <p>Get insights into earnings, ratings, and customer satisfaction feeds.</p>

        <!-- Stats Grid -->
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-label">Total Earnings</div>
                <div class="stat-value" style="color: var(--primary);">Rs. <?= number_format($total_earnings, 2) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Platform Rating</div>
                <div class="stat-value" style="color: var(--accent);">★ <?= number_format($avg_rating, 1) ?> / 5.0</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Clients</div>
                <div class="stat-value"><?= $cust_cnt ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Active Technicians</div>
                <div class="stat-value"><?= $tech_cnt ?></div>
            </div>
        </div>

        <div class="dashboard-grid" style="align-items: start;">
            <!-- Request Status Metrics -->
            <div class="dashboard-card" style="padding: 24px;">
                <h3>📊 Service Booking Statuses</h3>
                <p>Breakdown of all registered home service requests.</p>
                
                <?php
                $total_requests = array_sum($status_counts) ?: 1;
                foreach ($status_counts as $status => $count):
                    $pct = ($count / $total_requests) * 100;
                ?>
                    <div style="margin-bottom: 16px;">
                        <div style="display:flex; justify-content:space-between; font-size:14px; font-weight:600;">
                            <span><?= $status ?></span>
                            <span><?= $count ?> (<?= round($pct) ?>%)</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar" style="width: <?= $pct ?>%; 
                                background: <?= $status === 'Completed' ? 'hsl(150, 80%, 35%)' : ($status === 'Cancelled' ? 'hsl(0, 80%, 45%)' : 'var(--primary)') ?>;">
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Customer Reviews Feedback Feed -->
            <div class="table-wrapper" style="grid-column: span 2; margin-top: 0;">
                <h3 style="padding: 20px 24px 0 24px;">📝 Latest Customer Reviews</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Technician</th>
                            <th>Rating</th>
                            <th>Comments</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($latest_reviews->num_rows === 0): ?>
                            <tr><td colspan="5" style="text-align:center;">No customer reviews submitted yet.</td></tr>
                        <?php else: ?>
                            <?php while ($rev = $latest_reviews->fetch_assoc()): ?>
                                <tr>
                                    <td><?= date('Y-m-d', strtotime($rev['ReviewDate'])) ?></td>
                                    <td><strong><?= htmlspecialchars($rev['CustomerName']) ?></strong></td>
                                    <td><?= htmlspecialchars($rev['TechnicianName']) ?></td>
                                    <td>
                                        <span style="color:var(--accent); font-weight:700;">
                                            <?= str_repeat('★', $rev['Rating']) ?><?= str_repeat('☆', 5 - $rev['Rating']) ?>
                                        </span>
                                    </td>
                                    <td>"<?= htmlspecialchars($rev['Comment'] ?: 'No comment left') ?>"</td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
