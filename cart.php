<?php
require_once 'includes/db_connection.php';
require_once 'includes/functions.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

include 'includes/header.php';

// Handle Remove from Cart
if (isset($_GET['remove'])) {
    $product_id = (int)$_GET['remove'];
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
}

// Handle Update Quantity
if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $product_id => $qty) {
        $qty = (int)$qty;
        if ($qty <= 0) {
            unset($_SESSION['cart'][$product_id]);
        } else {
            // Check stock before updating
            $stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $stock = $stmt->fetchColumn();
            if ($qty <= $stock) {
                $_SESSION['cart'][$product_id] = $qty;
            } else {
                $_SESSION['cart'][$product_id] = $stock;
                echo "<div class='error-msg'>Sorry, only $stock items available in stock.</div>";
            }
        }
    }
}

$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$total_amount = 0;
?>

<h2 style="margin-bottom: 2rem;">Your Shopping Cart</h2>

<?php if (empty($cart_items)): ?>
    <div style="text-align: center; padding: 3rem; background: white; border-radius: 8px;">
        <i class="fas fa-shopping-cart fa-4x" style="color: #eee; margin-bottom: 1rem;"></i>
        <p>Your cart is empty.</p>
        <a href="index.php" class="btn-primary" style="display: inline-block; width: auto; margin-top: 1rem;">Start Shopping</a>
    </div>
<?php else: ?>
    <form action="cart.php" method="POST">
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($cart_items as $product_id => $quantity) {
                    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
                    $stmt->execute([$product_id]);
                    $product = $stmt->fetch();
                    
                    if ($product) {
                        $subtotal = $product['price'] * $quantity;
                        $total_amount += $subtotal;
                        ?>
                        <tr class="cart-row">
                            <td>
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <img src="uploads/<?php echo $product['image']; ?>" alt="" width="50">
                                    <span><?php echo $product['name']; ?></span>
                                </div>
                            </td>
                            <td class="product-price" data-price="<?php echo $product['price']; ?>">NPR <?php echo number_format($product['price'], 2); ?></td>
                            <td>
                                <input type="number" name="quantity[<?php echo $product_id; ?>]" class="qty-input" value="<?php echo $quantity; ?>" min="1" max="<?php echo $product['stock']; ?>" style="width: 60px; padding: 0.3rem;">
                            </td>
                            <td class="product-subtotal">NPR <?php echo number_format($subtotal, 2); ?></td>
                            <td>
                                <a href="cart.php?remove=<?php echo $product_id; ?>" style="color: #e74c3c;"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php
                    }
                }
                ?>
            </tbody>
        </table>

        <div class="cart-total" id="cart-total-container">
            Total Amount: NPR <span id="cart-total-amount"><?php echo number_format($total_amount, 2); ?></span>
        </div>

        <div style="display: flex; justify-content: space-between;">
            <button type="submit" name="update_cart" class="btn-primary" style="width: auto; background-color: #34495e;">Update Cart</button>
            <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
        </div>
    </form>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
