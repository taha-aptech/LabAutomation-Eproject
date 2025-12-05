<?php
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    
    echo '<script>
        Swal.fire({
            title: "Authentication Required",
            text: "Please login to access this page",
            icon: "warning",
            confirmButtonText: "Login Now"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "login.php";
            }
        });
    </script>';
    
    // Redirect after 2 seconds if JavaScript is disabled
    header("refresh:2;url=login.php");
    exit();
}

// Check if user is customer
if (isset($_SESSION['role_id']) && $_SESSION['role_id'] != 4) {
    echo '<script>
        Swal.fire({
            title: "Access Denied",
            text: "This page is for customers only",
            icon: "error",
            confirmButtonText: "Go to Home"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "index.php";
            }
        });
    </script>';
    exit();
}
?>