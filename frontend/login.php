<?php
$page_title = "Login - ElectraLab";
include 'db.php';
include 'header.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize_input($_POST['username'], $conn);
    $password = $_POST['password'];

    // Check user credentials
    $query = "SELECT u.user_id, u.full_name, u.username, u.password_hash, u.role_id, r.role_name
              FROM users u
              JOIN roles r ON u.role_id = r.role_id
              WHERE u.username = ? AND u.is_active = 1";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        // Verify password (Note: In production, use password_verify with hashed passwords)
        if ($password === $row['password_hash']) { // Simple check for demo
            // Set session variables
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['full_name'] = $row['full_name'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role_id'] = $row['role_id'];
            $_SESSION['role_name'] = $row['role_name'];

            // Set cart count
            if (!isset($_SESSION['cart_count'])) {
                $_SESSION['cart_count'] = 0;
            }

            // Redirect based on role
            // In login.php, after successful login, add:
            if ($row['role_id'] == 4) { // Customer
                // Load cart from database
                $cart_query = "SELECT c.product_code, p.product_name, p.product_img, 
                          ft.random_cost as price, c.quantity
                   FROM cart c
                   JOIN products p ON c.product_code = p.product_code
                   JOIN financial_tracking ft ON p.product_code = ft.product_code
                   WHERE c.user_id = ?";

                $cart_stmt = mysqli_prepare($conn, $cart_query);
                mysqli_stmt_bind_param($cart_stmt, "i", $row['user_id']);
                mysqli_stmt_execute($cart_stmt);
                $cart_result = mysqli_stmt_get_result($cart_stmt);

                $_SESSION['cart'] = [];
                while ($cart_item = mysqli_fetch_assoc($cart_result)) {
                    $_SESSION['cart'][$cart_item['product_code']] = [
                        'name' => $cart_item['product_name'],
                        'img' => $cart_item['product_img'],
                        'price' => $cart_item['price'],
                        'quantity' => $cart_item['quantity']
                    ];
                }

                $_SESSION['cart_count'] = count($_SESSION['cart']);

                $redirect = isset($_SESSION['redirect_url']) ? $_SESSION['redirect_url'] : 'index.php';
                unset($_SESSION['redirect_url']);

                echo '<script>
        Swal.fire({
            title: "Login Successful!",
            text: "Welcome back, ' . $row['full_name'] . '",
            icon: "success",
            timer: 1500,
            showConfirmButton: false
        }).then(() => {
            window.location.href = "' . $redirect . '";
        });
    </script>';
            } else {
                // Non-customer roles redirect to admin or respective dashboard
                echo '<script>
                    Swal.fire({
                        title: "Login Successful",
                        text: "Redirecting to dashboard",
                        icon: "success",
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = "../backend/dashboard.php";
                    });
                </script>';
            }
        } else {
            echo '<script>
                Swal.fire({
                    title: "Login Failed",
                    text: "Invalid password",
                    icon: "error"
                });
            </script>';
        }
    } else {
        echo '<script>
            Swal.fire({
                title: "Login Failed",
                text: "Username not found",
                icon: "error"
            });
        </script>';
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white text-center">
                <h3><i class="fas fa-sign-in-alt"></i> Login</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="login.php">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </button>
                        <a href="signup.php" class="btn btn-outline-secondary">
                            <i class="fas fa-user-plus"></i> Create New Account
                        </a>
                    </div>

                    <div class="text-center mt-3">
                        <a href="forgot-password.php" class="text-decoration-none">Forgot Password?</a>
                    </div>
                </form>

                <div class="mt-4 text-center">
                    <h6>Demo Accounts:</h6>
                    <div class="small">
                        <strong>Customer:</strong> ali / cust123<br>
                        <strong>Manufacturer:</strong> mfg01 / maf123<br>
                        <strong>CPRI:</strong> cpri01 / cpri123
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>