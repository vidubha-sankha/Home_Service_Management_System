<?php
$required_role = 'employee';
require_once __DIR__ . '/../includes/auth_check.php';

$employee_id = $_SESSION['employee_id'];
$message = "";

// Handle status update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $request_id = $_POST['request_id'];
    $new_status = $_POST['new_status'];

    $allowed_statuses = ['In Progress', 'Completed'];
    if (in_array($new_status, $allowed_statuses)) {
        $stmt = $conn->prepare("UPDATE ServiceRequest SET Status = ? WHERE RequestID = ? AND EmployeeID = ?");
        $stmt->bind_param("sii", $new_status, $request_id, $employee_id);
        if ($stmt->execute()) {
            $message = "Job #$request_id marked as $new_status.";
            require_once __DIR__ . '/../includes/notifications/NotificationService.php';
            $ns = new NotificationService($conn);
            if ($new_status === 'In Progress') {
                $ns->notifyServiceStarted($request_id);
            } elseif ($new_status === 'Completed') {
                $ns->notifyServiceCompleted($request_id);
            }
        }
    }
}

$jobs = $conn->prepare("
    SELECT sr.RequestID, c.Name AS CustomerName, c.Phone, c.Address, sc.ServiceName,
           sr.PreferredDate, sr.Description, sr.Status, sr.PhotoPath, sr.Latitude, sr.Longitude
    FROM ServiceRequest sr
    JOIN Customer c ON sr.CustomerID = c.CustomerID
    JOIN ServiceCategory sc ON sr.ServiceID = sc.ServiceID
    WHERE sr.EmployeeID = ? AND sr.Status != 'Cancelled'
    ORDER BY sr.PreferredDate ASC
");
$jobs->bind_param("i", $employee_id);
$jobs->execute();
$result = $jobs->get_result();

// Retrieve stats
$stmt_assigned = $conn->prepare("SELECT COUNT(*) as cnt FROM ServiceRequest WHERE EmployeeID = ? AND Status = 'Assigned'");
$stmt_assigned->bind_param("i", $employee_id);
$stmt_assigned->execute();
$assigned_cnt = $stmt_assigned->get_result()->fetch_assoc()['cnt'];

$stmt_in_progress = $conn->prepare("SELECT COUNT(*) as cnt FROM ServiceRequest WHERE EmployeeID = ? AND Status = 'In Progress'");
$stmt_in_progress->bind_param("i", $employee_id);
$stmt_in_progress->execute();
$in_progress_cnt = $stmt_in_progress->get_result()->fetch_assoc()['cnt'];

$stmt_completed = $conn->prepare("SELECT COUNT(*) as cnt FROM ServiceRequest WHERE EmployeeID = ? AND Status = 'Completed'");
$stmt_completed->bind_param("i", $employee_id);
$stmt_completed->execute();
$completed_cnt = $stmt_completed->get_result()->fetch_assoc()['cnt'];

$stmt_rating = $conn->prepare("SELECT Rating FROM Employee WHERE EmployeeID = ?");
$stmt_rating->bind_param("i", $employee_id);
$stmt_rating->execute();
$rating = $stmt_rating->get_result()->fetch_assoc()['Rating'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Jobs - HSMS Technician</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="page-container">

        <h2>Technician Dashboard</h2>
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-label">Assigned Jobs</div>
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
                <div class="stat-label">My Rating</div>
                <div class="stat-value">★ <?= number_format($rating, 1) ?></div>
            </div>
        </div>

        <h2>My Jobs</h2>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($result->num_rows === 0): ?>
            <p>No jobs assigned right now.</p>
        <?php endif; ?>

        <?php while ($job = $result->fetch_assoc()): ?>
            <div class="job-card">
                <h3>#<?= $job['RequestID'] ?> — <?= htmlspecialchars($job['ServiceName']) ?></h3>
                <p><strong>Customer:</strong> <?= htmlspecialchars($job['CustomerName']) ?> (<?= htmlspecialchars($job['Phone']) ?>)</p>
                <p>
                    <strong>Address:</strong> <?= htmlspecialchars($job['Address']) ?>
                    <?php if (!empty($job['Latitude']) && !empty($job['Longitude'])): ?>
                        <br>
                        <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($job['Latitude'] . ',' . $job['Longitude']) ?>" target="_blank" class="map-link">🗺️ Navigate with Map</a>
                    <?php endif; ?>
                </p>
                <p><strong>Preferred Date:</strong> <?= htmlspecialchars($job['PreferredDate']) ?></p>
                <p><strong>Problem:</strong> <?= htmlspecialchars($job['Description']) ?></p>
                <?php if ($job['PhotoPath']): ?>
                    <img src="../<?= htmlspecialchars($job['PhotoPath']) ?>" class="job-photo" alt="Problem photo">
                <?php endif; ?>
                <p><strong>Status:</strong> <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $job['Status'])) ?>"><?= $job['Status'] ?></span></p>

                <?php if ($job['Status'] !== 'Completed'): ?>
                    <form method="POST" action="" class="inline-form">
                        <input type="hidden" name="request_id" value="<?= $job['RequestID'] ?>">
                        <?php if ($job['Status'] === 'Assigned'): ?>
                            <button type="submit" name="new_status" value="In Progress" class="btn-small">Start Job</button>
                        <?php elseif ($job['Status'] === 'In Progress'): ?>
                            <button type="submit" name="new_status" value="Completed" class="btn-small">Mark Completed</button>
                        <?php endif; ?>
                    </form>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
