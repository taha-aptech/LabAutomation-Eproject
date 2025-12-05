<?php
$page_title = "My Orders - ElectraLab";
include 'db.php';
include 'header.php';
include 'auth_check.php'; // Ensure user is logged in

$user_id = $_SESSION['user_id'];

// Handle order cancellation
if (isset($_GET['cancel']) && is_numeric($_GET['cancel'])) {
    $order_id = intval($_GET['cancel']);
    
    // Verify order belongs to user
    $verify_query = "SELECT status FROM orders WHERE order_id = ? AND customer_id = ?";
    $verify_stmt = mysqli_prepare($conn, $verify_query);
    mysqli_stmt_bind_param($verify_stmt, "ii", $order_id, $user_id);
    mysqli_stmt_execute($verify_stmt);
    $verify_result = mysqli_stmt_get_result($verify_stmt);
    
    if ($order = mysqli_fetch_assoc($verify_result)) {
        if ($order['status'] == 'Pending' || $order['status'] == 'Confirmed') {
            // Update order status to Cancelled
            $cancel_query = "UPDATE orders SET status = 'Cancelled' WHERE order_id = ?";
            $cancel_stmt = mysqli_prepare($conn, $cancel_query);
            mysqli_stmt_bind_param($cancel_stmt, "i", $order_id);
            
            if (mysqli_stmt_execute($cancel_stmt)) {
                echo '<script>
                    Swal.fire({
                        title: "Order Cancelled",
                        text: "Order #' . $order_id . ' has been cancelled successfully.",
                        icon: "success",
                        timer: 2000
                    });
                </script>';
            }
        } else {
            echo '<script>
                Swal.fire({
                    title: "Cannot Cancel",
                    text: "This order cannot be cancelled at this stage.",
                    icon: "error",
                    timer: 2000
                });
            </script>';
        }
    }
}

// Fetch all orders for this user
$orders_query = "SELECT o.order_id, o.order_date, o.status, 
                        COUNT(oi.item_id) as item_count,
                        SUM(oi.quantity * oi.price) as total_amount
                 FROM orders o
                 LEFT JOIN order_items oi ON o.order_id = oi.order_id
                 WHERE o.customer_id = ?
                 GROUP BY o.order_id
                 ORDER BY o.order_date DESC";
                 
$orders_stmt = mysqli_prepare($conn, $orders_query);
mysqli_stmt_bind_param($orders_stmt, "i", $user_id);
mysqli_stmt_execute($orders_stmt);
$orders_result = mysqli_stmt_get_result($orders_stmt);
?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4"><i class="fas fa-clipboard-list"></i> My Orders</h1>
            
            <?php if (mysqli_num_rows($orders_result) == 0): ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                        <h3>No Orders Yet</h3>
                        <p class="text-muted">You haven't placed any orders yet.</p>
                        <a href="products.php" class="btn btn-primary">Browse Products</a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Orders Summary Cards -->
                <div class="row mb-4">
                    <?php
                    // Reset result pointer
                    mysqli_data_seek($orders_result, 0);
                    
                    // Count orders by status
                    $status_counts = ['Total' => 0, 'Pending' => 0, 'Confirmed' => 0, 'Shipped' => 0, 'Delivered' => 0, 'Cancelled' => 0];
                    
                    while ($order = mysqli_fetch_assoc($orders_result)) {
                        $status_counts['Total']++;
                        if (isset($status_counts[$order['status']])) {
                            $status_counts[$order['status']]++;
                        }
                    }
                    
                    // Reset again for main display
                    mysqli_data_seek($orders_result, 0);
                    ?>
                    
                    <div class="col-md-2 col-6 mb-3">
                        <div class="card bg-primary text-white text-center">
                            <div class="card-body">
                                <h2 class="mb-0"><?php echo $status_counts['Total']; ?></h2>
                                <small>Total Orders</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-2 col-6 mb-3">
                        <div class="card bg-warning text-white text-center">
                            <div class="card-body">
                                <h2 class="mb-0"><?php echo $status_counts['Pending']; ?></h2>
                                <small>Pending</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-2 col-6 mb-3">
                        <div class="card bg-info text-white text-center">
                            <div class="card-body">
                                <h2 class="mb-0"><?php echo $status_counts['Confirmed']; ?></h2>
                                <small>Confirmed</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-2 col-6 mb-3">
                        <div class="card bg-success text-white text-center">
                            <div class="card-body">
                                <h2 class="mb-0"><?php echo $status_counts['Delivered']; ?></h2>
                                <small>Delivered</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Orders Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Date</th>
                                        <th>Items</th>
                                        <th>Total Amount</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($order = mysqli_fetch_assoc($orders_result)): 
                                        $status_color = match($order['status']) {
                                            'Pending' => 'warning',
                                            'Confirmed' => 'info',
                                            'Shipped' => 'primary',
                                            'Delivered' => 'success',
                                            'Cancelled' => 'danger',
                                            default => 'secondary'
                                        };
                                    ?>
                                        <tr>
                                            <td>
                                                <strong>#<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></strong>
                                            </td>
                                            <td>
                                                <?php echo date('M d, Y', strtotime($order['order_date'])); ?><br>
                                                <small class="text-muted"><?php echo date('h:i A', strtotime($order['order_date'])); ?></small>
                                            </td>
                                            <td><?php echo $order['item_count']; ?> item(s)</td>
                                            <td>
                                                <strong>Rs. <?php echo number_format($order['total_amount'] ?? 0); ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $status_color; ?>">
                                                    <?php echo $order['status']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="order-details.php?id=<?php echo $order['order_id']; ?>" 
                                                       class="btn btn-outline-primary" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    <?php if ($order['status'] == 'Pending' || $order['status'] == 'Confirmed'): ?>
                                                    <a href="my-orders.php?cancel=<?php echo $order['order_id']; ?>" 
                                                       class="btn btn-outline-danger" 
                                                       onclick="return confirm('Are you sure you want to cancel order #<?php echo $order['order_id']; ?>?')"
                                                       title="Cancel Order">
                                                        <i class="fas fa-times"></i>
                                                    </a>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($order['status'] == 'Delivered'): ?>
    <!-- <a href="invoice.php?id=<?php echo $order_id; ?>" 
       class="btn btn-success "
       download="invoice_<?php echo $order_id; ?>.pdf">
        <i class="fas fa-file-invoice"></i> 
    </a> -->
<?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Order Status Legend -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Order Status Guide</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2 col-6 mb-2">
                                <span class="badge bg-warning">Pending</span>
                                <small class="text-muted">Order received, awaiting confirmation</small>
                            </div>
                            <div class="col-md-2 col-6 mb-2">
                                <span class="badge bg-info">Confirmed</span>
                                <small class="text-muted">Order confirmed, processing</small>
                            </div>
                            <div class="col-md-2 col-6 mb-2">
                                <span class="badge bg-primary">Shipped</span>
                                <small class="text-muted">Order shipped, in transit</small>
                            </div>
                            <div class="col-md-2 col-6 mb-2">
                                <span class="badge bg-success">Delivered</span>
                                <small class="text-muted">Order delivered successfully</small>
                            </div>
                            <div class="col-md-2 col-6 mb-2">
                                <span class="badge bg-danger">Cancelled</span>
                                <small class="text-muted">Order cancelled</small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>