<?php
require_once 'includes/db_connection.php';
require_once 'includes/functions.php';

$category_id = (int)$_GET['id'];
$stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$category_id]);
$category = $stmt->fetch();

if (!$category) {
    redirect('index.php');
}

// Handle Add to Cart
$cart_status = handleCart();

include 'includes/header.php';

if ($cart_status) {
    $class = $cart_status['is_error'] ? 'error-msg' : 'success-msg';
    echo "<div class='$class'>{$cart_status['message']}</div>";
}
?>

<div class="section-title">
    <h2>Category: <?php echo $category['name']; ?></h2>
    <p style="color: #7f8c8d; font-size: 1.1rem; margin-top: -10px;">Explore our selection of <?php echo strtolower($category['name']); ?> products.</p>
</div>

<div class="product-grid">
    <?php
    $stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.category_id = ? ORDER BY p.id DESC");
    $stmt->execute([$category_id]);
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
                    
                    <form action="category.php?id=<?php echo $category_id; ?>" method="POST" style="margin-top: auto;">
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
        echo "<div style='text-align: center; padding: 4rem; background: #fff; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); width: 100%; grid-column: 1 / -1;'>
                <i class='fas fa-box-open fa-3x' style='color: #bdc3c7; margin-bottom: 1.5rem;'></i>
                <h3 style='color: #2c3e50;'>No products found!</h3>
                <p style='color: #7f8c8d;'>We couldn't find any products in this category at the moment.</p>
                <a href='index.php' class='btn-primary' style='display: inline-block; width: auto; padding: 0.8rem 2rem; margin-top: 1.5rem;'>Back to Home</a>
              </div>";
    }
    ?>
</div>

<?php include 'includes/footer.php'; ?>
