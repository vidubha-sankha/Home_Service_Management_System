<?php
$required_role = 'admin';
require_once __DIR__ . '/../includes/auth_check.php';

// Fetch all customers along with their booking stats
$customers = $conn->query("
    SELECT c.CustomerID, c.Name, c.Phone, c.Email, c.Address, c.CreatedAt, 
           COUNT(sr.RequestID) as TotalBookings
    FROM Customer c
    LEFT JOIN ServiceRequest sr ON c.CustomerID = sr.CustomerID
    GROUP BY c.CustomerID
    ORDER BY c.Name ASC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Customers - HSMS Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="page-container">
        <h2>Registered Customers</h2>
        <p>View registered clients, their contact information, and total booking counts.</p>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Address</th>
                        <th>Total Bookings</th>
                        <th>Joined On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($customers->num_rows === 0): ?>
                        <tr><td colspan="7">No registered customers found.</td></tr>
                    <?php else: ?>
                        <?php while ($row = $customers->fetch_assoc()): ?>
                            <tr>
                                <td>#<?= $row['CustomerID'] ?></td>
                                <td><strong><?= htmlspecialchars($row['Name']) ?></strong></td>
                                <td><?= htmlspecialchars($row['Phone']) ?></td>
                                <td><a href="mailto:<?= htmlspecialchars($row['Email']) ?>"><?= htmlspecialchars($row['Email']) ?></a></td>
                                <td><?= htmlspecialchars($row['Address'] ?: 'N/A') ?></td>
                                <td><span class="status-badge status-assigned" style="font-size:12px;"><?= $row['TotalBookings'] ?> Bookings</span></td>
                                <td><?= date('Y-m-d', strtotime($row['CreatedAt'])) ?></td>
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
