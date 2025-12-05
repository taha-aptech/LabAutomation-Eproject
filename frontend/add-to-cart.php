<?php
session_start();
include 'db.php';

// Only allow logged-in users
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 4) {
    // Store redirect URL
    $_SESSION['redirect_url'] = $_SERVER['HTTP_REFERER'] ?? 'products.php';
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_code = sanitize_input($_POST['product_code'], $conn);
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    
    // Validate quantity
    if ($quantity < 1) $quantity = 1;
    if ($quantity > 10) $quantity = 10;
    
    // Check if product exists and is approved
    $product_query = "SELECT p.product_name, p.product_img, ft.random_cost as price
                      FROM products p
                      JOIN financial_tracking ft ON p.product_code = ft.product_code
                      WHERE p.product_code = ? AND ft.approval_status = 'Approved'";
    
    $stmt = mysqli_prepare($conn, $product_query);
    mysqli_stmt_bind_param($stmt, "s", $product_code);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($product = mysqli_fetch_assoc($result)) {
        $user_id = $_SESSION['user_id'];
        
        // Check if item already exists in cart
        $check_query = "SELECT cart_id, quantity FROM cart WHERE user_id = ? AND product_code = ?";
        $check_stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, "is", $user_id, $product_code);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if ($existing_item = mysqli_fetch_assoc($check_result)) {
            // Update quantity
            $new_quantity = $existing_item['quantity'] + $quantity;
            if ($new_quantity > 10) $new_quantity = 10;
            
            $update_query = "UPDATE cart SET quantity = ? WHERE cart_id = ?";
            $update_stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($update_stmt, "ii", $new_quantity, $existing_item['cart_id']);
            mysqli_stmt_execute($update_stmt);
            
            $action = 'updated';
        } else {
            // Insert new item
            $insert_query = "INSERT INTO cart (user_id, product_code, quantity) VALUES (?, ?, ?)";
            $insert_stmt = mysqli_prepare($conn, $insert_query);
            mysqli_stmt_bind_param($insert_stmt, "isi", $user_id, $product_code, $quantity);
            mysqli_stmt_execute($insert_stmt);
            
            $action = 'added';
        }
        
        // Update session cart
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        if (isset($_SESSION['cart'][$product_code])) {
            $_SESSION['cart'][$product_code]['quantity'] += $quantity;
            if ($_SESSION['cart'][$product_code]['quantity'] > 10) {
                $_SESSION['cart'][$product_code]['quantity'] = 10;
            }
        } else {
            $_SESSION['cart'][$product_code] = [
                'name' => $product['product_name'],
                'img' => $product['product_img'],
                'price' => $product['price'],
                'quantity' => $quantity
            ];
        }
        
        $_SESSION['cart_count'] = count($_SESSION['cart']);
        
        // Store success message in session for JavaScript
        $_SESSION['add_to_cart_success'] = "{$product['product_name']} added to cart!";
        
        // Redirect back to products page
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'products.php'));
        exit();
        
    } else {
        $_SESSION['error_message'] = "Product not found or not approved!";
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'products.php'));
        exit();
    }
}

// If not POST request, redirect to products
header('Location: products.php');
exit();
?>