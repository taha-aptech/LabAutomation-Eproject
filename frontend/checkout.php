<?php
$page_title = "Checkout - ElectraLab";
include 'db.php';
include 'header.php';
include 'auth_check.php';

// Check if cart is empty
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit();
}

// Calculate totals
$subtotal = 0;
$shipping = 500;
$tax_rate = 0.17;

foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$tax = $subtotal * $tax_rate;
$total = $subtotal + $shipping + $tax;

// Handle checkout form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $address = sanitize_input($_POST['address'], $conn);
    $city = sanitize_input($_POST['city'], $conn);
    $phone = sanitize_input($_POST['phone'], $conn);
    $payment_method = sanitize_input($_POST['payment_method'], $conn);
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Insert order
        $user_id = $_SESSION['user_id'];
        $order_query = "INSERT INTO orders (customer_id, status) VALUES (?, 'Confirmed')";
        $stmt = mysqli_prepare($conn, $order_query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $order_id = mysqli_insert_id($conn);
        
        // Insert order items
        $item_query = "INSERT INTO order_items (order_id, product_code, quantity, price) VALUES (?, ?, ?, ?)";
        $item_stmt = mysqli_prepare($conn, $item_query);
        
        foreach ($_SESSION['cart'] as $code => $item) {
            mysqli_stmt_bind_param($item_stmt, "isid", $order_id, $code, $item['quantity'], $item['price']);
            mysqli_stmt_execute($item_stmt);
        }
        
        // Clear cart from database
        $clear_cart_query = "DELETE FROM cart WHERE user_id = ?";
        $clear_stmt = mysqli_prepare($conn, $clear_cart_query);
        mysqli_stmt_bind_param($clear_stmt, "i", $user_id);
        mysqli_stmt_execute($clear_stmt);
        
        // Commit transaction
        mysqli_commit($conn);
        
        // Clear session cart
        $_SESSION['cart'] = [];
        $_SESSION['cart_count'] = 0;
        
        // Show success message
        echo '<script>
            Swal.fire({
                title: "Order Confirmed!",
                text: "Your order #' . $order_id . ' has been placed successfully.",
                icon: "success",
                confirmButtonText: "View Orders"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "order-summary.php?id=' . $order_id . '";
                }
            });
        </script>';
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo '<script>
            Swal.fire({
                title: "Error!",
                text: "Failed to process order. Please try again.",
                icon: "error"
            });
        </script>';
    }
}
?>

<h1 class="mb-4">Checkout</h1>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h4>Shipping Information</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="checkout.php" id="checkoutForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="full_name" 
                                   value="<?php echo $_SESSION['full_name']; ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   pattern="[0-9]{11}" title="11 digit phone number" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city" required>
                        </div>
                        <div class="col-md-6">
                            <label for="payment_method" class="form-label">Payment Method</label>
                            <select class="form-select" id="payment_method" name="payment_method" required>
                                <option value="">Select Payment Method</option>
                                <option value="cash_on_delivery">Cash on Delivery</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="terms" required>
                        <label class="form-check-label" for="terms">
                            I agree to the terms and conditions
                        </label>
                    </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h4>Order Summary</h4>
            </div>
            <div class="card-body">
                <?php foreach ($_SESSION['cart'] as $code => $item): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span><?php echo $item['name']; ?> (x<?php echo $item['quantity']; ?>)</span>
                        <span>Rs. <?php echo number_format($item['price'] * $item['quantity']); ?></span>
                    </div>
                <?php endforeach; ?>
                
                <hr>
                
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal</span>
                    <span>Rs. <?php echo number_format($subtotal); ?></span>
                </div>
                
                <div class="d-flex justify-content-between mb-2">
                    <span>Shipping</span>
                    <span>Rs. <?php echo number_format($shipping); ?></span>
                </div>
                
                <div class="d-flex justify-content-between mb-2">
                    <span>Tax (17%)</span>
                    <span>Rs. <?php echo number_format($tax); ?></span>
                </div>
                
                <hr>
                
                <div class="d-flex justify-content-between mb-3">
                    <strong>Total</strong>
                    <strong>Rs. <?php echo number_format($total); ?></strong>
                </div>
                
                <button type="submit" class="btn btn-success w-100 btn-lg" id="placeOrderBtn">
                    <i class="fas fa-lock"></i> Place Order
                </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    const placeOrderBtn = document.getElementById('placeOrderBtn');
    placeOrderBtn.disabled = true;
    placeOrderBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
});
</script>

<?php include 'footer.php'; ?>