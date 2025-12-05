<?php
include 'db.php';
include 'auth_check.php';

// Only Manufacturer and Admin can access
if (!in_array($user_role, ['Manufacturer', 'Admin'])) {
    header("Location: dashboard.php");
    exit();
}

// ADD NEW PRODUCT
if (isset($_POST['addProduct'])) {
    $product_name = trim($_POST['product_name']);
    $manufacturer_id = $_SESSION['user_id'];
    $revise_number = $_POST['revise_number'];
    $created_by = $_SESSION['user_id'];
    
    // Image upload handling
    $product_img = '';
    if (isset($_FILES['product_img']) && $_FILES['product_img']['error'] == 0) {
        $target_dir = "../assets/uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $filename = time() . '_' . basename($_FILES['product_img']['name']);
        $target_file = $target_dir . $filename;
        
        // Check file type
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES['product_img']['tmp_name'], $target_file)) {
                $product_img = 'assets/uploads/' . $filename;
            }
        }
    }
    
    $stmt = mysqli_prepare($conn, 
        "INSERT INTO products (product_name, manufacturer_id, revise_number, created_by, product_img) 
         VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "siiss", $product_name, $manufacturer_id, $revise_number, $created_by, $product_img);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Product Added Successfully!',
                html: 'Product code will be auto-generated.<br>Please refresh to see the product code.',
                timer: 3000
            }).then(() => {
                window.location.reload();
            });
        </script>";
    } else {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Failed to add product. Please try again.',
            });
        </script>";
    }
    mysqli_stmt_close($stmt);
}

// DELETE PRODUCT
if (isset($_GET['delete'])) {
    $product_code = $_GET['delete'];
    
    // Check if product has test records
    $check_query = "SELECT COUNT(*) as count FROM test_records WHERE product_id_fk = '$product_code'";
    $check_result = mysqli_query($conn, $check_query);
    $check_row = mysqli_fetch_assoc($check_result);
    
    if ($check_row['count'] > 0) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Cannot Delete!',
                text: 'This product has test records. Delete test records first.',
            });
        </script>";
    } else {
        $delete_query = "DELETE FROM products WHERE product_code = '$product_code'";
        if (mysqli_query($conn, $delete_query)) {
            header("Location: manufacture.php?msg=deleted");
            exit();
        }
    }
}

// UPDATE PRODUCT
if (isset($_POST['updateProduct'])) {
    $product_code = $_POST['edit_product_code'];
    $product_name = trim($_POST['edit_product_name']);
    $revise_number = $_POST['edit_revise_number'];
    
    // Handle image update
    $product_img = $_POST['current_image'];
    if (isset($_FILES['edit_product_img']) && $_FILES['edit_product_img']['error'] == 0) {
        $target_dir = "../assets/uploads/";
        $filename = time() . '_' . basename($_FILES['edit_product_img']['name']);
        $target_file = $target_dir . $filename;
        
        // Check file type
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES['edit_product_img']['tmp_name'], $target_file)) {
                // Delete old image if exists
                if (!empty($_POST['current_image']) && file_exists('../' . $_POST['current_image'])) {
                    unlink('../' . $_POST['current_image']);
                }
                $product_img = 'assets/uploads/' . $filename;
            }
        }
    }
    
    $stmt = mysqli_prepare($conn, 
        "UPDATE products SET product_name = ?, revise_number = ?, product_img = ? WHERE product_code = ?");
    mysqli_stmt_bind_param($stmt, "siss", $product_name, $revise_number, $product_img, $product_code);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: manufacture.php?msg=updated");
        exit();
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'header.php'; ?>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 px-0 sidebar">
                <div class="p-3">
                    <ul class="nav flex-column">
                        <li class="nav-item mb-2">
                            <a class="nav-link" href="dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        
                        <?php if (in_array($user_role, ['Admin', 'Manufacturer'])): ?>
                        <li class="nav-item mb-2">
                            <a class="nav-link active" href="manufacture.php">
                                <i class="bi bi-gear"></i> Manufacturing
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php if (in_array($user_role, ['Admin', 'Manufacturer', 'CPRI'])): ?>
                        <li class="nav-item mb-2">
                            <a class="nav-link" href="products.php">
                                <i class="bi bi-box"></i> Products
                            </a>
                        </li>
                        
                        <li class="nav-item mb-2">
                            <a class="nav-link" href="test_records.php">
                                <i class="bi bi-file-earmark-text"></i> Test Records
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            
            <div class="col-md-10">
                <div class="p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3><i class="bi bi-gear"></i> Manufacturing Panel</h3>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                            <i class="bi bi-plus-circle"></i> Add New Product
                        </button>
                    </div>

                    <!-- Display Messages -->
                    <?php if (isset($_GET['msg'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php
                            $messages = [
                                'deleted' => 'Product deleted successfully!',
                                'updated' => 'Product updated successfully!'
                            ];
                            echo $messages[$_GET['msg']] ?? 'Action completed successfully!';
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Manufacturing Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="bi bi-box"></i> Total Products
                                    </h6>
                                    <?php
                                    $total_products = mysqli_fetch_assoc(mysqli_query($conn, 
                                        "SELECT COUNT(*) as count FROM products WHERE manufacturer_id = {$_SESSION['user_id']}"))['count'];
                                    ?>
                                    <h2><?php echo $total_products; ?></h2>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="bi bi-check-circle"></i> Passed Tests
                                    </h6>
                                    <?php
                                    $passed_tests = mysqli_fetch_assoc(mysqli_query($conn, 
                                        "SELECT COUNT(*) as count FROM test_records tr
                                         JOIN products p ON tr.product_id_fk = p.product_code
                                         WHERE p.manufacturer_id = {$_SESSION['user_id']} 
                                         AND tr.test_result = 'Passed'"))['count'];
                                    ?>
                                    <h2><?php echo $passed_tests; ?></h2>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="card bg-warning text-dark">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="bi bi-clock"></i> Pending Tests
                                    </h6>
                                    <?php
                                    $pending_tests = mysqli_fetch_assoc(mysqli_query($conn, 
                                        "SELECT COUNT(*) as count FROM test_records tr
                                         JOIN products p ON tr.product_id_fk = p.product_code
                                         WHERE p.manufacturer_id = {$_SESSION['user_id']} 
                                         AND tr.approval_status = 'Pending'"))['count'];
                                    ?>
                                    <h2><?php echo $pending_tests; ?></h2>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="bi bi-x-circle"></i> Failed Tests
                                    </h6>
                                    <?php
                                    $failed_tests = mysqli_fetch_assoc(mysqli_query($conn, 
                                        "SELECT COUNT(*) as count FROM test_records tr
                                         JOIN products p ON tr.product_id_fk = p.product_code
                                         WHERE p.manufacturer_id = {$_SESSION['user_id']} 
                                         AND tr.test_result = 'Failed'"))['count'];
                                    ?>
                                    <h2><?php echo $failed_tests; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Products Table -->
                    <div class="card shadow">
                        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">My Manufactured Products</h5>
                            <span class="badge bg-light text-dark">
                                <i class="bi bi-info-circle"></i> Product codes are auto-generated
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="manufactureTable" class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Product Code</th>
                                            <th>Product Name</th>
                                            <th>Revise No.</th>
                                            <th>Image</th>
                                            <th>Test Records</th>
                                            <th>Status</th>
                                            <th>Created At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $result = mysqli_query($conn, "
                                            SELECT p.*, 
                                                   (SELECT COUNT(*) FROM test_records WHERE product_id_fk = p.product_code) as test_count,
                                                   (SELECT COUNT(*) FROM test_records WHERE product_id_fk = p.product_code AND test_result = 'Passed') as passed_count
                                            FROM products p
                                            WHERE p.manufacturer_id = {$_SESSION['user_id']}
                                            ORDER BY p.created_at DESC
                                        ");
                                        
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            // Image display
                                            $image = '';
                                            if (!empty($row['product_img'])) {
                                                $image = "<img src='../{$row['product_img']}' width='50' height='50' 
                                                          class='rounded border' data-bs-toggle='tooltip' 
                                                          title='Click to view larger' 
                                                          onclick='viewImage(\"{$row['product_img']}\", \"{$row['product_name']}\")'>";
                                            } else {
                                                $image = "<i class='bi bi-image text-muted' style='font-size: 50px;'
                                                          data-bs-toggle='tooltip' title='No image uploaded'></i>";
                                            }
                                            
                                            // Test records badge
                                            $test_badge = '';
                                            if ($row['test_count'] > 0) {
                                                $pass_percentage = $row['passed_count'] > 0 ? 
                                                    round(($row['passed_count'] / $row['test_count']) * 100) : 0;
                                                $test_badge = "<span class='badge bg-info' data-bs-toggle='tooltip' 
                                                               title='{$row['passed_count']} passed out of {$row['test_count']} tests'>
                                                               {$row['test_count']} tests ({$pass_percentage}%)</span>";
                                            } else {
                                                $test_badge = "<span class='badge bg-secondary'>No tests</span>";
                                            }
                                            
                                            // Overall status
                                            $status_badge = '';
                                            if ($row['test_count'] == 0) {
                                                $status_badge = '<span class="badge bg-secondary">Not Tested</span>';
                                            } elseif ($row['passed_count'] == $row['test_count']) {
                                                $status_badge = '<span class="badge bg-success">All Passed</span>';
                                            } elseif ($row['passed_count'] > 0) {
                                                $status_badge = '<span class="badge bg-warning">Mixed Results</span>';
                                            } else {
                                                $status_badge = '<span class="badge bg-danger">All Failed</span>';
                                            }
                                            
                                            echo "<tr>
                                                <td><span class='badge bg-dark'>{$row['product_code']}</span></td>
                                                <td>{$row['product_name']}</td>
                                                <td><span class='badge bg-info'>{$row['revise_number']}</span></td>
                                                <td>{$image}</td>
                                                <td>{$test_badge}</td>
                                                <td>{$status_badge}</td>
                                                <td>{$row['created_at']}</td>
                                                <td>
                                                    <button class='btn btn-sm btn-outline-primary editProductBtn'
                                                        data-code='{$row['product_code']}'
                                                        data-name='{$row['product_name']}'
                                                        data-revise='{$row['revise_number']}'
                                                        data-image='{$row['product_img']}'>
                                                        <i class='bi bi-pencil-square'></i>
                                                    </button>
                                                    
                                                    <button class='btn btn-sm btn-outline-info addTestBtn'
                                                        data-code='{$row['product_code']}'
                                                        data-name='{$row['product_name']}'>
                                                        <i class='bi bi-clipboard-check'></i>
                                                    </button>
                                                    
                                                    <button class='btn btn-sm btn-outline-danger deleteProductBtn'
                                                        data-code='{$row['product_code']}'
                                                        data-name='{$row['product_name']}'
                                                        data-testcount='{$row['test_count']}'>
                                                        <i class='bi bi-trash'></i>
                                                    </button>
                                                </td>
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

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Add New Product</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Product Name *</label>
                            <input type="text" class="form-control" name="product_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Revise Number *</label>
                            <input type="number" class="form-control" name="revise_number" value="1" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Product Image</label>
                            <input type="file" class="form-control" name="product_img" accept="image/*">
                            <small class="text-muted">Allowed: JPG, JPEG, PNG, GIF (Max 2MB)</small>
                        </div>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            <strong>Product code will be auto-generated:</strong><br>
                            Format: [ManufacturerID(3)] + [ReviseNo(2)] + [AutoNumber(5)]
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="addProduct" class="btn btn-primary">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div class="modal fade" id="editProductModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="edit_product_code" id="edit_product_code">
                    <input type="hidden" name="current_image" id="current_image">
                    
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Edit Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-3">
                            <div id="currentImagePreview" class="mb-2"></div>
                            <small class="text-muted">Current Image</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Product Code</label>
                            <input type="text" class="form-control" id="display_product_code" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Product Name *</label>
                            <input type="text" class="form-control" id="edit_product_name" name="edit_product_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Revise Number *</label>
                            <input type="number" class="form-control" id="edit_revise_number" name="edit_revise_number" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Update Image</label>
                            <input type="file" class="form-control" name="edit_product_img" accept="image/*">
                            <small class="text-muted">Leave empty to keep current image</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="updateProduct" class="btn btn-warning">Update Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Image View Modal -->
    <div class="modal fade" id="imageViewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="imageViewTitle"></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="largeImageView" src="" class="img-fluid rounded" style="max-height: 500px;">
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
    $(document).ready(function() {
        $('#manufactureTable').DataTable({
            "pageLength": 10,
            "order": [[0, 'desc']]
        });
        
        // Initialize tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();
    });

    // VIEW LARGER IMAGE
    function viewImage(imagePath, productName) {
        if (imagePath) {
            document.getElementById('largeImageView').src = '../' + imagePath;
            document.getElementById('imageViewTitle').innerHTML = 
                `<i class="bi bi-image"></i> ${productName}`;
            
            const modal = new bootstrap.Modal(document.getElementById('imageViewModal'));
            modal.show();
        }
    }

    // EDIT PRODUCT
    document.querySelectorAll('.editProductBtn').forEach(btn => {
        btn.addEventListener('click', function() {
            const productCode = this.getAttribute('data-code');
            const productName = this.getAttribute('data-name');
            const reviseNumber = this.getAttribute('data-revise');
            const productImage = this.getAttribute('data-image');
            
            document.getElementById('edit_product_code').value = productCode;
            document.getElementById('display_product_code').value = productCode;
            document.getElementById('edit_product_name').value = productName;
            document.getElementById('edit_revise_number').value = reviseNumber;
            document.getElementById('current_image').value = productImage;
            
            // Display current image
            const imagePreview = document.getElementById('currentImagePreview');
            if (productImage) {
                imagePreview.innerHTML = `
                    <img src="../${productImage}" class="rounded border" style="max-height: 150px;">
                `;
            } else {
                imagePreview.innerHTML = `
                    <div class="text-muted">
                        <i class="bi bi-image" style="font-size: 100px;"></i><br>
                        No image uploaded
                    </div>
                `;
            }
            
            const modal = new bootstrap.Modal(document.getElementById('editProductModal'));
            modal.show();
        });
    });

    // ADD TEST RECORD
    document.querySelectorAll('.addTestBtn').forEach(btn => {
        btn.addEventListener('click', function() {
            const productCode = this.getAttribute('data-code');
            const productName = this.getAttribute('data-name');
            
            Swal.fire({
                title: 'Add Test Record',
                html: `Redirect to test records page for <b>${productName}</b>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Go to Tests',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // You can redirect to test_records.php with product pre-selected
                    window.location.href = 'test_records.php?product=' + productCode;
                }
            });
        });
    });

    // DELETE PRODUCT
    document.querySelectorAll('.deleteProductBtn').forEach(btn => {
        btn.addEventListener('click', function() {
            const productCode = this.getAttribute('data-code');
            const productName = this.getAttribute('data-name');
            const testCount = this.getAttribute('data-testcount');
            
            let message = `Are you sure you want to delete <b>${productName}</b> (${productCode})?`;
            
            if (parseInt(testCount) > 0) {
                message += `<br><br><span class="text-danger">
                    <i class="bi bi-exclamation-triangle"></i> 
                    This product has ${testCount} test records! 
                    Deleting will remove all associated test records.
                    </span>`;
            }
            
            Swal.fire({
                title: 'Delete Product?',
                html: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'manufacture.php?delete=' + productCode;
                }
            });
        });
    });
    </script>
    
    <?php include 'footer.php'; ?>
</body>
</html>