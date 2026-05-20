<?php
require_once __DIR__ . '/includes/db_connection.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

include 'includes/header.php';

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch order details
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    echo "<div class='error-msg'>Order not found or you don't have permission to view it.</div>";
    include 'includes/footer.php';
    exit();
}

// Status definitions
$status_info = [
    'pending' => ['icon' => 'fa-clock', 'color' => '#f39c12', 'desc' => 'We have received your order and it is awaiting processing.'],
    'processing' => ['icon' => 'fa-cog', 'color' => '#3498db', 'desc' => 'Your order is being packed and prepared for shipment.'],
    'shipped' => ['icon' => 'fa-truck', 'color' => '#9b59b6', 'desc' => 'Your order has left our warehouse and is on its way to you.'],
    'delivered' => ['icon' => 'fa-check-circle', 'color' => '#2ecc71', 'desc' => 'Your order has been delivered successfully. Enjoy your purchase!'],
    'cancelled' => ['icon' => 'fa-times-circle', 'color' => '#e74c3c', 'desc' => 'This order has been cancelled.']
];

$current_status = $order['status'];
$info = $status_info[$current_status];
?>

<div class="track-order-container" style="max-width: 900px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2 style="color: #2c3e50;"><i class="fas fa-map-marker-alt"></i> Track Order #<?php echo $order['id']; ?></h2>
        <a href="my_orders.php" style="color: #3498db;"><i class="fas fa-arrow-left"></i> Back to My Orders</a>
    </div>

    <!-- Tracking Card -->
    <div style="background: white; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 2rem;">
        <div style="background: <?php echo $info['color']; ?>; color: white; padding: 2rem; text-align: center;">
            <i class="fas <?php echo $info['icon']; ?> fa-4x" style="margin-bottom: 1rem;"></i>
            <h3 style="font-size: 1.8rem; text-transform: uppercase; letter-spacing: 1px;"><?php echo $current_status; ?></h3>
            <p style="opacity: 0.9; margin-top: 0.5rem;"><?php echo $info['desc']; ?></p>
        </div>

        <div style="padding: 2rem;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; text-align: center; border-bottom: 1px solid #eee; padding-bottom: 2rem;">
                <div>
                    <p style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 0.5rem;">Estimated Delivery</p>
                    <h4 style="font-size: 1.2rem; color: #2c3e50;">
                        <?php echo $order['estimated_delivery'] ? date('M d, Y', strtotime($order['estimated_delivery'])) : 'Processing...'; ?>
                    </h4>
                </div>
                <div>
                    <p style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 0.5rem;">Tracking Number</p>
                    <h4 style="font-size: 1.2rem; color: #2c3e50;">
                        <?php echo $order['tracking_number'] ?: 'Not assigned yet'; ?>
                    </h4>
                </div>
                <div>
                    <p style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 0.5rem;">Shipping Carrier</p>
                    <h4 style="font-size: 1.2rem; color: #2c3e50;">DekoMart Express</h4>
                </div>
            </div>

            <!-- Progress Timeline -->
            <div style="margin-top: 3rem; position: relative; padding: 0 2rem;">
                <div style="display: flex; justify-content: space-between; position: relative;">
                    <div style="position: absolute; top: 15px; left: 5%; width: 90%; height: 4px; background: #eee; z-index: 1;"></div>
                    <div style="position: absolute; top: 15px; left: 5%; width: <?php 
                        $prog = ['pending' => 0, 'processing' => 30, 'shipped' => 65, 'delivered' => 90];
                        echo ($current_status == 'cancelled') ? 0 : $prog[$current_status];
                    ?>%; height: 4px; background: <?php echo $info['color']; ?>; z-index: 2; transition: width 1s ease-in-out;"></div>
                    
                    <?php 
                    $steps = [
                        ['label' => 'Ordered', 'status' => 'pending', 'icon' => 'fa-shopping-cart'],
                        ['label' => 'Processing', 'status' => 'processing', 'icon' => 'fa-box-open'],
                        ['label' => 'Shipped', 'status' => 'shipped', 'icon' => 'fa-truck-loading'],
                        ['label' => 'Delivered', 'status' => 'delivered', 'icon' => 'fa-home']
                    ];
                    $current_idx = array_search($current_status, array_column($steps, 'status'));
                    foreach ($steps as $idx => $step):
                        $is_done = ($current_status != 'cancelled' && $idx <= $current_idx);
                    ?>
                        <div style="z-index: 3; text-align: center; width: 80px;">
                            <div style="width: 35px; height: 35px; border-radius: 50%; background: <?php echo $is_done ? $info['color'] : '#fff'; ?>; border: 3px solid <?php echo $is_done ? $info['color'] : '#eee'; ?>; margin: 0 auto; display: flex; align-items: center; justify-content: center; color: <?php echo $is_done ? 'white' : '#bdc3c7'; ?>; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                                <i class="fas <?php echo $step['icon']; ?>" style="font-size: 0.9rem;"></i>
                            </div>
                            <p style="font-size: 0.8rem; margin-top: 10px; font-weight: <?php echo $is_done ? 'bold' : 'normal'; ?>; color: <?php echo $is_done ? '#2c3e50' : '#bdc3c7'; ?>;"><?php echo $step['label']; ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Details Card -->
    <div style="background: white; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); padding: 2rem;">
        <h3 style="margin-bottom: 1.5rem; color: #2c3e50; border-bottom: 2px solid #f4f7f6; padding-bottom: 0.5rem;">Product Details</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align: left; color: #7f8c8d; font-size: 0.9rem;">
                    <th style="padding: 1rem 0;">Product</th>
                    <th style="padding: 1rem 0; text-align: center;">Qty</th>
                    <th style="padding: 1rem 0; text-align: right;">Price</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt_items = $conn->prepare("SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
                $stmt_items->execute([$order_id]);
                $items = $stmt_items->fetchAll();
                foreach ($items as $item):
                ?>
                    <tr style="border-bottom: 1px solid #f4f7f6;">
                        <td style="padding: 1rem 0;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <img src="uploads/<?php echo $item['image']; ?>" alt="" width="50" height="50" style="object-fit: cover; border-radius: 8px;">
                                <span style="font-weight: 600; color: #34495e;"><?php echo $item['name']; ?></span>
                            </div>
                        </td>
                        <td style="padding: 1rem 0; text-align: center; color: #7f8c8d;">x<?php echo $item['quantity']; ?></td>
                        <td style="padding: 1rem 0; text-align: right; font-weight: bold; color: #2c3e50;">NPR <?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" style="padding: 1.5rem 0; font-size: 1.1rem; color: #7f8c8d;">Order Total</td>
                    <td style="padding: 1.5rem 0; text-align: right; font-size: 1.3rem; font-weight: 800; color: #2ecc71;">NPR <?php echo number_format($order['total_amount'], 2); ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Shipping Address -->
    <div style="margin-top: 2rem; background: #f8f9fa; border-radius: 12px; padding: 1.5rem; border-left: 5px solid #3498db;">
        <h4 style="margin-bottom: 0.5rem; color: #2c3e50;"><i class="fas fa-shipping-fast"></i> Shipping Address</h4>
        <p style="color: #34495e; line-height: 1.6;">
            <strong><?php echo $order['shipping_name']; ?></strong><br>
            <?php echo $order['shipping_address']; ?><br>
            Phone: <?php echo $order['shipping_phone']; ?>
        </p>
    </div>
</div>

<style>
@media (max-width: 600px) {
    .track-order-container { padding: 1rem; }
    .track-order-container h2 { font-size: 1.2rem; }
}
</style>

<?php include 'includes/footer.php'; ?>
