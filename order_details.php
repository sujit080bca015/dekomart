<?php
/**
 * Order Details
 * DEKOMARTNEPAL E-commerce Website
 * 
 * Detailed view of a single order
 */

require_once 'includes/db_connection.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['error_message'] = 'Please login to view order details.';
    redirect('login.php');
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

if ($order_id <= 0) {
    $_SESSION['error_message'] = 'Invalid order ID.';
    redirect('my_orders.php');
}

// Get order details
$stmt = $conn->prepare("SELECT o.*, u.full_name, u.email, u.phone, u.address FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = ? AND o.user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['error_message'] = 'Order not found.';
    redirect('my_orders.php');
}

// Get order items
$stmt = $conn->prepare("SELECT oi.*, p.name as product_name, p.image FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll();

// Get order timeline
$timeline = [
    ['status' => 'pending', 'label' => 'Order Placed', 'completed' => true, 'date' => $order['created_at']],
    ['status' => 'Paid', 'label' => 'Payment Confirmed', 'completed' => in_array($order['payment_status'], ['Paid', 'shipped', 'delivered']), 'date' => $order['updated_at']],
    ['status' => 'shipped', 'label' => 'Order Shipped', 'completed' => in_array($order['payment_status'], ['shipped', 'delivered']), 'date' => null],
    ['status' => 'delivered', 'label' => 'Order Delivered', 'completed' => $order['payment_status'] === 'delivered', 'date' => null]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - DEKOMARTNEPAL</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .order-details-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .order-header {
            background: linear-gradient(135deg, #2E7D32, #43A047);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .order-title h1 {
            margin: 0 0 0.5rem 0;
        }
        
        .order-status {
            padding: 0.75rem 1.5rem;
            background: rgba(255,255,255,0.2);
            border-radius: 25px;
            font-weight: bold;
            font-size: 1.1rem;
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }
        
        .section-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .section-title {
            font-size: 1.3rem;
            color: #333;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .order-item {
            display: flex;
            gap: 1rem;
            padding: 1rem;
            border: 1px solid #f0f0f0;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        
        .item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: #ccc;
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-name {
            font-weight: bold;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .item-price {
            color: #666;
            font-size: 0.9rem;
        }
        
        .item-quantity {
            color: #666;
            font-size: 0.9rem;
        }
        
        .item-subtotal {
            font-weight: bold;
            color: #2E7D32;
            font-size: 1.1rem;
        }
        
        .address-info, .payment-info {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: bold;
            color: #333;
        }
        
        .info-value {
            color: #666;
        }
        
        .timeline {
            position: relative;
            padding-left: 2rem;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 0.5rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e0e0e0;
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 2rem;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -1.5rem;
            top: 0.25rem;
            width: 1rem;
            height: 1rem;
            border-radius: 50%;
            background: #e0e0e0;
            border: 2px solid white;
        }
        
        .timeline-item.completed::before {
            background: #2E7D32;
        }
        
        .timeline-item.active::before {
            background: #FF9800;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }
        
        .timeline-status {
            font-weight: bold;
            color: #333;
            margin-bottom: 0.25rem;
        }
        
        .timeline-date {
            color: #666;
            font-size: 0.9rem;
        }
        
        .order-summary {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            margin-top: 1rem;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        
        .summary-row.total {
            font-weight: bold;
            font-size: 1.2rem;
            color: #2E7D32;
            border-top: 2px solid #e0e0e0;
            padding-top: 0.5rem;
            margin-top: 0.5rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .action-btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .primary-btn {
            background: #2E7D32;
            color: white;
        }
        
        .primary-btn:hover {
            background: #1B5E20;
        }
        
        .secondary-btn {
            background: #6c757d;
            color: white;
        }
        
        .secondary-btn:hover {
            background: #545b62;
        }
        
        .danger-btn {
            background: #dc3545;
            color: white;
        }
        
        .danger-btn:hover {
            background: #c82333;
        }
        
        @media (max-width: 768px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .order-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <?php require_once 'includes/header.php'; ?>
    
    <div class="order-details-container">
        <!-- Order Header -->
        <div class="order-header">
            <div class="order-title">
                <h1>Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></h1>
                <p>Placed on <?php echo date('F d, Y at h:i A', strtotime($order['created_at'])); ?></p>
            </div>
            <div class="order-status">
                <?php echo ucfirst($order['payment_status'] ?? 'Pending'); ?>
            </div>
        </div>
        
        <div class="content-grid">
            <!-- Left Column -->
            <div>
                <!-- Order Items -->
                <div class="section-card">
                    <h2 class="section-title">📦 Order Items</h2>
                    
                    <?php foreach ($order_items as $item): ?>
                        <div class="order-item">
                            <div class="item-image">
                                <?php if (!empty($item['image']) && file_exists($item['image'])): ?>
                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    📦
                                <?php endif; ?>
                            </div>
                            <div class="item-details">
                                <div class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                <div class="item-price">Rs. <?php echo number_format($item['price'], 2); ?> each</div>
                                <div class="item-quantity">Quantity: <?php echo $item['quantity']; ?></div>
                            </div>
                            <div class="item-subtotal">
                                Rs. <?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <!-- Order Summary -->
                    <div class="order-summary">
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span>Rs. <?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Delivery Fee:</span>
                            <span>Free</span>
                        </div>
                        <div class="summary-row total">
                            <span>Total:</span>
                            <span>Rs. <?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Order Timeline -->
                <div class="section-card">
                    <h2 class="section-title">📅 Order Timeline</h2>
                    
                    <div class="timeline">
                        <?php foreach ($timeline as $index => $step): ?>
                            <div class="timeline-item <?php echo $step['completed'] ? 'completed' : ''; ?> <?php echo ($order['payment_status'] ?? 'pending') === $step['status'] ? 'active' : ''; ?>">
                                <div class="timeline-status"><?php echo $step['label']; ?></div>
                                <div class="timeline-date">
                                    <?php 
                                    if ($step['date']) {
                                        echo date('M d, Y h:i A', strtotime($step['date']));
                                    } elseif ($step['completed']) {
                                        echo 'Completed';
                                    } else {
                                        echo 'Pending';
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Right Column -->
            <div>
                <!-- Shipping Address -->
                <div class="section-card">
                    <h2 class="section-title">📍 Shipping Address</h2>
                    
                    <div class="address-info">
                        <div class="info-row">
                            <span class="info-label">Name:</span>
                            <span class="info-value"><?php echo htmlspecialchars($order['full_name']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Phone:</span>
                            <span class="info-value"><?php echo htmlspecialchars($order['phone'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Address:</span>
                            <span class="info-value"><?php echo htmlspecialchars($order['address'] ?? 'N/A'); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Information -->
                <div class="section-card">
                    <h2 class="section-title">💳 Payment Information</h2>
                    
                    <div class="payment-info">
                        <div class="info-row">
                            <span class="info-label">Method:</span>
                            <span class="info-value"><?php echo ucfirst($order['payment_method'] ?? 'COD'); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Status:</span>
                            <span class="info-value"><?php echo ucfirst($order['payment_status'] ?? 'pending'); ?></span>
                        </div>
                        <?php if (!empty($order['transaction_id'])): ?>
                            <div class="info-row">
                                <span class="info-label">Transaction ID:</span>
                                <span class="info-value"><?php echo htmlspecialchars($order['transaction_id']); ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="info-row">
                            <span class="info-label">Total Amount:</span>
                            <span class="info-value" style="font-weight: bold; color: #2E7D32;">Rs. <?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="action-buttons">
                    <a href="my_orders.php" class="action-btn secondary-btn">
                        ← Back to Orders
                    </a>
                    
                    <?php if (($order['payment_status'] ?? 'pending') === 'pending' || ($order['payment_status'] ?? 'pending') === 'Pending'): ?>
                        <a href="cancel_order.php?id=<?php echo $order['id']; ?>" 
                           class="action-btn danger-btn"
                           onclick="return confirm('Are you sure you want to cancel this order?');">
                            Cancel Order
                        </a>
                    <?php endif; ?>
                    
                    <?php if (($order['payment_status'] ?? 'pending') === 'delivered'): ?>
                        <a href="reorder.php?id=<?php echo $order['id']; ?>" class="action-btn primary-btn">
                            🛒 Reorder
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php require_once 'includes/footer.php'; ?>
</body>
</html>
