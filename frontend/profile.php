<?php
$page_title = "My Profile - ElectraLab";
include 'db.php';
include 'header.php';
include 'auth_check.php';

$user_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// Fetch current user data
$user_query = "SELECT u.*, r.role_name 
               FROM users u 
               JOIN roles r ON u.role_id = r.role_id 
               WHERE u.user_id = ?";
$user_stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($user_stmt, "i", $user_id);
mysqli_stmt_execute($user_stmt);
$user_result = mysqli_stmt_get_result($user_stmt);
$user = mysqli_fetch_assoc($user_result);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $full_name = sanitize_input($_POST['full_name'], $conn);
    $email = sanitize_input($_POST['email'], $conn);
    $phone = sanitize_input($_POST['phone'] ?? '', $conn);
    $address = sanitize_input($_POST['address'] ?? '', $conn);
    
    // Check if email already exists (excluding current user)
    $email_check = "SELECT user_id FROM users WHERE email = ? AND user_id != ?";
    $email_stmt = mysqli_prepare($conn, $email_check);
    mysqli_stmt_bind_param($email_stmt, "si", $email, $user_id);
    mysqli_stmt_execute($email_stmt);
    
    if (mysqli_stmt_num_rows($email_stmt) > 0) {
        $error_msg = "Email already exists!";
    } else {
        // Update user profile
        $update_query = "UPDATE users SET full_name = ?, email = ? WHERE user_id = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, "ssi", $full_name, $email, $user_id);
        
        if (mysqli_stmt_execute($update_stmt)) {
            $_SESSION['full_name'] = $full_name;
            $success_msg = "Profile updated successfully!";
            
            // Update session user data
            $user['full_name'] = $full_name;
            $user['email'] = $email;
        } else {
            $error_msg = "Failed to update profile!";
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify current password (using plain text for demo - use password_verify() in production)
    if ($current_password !== $user['password_hash']) {
        $error_msg = "Current password is incorrect!";
    } elseif ($new_password !== $confirm_password) {
        $error_msg = "New passwords do not match!";
    } elseif (strlen($new_password) < 6) {
        $error_msg = "Password must be at least 6 characters!";
    } else {
        // Update password
        $update_pwd_query = "UPDATE users SET password_hash = ? WHERE user_id = ?";
        $update_pwd_stmt = mysqli_prepare($conn, $update_pwd_query);
        mysqli_stmt_bind_param($update_pwd_stmt, "si", $new_password, $user_id);
        
        if (mysqli_stmt_execute($update_pwd_stmt)) {
            $success_msg = "Password changed successfully!";
        } else {
            $error_msg = "Failed to change password!";
        }
    }
}
?>

<div class="container py-5">
    <div class="row">
        <!-- Left Sidebar -->
        <div class="col-lg-3">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <div class="profile-avatar mb-3">
                        <i class="fas fa-user-circle fa-5x text-primary"></i>
                    </div>
                    <h5><?php echo htmlspecialchars($user['full_name']); ?></h5>
                    <p class="text-muted"><?php echo htmlspecialchars($user['role_name']); ?></p>
                    <span class="badge bg-<?php echo $user['is_active'] ? 'success' : 'danger'; ?>">
                        <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                    </span>
                </div>
                <div class="list-group list-group-flush">
                    <a href="#profile-info" class="list-group-item list-group-item-action active">
                        <i class="fas fa-user me-2"></i> Profile Information
                    </a>
                    <a href="#change-password" class="list-group-item list-group-item-action">
                        <i class="fas fa-lock me-2"></i> Change Password
                    </a>
                    <a href="#order-history" class="list-group-item list-group-item-action">
                        <i class="fas fa-shopping-bag me-2"></i> Order History
                    </a>
                    <a href="#activity" class="list-group-item list-group-item-action">
                        <i class="fas fa-chart-line me-2"></i> Activity
                    </a>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-chart-bar"></i> Quick Stats</h6>
                </div>
                <div class="card-body">
                    <?php
                    // Get order stats
                    $order_stats = "SELECT 
                        COUNT(*) as total_orders,
                        SUM(CASE WHEN status = 'Delivered' THEN 1 ELSE 0 END) as delivered_orders,
                        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_orders
                    FROM orders WHERE customer_id = ?";
                    
                    $stats_stmt = mysqli_prepare($conn, $order_stats);
                    mysqli_stmt_bind_param($stats_stmt, "i", $user_id);
                    mysqli_stmt_execute($stats_stmt);
                    $stats_result = mysqli_stmt_get_result($stats_stmt);
                    $stats = mysqli_fetch_assoc($stats_result);
                    ?>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Orders</span>
                        <span class="badge bg-primary"><?php echo $stats['total_orders'] ?? 0; ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Delivered</span>
                        <span class="badge bg-success"><?php echo $stats['delivered_orders'] ?? 0; ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Pending</span>
                        <span class="badge bg-warning"><?php echo $stats['pending_orders'] ?? 0; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <!-- Messages -->
            <?php if ($success_msg): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php echo $success_msg; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error_msg): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo $error_msg; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Profile Information -->
            <div class="card mb-4" id="profile-info">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-user me-2"></i> Profile Information</h4>
                    <button class="btn btn-sm btn-outline-primary" id="editProfileBtn">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                </div>
                <div class="card-body">
                    <form method="POST" action="profile.php" id="profileForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Role</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['role_name']); ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="full_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['full_name']); ?>" 
                                       id="fullNameInput" readonly required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email Address *</label>
                                <input type="email" name="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" 
                                       id="emailInput" readonly required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Account Created</label>
                                <input type="text" class="form-control" 
                                       value="<?php echo date('F j, Y', strtotime($user['created_at'])); ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Updated</label>
                                <input type="text" class="form-control" 
                                       value="<?php echo date('F j, Y', strtotime($user['updated_at'])); ?>" readonly>
                            </div>
                        </div>
                        
                        <div class="d-none" id="profileActions">
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="cancelEditBtn">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Change Password -->
            <div class="card mb-4" id="change-password">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-lock me-2"></i> Change Password</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="profile.php">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Current Password *</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">New Password *</label>
                                <input type="password" name="new_password" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm New Password *</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-primary">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                    </form>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="card" id="order-history">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-shopping-bag me-2"></i> Recent Orders</h4>
                    <a href="my-orders.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php
                    $recent_orders = "SELECT o.order_id, o.order_date, o.status, 
                                             COUNT(oi.item_id) as item_count,
                                             SUM(oi.quantity * oi.price) as total_amount
                                      FROM orders o
                                      LEFT JOIN order_items oi ON o.order_id = oi.order_id
                                      WHERE o.customer_id = ?
                                      GROUP BY o.order_id
                                      ORDER BY o.order_date DESC
                                      LIMIT 5";
                    
                    $recent_stmt = mysqli_prepare($conn, $recent_orders);
                    mysqli_stmt_bind_param($recent_stmt, "i", $user_id);
                    mysqli_stmt_execute($recent_stmt);
                    $recent_result = mysqli_stmt_get_result($recent_stmt);
                    
                    if (mysqli_num_rows($recent_result) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Date</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($order = mysqli_fetch_assoc($recent_result)): ?>
                                        <tr>
                                            <td>#<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                            <td><?php echo $order['item_count']; ?></td>
                                            <td>Rs. <?php echo number_format($order['total_amount']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo match($order['status']) {
                                                        'Pending' => 'warning',
                                                        'Confirmed' => 'info',
                                                        'Shipped' => 'primary',
                                                        'Delivered' => 'success',
                                                        'Cancelled' => 'danger',
                                                        default => 'secondary'
                                                    };
                                                ?>">
                                                    <?php echo $order['status']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="order-details.php?id=<?php echo $order['order_id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center py-3">No orders found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Profile Edit Toggle
document.addEventListener('DOMContentLoaded', function() {
    const editBtn = document.getElementById('editProfileBtn');
    const cancelBtn = document.getElementById('cancelEditBtn');
    const profileActions = document.getElementById('profileActions');
    const fullNameInput = document.getElementById('fullNameInput');
    const emailInput = document.getElementById('emailInput');
    
    if (editBtn) {
        editBtn.addEventListener('click', function() {
            // Enable editing
            fullNameInput.readOnly = false;
            emailInput.readOnly = false;
            fullNameInput.focus();
            
            // Show action buttons
            profileActions.classList.remove('d-none');
            profileActions.classList.add('d-flex', 'gap-2', 'mt-3');
            
            // Hide edit button
            this.classList.add('d-none');
        });
    }
    
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            // Disable editing
            fullNameInput.readOnly = true;
            emailInput.readOnly = true;
            
            // Hide action buttons
            profileActions.classList.add('d-none');
            profileActions.classList.remove('d-flex', 'gap-2', 'mt-3');
            
            // Show edit button
            editBtn.classList.remove('d-none');
            
            // Reload form to reset changes
            document.getElementById('profileForm').reset();
        });
    }
    
    // Smooth scrolling for sidebar links
    document.querySelectorAll('.list-group-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 100,
                    behavior: 'smooth'
                });
                
                // Update active state
                document.querySelectorAll('.list-group-item').forEach(link => {
                    link.classList.remove('active');
                });
                this.classList.add('active');
            }
        });
    });
});
</script>

<style>
/* .profile-avatar {
    width: 100px;
    height: 100px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-radius: 50%;
} */

.list-group-item.active {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.table-hover tbody tr:hover {
    background-color: rgba(13, 110, 253, 0.05);
}
</style>

<?php include 'footer.php'; ?>