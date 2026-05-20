<?php
require_once 'includes/db_connection.php';
require_once 'includes/functions.php';

// Handle Add to Cart
$cart_status = handleCart();

include 'includes/header.php';

// Show Success or Error Messages
if (isset($_SESSION['order_success'])) {
    echo "<div class='success-msg'>" . $_SESSION['order_success'] . "</div>";
    unset($_SESSION['order_success']);
}

if (isset($_SESSION['payment_error'])) {
    echo "<div class='error-msg'>" . $_SESSION['payment_error'] . "</div>";
    unset($_SESSION['payment_error']);
}

if ($cart_status) {
    $class = $cart_status['is_error'] ? 'error-msg' : 'success-msg';
    echo "<div class='$class'>{$cart_status['message']}</div>";
}
?>

<div class="hero-section">
    <div class="hero-content">
        <h1>Welcome to DekoMartNepal</h1>
        <p>Get fresh and quality products delivered to your doorstep.</p>
    </div>
</div>

<div class="section-title" style="display: flex; justify-content: space-between; align-items: flex-end;">
    <h2>Featured Products</h2>
    <div class="category-tabs" style="margin-bottom: 10px;">
        <?php
        $stmt = $conn->query("SELECT * FROM categories LIMIT 5");
        $cats = $stmt->fetchAll();
        foreach ($cats as $cat) {
            echo "<a href='category.php?id={$cat['id']}' style='margin-left: 1.5rem; color: #2ecc71; font-weight: 600; font-size: 0.9rem;'>{$cat['name']}</a>";
        }
        ?>
    </div>
</div>

<div class="product-grid">
    <?php
    $stmt = $conn->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
    $products = $stmt->fetchAll();

    if (count($products) > 0) {
        foreach ($products as $p) {
            ?>
            <div class="product-card">
                <div style="overflow: hidden; height: 240px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; position: relative;">
                    <?php 
                    $image_path = "uploads/" . $p['image'];
                    if (!empty($p['image']) && file_exists($image_path) && is_file($image_path)): 
                    ?>
                        <img src="<?php echo $image_path; ?>" alt="" class="product-image">
                    <?php else: ?>
                        <div class="no-image-placeholder">
                            <i class="fas fa-shopping-basket"></i>
                            <span>DekoMart</span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="product-info">
                    <span class="product-category"><?php echo $p['category_name']; ?></span>
                    <h3 class="product-name"><?php echo $p['name']; ?></h3>
                    <div class="product-price">NPR <?php echo number_format($p['price'], 2); ?></div>
                    
                    <div class="product-stock <?php echo $p['stock'] > 0 ? 'stock-in' : 'stock-out'; ?>">
                        <?php if ($p['stock'] > 0): ?>
                            <i class="fas fa-check-circle"></i> In Stock (<?php echo $p['stock']; ?>)
                        <?php else: ?>
                            <i class="fas fa-times-circle"></i> Out of Stock
                        <?php endif; ?>
                    </div>
                    
                    <form action="index.php" method="POST" style="margin-top: auto;">
                        <input type="hidden" name="csrf_token" value="<?php echo getCsrfToken(); ?>">
                        <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                        <button type="submit" name="add_to_cart" class="add-to-cart-btn" <?php echo $p['stock'] <= 0 ? 'disabled' : ''; ?>>
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                    </form>
                </div>
            </div>
            <?php
        }
    } else {
        echo "<div style='text-align: center; padding: 2rem; background: #fff; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); width: 100%; grid-column: 1 / -1;'>
                <i class='fas fa-exclamation-triangle fa-3x' style='color: #f39c12; margin-bottom: 1rem;'></i>
                <h3>No products found!</h3>
                <p style='margin-bottom: 1.5rem;'>The database seems to be empty. Please seed the database to add sample products.</p>
                <a href='seed.php?reset=1' class='btn-primary' style='display: inline-block; width: auto; padding: 0.8rem 2rem;'>Setup/Seed Database</a>
              </div>";
    }
    ?>
</div>

<?php include 'includes/footer.php'; ?>
