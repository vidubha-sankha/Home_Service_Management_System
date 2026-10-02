<?php
// includes/auth_check.php
// Include this at the top of any page that requires login.
// Usage: define $required_role before including, e.g. $required_role = 'customer';

require_once __DIR__ . '/../config/db.php';

if (!isset($required_role)) {
    die("Page misconfigured: required_role not set.");
}

switch ($required_role) {
    case 'customer':
        if (!isset($_SESSION['customer_id'])) {
            header("Location: /customer/login.php");
            exit;
        }
        break;

    case 'admin':
        if (!isset($_SESSION['admin_id'])) {
            header("Location: /admin/login.php");
            exit;
        }
        break;

    case 'employee':
        if (!isset($_SESSION['employee_id'])) {
            header("Location: /technician/login.php");
            exit;
        }
        break;

    default:
        die("Unknown role.");
}
?>
