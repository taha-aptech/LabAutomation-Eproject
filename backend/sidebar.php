<?php include 'auth_check.php'; ?>

<!-- <div class="col-md-2 px-0 sidebar">
    <div class="p-3">
        <ul class="nav flex-column">
            <li class="nav-item mb-2">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" 
                   href="dashboard.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            
            <?php if ($user_role == 'Admin'): ?>
            <li class="nav-item mb-2">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>" 
                   href="users.php">
                    <i class="bi bi-people"></i> Users
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (in_array($user_role, ['Admin', 'Manufacturer'])): ?>
            <li class="nav-item mb-2">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>" 
                   href="products.php">
                    <i class="bi bi-box"></i> Products
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (in_array($user_role, ['Admin', 'Manufacturer', 'CPRI'])): ?>
            <li class="nav-item mb-2">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'testing_types.php' ? 'active' : ''; ?>" 
                   href="testing_types.php">
                    <i class="bi bi-clipboard-check"></i> Testing Types
                </a>
            </li>
            
            <li class="nav-item mb-2">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'test_records.php' ? 'active' : ''; ?>" 
                   href="test_records.php">
                    <i class="bi bi-file-earmark-text"></i> Test Records
                </a>
            </li>
            <?php endif; ?>
            
            <?php if ($user_role == 'CPRI'): ?>
            <li class="nav-item mb-2">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'cpri.php' ? 'active' : ''; ?>" 
                   href="cpri.php">
                    <i class="bi bi-shield-check"></i> CPRI Approval
                </a>
            </li>
            <?php endif; ?>
            
            <?php if ($user_role == 'Customer'): ?>
            <li class="nav-item mb-2">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>" 
                   href="orders.php">
                    <i class="bi bi-cart"></i> My Orders
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (in_array($user_role, ['Admin', 'CPRI'])): ?>
            <li class="nav-item mb-2">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'financial_tracking.php' ? 'active' : ''; ?>" 
                   href="financial_tracking.php">
                    <i class="bi bi-cash-stack"></i> Financial Tracking
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </div>
</div> -->