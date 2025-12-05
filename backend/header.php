<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Automation System</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
    <style>
        .sidebar {
            min-height: calc(100vh - 56px);
            background: linear-gradient(180deg, #2c3e50, #34495e);
        }
        .nav-link {
            color: #ecf0f1;
            transition: all 0.3s;
        }
        .nav-link:hover {
            background-color: #3498db;
            color: white;
            border-radius: 5px;
        }
        .nav-link.active {
            background-color: #2980b9;
            color: white;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-speedometer2"></i> Lab Automation System
            </a>
            
            <div class="d-flex align-items-center">
                <span class="text-light me-3">
                    <i class="bi bi-person-circle"></i> <?php echo $_SESSION['full_name']; ?>
                    <span class="badge bg-info"><?php echo $user_role; ?></span>
                </span>
                <a href="logout.php" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 px-0 sidebar">
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
            </div>
            
            <div class="col-md-10">
                <div class="p-4">