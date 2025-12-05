<?php
$page_title = "Shopping Cart - ElectraLab";
include 'db.php';
include 'header.php';
include 'auth_check.php';

// Load cart from database on every page load
$user_id = $_SESSION['user_id'];
$cart_query = "SELECT c.product_code, p.product_name, p.product_img, 
                      ft.random_cost as price, c.quantity
               FROM cart c
               JOIN products p ON c.product_code = p.product_code
               JOIN financial_tracking ft ON p.product_code = ft.product_code
               WHERE c.user_id = ?";
               
$stmt = mysqli_prepare($conn, $cart_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Reset session cart
$_SESSION['cart'] = [];
$cart_total = 0;

while ($item = mysqli_fetch_assoc($result)) {
    $_SESSION['cart'][$item['product_code']] = [
        'name' => $item['product_name'],
        'img' => $item['product_img'],
        'price' => $item['price'],
        'quantity' => $item['quantity']
    ];
}

$_SESSION['cart_count'] = count($_SESSION['cart']);

// Handle remove from cart
if (isset($_GET['remove'])) {
    $remove_code = sanitize_input($_GET['remove'], $conn);
    
    // Remove from database
    $delete_query = "DELETE FROM cart WHERE user_id = ? AND product_code = ?";
    $delete_stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($delete_stmt, "is", $user_id, $remove_code);
    mysqli_stmt_execute($delete_stmt);
    
    // Remove from session
    if (isset($_SESSION['cart'][$remove_code])) {
        unset($_SESSION['cart'][$remove_code]);
        $_SESSION['cart_count'] = count($_SESSION['cart']);
    }
    
    echo '<script>
        Swal.fire({
            title: "Removed!",
            text: "Item removed from cart",
            icon: "success",
            timer: 1500,
            showConfirmButton: false
        }).then(() => {
            window.location.href = "cart.php";
        });
    </script>';
    exit();
}

// Handle quantity update
if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $code => $quantity) {
        $quantity = intval($quantity);
        
        if ($quantity > 0 && $quantity <= 10) {
            // Update in database
            $update_query = "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_code = ?";
            $update_stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($update_stmt, "iis", $quantity, $user_id, $code);
            mysqli_stmt_execute($update_stmt);
            
            // Update session
            if (isset($_SESSION['cart'][$code])) {
                $_SESSION['cart'][$code]['quantity'] = $quantity;
            }
        } elseif ($quantity == 0) {
            // Remove from database
            $delete_query = "DELETE FROM cart WHERE user_id = ? AND product_code = ?";
            $delete_stmt = mysqli_prepare($conn, $delete_query);
            mysqli_stmt_bind_param($delete_stmt, "is", $user_id, $code);
            mysqli_stmt_execute($delete_stmt);
            
            // Remove from session
            unset($_SESSION['cart'][$code]);
        }
    }
    
    $_SESSION['cart_count'] = count($_SESSION['cart']);
    
    echo '<script>
        Swal.fire({
            title: "Updated!",
            text: "Cart updated successfully",
            icon: "success",
            timer: 1500,
            showConfirmButton: false
        }).then(() => {
            window.location.href = "cart.php";
        });
    </script>';
    exit();
}

// Calculate total
foreach ($_SESSION['cart'] as $item) {
    $cart_total += $item['price'] * $item['quantity'];
}
?>

<h1 class="mb-4">Your Shopping Cart</h1>

<?php if (empty($_SESSION['cart'])): ?>
    <div class="alert alert-info text-center">
        <i class="fas fa-shopping-cart fa-3x mb-3"></i>
        <h4>Your cart is empty</h4>
        <p>Browse our products and add items to your cart.</p>
        <a href="products.php" class="btn btn-primary">Browse Products</a>
    </div>
<?php else: ?>
    <form method="POST" action="cart.php">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION['cart'] as $code => $item): 
                        $item_total = $item['price'] * $item['quantity'];
                    ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="./assets/<?php echo $item['img']; ?>" 
                                         alt="<?php echo $item['name']; ?>" 
                                         class="cart-product-img me-3">
                                    <div>
                                        <h6 class="mb-0"><?php echo $item['name']; ?></h6>
                                        <small class="text-muted">Code: <?php echo $code; ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>Rs. <?php echo number_format($item['price']); ?></td>
                            <td>
                                <input type="number" name="quantity[<?php echo $code; ?>]" 
                                       value="<?php echo $item['quantity']; ?>" 
                                       min="1" max="10" class="form-control" style="width: 80px;">
                            </td>
                            <td>Rs. <?php echo number_format($item_total); ?></td>
                            <td>
                                <a href="cart.php?remove=<?php echo $code; ?>" 
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Are you sure you want to remove this item?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                        <td colspan="2"><strong>Rs. <?php echo number_format($cart_total); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <a href="products.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Continue Shopping
                </a>
                <button type="submit" name="update_cart" class="btn btn-warning">
                    <i class="fas fa-sync"></i> Update Cart
                </button>
            </div>
            <div class="col-md-6 text-end">
                <a href="checkout.php" class="btn btn-success btn-lg">
                    <i class="fas fa-lock"></i> Proceed to Checkout
                </a>
            </div>
        </div>
    </form>
<?php endif; ?>

<?php include 'footer.php'; ?>