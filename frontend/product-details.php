<?php
$page_title = "Product Details - ElectraLab";
include 'db.php';
include 'header.php';

// Check if product code is provided
if (!isset($_GET['code'])) {
    header('Location: products.php');
    exit();
}

$product_code = sanitize_input($_GET['code'], $conn);

// Fetch product details
$query = "SELECT p.*, ft.random_cost as price, ft.approval_status,
                 u.full_name as manufacturer_name,
                 tr.test_result, tr.test_date, tr.tester_remarks,
                 tt.type_name as test_type
          FROM products p
          JOIN financial_tracking ft ON p.product_code = ft.product_code
          JOIN users u ON p.manufacturer_id = u.user_id
          LEFT JOIN test_records tr ON p.product_code = tr.product_id_fk
          LEFT JOIN testing_type tt ON tr.test_type_id = tt.test_type_id
          WHERE p.product_code = ? AND ft.approval_status = 'Approved'
          ORDER BY tr.test_date DESC LIMIT 1";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $product_code);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    echo '<div class="alert alert-danger">Product not found or not approved!</div>';
    include 'footer.php';
    exit();
}

$product = mysqli_fetch_assoc($result);

// Fetch all test records for this product
$tests_query = "SELECT tr.*, tt.type_name, tt.test_code
                FROM test_records tr
                JOIN testing_type tt ON tr.test_type_id = tt.test_type_id
                WHERE tr.product_id_fk = ?
                ORDER BY tr.test_date DESC";
$tests_stmt = mysqli_prepare($conn, $tests_query);
mysqli_stmt_bind_param($tests_stmt, "s", $product_code);
mysqli_stmt_execute($tests_stmt);
$tests_result = mysqli_stmt_get_result($tests_stmt);
?>

<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="products.php">Products</a></li>
            <li class="breadcrumb-item active"><?php echo $product['product_name']; ?></li>
        </ol>
    </nav>

    <div class="row">
        <!-- Product Image -->
        <div class="col-lg-6">
            <div class="card h-50">
                <div class="card-body text-center">
                    <img src="./assets/<?php echo $product['product_img']; ?>" 
                         alt="<?php echo $product['product_name']; ?>" 
                         class="img-fluid product-detail-img">
                </div>
            </div>
            
            <!-- CPRI Badge -->
            <div class="text-center mt-3">
                <span class="badge bg-success  p-2">
                    <i class="fas fa-certificate"></i> CPRI Approved Product
                </span>
            </div>
        </div>

        <!-- Product Details -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h1 class="card-title"><?php echo $product['product_name']; ?></h1>
                    
                    <!-- Price -->
                    <div class="product-price-detail mb-4">
                        <h2 class="text-primary">Rs. <?php echo number_format($product['price']); ?></h2>
                    </div>
                    
                    <!-- Product Info -->
                    <div class="product-info mb-4">
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">Product Code</th>
                                <td><?php echo $product['product_code']; ?></td>
                            </tr>
                            <tr>
                                <th>Manufacturer</th>
                                <td><?php echo $product['manufacturer_name']; ?></td>
                            </tr>
                            <tr>
                                <th>Latest Test Result</th>
                                <td>
                                    <span class="badge bg-<?php echo $product['test_result'] == 'Passed' ? 'success' : 'danger'; ?>">
                                        <?php echo $product['test_result'] ?? 'Not Tested'; ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Test Type</th>
                                <td><?php echo $product['test_type'] ?? 'N/A'; ?></td>
                            </tr>
                            <tr>
                                <th>Approval Status</th>
                                <td>
                                    <span class="badge bg-<?php echo $product['approval_status'] == 'Approved' ? 'success' : 'warning'; ?>">
                                        <?php echo $product['approval_status']; ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Revision Number</th>
                                <td><?php echo $product['revise_number']; ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Add to Cart Form -->
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['role_id'] == 4): ?>
                    <form method="POST" action="add-to-cart.php" class="mb-3">
                        <input type="hidden" name="product_code" value="<?php echo $product['product_code']; ?>">
                        
                        <div class="row align-items-center">
                            <div class="col-md-4 mb-3">
                                <label for="quantity" class="form-label">Quantity:</label>
                                <select name="quantity" id="quantity" class="form-select">
                                    <?php for ($i = 1; $i <= 10; $i++): ?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-cart-plus"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    </form>
                    <?php else: ?>
                    <div class="alert alert-info">
                        <a href="login.php" class="btn btn-primary">Login to Purchase</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Description & Test Results -->
    <div class="row mt-4">
        <!-- Test Remarks -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-clipboard-check"></i> Test Remarks</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($product['tester_remarks'])): ?>
                        <p><?php echo nl2br(htmlspecialchars($product['tester_remarks'])); ?></p>
                        <small class="text-muted">
                            Test Date: <?php echo date('F j, Y', strtotime($product['test_date'])); ?>
                        </small>
                    <?php else: ?>
                        <p class="text-muted">No test remarks available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Testing History -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0"><i class="fas fa-history"></i> Testing History</h4>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($tests_result) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Test Type</th>
                                        <th>Date</th>
                                        <th>Result</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($test = mysqli_fetch_assoc($tests_result)): ?>
                                    <tr>
                                        <td><?php echo $test['type_name']; ?> (<?php echo $test['test_code']; ?>)</td>
                                        <td><?php echo date('M d, Y', strtotime($test['test_date'])); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $test['test_result'] == 'Passed' ? 'success' : 'danger'; ?>">
                                                <?php echo $test['test_result']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $test['approval_status'] == 'Approved' ? 'success' : 
                                                     ($test['approval_status'] == 'Pending' ? 'warning' : 'danger');
                                            ?>">
                                                <?php echo $test['approval_status']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No testing history available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <div class="row mt-5">
        <div class="col-12">
            <h3 class="mb-4">Related Products</h3>
            <div class="row">
                <?php
                // Fetch related products (same manufacturer, different products)
                $related_query = "SELECT p.product_code, p.product_name, p.product_img, 
                                         ft.random_cost as price, ft.approval_status
                                  FROM products p
                                  JOIN financial_tracking ft ON p.product_code = ft.product_code
                                  WHERE p.manufacturer_id = ? 
                                    AND p.product_code != ? 
                                    AND ft.approval_status = 'Approved'
                                  LIMIT 3";
                
                $related_stmt = mysqli_prepare($conn, $related_query);
                mysqli_stmt_bind_param($related_stmt, "is", 
                    $product['manufacturer_id'], $product['product_code']);
                mysqli_stmt_execute($related_stmt);
                $related_result = mysqli_stmt_get_result($related_stmt);
                
                if (mysqli_num_rows($related_result) > 0):
                    while ($related = mysqli_fetch_assoc($related_result)):
                ?>
                <div class="col-md-4">
                    <div class="card product-card h-100">
                        <img src="./assets/<?php echo $related['product_img']; ?>" 
                             class="card-img-top" alt="<?php echo $related['product_name']; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $related['product_name']; ?></h5>
                            <p class="price">Rs. <?php echo number_format($related['price']); ?></p>
                            <div class="d-grid gap-2">
                                <a href="product-detail.php?code=<?php echo $related['product_code']; ?>" 
                                   class="btn btn-outline-primary">View Details</a>
                                <?php if (isset($_SESSION['user_id']) && $_SESSION['role_id'] == 4): ?>
                                <form method="POST" action="add-to-cart.php" class="d-inline">
                                    <input type="hidden" name="product_code" value="<?php echo $related['product_code']; ?>">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-cart-plus"></i> Add to Cart
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php 
                    endwhile;
                else:
                    echo '<div class="col-12"><p class="text-muted">No related products found.</p></div>';
                endif;
                ?>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>