<?php
$required_role = 'customer';
require_once __DIR__ . '/../includes/auth_check.php';

$customer_id = $_SESSION['customer_id'];
$request_id = isset($_GET['request_id']) ? intval($_GET['request_id']) : 0;
$error = "";
$success = "";

// Verify the service request belongs to this customer and is Completed
$stmt = $conn->prepare("
    SELECT sr.RequestID, sr.EmployeeID, e.Name as TechnicianName, sc.ServiceName
    FROM ServiceRequest sr
    JOIN ServiceCategory sc ON sr.ServiceID = sc.ServiceID
    JOIN Employee e ON sr.EmployeeID = e.EmployeeID
    WHERE sr.RequestID = ? AND sr.CustomerID = ? AND sr.Status = 'Completed'
");
$stmt->bind_param("ii", $request_id, $customer_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows !== 1) {
    die("Invalid request or service is not completed yet.");
}

$request_data = $res->fetch_assoc();
$employee_id = $request_data['EmployeeID'];
$technician_name = $request_data['TechnicianName'];
$service_name = $request_data['ServiceName'];

// Handle review form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);

    if ($rating < 1 || $rating > 5) {
        $error = "Please choose a rating between 1 and 5 stars.";
    } else {
        // Insert review
        $stmt_insert = $conn->prepare("INSERT INTO Review (CustomerID, EmployeeID, RequestID, Rating, Comment) VALUES (?, ?, ?, ?, ?)");
        $stmt_insert->bind_param("iiiis", $customer_id, $employee_id, $request_id, $rating, $comment);
        
        if ($stmt_insert->execute()) {
            // Update the technician's average rating in the Employee table
            $stmt_avg = $conn->prepare("
                UPDATE Employee 
                SET Rating = (SELECT AVG(Rating) FROM Review WHERE EmployeeID = ?) 
                WHERE EmployeeID = ?
            ");
            $stmt_avg->bind_param("ii", $employee_id, $employee_id);
            $stmt_avg->execute();

            $success = "Thank you for your feedback! Review submitted successfully.";
        } else {
            $error = "Failed to submit review. You may have already reviewed this request.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Review - HSMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="page-container">
        <div class="form-container" style="max-width: 600px; margin: 20px auto;">
            <h2>Leave a Review</h2>
            <p>Rate the service provided by <strong><?= htmlspecialchars($technician_name) ?></strong> for your **<?= htmlspecialchars($service_name) ?>** job (#<?= $request_id ?>).</p>

            <?php if ($error): ?>
                <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
                <p><a href="track_service.php" class="btn-small" style="text-decoration:none; display:inline-block; margin-top:10px;">Back to My Requests</a></p>
            <?php else: ?>
                <form method="POST" action="">
                    <label>Rating (1 to 5 Stars)</label>
                    <div class="star-rating">
                        <span class="star" data-value="1">★</span>
                        <span class="star" data-value="2">★</span>
                        <span class="star" data-value="3">★</span>
                        <span class="star" data-value="4">★</span>
                        <span class="star" data-value="5">★</span>
                    </div>
                    <input type="hidden" name="rating" id="rating-input" required>

                    <label>Comments / Feedback</label>
                    <textarea name="comment" rows="5" placeholder="Share your experience with this technician..."></textarea>

                    <button type="submit" class="btn-large">Submit Review</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const stars = document.querySelectorAll('.star-rating .star');
            const ratingInput = document.getElementById('rating-input');

            stars.forEach((star, index) => {
                star.addEventListener('click', () => {
                    const ratingValue = star.getAttribute('data-value');
                    ratingInput.value = ratingValue;

                    // Update UI state
                    stars.forEach((s, i) => {
                        if (i <= index) {
                            s.classList.add('active');
                        } else {
                            s.classList.remove('active');
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>
