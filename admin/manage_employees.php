<?php
$required_role = 'admin';
require_once __DIR__ . '/../includes/auth_check.php';

$error = "";
$success = "";

// Handle registering a new employee
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['register_employee'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $specialization = trim($_POST['specialization']);
    $experience = intval($_POST['experience']);

    if ($name === "" || $phone === "" || $email === "" || $password === "" || $specialization === "") {
        $error = "Please fill in all required fields.";
    } else {
        // check if email already exists
        $stmt = $conn->prepare("SELECT EmployeeID FROM Employee WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = "A technician account with this email already exists.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt_insert = $conn->prepare("INSERT INTO Employee (Name, Phone, Email, Password, Specialization, Experience, Availability) VALUES (?, ?, ?, ?, ?, ?, 'Available')");
            $stmt_insert->bind_param("sssssi", $name, $phone, $email, $hashed, $specialization, $experience);
            if ($stmt_insert->execute()) {
                $success = "Technician account registered successfully!";
            } else {
                $error = "Failed to register technician. Try again.";
            }
        }
    }
}

// Handle deleting a technician
if (isset($_GET['delete'])) {
    $employee_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM Employee WHERE EmployeeID = ?");
    $stmt->bind_param("i", $employee_id);
    try {
        if ($stmt->execute()) {
            $success = "Technician account removed successfully.";
        } else {
            $error = "Failed to delete technician.";
        }
    } catch (Exception $e) {
        $error = "Cannot delete technician. Active jobs are linked to this account.";
    }
}

// Fetch all technicians
$technicians = $conn->query("SELECT EmployeeID, Name, Phone, Email, Specialization, Experience, Availability, Rating FROM Employee ORDER BY Name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Technicians - HSMS Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="page-container">
        <h2>Manage Technicians</h2>
        <p>Register new service technicians and check their availability and ratings.</p>

        <?php if ($error): ?>
            <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div class="dashboard-grid" style="align-items: start;">
            <!-- Technician List -->
            <div class="table-wrapper" style="grid-column: span 2; margin-top: 0;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Specialization</th>
                            <th>Experience</th>
                            <th>Status</th>
                            <th>Rating</th>
                            <th>Contact</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($technicians->num_rows === 0): ?>
                            <tr><td colspan="7">No technicians registered.</td></tr>
                        <?php else: ?>
                            <?php while ($row = $technicians->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?= $row['EmployeeID'] ?></td>
                                    <td><strong><?= htmlspecialchars($row['Name']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['Specialization']) ?></td>
                                    <td><?= $row['Experience'] ?> Years</td>
                                    <td>
                                        <?php
                                        $badge = 'status-completed';
                                        if ($row['Availability'] === 'Busy') $badge = 'status-assigned';
                                        if ($row['Availability'] === 'Offline') $badge = 'status-cancelled';
                                        ?>
                                        <span class="status-badge <?= $badge ?>"><?= $row['Availability'] ?></span>
                                    </td>
                                    <td>★ <?= number_format($row['Rating'], 1) ?></td>
                                    <td>
                                        <?= htmlspecialchars($row['Phone']) ?><br>
                                        <small><?= htmlspecialchars($row['Email']) ?></small>
                                    </td>
                                    <td>
                                        <a href="?delete=<?= $row['EmployeeID'] ?>" class="status-badge status-cancelled" style="text-decoration:none;" onclick="return confirm('Are you sure you want to delete this technician?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Add Technician Form -->
            <div class="form-container" style="margin: 0; padding: 30px;">
                <h3 style="margin-bottom: 20px;">Add New Technician</h3>
                <form method="POST" action="">
                    <input type="hidden" name="register_employee" value="1">
                    
                    <label>Full Name *</label>
                    <input type="text" name="name" required>

                    <label>Phone Number *</label>
                    <input type="text" name="phone" required>

                    <label>Email *</label>
                    <input type="email" name="email" required>

                    <label>Password *</label>
                    <input type="password" name="password" required>

                    <label>Specialization *</label>
                    <select name="specialization" required>
                        <option value="">-- Choose --</option>
                        <option value="TV Repair">TV Repair</option>
                        <option value="Plumbing">Plumbing</option>
                        <option value="Electrical Services">Electrical Services</option>
                        <option value="AC Repair & Maintenance">AC Repair & Maintenance</option>
                        <option value="RO Water Filter Service">RO Water Filter Service</option>
                        <option value="Carpenter Services">Carpenter Services</option>
                        <option value="Painting">Painting</option>
                        <option value="CCTV Installation">CCTV Installation</option>
                        <option value="Refrigerator Repair">Refrigerator Repair</option>
                        <option value="Washing Machine Repair">Washing Machine Repair</option>
                    </select>

                    <label>Experience (Years) *</label>
                    <input type="number" name="experience" min="0" max="50" required style="width: 100%; padding: 12px; font-size: 16px; border: 1px solid #ccc; border-radius: 6px; margin-bottom: 20px;">

                    <button type="submit" class="btn-large">Register Technician</button>
                </form>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
