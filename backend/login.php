<?php
session_start();

// Check if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// SIMPLE DATABASE CONNECTION FOR TESTING
$conn = mysqli_connect("localhost", "root", "", "Lab_Automation");

if (!$conn) {
    die("Database connection failed!");
}

// Debug: Check if form is submitted
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    echo "<script>console.log('Login attempt:', 'Username:', '$username', 'Password:', '$password')</script>";
    
    // Simple query without prepared statement for testing
    $sql = "SELECT user_id, full_name, username, password_hash, role_id 
            FROM users 
            WHERE username = '$username'";
    
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        echo "<script>console.log('Query error:', '" . mysqli_error($conn) . "')</script>";
        $error = "Database query error!";
    } elseif (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        echo "<script>console.log('User found:', '" . $user['username'] . "', 'Stored password:', '" . $user['password_hash'] . "')</script>";
        
        // Simple password check (plain text for demo)
        if ($password === $user['password_hash']) {
            // Login successful
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role_id'] = $user['role_id'];
            
            echo "<script>
                alert('Login successful! Welcome " . $user['full_name'] . "');
                window.location.href = 'dashboard.php';
            </script>";
            exit();
        } else {
            $error = "Invalid password!";
            echo "<script>console.log('Password mismatch')</script>";
        }
    } else {
        $error = "User not found!";
        echo "<script>console.log('No user found with username:', '$username')</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Lab Automation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-lg">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h3 class="text-primary">
                                <i class="bi bi-shield-lock"></i> Lab Automation
                            </h3>
                            <p class="text-muted">Please sign in to your account</p>
                        </div>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- DEMO CREDENTIALS BOX -->
                        <div class="alert alert-info mb-4">
                            <h6><i class="bi bi-info-circle"></i> Demo Credentials:</h6>
                            <div class="row small">
                                <div class="col-6">
                                    <strong>Admin:</strong><br>
                                    admin / admin123
                                </div>
                                <div class="col-6">
                                    <strong>Manufacturer:</strong><br>
                                    mfg01 / maf123
                                </div>
                            </div>
                        </div>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-person"></i>
                                    </span>
                                    <input type="text" class="form-control" name="username" 
                                           value="admin" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-key"></i>
                                    </span>
                                    <input type="password" class="form-control" name="password" 
                                           value="admin123" required>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" name="login" class="btn btn-primary">
                                    <i class="bi bi-box-arrow-in-right"></i> Sign In
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-3">
                            <p class="mb-0 text-muted small">
                                <i class="bi bi-lightbulb"></i> 
                                Username and password are pre-filled for testing
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Auto-submit for testing
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Page loaded');
        
        // Optional: Auto-login for testing
        // setTimeout(function() {
        //     document.querySelector('form').submit();
        // }, 1000);
    });
    </script>
</body>
</html>