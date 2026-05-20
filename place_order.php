<?php
require_once 'includes/db_connection.php';
require_once 'includes/functions.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $shipping_name = sanitize($_POST['shipping_name']);
    $shipping_phone = sanitize($_POST['shipping_phone']);
    $shipping_email = sanitize($_POST['shipping_email']);
    $shipping_address = sanitize($_POST['shipping_address']);
    $payment_method = $_POST['payment_method'];
    
    // Check if cart is empty
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        redirect('cart.php');
    }
    
    // Calculate total amount
    $total_amount = 0;
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $stmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $total_amount += $stmt->fetchColumn() * $quantity;
    }
    
    // Insert order into database
    try {
        $conn->beginTransaction();
        
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, payment_method, payment_status, shipping_name, shipping_phone, shipping_email, shipping_address) VALUES (?, ?, ?, 'Pending', ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $total_amount, $payment_method, $shipping_name, $shipping_phone, $shipping_email, $shipping_address]);
        $order_id = $conn->lastInsertId();
        
        // Insert order items and update stock
        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            // Get current price
            $stmt = $conn->prepare("SELECT price, stock FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();
            
            // Insert order item
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$order_id, $product_id, $quantity, $product['price']]);
            
            // Update stock
            $new_stock = $product['stock'] - $quantity;
            $stmt = $conn->prepare("UPDATE products SET stock = ? WHERE id = ?");
            $stmt->execute([$new_stock, $product_id]);
        }
        
        $conn->commit();
        
        // If payment method is Khalti, redirect to working version
        if ($payment_method === 'Khalti') {
            $_SESSION['pending_order_id'] = $order_id;
            $_SESSION['pending_order_amount'] = $total_amount;
            header('Location: khalti_working_now.php');
            exit();
        } else {
            // COD Order success
            unset($_SESSION['cart']);
            $_SESSION['order_success'] = "Order placed successfully! Order ID: #$order_id. You can view your order status in <a href='orders.php' style='color: #2ecc71; font-weight: bold; text-decoration: underline;'>My Orders</a>.";
            header('Location: index.php');
            exit();
        }
        
    } catch (Exception $e) {
        $conn->rollBack();
        die("Error: " . $e->getMessage());
    }
} else {
    redirect('index.php');
}
?>
