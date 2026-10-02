<?php
require_once __DIR__ . '/../config/db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT EmployeeID, Name, Password FROM Employee WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $emp = $result->fetch_assoc();
        if (password_verify($password, $emp['Password'])) {
            $_SESSION['employee_id'] = $emp['EmployeeID'];
            $_SESSION['employee_name'] = $emp['Name'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "No technician account found with that email.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Technician Login - HSMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="page-container">
        <div class="form-container">
            <h2>Technician Login</h2>
            <p>Sign in to view assignments and update work status.</p>

            <?php if ($error): ?>
                <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <label>Technician Email</label>
                <input type="email" name="email" placeholder="technician@hsms.com" required>

                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>

                <button type="submit" class="btn-large" style="background: var(--primary);">Login</button>
            </form>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>

