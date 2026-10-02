<?php
// includes/header.php

$unread_notifications_count = 0;
$n_user_id = 0;
$n_user_type = '';

if (isset($_SESSION['customer_id'])) {
    $n_user_id = $_SESSION['customer_id'];
    $n_user_type = 'CUSTOMER';
} elseif (isset($_SESSION['admin_id'])) {
    $n_user_id = $_SESSION['admin_id'];
    $n_user_type = 'ADMIN';
} elseif (isset($_SESSION['employee_id'])) {
    $n_user_id = $_SESSION['employee_id'];
    $n_user_type = 'TECHNICIAN';
}

if ($n_user_id > 0 && isset($conn)) {
    $stmt_nav = $conn->prepare("SELECT COUNT(*) as unread FROM InAppNotification WHERE user_id = ? AND user_type = ? AND is_read = 0");
    if ($stmt_nav) {
        $stmt_nav->bind_param("is", $n_user_id, $n_user_type);
        $stmt_nav->execute();
        $unread_notifications_count = $stmt_nav->get_result()->fetch_assoc()['unread'];
    }
}
?>
<header class="site-header">
    <a href="/index.php" class="logo">🛠️ HSMS</a>
    <nav>
        <?php if (isset($_SESSION['customer_id'])): ?>
            <a href="/customer/dashboard.php">Dashboard</a>
            <a href="/customer/book_service.php">Book Service</a>
            <a href="/customer/track_service.php">My Requests</a>
            <a href="/notifications.php">🔔 (<?= $unread_notifications_count ?>)</a>
            <a href="/logout.php" class="logout-btn">Logout (<?= htmlspecialchars($_SESSION['customer_name']) ?>)</a>
        <?php elseif (isset($_SESSION['admin_id'])): ?>
            <a href="/admin/dashboard.php">Dashboard</a>
            <a href="/admin/manage_customers.php">Customers</a>
            <a href="/admin/manage_employees.php">Employees</a>
            <a href="/admin/manage_services.php">Services</a>
            <a href="/admin/assign_job.php">Assign Jobs</a>
            <a href="/admin/reports.php">Reports</a>
            <a href="/admin/notification_log.php">Logs</a>
            <a href="/notifications.php">🔔 (<?= $unread_notifications_count ?>)</a>
            <a href="/logout.php" class="logout-btn">Logout</a>
        <?php elseif (isset($_SESSION['employee_id'])): ?>
            <a href="/technician/dashboard.php">My Jobs</a>
            <a href="/notifications.php">🔔 (<?= $unread_notifications_count ?>)</a>
            <a href="/logout.php" class="logout-btn">Logout (<?= htmlspecialchars($_SESSION['employee_name']) ?>)</a>
        <?php else: ?>
            <a href="/index.php">Home</a>
            <a href="/customer/login.php">Customer Login</a>
            <a href="/admin/login.php">Admin Login</a>
            <a href="/technician/login.php">Technician Login</a>
        <?php endif; ?>
    </nav>
</header>

