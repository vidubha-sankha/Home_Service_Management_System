<?php
// We don't have a single $required_role because it's shared.
require_once __DIR__ . '/config/db.php';

// Custom auth check since it is cross-role
$user_id = 0;
$user_type = '';

if (isset($_SESSION['customer_id'])) {
    $user_id = $_SESSION['customer_id'];
    $user_type = 'CUSTOMER';
} elseif (isset($_SESSION['admin_id'])) {
    $user_id = $_SESSION['admin_id'];
    $user_type = 'ADMIN';
} elseif (isset($_SESSION['employee_id'])) {
    $user_id = $_SESSION['employee_id'];
    $user_type = 'TECHNICIAN';
} else {
    header("Location: /index.php");
    exit;
}

// Mark all as read when visited
$stmt_update = $conn->prepare("UPDATE InAppNotification SET is_read = 1 WHERE user_id = ? AND user_type = ?");
$stmt_update->bind_param("is", $user_id, $user_type);
$stmt_update->execute();

$stmt = $conn->prepare("SELECT * FROM InAppNotification WHERE user_id = ? AND user_type = ? ORDER BY created_at DESC LIMIT 50");
$stmt->bind_param("is", $user_id, $user_type);
$stmt->execute();
$notifications = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Notifications - HSMS</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>

    <div class="page-container">
        <h2>🔔 Notifications</h2>

        <?php if ($notifications->num_rows === 0): ?>
            <p>You have no new notifications.</p>
        <?php else: ?>
            <div class="dashboard-grid" style="grid-template-columns: 1fr;">
            <?php while ($notif = $notifications->fetch_assoc()): ?>
                <div class="dashboard-card" style="padding: 15px; margin-bottom: 10px;">
                    <p style="margin: 0;"><strong><?= htmlspecialchars($notif['message']) ?></strong></p>
                    <small style="color: var(--text-muted);"><?= htmlspecialchars($notif['created_at']) ?></small>
                </div>
            <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
