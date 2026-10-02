<?php
$required_role = 'customer';
require_once __DIR__ . '/../includes/auth_check.php';

$customer_id = $_SESSION['customer_id'];

$stmt = $conn->prepare("
    SELECT sr.RequestID, sc.ServiceName, sc.Price, sr.PreferredDate, sr.Status, sr.IsEmergency,
           e.Name AS TechnicianName, e.Phone AS TechnicianPhone
    FROM ServiceRequest sr
    JOIN ServiceCategory sc ON sr.ServiceID = sc.ServiceID
    LEFT JOIN Employee e ON sr.EmployeeID = e.EmployeeID
    WHERE sr.CustomerID = ?
    ORDER BY sr.RequestDate DESC
");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$requests = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Requests - HSMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="page-container">
        <h2>My Service Requests</h2>
        <p>View historical and pending requests, and leave ratings for completed jobs.</p>

        <div class="table-wrapper">
            <table class="data-table">

            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>Service</th>
                    <th>Price</th>
                    <th>Preferred Date</th>
                    <th>Technician</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($requests->num_rows === 0): ?>
                    <tr><td colspan="6">No service requests yet.</td></tr>
                <?php else: ?>
                    <?php while ($row = $requests->fetch_assoc()): ?>
                        <tr>
                            <td>#<?= $row['RequestID'] ?> <?= $row['IsEmergency'] ? '🔴 URGENT' : '' ?></td>
                            <td><?= htmlspecialchars($row['ServiceName']) ?></td>
                            <td>Rs. <?= number_format($row['Price'], 2) ?></td>
                            <td><?= htmlspecialchars($row['PreferredDate']) ?></td>
                            <td><?= $row['TechnicianName'] ? htmlspecialchars($row['TechnicianName']) . ' (' . $row['TechnicianPhone'] . ')' : 'Not yet assigned' ?></td>
                            <td><span class="status-badge status-<?= strtolower(str_replace(' ', '-', $row['Status'])) ?>"><?= $row['Status'] ?></span></td>
                            <td>
                                <?php if ($row['Status'] === 'Completed'): ?>
                                    <a href="review.php?request_id=<?= $row['RequestID'] ?>">Leave Review</a>
                                <?php endif; ?>
                            </td>
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

