<?php
$required_role = 'customer';
require_once __DIR__ . '/../includes/auth_check.php';

$error = "";
$success = "";

// fetch service categories for dropdown
$services = $conn->query("SELECT ServiceID, ServiceName, Price FROM ServiceCategory ORDER BY ServiceName");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $customer_id = $_SESSION['customer_id'];
    $service_id = $_POST['service_id'];
    $address = trim($_POST['address']);
    $description = trim($_POST['description']);
    $preferred_date = $_POST['preferred_date'];
    $preferred_time = $_POST['preferred_time'];
    $is_emergency = isset($_POST['is_emergency']) ? 1 : 0;
    $latitude = isset($_POST['latitude']) && $_POST['latitude'] !== "" ? $_POST['latitude'] : null;
    $longitude = isset($_POST['longitude']) && $_POST['longitude'] !== "" ? $_POST['longitude'] : null;
    $photo_path = null;

    if ($address === "" || $preferred_date === "") {
        $error = "Please fill in address and preferred date.";
    } else {
        // handle photo upload
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png'];
            $filename = $_FILES['photo']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                $upload_dir = __DIR__ . '/../assets/uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $newname = uniqid('req_') . '.' . $ext;
                $target = $upload_dir . $newname;
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
                    $photo_path = 'assets/uploads/' . $newname;
                }
            } else {
                $error = "Only JPG and PNG photos are allowed.";
            }
        }

        if ($error === "") {
            $stmt = $conn->prepare("INSERT INTO ServiceRequest 
                (CustomerID, ServiceID, PreferredDate, PreferredTime, Address, Description, PhotoPath, IsEmergency, Status, Latitude, Longitude) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?, ?)");
            $stmt->bind_param("iisssssiss", $customer_id, $service_id, $preferred_date, $preferred_time, $address, $description, $photo_path, $is_emergency, $latitude, $longitude);

            if ($stmt->execute()) {
                $success = "Service request submitted! You can track its status in 'My Requests'.";
                $newRequestId = $conn->insert_id;
                require_once __DIR__ . '/../includes/notifications/NotificationService.php';
                $ns = new NotificationService($conn);
                $ns->notifyRequestCreated($newRequestId);
            } else {
                $error = "Failed to submit request. Please try again.";
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
    <title>Book a Service - HSMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="page-container">
        <div class="form-container" style="max-width: 650px; margin: 20px auto;">
            <h2>Book a Service</h2>
            <p>Select your category, describe your problem, and choose a preferred date & time.</p>

            <?php if ($error): ?>
                <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
            <?php endif; ?>


        <form method="POST" action="" enctype="multipart/form-data">
            <label>Service Category</label>
            <select name="service_id" required>
                <option value="">-- Select a service --</option>
                <?php while ($row = $services->fetch_assoc()): ?>
                    <option value="<?= $row['ServiceID'] ?>">
                        <?= htmlspecialchars($row['ServiceName']) ?> (Rs. <?= number_format($row['Price'], 2) ?>)
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Your Address</label>
            <textarea name="address" rows="2" required></textarea>

            <label>GPS Location (Optional)</label>
            <div class="gps-container">
                <input type="text" id="latitude" name="latitude" placeholder="Latitude" readonly>
                <input type="text" id="longitude" name="longitude" placeholder="Longitude" readonly>
                <button type="button" class="gps-btn" onclick="getLocation()">📍 Get Location</button>
            </div>
            <small id="gps-status" style="display: block; margin-top: -15px; margin-bottom: 20px; color: var(--text-muted); font-size: 13px;"></small>

            <label>Describe the Problem</label>
            <textarea name="description" rows="4" placeholder="e.g. TV screen flickering, no sound"></textarea>

            <label>Upload a Photo (optional)</label>
            <input type="file" name="photo" accept=".jpg,.jpeg,.png">

            <label>Preferred Date</label>
            <input type="date" name="preferred_date" required min="<?= date('Y-m-d') ?>">

            <label>Preferred Time</label>
            <input type="time" name="preferred_time">

            <label class="checkbox-label">
                <input type="checkbox" name="is_emergency" value="1">
                This is an emergency / urgent request
            </label>

            <button type="submit" class="btn-large">Submit Request</button>
        </form>
        </div>
    </div>

    <script>
    function getLocation() {
        const status = document.getElementById('gps-status');
        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');

        if (!navigator.geolocation) {
            status.textContent = '❌ Geolocation is not supported by your browser';
            status.style.color = 'var(--accent)';
            return;
        }

        status.textContent = 'Searching location... 📡';
        status.style.color = 'var(--text-muted)';

        navigator.geolocation.getCurrentPosition(
            (position) => {
                latInput.value = position.coords.latitude.toFixed(8);
                lngInput.value = position.coords.longitude.toFixed(8);
                status.textContent = '✅ Location retrieved successfully!';
                status.style.color = 'var(--primary)';
            },
            (error) => {
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        status.textContent = '❌ Permission denied. Please allow location access.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        status.textContent = '❌ Location information is unavailable.';
                        break;
                    case error.TIMEOUT:
                        status.textContent = '❌ Request to get location timed out.';
                        break;
                    default:
                        status.textContent = '❌ An unknown error occurred.';
                        break;
                }
                status.style.color = 'var(--accent)';
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    }
    </script>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>

