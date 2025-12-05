<?php
$servername = "localhost";
$username = "root";
$password = "";  // Usually empty for XAMPP
$database = "Lab_Automation";

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>