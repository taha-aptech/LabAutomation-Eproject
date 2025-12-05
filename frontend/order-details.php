<?php
$page_title = "Order Details - ElectraLab";
include 'db.php';
include 'header.php';
include 'auth_check.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: my-orders.php');
    exit();
}

$order_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Fetch order details
$order_query = "SELECT o.*, u.full_name, u.username
                FROM orders o
                JOIN users u ON o.customer_id = u.user_id
                WHERE o.order_id = ? AND o.customer_id = ?";

$order_stmt = mysqli_prepare($conn, $order_query);
mysqli_stmt_bind_param($order_stmt, "ii", $order_id, $user_id);
mysqli_stmt_execute($order_stmt);
$order_result = mysqli_stmt_get_result($order_stmt);

if (mysqli_num_rows($order_result) == 0) {
    echo '<div class="alert alert-danger">Order not found!</div>';
    include 'footer.php';
    exit();
}

$order = mysqli_fetch_assoc($order_result);

// Fetch order items
$items_query = "SELECT oi.*, p.product_name, p.product_img
                FROM order_items oi
                JOIN products p ON oi.product_code = p.product_code
                WHERE oi.order_id = ?
                ORDER BY oi.item_id";

$items_stmt = mysqli_prepare($conn, $items_query);
mysqli_stmt_bind_param($items_stmt, "i", $order_id);
mysqli_stmt_execute($items_stmt);
$items_result = mysqli_stmt_get_result($items_stmt);

// Calculate totals
$subtotal = 0;
$shipping = 500;
$tax_rate = 0.17;

// Store items for display
$order_items = [];
while ($item = mysqli_fetch_assoc($items_result)) {
    $order_items[] = $item;
    $subtotal += $item['price'] * $item['quantity'];
}

$tax = $subtotal * $tax_rate;
$total = $subtotal + $shipping + $tax;
?>

<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="my-orders.php">My Orders</a></li>
            <li class="breadcrumb-item active">Order #<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></li>
        </ol>
    </nav>

    <div class="row">
        <!-- Order Header -->
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">Order Details</h3>
                    <span class="badge bg-<?php
                                            echo match ($order['status']) {
                                                'Pending' => 'warning',
                                                'Confirmed' => 'info',
                                                'Shipped' => 'primary',
                                                'Delivered' => 'success',
                                                'Cancelled' => 'danger',
                                                default => 'secondary'
                                            };
                                            ?> fs-6">
                        <?php echo $order['status']; ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Order ID:</strong><br>
                            <span class="fs-5">#<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></span>
                        </div>
                        <div class="col-md-3">
                            <strong>Order Date:</strong><br>
                            <?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Customer:</strong><br>
                            <?php echo $order['full_name']; ?><br>
                            <small class="text-muted"><?php echo $order['username']; ?></small>
                        </div>
                        <div class="col-md-3">
                            <strong>Total Items:</strong><br>
                            <?php echo count($order_items); ?> item(s)
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-boxes"></i> Order Items</h4>
                </div>
                <div class="card-body">
                    <?php if (count($order_items) > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order_items as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="./assets/<?php echo $item['product_img']; ?>"
                                                        alt="<?php echo $item['product_name']; ?>"
                                                        class="me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                                    <div>
                                                        <strong><?php echo $item['product_name']; ?></strong><br>
                                                        <small class="text-muted">Code: <?php echo $item['product_code']; ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>Rs. <?php echo number_format($item['price']); ?></td>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td><strong>Rs. <?php echo number_format($item['price'] * $item['quantity']); ?></strong></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No items found in this order.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Order Timeline -->
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-history"></i> Order Timeline</h4>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <?php
                        $timeline = [
                            ['status' => 'order_placed', 'label' => 'Order Placed', 'date' => $order['order_date'], 'active' => true],
                            ['status' => 'order_confirmed', 'label' => 'Order Confirmed', 'date' => $order['status'] == 'Confirmed' ? date('Y-m-d H:i:s', strtotime($order['order_date'] . ' +1 hour')) : null, 'active' => in_array($order['status'], ['Confirmed', 'Shipped', 'Delivered'])],
                            ['status' => 'order_shipped', 'label' => 'Order Shipped', 'date' => $order['status'] == 'Shipped' ? date('Y-m-d H:i:s', strtotime($order['order_date'] . ' +1 day')) : null, 'active' => in_array($order['status'], ['Shipped', 'Delivered'])],
                            ['status' => 'order_delivered', 'label' => 'Order Delivered', 'date' => $order['status'] == 'Delivered' ? date('Y-m-d H:i:s', strtotime($order['order_date'] . ' +3 days')) : null, 'active' => $order['status'] == 'Delivered'],
                        ];
                        ?>

                        <div class="row">
                            <?php foreach ($timeline as $step): ?>
                                <div class="col-3 text-center">
                                    <div class="timeline-step">
                                        <div class="timeline-icon <?php echo $step['active'] ? 'active' : 'inactive'; ?>">
                                            <i class="fas fa-<?php
                                                                echo match ($step['status']) {
                                                                    'order_placed' => 'shopping-cart',
                                                                    'order_confirmed' => 'check-circle',
                                                                    'order_shipped' => 'shipping-fast',
                                                                    'order_delivered' => 'home',
                                                                    default => 'circle'
                                                                };
                                                                ?>"></i>
                                        </div>
                                        <h6 class="mt-2"><?php echo $step['label']; ?></h6>
                                        <?php if ($step['date']): ?>
                                            <small class="text-muted"><?php echo date('M d, Y', strtotime($step['date'])); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-receipt"></i> Order Summary</h4>
                </div>
                <div class="card-body">
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
                        <strong>Total Amount</strong>
                        <strong class="fs-5">Rs. <?php echo number_format($total); ?></strong>
                    </div>

                    <div class="mt-4">
                        <?php if ($order['status'] == 'Pending' || $order['status'] == 'Confirmed'): ?>
                            <a href="my-orders.php?cancel=<?php echo $order_id; ?>"
                                class="btn btn-danger w-100 mb-2"
                                onclick="return confirm('Are you sure you want to cancel this order?')">
                                <i class="fas fa-times"></i> Cancel Order
                            </a>
                        <?php endif; ?>

                        <?php if ($order['status'] == 'Delivered'): ?>
                            <!-- <a href="#"
                                class="btn btn-success w-100 mb-2"
                                
                                <i class="fas fa-file-invoice"></i> Download Invoice
                            </a> -->
                        <?php endif; ?>

                        <a href="my-orders.php" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-arrow-left"></i> Back to Orders
                        </a>
                    </div>
                </div>
            </div>

            <!-- Customer Support -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-headset"></i> Need Help?</h5>
                </div>
                <div class="card-body">
                    <p>If you have any questions about your order:</p>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-phone text-primary me-2"></i> +92 300 1234567</li>
                        <li><i class="fas fa-envelope text-primary me-2"></i> support@electralab.com</li>
                    </ul>
                    <a href="contact.php" class="btn btn-outline-primary w-100">
                        <i class="fas fa-comment"></i> Contact Support
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .timeline-step {
        position: relative;
        padding-bottom: 20px;
    }

    .timeline-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        font-size: 1.2rem;
    }

    .timeline-icon.active {
        background-color: #0d6efd;
        color: white;
    }

    .timeline-icon.inactive {
        background-color: #e9ecef;
        color: #6c757d;
    }

    .timeline-step:not(:last-child):after {
        content: '';
        position: absolute;
        top: 25px;
        left: 75%;
        width: 100%;
        height: 2px;
        background-color: #e9ecef;
        z-index: -1;
    }

    .timeline-step.active:not(:last-child):after {
        background-color: #0d6efd;
    }
</style>

<?php include 'footer.php'; ?>