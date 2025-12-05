<?php
include 'db.php';
include 'auth_check.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'header.php'; ?>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- <div class="col-md-2 px-0 sidebar"> -->
                <!-- <div class="p-3">
                    <ul class="nav flex-column">
                        <li class="nav-item mb-2">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        
                        <?php if ($user_role == 'Admin'): ?>
                        <li class="nav-item mb-2">
                            <a class="nav-link" href="users.php">
                                <i class="bi bi-people"></i> Users
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php if (in_array($user_role, ['Admin', 'Manufacturer'])): ?>
                        <li class="nav-item mb-2">
                            <a class="nav-link" href="products.php">
                                <i class="bi bi-box"></i> Products
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php if (in_array($user_role, ['Admin', 'Manufacturer', 'CPRI'])): ?>
                        <li class="nav-item mb-2">
                            <a class="nav-link" href="testing_types.php">
                                <i class="bi bi-clipboard-check"></i> Testing Types
                            </a>
                        </li>
                        
                        <li class="nav-item mb-2">
                            <a class="nav-link" href="test_records.php">
                                <i class="bi bi-file-earmark-text"></i> Test Records
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php if ($user_role == 'CPRI'): ?>
                        <li class="nav-item mb-2">
                            <a class="nav-link" href="cpri.php">
                                <i class="bi bi-shield-check"></i> CPRI Approval
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php if ($user_role == 'Customer'): ?>
                        <li class="nav-item mb-2">
                            <a class="nav-link" href="orders.php">
                                <i class="bi bi-cart"></i> My Orders
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php if (in_array($user_role, ['Admin', 'CPRI'])): ?>
                        <li class="nav-item mb-2">
                            <a class="nav-link" href="financial_tracking.php">
                                <i class="bi bi-cash-stack"></i> Financial Tracking
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div> -->
            <!-- </div> -->
            
            <div class="col-md-10">
                <div class="p-4">
                    <h3><i class="bi bi-speedometer2"></i> Dashboard</h3>
                    <p class="text-muted">Welcome, <?php echo $_SESSION['full_name']; ?>!</p>
                    
                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="bi bi-people"></i> Total Users
                                    </h5>
                                    <?php
                                    $user_count = mysqli_fetch_assoc(mysqli_query($conn, 
                                        "SELECT COUNT(*) as count FROM users"))['count'];
                                    ?>
                                    <h2><?php echo $user_count; ?></h2>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="bi bi-box"></i> Products
                                    </h5>
                                    <?php
                                    $product_count = mysqli_fetch_assoc(mysqli_query($conn, 
                                        "SELECT COUNT(*) as count FROM products"))['count'];
                                    ?>
                                    <h2><?php echo $product_count; ?></h2>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="card bg-warning text-dark">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="bi bi-clipboard-check"></i> Test Records
                                    </h5>
                                    <?php
                                    $test_count = mysqli_fetch_assoc(mysqli_query($conn, 
                                        "SELECT COUNT(*) as count FROM test_records"))['count'];
                                    ?>
                                    <h2><?php echo $test_count; ?></h2>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="bi bi-cart"></i> Orders
                                    </h5>
                                    <?php
                                    $order_count = mysqli_fetch_assoc(mysqli_query($conn, 
                                        "SELECT COUNT(*) as count FROM orders"))['count'];
                                    ?>
                                    <h2><?php echo $order_count; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Test Records -->
                    <div class="card shadow">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Test Records</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Testing ID</th>
                                            <th>Product</th>
                                            <th>Test Type</th>
                                            <th>Result</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $recent_tests = mysqli_query($conn, "
                                            SELECT tr.testing_id, p.product_name, tt.type_name, 
                                                   tr.test_result, tr.approval_status, tr.test_date
                                            FROM test_records tr
                                            JOIN products p ON tr.product_id_fk = p.product_code
                                            JOIN testing_type tt ON tr.test_type_id = tt.test_type_id
                                            ORDER BY tr.created_at DESC LIMIT 5
                                        ");
                                        
                                        while ($test = mysqli_fetch_assoc($recent_tests)) {
                                            $result_badge = $test['test_result'] == 'Passed' 
                                                ? '<span class="badge bg-success">Passed</span>' 
                                                : '<span class="badge bg-danger">Failed</span>';
                                            
                                            $status_badge = $test['approval_status'] == 'Approved' 
                                                ? '<span class="badge bg-success">Approved</span>' 
                                                : ($test['approval_status'] == 'Rejected' 
                                                    ? '<span class="badge bg-danger">Rejected</span>' 
                                                    : '<span class="badge bg-warning">Pending</span>');
                                            
                                            echo "<tr>
                                                <td>{$test['testing_id']}</td>
                                                <td>{$test['product_name']}</td>
                                                <td>{$test['type_name']}</td>
                                                <td>{$result_badge}</td>
                                                <td>{$status_badge}</td>
                                                <td>{$test['test_date']}</td>
                                            </tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>