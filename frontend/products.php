<?php
$page_title = "Products - ElectraLab";
include 'db.php';
include 'header.php';


// Check for add to cart success message
if (isset($_SESSION['add_to_cart_success'])) {
    echo '<script>window.addToCartSuccess = "' . $_SESSION['add_to_cart_success'] . '";</script>';
    unset($_SESSION['add_to_cart_success']);
}

// Handle search
$search = isset($_GET['search']) ? sanitize_input($_GET['search'], $conn) : '';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1>CPRI Approved Products</h1>
        <p class="text-muted">Browse our quality-tested electrical products</p>
    </div>
    <div class="col-md-4">
        <form method="GET" action="products.php" class="d-flex">
            <input type="text" name="search" class="form-control me-2"
                placeholder="Search products..." value="<?php echo $search; ?>">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>
</div>

<div class="row">
    <?php
    // Build query with search filter
    $query = "SELECT p.product_code, p.product_name, p.product_img, 
                     ft.random_cost as price, tr.test_result,
                     COUNT(tr.test_result) as test_count
              FROM products p
              JOIN financial_tracking ft ON p.product_code = ft.product_code
              LEFT JOIN test_records tr ON p.product_code = tr.product_id_fk
              WHERE ft.approval_status = 'Approved'";

    if (!empty($search)) {
        $query .= " AND (p.product_name LIKE '%$search%' 
                   OR p.product_code LIKE '%$search%')";
    }

    $query .= " GROUP BY p.product_code 
                ORDER BY p.created_at DESC";

    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
    ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card product-card h-100">
                    <div class="card-img-container">
                        <img src="./assets/<?php echo $row['product_img']; ?>"
                            class="card-img-top" alt="<?php echo $row['product_name']; ?>">
                        <div class="product-badge">CPRI Approved</div>
                    </div>
                    <!-- In products.php -->
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $row['product_name']; ?></h5>
                        <p class="card-text">
                            <strong>Product Code:</strong> <?php echo $row['product_code']; ?><br>
                            <strong>Test Result:</strong>
                            <span class="badge bg-<?php echo $row['test_result'] == 'Passed' ? 'success' : 'danger'; ?>">
                                <?php echo $row['test_result']; ?>
                            </span>
                        </p>
                        <div class="product-price">
                            <h4>Rs. <?php echo number_format($row['price']); ?></h4>
                        </div>
                        <div class="d-grid gap-2">
                            <!-- FORM-BASED ADD TO CART -->
                            <form method="POST" action="add-to-cart.php" class="d-inline">
                                <input type="hidden" name="product_code" value="<?php echo $row['product_code']; ?>">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-cart-plus"></i> Add to Cart
                                </button>
                            </form>
                            <a href="product-details.php?code=<?php echo $row['product_code']; ?>"
                                class="btn btn-outline-secondary">
                                <i class="fas fa-info-circle"></i> View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
    <?php
        }
    } else {
        echo '<div class="col-12"><div class="alert alert-info text-center">No products found.</div></div>';
    }
    ?>
</div>

<?php include 'footer.php'; ?>