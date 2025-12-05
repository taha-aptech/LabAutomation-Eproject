<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role_id = $_SESSION['role_id'];

$role_query = mysqli_query($conn, "SELECT role_name FROM roles WHERE role_id = $user_role_id");
$role_row = mysqli_fetch_assoc($role_query);
$user_role = $role_row['role_name'];

$allowed_pages = [
    'Admin' => ['dashboard.php', 'users.php', 'products.php', 'orders.php', 
                'testing_types.php', 'test_records.php', 'cpri.php', 'financial_tracking.php'],
    'Manufacturer' => ['dashboard.php', 'products.php', 'test_records.php'],
    'CPRI' => ['dashboard.php', 'test_records.php', 'financial_tracking.php', 'cpri.php'],
    'Customer' => ['dashboard.php', 'orders.php']
];

$current_page = basename($_SERVER['PHP_SELF']);

if (isset($allowed_pages[$user_role]) && !in_array($current_page, $allowed_pages[$user_role])) {
    header("Location: dashboard.php");
    exit();
}
?>
