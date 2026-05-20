<?php
/**
 * My Orders - Simple Version
 * DEKOMARTNEPAL E-commerce Website
 * 
 * Simple orders page without complex database queries
 */

require_once __DIR__ . '/includes/db_connection.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

include 'includes/header.php';

$user_id = $_SESSION['user_id'];
$msg = "";

// Handle Cancel Order (simplified)
if (isset($_POST['cancel_order'])) {
    $order_id = (int)$_POST['order_id'];
    // Simple update without complex verification
    try {
        $stmt = $conn->prepare("UPDATE orders SET payment_status = 'cancelled' WHERE id = ? AND user_id = ?");
        $stmt->execute([$order_id, $user_id]);
        $msg = "<div class='success-msg'>Order #$order_id has been cancelled.</div>";
    } catch (Exception $e) {
        $msg = "<div class='error-msg'>Could not cancel order.</div>";
    }
}

// Fetch user orders (simplified)
$orders = [];
try {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll();
} catch (Exception $e) {
    $orders = []; // Empty array if query fails
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - DEKOMARTNEPAL</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .orders-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .orders-header {
            background: linear-gradient(135deg, #2E7D32, #43A047);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .orders-header h1 {
            margin: 0 0 0.5rem 0;
        }
        
        .message {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 5px;
        }
        
        .success-msg {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error-msg {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .order-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
            transition: transform 0.3s;
        }
        
        .order-card:hover {
            transform: translateY(-2px);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .order-id {
            font-size: 1.2rem;
            font-weight: bold;
            color: #2E7D32;
        }
        
        .order-status {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-paid { background: #d4edda; color: #155724; }
        .status-shipped { background: #cce5ff; color: #004085; }
        .status-delivered { background: #d1ecf1; color: #0c5460; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
        .order-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .detail-item {
            padding: 0.5rem;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .detail-label {
            font-size: 0.8rem;
            color: #666;
            margin-bottom: 0.25rem;
        }
        
        .detail-value {
            font-weight: bold;
            color: #333;
        }
        
        .order-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .action-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .cancel-btn {
            background: #dc3545;
            color: white;
        }
        
        .cancel-btn:hover {
            background: #c82333;
        }
        
        .view-btn {
            background: #007bff;
            color: white;
        }
        
        .view-btn:hover {
            background: #0056b3;
        }
        
        .no-orders {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .no-orders h3 {
            color: #666;
            margin-bottom: 1rem;
        }
        
        .shop-btn {
            background: #2E7D32;
            color: white;
            padding: 1rem 2rem;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-top: 1rem;
        }
        
        .shop-btn:hover {
            background: #1B5E20;
        }
        
        @media (max-width: 768px) {
            .order-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .order-details {
                grid-template-columns: 1fr;
            }
            
            .order-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="orders-container">
        <!-- Orders Header -->
        <div class="orders-header">
            <h1>📦 My Orders</h1>
            <p>Track and manage your orders</p>
        </div>
        
        <!-- Message Display -->
        <?php if (!empty($msg)): ?>
            <?php echo $msg; ?>
        <?php endif; ?>
        
        <!-- Orders List -->
        <?php if (!empty($orders)): ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-id">Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></div>
                        <div class="order-status status-<?php echo $order['payment_status'] ?? 'pending'; ?>">
                            <?php echo ucfirst($order['payment_status'] ?? 'Pending'); ?>
                        </div>
                    </div>
                    
                    <div class="order-details">
                        <div class="detail-item">
                            <div class="detail-label">Date</div>
                            <div class="detail-value"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Total Amount</div>
                            <div class="detail-value">Rs. <?php echo number_format($order['total_amount'], 2); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Payment Method</div>
                            <div class="detail-value"><?php echo ucfirst($order['payment_method'] ?? 'COD'); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Items</div>
                            <div class="detail-value"><?php echo $order['items'] ?? 'Multiple'; ?></div>
                        </div>
                    </div>
                    
                    <div class="order-actions">
                        <a href="order_details.php?id=<?php echo $order['id']; ?>" class="action-btn view-btn">📋 View Details</a>
                        
                        <?php if (($order['payment_status'] ?? 'pending') === 'pending' || ($order['payment_status'] ?? 'pending') === 'Pending'): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <button type="submit" name="cancel_order" class="action-btn cancel-btn" 
                                        onclick="return confirm('Are you sure you want to cancel this order?');">
                                    ❌ Cancel Order
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-orders">
                <h3>📦 No Orders Yet</h3>
                <p>You haven\'t placed any orders yet. Start shopping to see your orders here!</p>
                <a href="index.php" class="shop-btn">🛍️ Start Shopping</a>
            </div>
        <?php endif; ?>
        
        <!-- Back to Dashboard -->
        <div style="text-align: center; margin-top: 2rem;">
            <a href="customer_dashboard.php" style="color: #2E7D32; text-decoration: none; font-weight: bold;">
                ← Back to Dashboard
            </a>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
