<?php
session_start();

$host = '127.0.0.1';
$username = 'root'; // Change as per your setup
$password = ''; // Change as per your setup
$database = 'lab_automation';

// Create connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to UTF-8
mysqli_set_charset($conn, "utf8mb4");

// Function to sanitize input
function sanitize_input($data, $conn) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($conn, $data);
    return $data;
}


function getCartCount($conn, $user_id) {
    $count_query = "SELECT COUNT(*) as count FROM cart WHERE user_id = ?";
    $count_stmt = mysqli_prepare($conn, $count_query);
    mysqli_stmt_bind_param($count_stmt, "i", $user_id);
    mysqli_stmt_execute($count_stmt);
    $count_result = mysqli_stmt_get_result($count_stmt);
    $count_row = mysqli_fetch_assoc($count_result);
    return $count_row['count'] ?? 0;
}
?>