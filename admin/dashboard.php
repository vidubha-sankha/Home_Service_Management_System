<?php
$required_role = 'admin';
require_once __DIR__ . '/../includes/auth_check.php';

// Fetch stats for dashboard cards
$pending_cnt = $conn->query("SELECT COUNT(*) as cnt FROM ServiceRequest WHERE Status = 'Pending'")->fetch_assoc()['cnt'];
$active_cnt = $conn->query("SELECT COUNT(*) as cnt FROM ServiceRequest WHERE Status IN ('Assigned', 'In Progress')")->fetch_assoc()['cnt'];
$completed_cnt = $conn->query("SELECT COUNT(*) as cnt FROM ServiceRequest WHERE Status = 'Completed'")->fetch_assoc()['cnt'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - HSMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="page-container">
        <h2>Admin Portal</h2>
        <p>Manage customers, technicians, pricing packages, and check platform performance.</p>

        <!-- Stats Grid -->
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-label">Pending Assignments</div>
                <div class="stat-value" style="color: var(--accent);"><?= $pending_cnt ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Active Jobs</div>
                <div class="stat-value" style="color: var(--primary);"><?= $active_cnt ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Completed Jobs</div>
                <div class="stat-value" style="color: var(--text-main);"><?= $completed_cnt ?></div>
            </div>
        </div>

        <div class="dashboard-grid">
            <a class="dashboard-card" href="manage_customers.php">
                <h3>👥 Manage Customers</h3>
                <p>View registered clients, their contact information, and service histories.</p>
            </a>
            <a class="dashboard-card" href="manage_employees.php">
                <h3>🛠️ Manage Technicians</h3>
                <p>Add and view technician accounts, specializations, and availability states.</p>
            </a>
            <a class="dashboard-card" href="manage_services.php">
                <h3>💰 Service Packages</h3>
                <p>Configure plumbing, electrical, and other service category definitions and rates.</p>
            </a>
            <a class="dashboard-card" href="assign_job.php">
                <h3>🗓️ Assign Pending Jobs</h3>
                <p>Assign incoming requests to matching available technicians.</p>
            </a>
            <a class="dashboard-card" href="reports.php" style="grid-column: 1 / -1;">
                <h3>📊 Performance & Earnings Reports</h3>
                <p>Get data insights on average technician ratings, requests by type, and overall earnings.</p>
            </a>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>

