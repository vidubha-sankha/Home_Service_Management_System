<?php
$required_role = 'admin';
require_once __DIR__ . '/../includes/auth_check.php';

$logs = $conn->query("
    SELECT * FROM NotificationLog 
    ORDER BY created_at DESC 
    LIMIT 100
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Logs - HSMS Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="page-container">
        <h2>Notification Logs</h2>
        <p>Monitor all outgoing SMS and Email notifications.</p>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User Type</th>
                        <th>Channel</th>
                        <th>Notification Type</th>
                        <th>Request #</th>
                        <th>Status</th>
                        <th>Date Sent</th>
                        <th>Error (if any)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($logs->num_rows === 0): ?>
                        <tr><td colspan="8">No notifications sent yet.</td></tr>
                    <?php else: ?>
                        <?php while ($log = $logs->fetch_assoc()): ?>
                            <tr>
                                <td>#<?= $log['id'] ?></td>
                                <td><?= htmlspecialchars($log['user_type']) ?></td>
                                <td><?= htmlspecialchars($log['channel']) ?></td>
                                <td><?= htmlspecialchars($log['notification_type']) ?></td>
                                <td><?= $log['request_id'] ? '#' . $log['request_id'] : '-' ?></td>
                                <td>
                                    <span class="status-badge <?= $log['status'] === 'SENT' ? 'status-completed' : ($log['status'] === 'FAILED' ? 'status-cancelled' : 'status-pending') ?>">
                                        <?= $log['status'] ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($log['sent_at'] ?? $log['created_at']) ?></td>
                                <td style="color:red; font-size:12px;"><?= htmlspecialchars($log['error_message']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
