<?php
include 'db.php';
include 'auth_check.php';

// PLACE NEW ORDER (Customer only)
if (isset($_POST['placeOrder']) && $user_role == 'Customer') {
    $customer_id = $_SESSION['user_id'];
    
    // Create order
    $order_query = "INSERT INTO orders (customer_id) VALUES ($customer_id)";
    mysqli_query($conn, $order_query);
    $order_id = mysqli_insert_id($conn);
    
    // Add order items
    if (isset($_POST['products']) && is_array($_POST['products'])) {
        foreach ($_POST['products'] as $product) {
            $product_code = $product['code'];
            $quantity = $product['quantity'];
            $price = $product['price'];
            
            $item_stmt = mysqli_prepare($conn, "
                INSERT INTO order_items (order_id, product_code, quantity, price) 
                VALUES (?, ?, ?, ?)
            ");
            mysqli_stmt_bind_param($item_stmt, "isid", $order_id, $product_code, $quantity, $price);
            mysqli_stmt_execute($item_stmt);
            mysqli_stmt_close($item_stmt);
        }
    }
    
    echo "<script>
        Swal.fire({
            icon: 'success',
            title: 'Order Placed!',
            text: 'Order ID: $order_id',
            timer: 3000
        }).then(() => {
            window.location.reload();
        });
    </script>";
}

// UPDATE ORDER STATUS (Admin/Manufacturer)
if (isset($_POST['updateStatus'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    
    $stmt = mysqli_prepare($conn, "UPDATE orders SET status = ? WHERE order_id = ?");
    mysqli_stmt_bind_param($stmt, "si", $status, $order_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    header("Location: orders.php?msg=updated");
    exit();
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
            <?php include 'sidebar.php'; ?>
            
            <div class="col-md-10">
                <div class="p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3><i class="bi bi-cart"></i> 
                            <?php echo $user_role == 'Customer' ? 'My Orders' : 'All Orders'; ?>
                        </h3>
                        
                        <?php if ($user_role == 'Customer'): ?>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newOrderModal">
                            <i class="bi bi-cart-plus"></i> Place New Order
                        </button>
                        <?php endif; ?>
                    </div>

                    <!-- Orders Table -->
                    <div class="card shadow">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="ordersTable" class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Customer</th>
                                            <th>Order Date</th>
                                            <th>Status</th>
                                            <th>Items</th>
                                            <th>Total Amount</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Build query based on role
                                        if ($user_role == 'Customer') {
                                            $customer_id = $_SESSION['user_id'];
                                            $query = "
                                                SELECT o.*, u.full_name as customer_name 
                                                FROM orders o
                                                JOIN users u ON o.customer_id = u.user_id
                                                WHERE o.customer_id = $customer_id
                                                ORDER BY o.order_date DESC
                                            ";
                                        } else {
                                            $query = "
                                                SELECT o.*, u.full_name as customer_name 
                                                FROM orders o
                                                JOIN users u ON o.customer_id = u.user_id
                                                ORDER BY o.order_date DESC
                                            ";
                                        }
                                        
                                        $result = mysqli_query($conn, $query);
                                        
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            // Get order items and total
                                            $order_id = $row['order_id'];
                                            $items_query = mysqli_query($conn, "
                                                SELECT oi.*, p.product_name 
                                                FROM order_items oi
                                                JOIN products p ON oi.product_code = p.product_code
                                                WHERE oi.order_id = $order_id
                                            ");
                                            
                                            $items_html = '';
                                            $total_amount = 0;
                                            while ($item = mysqli_fetch_assoc($items_query)) {
                                                $items_html .= "
                                                    <div class='border-bottom pb-1 mb-1'>
                                                        <small>{$item['product_name']} 
                                                        (x{$item['quantity']}) - Rs. {$item['price']}</small>
                                                    </div>
                                                ";
                                                $total_amount += $item['quantity'] * $item['price'];
                                            }
                                            
                                            // Status badge
                                            $status_badge = '';
                                            switch($row['status']) {
                                                case 'Pending':
                                                    $status_badge = '<span class="badge bg-warning">Pending</span>';
                                                    break;
                                                case 'Confirmed':
                                                    $status_badge = '<span class="badge bg-info">Confirmed</span>';
                                                    break;
                                                case 'Shipped':
                                                    $status_badge = '<span class="badge bg-primary">Shipped</span>';
                                                    break;
                                                case 'Delivered':
                                                    $status_badge = '<span class="badge bg-success">Delivered</span>';
                                                    break;
                                                case 'Cancelled':
                                                    $status_badge = '<span class="badge bg-danger">Cancelled</span>';
                                                    break;
                                            }
                                            
                                            // Actions
                                            $actions = '';
                                            if (in_array($user_role, ['Admin', 'Manufacturer'])) {
                                                $actions = "
                                                    <button class='btn btn-sm btn-outline-primary updateStatusBtn'
                                                        data-id='{$row['order_id']}'
                                                        data-status='{$row['status']}'>
                                                        <i class='bi bi-arrow-repeat'></i> Update Status
                                                    </button>
                                                ";
                                            } else {
                                                $actions = '<span class="text-muted">No action</span>';
                                            }
                                            
                                            echo "<tr>
                                                <td>#{$row['order_id']}</td>
                                                <td>{$row['customer_name']}</td>
                                                <td>{$row['order_date']}</td>
                                                <td>{$status_badge}</td>
                                                <td>
                                                    <button class='btn btn-sm btn-outline-info viewItemsBtn'
                                                        data-items='".htmlspecialchars($items_html)."'>
                                                        <i class='bi bi-list'></i> View Items
                                                    </button>
                                                </td>
                                                <td>Rs. {$total_amount}</td>
                                                <td>{$actions}</td>
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

    <!-- New Order Modal (Customer only) -->
    <?php if ($user_role == 'Customer'): ?>
    <div class="modal fade" id="newOrderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" id="orderForm">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="bi bi-cart-plus"></i> Place New Order</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="orderItemsContainer">
                            <div class="order-item row mb-3 border p-3 rounded">
                                <div class="col-md-5">
                                    <label class="form-label">Product</label>
                                    <select class="form-select product-select" name="products[0][code]" required>
                                        <option value="">Select Product</option>
                                        <?php
                                        $products = mysqli_query($conn, "SELECT * FROM products");
                                        while ($product = mysqli_fetch_assoc($products)) {
                                            echo "<option value='{$product['product_code']}'
                                                    data-price='".rand(500, 5000)."'>
                                                    {$product['product_code']} - {$product['product_name']}
                                                </option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Quantity</label>
                                    <input type="number" class="form-control quantity-input" 
                                           name="products[0][quantity]" value="1" min="1" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Price (Rs.)</label>
                                    <input type="number" class="form-control price-input" 
                                           name="products[0][price]" readonly>
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="button" class="btn btn-danger remove-item-btn">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <button type="button" id="addMoreItems" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-plus-circle"></i> Add More Items
                        </button>
                        
                        <div class="mt-3 border-top pt-3">
                            <h5>Order Summary</h5>
                            <table class="table table-sm">
                                <tr>
                                    <th>Total Items:</th>
                                    <td id="totalItems">1</td>
                                </tr>
                                <tr>
                                    <th>Total Quantity:</th>
                                    <td id="totalQuantity">1</td>
                                </tr>
                                <tr>
                                    <th>Total Amount:</th>
                                    <td>Rs. <span id="totalAmount">0</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="placeOrder" class="btn btn-primary">Place Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- View Items Modal -->
    <div class="modal fade" id="viewItemsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="bi bi-list"></i> Order Items</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="itemsList">
                    <!-- Items will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Update Status Modal -->
    <?php if (in_array($user_role, ['Admin', 'Manufacturer'])): ?>
    <div class="modal fade" id="updateStatusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="order_id" id="statusOrderId">
                    
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="bi bi-arrow-repeat"></i> Update Order Status</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">New Status</label>
                            <select class="form-select" name="status" id="orderStatus" required>
                                <option value="Pending">Pending</option>
                                <option value="Confirmed">Confirmed</option>
                                <option value="Shipped">Shipped</option>
                                <option value="Delivered">Delivered</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="updateStatus" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script>
    $(document).ready(function() {
        $('#ordersTable').DataTable();
    });

    // VIEW ORDER ITEMS
    document.querySelectorAll('.viewItemsBtn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('itemsList').innerHTML = this.getAttribute('data-items');
            const modal = new bootstrap.Modal(document.getElementById('viewItemsModal'));
            modal.show();
        });
    });

    // UPDATE STATUS
    <?php if (in_array($user_role, ['Admin', 'Manufacturer'])): ?>
    document.querySelectorAll('.updateStatusBtn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('statusOrderId').value = this.getAttribute('data-id');
            document.getElementById('orderStatus').value = this.getAttribute('data-status');
            
            const modal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
            modal.show();
        });
    });
    <?php endif; ?>

    // ORDER FORM DYNAMIC FIELDS
    <?php if ($user_role == 'Customer'): ?>
    let itemIndex = 0;
    
    // Add more items
    document.getElementById('addMoreItems').addEventListener('click', function() {
        itemIndex++;
        const container = document.getElementById('orderItemsContainer');
        const newItem = document.createElement('div');
        newItem.className = 'order-item row mb-3 border p-3 rounded';
        newItem.innerHTML = `
            <div class="col-md-5">
                <select class="form-select product-select" name="products[${itemIndex}][code]" required>
                    <option value="">Select Product</option>
                    <?php
                    $products = mysqli_query($conn, "SELECT * FROM products");
                    while ($product = mysqli_fetch_assoc($products)) {
                        echo "<option value='{$product['product_code']}'
                                data-price='".rand(500, 5000)."'>
                                {$product['product_code']} - {$product['product_name']}
                            </option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <input type="number" class="form-control quantity-input" 
                       name="products[${itemIndex}][quantity]" value="1" min="1" required>
            </div>
            <div class="col-md-3">
                <input type="number" class="form-control price-input" 
                       name="products[${itemIndex}][price]" readonly>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-danger remove-item-btn">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;
        container.appendChild(newItem);
        
        // Add event listeners to new item
        addEventListenersToItem(newItem);
        updateOrderSummary();
    });
    
    // Add event listeners to item
    function addEventListenersToItem(item) {
        const productSelect = item.querySelector('.product-select');
        const quantityInput = item.querySelector('.quantity-input');
        const priceInput = item.querySelector('.price-input');
        const removeBtn = item.querySelector('.remove-item-btn');
        
        productSelect.addEventListener('change', function() {
            const price = this.options[this.selectedIndex].getAttribute('data-price');
            priceInput.value = price || 0;
            updateOrderSummary();
        });
        
        quantityInput.addEventListener('input', updateOrderSummary);
        removeBtn.addEventListener('click', function() {
            item.remove();
            updateOrderSummary();
        });
    }
    
    // Update order summary
    function updateOrderSummary() {
        let totalItems = 0;
        let totalQuantity = 0;
        let totalAmount = 0;
        
        document.querySelectorAll('.order-item').forEach(item => {
            const quantity = parseInt(item.querySelector('.quantity-input').value) || 0;
            const price = parseFloat(item.querySelector('.price-input').value) || 0;
            
            totalItems++;
            totalQuantity += quantity;
            totalAmount += quantity * price;
        });
        
        document.getElementById('totalItems').textContent = totalItems;
        document.getElementById('totalQuantity').textContent = totalQuantity;
        document.getElementById('totalAmount').textContent = totalAmount.toFixed(2);
    }
    
    // Initialize first item
    document.querySelectorAll('.order-item').forEach(addEventListenersToItem);
    updateOrderSummary();
    <?php endif; ?>
    </script>
    
    <?php include 'footer.php'; ?>
</body>
</html>