<?php
$page_title = "Order Summary - ElectraLab";
include 'db.php';
include 'header.php';
include 'auth_check.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$order_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Fetch order details
$order_query = "SELECT o.order_id, o.order_date, o.status, 
                       u.full_name, u.username
                FROM orders o
                JOIN users u ON o.customer_id = u.user_id
                WHERE o.order_id = ? AND o.customer_id = ?";
                
$stmt = mysqli_prepare($conn, $order_query);
mysqli_stmt_bind_param($stmt, "ii", $order_id, $user_id);
mysqli_stmt_execute($stmt);
$order_result = mysqli_stmt_get_result($stmt);

if (!$order = mysqli_fetch_assoc($order_result)) {
    echo '<div class="alert alert-danger">Order not found!</div>';
    include 'footer.php';
    exit();
}

// Fetch order items
$items_query = "SELECT oi.product_code, oi.quantity, oi.price, 
                       p.product_name, p.product_img
                FROM order_items oi
                JOIN products p ON oi.product_code = p.product_code
                WHERE oi.order_id = ?";
                
$items_stmt = mysqli_prepare($conn, $items_query);
mysqli_stmt_bind_param($items_stmt, "i", $order_id);
mysqli_stmt_execute($items_stmt);
$items_result = mysqli_stmt_get_result($items_stmt);
?>

<div class="container">
    <div class="card">
        <div class="card-header bg-success text-white">
            <h3 class="mb-0"><i class="fas fa-check-circle"></i> Order Confirmation</h3>
        </div>
        <div class="card-body">
            <div class="alert alert-success">
                <h4 class="alert-heading">Thank you for your order!</h4>
                <p>Your order has been placed successfully. Order details are below.</p>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <h5>Order Information</h5>
                    <table class="table table-bordered">
                        <tr>
                            <th>Order ID:</th>
                            <td>#<?php echo $order['order_id']; ?></td>
                        </tr>
                        <tr>
                            <th>Order Date:</th>
                            <td><?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?></td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $order['status'] == 'Confirmed' ? 'success' : 
                                         ($order['status'] == 'Pending' ? 'warning' : 'info'); 
                                ?>">
                                    <?php echo $order['status']; ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Customer:</th>
                            <td><?php echo $order['full_name']; ?> (<?php echo $order['username']; ?>)</td>
                        </tr>
                    </table>
                </div>
                
                <div class="col-md-6">
                    <h5>Order Items</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $subtotal = 0;
                                while ($item = mysqli_fetch_assoc($items_result)): 
                                    $item_total = $item['price'] * $item['quantity'];
                                    $subtotal += $item_total;
                                ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="./assets/<?php echo $item['product_img']; ?>" 
                                                     alt="<?php echo $item['product_name']; ?>" 
                                                     class="me-2" style="width: 40px; height: 40px;">
                                                <?php echo $item['product_name']; ?>
                                            </div>
                                        </td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td>Rs. <?php echo number_format($item['price']); ?></td>
                                        <td>Rs. <?php echo number_format($item_total); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td><strong>Rs. <?php echo number_format($subtotal); ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 text-center">
                <a href="products.php" class="btn btn-primary">
                    <i class="fas fa-shopping-cart"></i> Continue Shopping
                </a>
                <a href="my-orders.php" class="btn btn-outline-secondary">
                    <i class="fas fa-list"></i> View All Orders
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>