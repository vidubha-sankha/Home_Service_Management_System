<?php 
session_start(); 
require_once __DIR__ . '/config/db.php';

// Fetch the service categories to display on the landing page
$services = $conn->query("SELECT ServiceName, Price, Description FROM ServiceCategory LIMIT 6");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Service Management System - Trusted Home Services</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>

    <div class="page-container">
        <!-- Hero Section -->
        <section class="hero-section">
            <h1>Your Trusted Partner for <span>Home Services</span></h1>
            <p>Book expert and background-verified local technicians for repairs, maintenance, and installations at transparent upfront rates.</p>
            
            <div class="cta-grid">
                <a href="customer/login.php" class="cta-card">
                    <div class="cta-icon">👤</div>
                    <h3>I'm a Customer</h3>
                    <p>Book services, track requests in real-time, and rate our professional technicians.</p>
                    <span class="cta-button">Customer Login</span>
                </a>
                
                <a href="technician/login.php" class="cta-card">
                    <div class="cta-icon">🛠️</div>
                    <h3>I'm a Technician</h3>
                    <p>Access your assigned tasks, update job progress, and check customer feedback.</p>
                    <span class="cta-button" style="background: var(--dark);">Technician Login</span>
                </a>

                <a href="admin/login.php" class="cta-card">
                    <div class="cta-icon">💼</div>
                    <h3>I'm an Admin</h3>
                    <p>Manage bookings, handle system configurations, assign tasks, and view reports.</p>
                    <span class="cta-button" style="background: var(--accent);">Admin Dashboard</span>
                </a>
            </div>
        </section>

        <!-- Service Categories -->
        <h2 class="section-title">Our Featured Services</h2>
        <div class="category-grid">
            <?php 
            $icons = [
                'TV Repair' => '📺',
                'Plumbing' => '🚰',
                'Electrical Services' => '⚡',
                'AC Repair & Maintenance' => '❄️',
                'RO Water Filter Service' => '💧',
                'Carpenter Services' => '🪚',
                'Painting' => '🎨',
                'CCTV Installation' => '📹',
                'Refrigerator Repair' => '🧊',
                'Washing Machine Repair' => '🧺'
            ];
            
            if ($services && $services->num_rows > 0): 
                while ($row = $services->fetch_assoc()):
                    $name = $row['ServiceName'];
                    $icon = isset($icons[$name]) ? $icons[$name] : '🔧';
            ?>
                <div class="category-card">
                    <div class="category-icon"><?= $icon ?></div>
                    <div class="category-name"><?= htmlspecialchars($name) ?></div>
                    <div class="category-price">Starts from Rs. <?= number_format($row['Price'], 2) ?></div>
                </div>
            <?php 
                endwhile; 
            else:
            ?>
                <div class="category-card"><div class="category-icon">📺</div><div class="category-name">TV Repair</div></div>
                <div class="category-card"><div class="category-icon">🚰</div><div class="category-name">Plumbing</div></div>
                <div class="category-card"><div class="category-icon">⚡</div><div class="category-name">Electrical Services</div></div>
                <div class="category-card"><div class="category-icon">❄️</div><div class="category-name">AC Repair</div></div>
                <div class="category-card"><div class="category-icon">💧</div><div class="category-name">Water Filter</div></div>
                <div class="category-card"><div class="category-icon">🪚</div><div class="category-name">Carpentry</div></div>
            <?php endif; ?>
        </div>
    </div>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
