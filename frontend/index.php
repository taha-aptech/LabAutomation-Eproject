<?php
$page_title = "Home - ElectraLab";
include 'db.php';
include 'header.php';
?>

<!-- Hero Section -->
<section class="hero-section text-center py-5 text-white" style="background: var(--primary-color)">
    <div class="container">
        <h1 class="display-4 fw-bold">Premium Electrical Products</h1>
        <p class="lead">CPRI-approved quality with advanced testing standards</p>
        <a href="products.php" class="btn btn-light btn-lg mt-3">Browse Products</a>
    </div>
</section>

<!-- Featured Products -->
<section class="featured-products py-5">
    <div class="container">
        <h2 class="text-center mb-5">Featured Products</h2>
        <div class="row">
            <?php
            // Fetch 3 approved products for featured section
            $query = "SELECT p.product_code, p.product_name, p.product_img, f.approval_status, 
                             f.random_cost as price
                      FROM products p
                      JOIN financial_tracking f ON p.product_code = f.product_code
                      WHERE f.approval_status = 'Approved'
                      LIMIT 3";

            $result = mysqli_query($conn, $query);

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
            ?>
                    <div class="col-md-4 mb-4">
                        <div class="card product-card h-100">
                            <img src="./assets/<?php echo $row['product_img']; ?>"
                                class="card-img-top" alt="<?php echo $row['product_img']; ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $row['product_name']; ?></h5>
                                <p class="card-text">CPRI Approved Product</p>
                                <p class="price">Rs. <?php echo number_format($row['price']); ?></p>
                                <form method="POST" action="add-to-cart.php" class="d-inline-block">
                                    <input type="hidden" name="product_code" value="<?php echo $row['product_code']; ?>">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-cart-plus"></i> Add to Cart
                                    </button>
                                </form>
                                <a href="product-details.php?code=<?php echo $row['product_code']; ?>"
                                    class="btn btn-outline-secondary">View Details</a>
                            </div>
                        </div>
                    </div>
            <?php
                }
            } else {
                echo '<div class="col-12"><p class="text-center">No featured products available.</p></div>';
            }
            ?>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features py-5 bg-light">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-4">
                <div class="feature-icon mb-3">
                    <i class="fas fa-certificate fa-3x text-primary"></i>
                </div>
                <h4>CPRI Approved</h4>
                <p>All products tested and approved by CPRI authorities</p>
            </div>
            <div class="col-md-4">
                <div class="feature-icon mb-3">
                    <i class="fas fa-shield-alt fa-3x text-primary"></i>
                </div>
                <h4>Quality Assurance</h4>
                <p>Rigorous testing for safety and performance</p>
            </div>
            <div class="col-md-4">
                <div class="feature-icon mb-3">
                    <i class="fas fa-shipping-fast fa-3x text-primary"></i>
                </div>
                <h4>Fast Delivery</h4>
                <p>Nationwide shipping with tracking</p>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>