<?php
/**
 * Cancel Order
 * DEKOMARTNEPAL E-commerce Website
 * 
 * Handles order cancellation
 */

require_once 'includes/db_connection.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['error_message'] = 'Please login to cancel orders.';
    redirect('login.php');
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

if ($order_id <= 0) {
    $_SESSION['error_message'] = 'Invalid order ID.';
    redirect('my_orders.php');
}

// Get order details
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['error_message'] = 'Order not found.';
    redirect('my_orders.php');
}

// Check if order can be cancelled
if (($order['payment_status'] ?? 'pending') !== 'pending' && ($order['payment_status'] ?? 'pending') !== 'Pending') {
    $_SESSION['error_message'] = 'This order cannot be cancelled. Only pending orders can be cancelled.';
    redirect('order_details.php?id=' . $order_id);
}

// Cancel the order
try {
    $conn->beginTransaction();
    
    // Update order status
    $stmt = $conn->prepare("UPDATE orders SET payment_status = 'cancelled', updated_at = NOW() WHERE id = ?");
    $stmt->execute([$order_id]);
    
    // Restore stock to products
    $stmt = $conn->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $order_items = $stmt->fetchAll();
    
    foreach ($order_items as $item) {
        $stmt = $conn->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
        $stmt->execute([$item['quantity'], $item['product_id']]);
    }
    
    $conn->commit();
    $_SESSION['success_message'] = 'Order cancelled successfully. Items have been restored to stock.';
    
} catch (Exception $e) {
    $conn->rollBack();
    $_SESSION['error_message'] = 'Failed to cancel order. Please try again.';
}

redirect('order_details.php?id=' . $order_id);
?>
