<?php
$required_role = 'admin';
require_once __DIR__ . '/../includes/auth_check.php';

$error = "";
$success = "";

// Handle adding a new service
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_service'])) {
    $service_name = trim($_POST['service_name']);
    $price = floatval($_POST['price']);
    $description = trim($_POST['description']);

    if ($service_name === "" || $price <= 0) {
        $error = "Please fill in valid name and price details.";
    } else {
        $stmt = $conn->prepare("INSERT INTO ServiceCategory (ServiceName, Price, Description) VALUES (?, ?, ?)");
        $stmt->bind_param("sds", $service_name, $price, $description);
        if ($stmt->execute()) {
            $success = "New service package added successfully!";
        } else {
            $error = "Failed to add service. It may already exist.";
        }
    }
}

// Handle updating service price
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_price'])) {
    $service_id = intval($_POST['service_id']);
    $new_price = floatval($_POST['new_price']);

    if ($service_id > 0 && $new_price > 0) {
        $stmt = $conn->prepare("UPDATE ServiceCategory SET Price = ? WHERE ServiceID = ?");
        $stmt->bind_param("di", $new_price, $service_id);
        if ($stmt->execute()) {
            $success = "Pricing updated successfully!";
        } else {
            $error = "Failed to update pricing.";
        }
    }
}

// Handle deleting a service category
if (isset($_GET['delete'])) {
    $service_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM ServiceCategory WHERE ServiceID = ?");
    $stmt->bind_param("i", $service_id);
    try {
        if ($stmt->execute()) {
            $success = "Service category removed successfully.";
        } else {
            $error = "Failed to delete service category.";
        }
    } catch (Exception $e) {
        $error = "Cannot delete service. Active service requests are linked to it.";
    }
}

// Fetch all services
$services = $conn->query("SELECT ServiceID, ServiceName, Price, Description FROM ServiceCategory ORDER BY ServiceName ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Services - HSMS Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="page-container">
        <h2>Service Packages & Pricing</h2>
        <p>Define new service items, manage descriptions, and adjust local pricing.</p>

        <?php if ($error): ?>
            <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div class="dashboard-grid" style="align-items: start;">
            <!-- Service Listing -->
            <div class="table-wrapper" style="grid-column: span 2; margin-top: 0;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Service ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Current Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($services->num_rows === 0): ?>
                            <tr><td colspan="5">No services configured.</td></tr>
                        <?php else: ?>
                            <?php while ($row = $services->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?= $row['ServiceID'] ?></td>
                                    <td><strong><?= htmlspecialchars($row['ServiceName']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['Description'] ?: 'No description provided') ?></td>
                                    <td>
                                        <form method="POST" action="" class="inline-form" style="margin-top:0;">
                                            <input type="hidden" name="update_price" value="1">
                                            <input type="hidden" name="service_id" value="<?= $row['ServiceID'] ?>">
                                            <input type="number" name="new_price" value="<?= $row['Price'] ?>" step="50" min="100" style="width: 100px; padding: 6px; margin-bottom: 0; display: inline-block;">
                                            <button type="submit" class="btn-small">Update</button>
                                        </form>
                                    </td>
                                    <td>
                                        <a href="?delete=<?= $row['ServiceID'] ?>" class="status-badge status-cancelled" style="text-decoration:none;" onclick="return confirm('Are you sure you want to delete this service?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Create Service form -->
            <div class="form-container" style="margin: 0; padding: 30px;">
                <h3 style="margin-bottom: 20px;">Add New Service</h3>
                <form method="POST" action="">
                    <input type="hidden" name="add_service" value="1">

                    <label>Service Name *</label>
                    <input type="text" name="service_name" placeholder="e.g. Garden Landscaping" required>

                    <label>Base Price (Rs.) *</label>
                    <input type="number" name="price" min="10" placeholder="1500.00" required style="width: 100%; padding: 12px; font-size: 16px; border: 1px solid #ccc; border-radius: 6px; margin-bottom: 20px;">

                    <label>Description</label>
                    <textarea name="description" rows="3" placeholder="Brief detail about what's covered under this service package"></textarea>

                    <button type="submit" class="btn-large">Create Service Package</button>
                </form>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
