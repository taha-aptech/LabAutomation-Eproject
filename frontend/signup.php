<?php
$page_title = "Sign Up - ElectraLab";
include 'db.php';
include 'header.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Handle signup form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = sanitize_input($_POST['full_name'], $conn);
    $username = sanitize_input($_POST['username'], $conn);
    $email = sanitize_input($_POST['email'], $conn);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = sanitize_input($_POST['phone'], $conn);
    
    // Validate passwords match
    if ($password !== $confirm_password) {
        echo '<script>
            Swal.fire({
                title: "Error!",
                text: "Passwords do not match",
                icon: "error"
            });
        </script>';
    } else {
        // Check if username already exists
        $check_query = "SELECT user_id FROM users WHERE username = ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) > 0) {
            echo '<script>
                Swal.fire({
                    title: "Error!",
                    text: "Username already exists",
                    icon: "error"
                });
            </script>';
        } else {
            // Insert new customer (role_id = 4 for customer)
            $role_id = 4;
            $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Use in production
            
            $insert_query = "INSERT INTO users (full_name, username, password_hash, role_id) 
                             VALUES (?, ?, ?, ?)";
            
            $stmt = mysqli_prepare($conn, $insert_query);
            // For demo, using plain password. In production, use: $hashed_password
            mysqli_stmt_bind_param($stmt, "sssi", $full_name, $username, $password, $role_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $user_id = mysqli_insert_id($conn);
                
                // Auto-login after registration
                $_SESSION['user_id'] = $user_id;
                $_SESSION['full_name'] = $full_name;
                $_SESSION['username'] = $username;
                $_SESSION['role_id'] = $role_id;
                $_SESSION['role_name'] = 'Customer';
                $_SESSION['cart_count'] = 0;
                
                echo '<script>
                    Swal.fire({
                        title: "Registration Successful!",
                        text: "Welcome to ElectraLab, ' . $full_name . '",
                        icon: "success",
                        confirmButtonText: "Continue Shopping"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "index.php";
                        }
                    });
                </script>';
            } else {
                echo '<script>
                    Swal.fire({
                        title: "Error!",
                        text: "Registration failed. Please try again.",
                        icon: "error"
                    });
                </script>';
            }
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow">
            <div class="card-header bg-success text-white text-center">
                <h3><i class="fas fa-user-plus"></i> Create Account</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="signup.php" id="signupForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="full_name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Username *</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                            <div class="form-text">Choose a unique username</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address *</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password *</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="password-strength mt-1">
                                <div class="progress" style="height: 5px;">
                                    <div class="progress-bar" id="passwordStrength" style="width: 0%"></div>
                                </div>
                                <small id="passwordHelp" class="form-text"></small>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password *</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="showPassword">
                                <label class="form-check-label" for="showPassword">Show passwords</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone">
                    </div>
                    
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" id="terms" required>
                        <label class="form-check-label" for="terms">
                            I agree to the <a href="terms.php" class="text-decoration-none">Terms and Conditions</a>
                        </label>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-user-check"></i> Create Account
                        </button>
                        <a href="login.php" class="btn btn-outline-primary">
                            <i class="fas fa-sign-in-alt"></i> Already have an account? Login
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Show/hide password
document.getElementById('showPassword').addEventListener('change', function() {
    var password = document.getElementById('password');
    var confirm = document.getElementById('confirm_password');
    
    if (this.checked) {
        password.type = 'text';
        confirm.type = 'text';
    } else {
        password.type = 'password';
        confirm.type = 'password';
    }
});

// Password strength checker
document.getElementById('password').addEventListener('input', function() {
    var password = this.value;
    var strengthBar = document.getElementById('passwordStrength');
    var helpText = document.getElementById('passwordHelp');
    
    var strength = 0;
    var tips = "";
    
    if (password.length >= 8) strength += 25;
    if (/[a-z]/.test(password)) strength += 25;
    if (/[A-Z]/.test(password)) strength += 25;
    if (/[0-9]/.test(password) || /[^A-Za-z0-9]/.test(password)) strength += 25;
    
    strengthBar.style.width = strength + '%';
    
    if (strength < 50) {
        strengthBar.className = 'progress-bar bg-danger';
        helpText.textContent = 'Weak password';
    } else if (strength < 75) {
        strengthBar.className = 'progress-bar bg-warning';
        helpText.textContent = 'Moderate password';
    } else {
        strengthBar.className = 'progress-bar bg-success';
        helpText.textContent = 'Strong password';
    }
});
</script>

<?php include 'footer.php'; ?>