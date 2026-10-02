<?php
$required_role = 'customer';
require_once __DIR__ . '/../includes/auth_check.php';

$customer_name = htmlspecialchars($_SESSION['customer_name']);
$customer_id = $_SESSION['customer_id'];

// Retrieve stats
$stmt_pending = $conn->prepare("SELECT COUNT(*) as cnt FROM ServiceRequest WHERE CustomerID = ? AND Status = 'Pending'");
$stmt_pending->bind_param("i", $customer_id);
$stmt_pending->execute();
$pending_cnt = $stmt_pending->get_result()->fetch_assoc()['cnt'];

$stmt_assigned = $conn->prepare("SELECT COUNT(*) as cnt FROM ServiceRequest WHERE CustomerID = ? AND Status = 'Assigned'");
$stmt_assigned->bind_param("i", $customer_id);
$stmt_assigned->execute();
$assigned_cnt = $stmt_assigned->get_result()->fetch_assoc()['cnt'];

$stmt_in_progress = $conn->prepare("SELECT COUNT(*) as cnt FROM ServiceRequest WHERE CustomerID = ? AND Status = 'In Progress'");
$stmt_in_progress->bind_param("i", $customer_id);
$stmt_in_progress->execute();
$in_progress_cnt = $stmt_in_progress->get_result()->fetch_assoc()['cnt'];

$stmt_completed = $conn->prepare("SELECT COUNT(*) as cnt FROM ServiceRequest WHERE CustomerID = ? AND Status = 'Completed'");
$stmt_completed->bind_param("i", $customer_id);
$stmt_completed->execute();
$completed_cnt = $stmt_completed->get_result()->fetch_assoc()['cnt'];

$stmt_cancelled = $conn->prepare("SELECT COUNT(*) as cnt FROM ServiceRequest WHERE CustomerID = ? AND Status = 'Cancelled'");
$stmt_cancelled->bind_param("i", $customer_id);
$stmt_cancelled->execute();
$cancelled_cnt = $stmt_cancelled->get_result()->fetch_assoc()['cnt'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - HSMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="page-container">
        <h2>Welcome back, <?= $customer_name ?>! 🌟</h2>
        <p>Manage your home repairs, track technician statuses, or schedule a new service.</p>

        <!-- Stats Overview -->
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-label">Pending</div>
                <div class="stat-value" style="color: var(--text-muted);"><?= $pending_cnt ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Assigned</div>
                <div class="stat-value" style="color: var(--primary);"><?= $assigned_cnt ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">In Progress</div>
                <div class="stat-value" style="color: var(--accent);"><?= $in_progress_cnt ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Completed</div>
                <div class="stat-value" style="color: var(--text-main);"><?= $completed_cnt ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Cancelled</div>
                <div class="stat-value" style="color: red;"><?= $cancelled_cnt ?></div>
            </div>
        </div>

        <div class="dashboard-grid">
            <a class="dashboard-card" href="book_service.php">
                <h3>🗓️ Book a New Service</h3>
                <p>Schedule a professional technician for plumbing, electrical, AC repair, carpentry, and more.</p>
            </a>
            <a class="dashboard-card" href="track_service.php">
                <h3>🔍 Track My Requests</h3>
                <p>Monitor status, view assigned technician details, and review completed jobs.</p>
            </a>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>

