<?php
require_once 'includes/db_connection.php';
require_once 'includes/functions.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Check if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    redirect('cart.php');
}

include 'includes/header.php';

$total_amount = 0;
foreach ($_SESSION['cart'] as $product_id => $quantity) {
    $stmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $total_amount += $stmt->fetchColumn() * $quantity;
}

// Fetch user details for auto-filling
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>

<h2 style="margin-bottom: 2rem;">Checkout</h2>

<div style="display: flex; gap: 2rem; flex-wrap: wrap;">
    <!-- Shipping Form -->
    <div style="flex: 2; background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
        <h3>Shipping Information</h3>
        <form action="place_order.php" method="POST" id="checkout-form">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem;">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="shipping_name" value="<?php echo isset($user['full_name']) ? $user['full_name'] : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="shipping_phone" value="<?php echo isset($user['phone']) ? $user['phone'] : ''; ?>" required maxlength="10">
                </div>
            </div>
            
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="shipping_email" value="<?php echo isset($user['email']) ? $user['email'] : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label>Shipping Address</label>
                <textarea name="shipping_address" required rows="3" style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 4px;"><?php echo isset($user['address']) ? $user['address'] : ''; ?></textarea>
            </div>
            
            <h3 style="margin-top: 2rem;">Payment Method</h3>
            <div style="margin-top: 1rem;">
                <label style="display: block; margin-bottom: 1rem; cursor: pointer; padding: 1rem; border: 2px solid #ddd; border-radius: 8px; transition: all 0.3s;">
                    <input type="radio" name="payment_method" value="COD" checked style="margin-right: 0.5rem;"> 
                    <span style="font-weight: bold;">💵 Cash on Delivery (COD)</span>
                    <span style="display: block; color: #666; font-size: 0.9rem; margin-top: 0.25rem;">Pay when you receive your order</span>
                </label>
                
                <label style="display: block; cursor: pointer; padding: 1rem; border: 2px solid #5625B1; border-radius: 8px; background: #f8f6ff; transition: all 0.3s;">
                    <input type="radio" name="payment_method" value="Khalti" style="margin-right: 0.5rem;"> 
                    <span style="font-weight: bold; color: #5625B1;">💜 Khalti Online Payment</span>
                    <span style="display: block; color: #5625B1; font-size: 0.9rem; margin-top: 0.25rem;">Pay instantly with Khalti - Fast, Secure & Convenient</span>
                </label>
            </div>
            
            <button type="submit" name="place_order" class="btn-primary" style="margin-top: 2rem;">Place Order (NPR <?php echo number_format($total_amount, 2); ?>)</button>
        </form>
    </div>

    <!-- Order Summary -->
    <div style="flex: 1; background: #34495e; color: white; padding: 2rem; border-radius: 8px; height: fit-content;">
        <h3>Order Summary</h3>
        <ul style="margin-top: 1rem; list-style: none;">
            <?php
            foreach ($_SESSION['cart'] as $product_id => $quantity) {
                $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
                $stmt->execute([$product_id]);
                $product = $stmt->fetch();
                if ($product) {
                    echo "<li style='margin-bottom: 0.5rem; display: flex; justify-content: space-between;'>
                            <span>{$product['name']} x $quantity</span>
                            <span>NPR " . number_format($product['price'] * $quantity, 2) . "</span>
                          </li>";
                }
            }
            ?>
        </ul>
        <hr style="margin: 1rem 0; border: 0.5px solid #5d6d7e;">
        <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: bold;">
            <span>Total</span>
            <span>NPR <?php echo number_format($total_amount, 2); ?></span>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
