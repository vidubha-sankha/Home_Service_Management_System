<?php
require_once __DIR__ . '/../config/db.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($name === "" || $phone === "" || $email === "" || $password === "") {
        $error = "Please fill in all required fields.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // check if email already exists
        $stmt = $conn->prepare("SELECT CustomerID FROM Customer WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = "An account with this email already exists.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO Customer (Name, Phone, Email, Address, Password) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $phone, $email, $address, $hashed);
            if ($stmt->execute()) {
                $success = "Registration successful! You can now log in.";
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Register - HSMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="page-container">
        <div class="form-container">
            <h2>Customer Registration</h2>
            <p>Create an account to get professional home repair and assistance.</p>

            <?php if ($error): ?>
                <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    ✅ <?= htmlspecialchars($success) ?> 
                    <a href="login.php" style="color: inherit; text-decoration: underline; margin-left: 8px;">Go to Login</a>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <label>Full Name *</label>
                <input type="text" name="name" placeholder="John Doe" required>

                <label>Phone Number *</label>
                <input type="text" name="phone" placeholder="077XXXXXXX" required>

                <label>Email Address *</label>
                <input type="email" name="email" placeholder="email@example.com" required>

                <label>Address</label>
                <textarea name="address" rows="3" placeholder="Enter your full street address"></textarea>

                <label>Password *</label>
                <input type="password" name="password" placeholder="Min. 6 characters" required>

                <label>Confirm Password *</label>
                <input type="password" name="confirm_password" placeholder="••••••••" required>

                <button type="submit" class="btn-large">Register</button>
            </form>

            <div class="forgot-register-links">
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>

