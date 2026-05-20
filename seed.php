<?php
require_once 'includes/db_connection.php';
require_once 'includes/functions.php';

echo "<h2>DekoMartNepal - Database Seeder</h2>";

try {
    // 0. Check for Reset
    if (isset($_GET['reset']) && $_GET['reset'] == '1') {
        echo "<p>Resetting database...</p>";
        $conn->exec("SET FOREIGN_KEY_CHECKS = 0");
        $conn->exec("TRUNCATE TABLE order_items");
        $conn->exec("TRUNCATE TABLE orders");
        $conn->exec("TRUNCATE TABLE products");
        $conn->exec("TRUNCATE TABLE categories");
        $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
        echo "<p style='color: orange;'>All products and categories cleared.</p>";
        
        // Also reset admin
        $conn->exec("TRUNCATE TABLE admins");
        $plain_pass = 'admin123';
        $admin_pass = password_hash($plain_pass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO admins (username, password) VALUES ('admin', ?)");
        $stmt->execute([$admin_pass]);
        echo "<p style='color: green;'>Admin account reset! Username: <strong>admin</strong> | Password: <strong>$plain_pass</strong></p>";
    }

    // 0.5 Ensure at least one admin exists if not resetting
    $stmt = $conn->query("SELECT COUNT(*) FROM admins");
    $adminCount = $stmt->fetchColumn();
    if ($adminCount == 0) {
        $plain_pass = 'admin123';
        $admin_pass = password_hash($plain_pass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO admins (username, password) VALUES ('admin', ?)");
        $stmt->execute([$admin_pass]);
        echo "<p style='color: green;'>Default admin account created! Username: <strong>admin</strong> | Password: <strong>$plain_pass</strong></p>";
    }

    // 1. Check if Categories exist, if not, add them
    $stmt = $conn->query("SELECT COUNT(*) FROM categories");
    $catCount = $stmt->fetchColumn();

    if ($catCount == 0) {
        echo "<p>Adding categories...</p>";
        $categories = ['Rice', 'Salt', 'Sugar', 'Cooking Oil', 'Flour', 'Vegetables', 'Fruits'];
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        foreach ($categories as $cat) {
            $stmt->execute([$cat]);
        }
        echo "<p style='color: green;'>Categories added successfully!</p>";
    } else {
        echo "<p>Categories already exist.</p>";
    }

    // 2. Fetch category IDs to ensure products are linked correctly
    $stmt = $conn->query("SELECT id, name FROM categories");
    $catMap = [];
    while ($row = $stmt->fetch()) {
        $catMap[strtolower($row['name'])] = $row['id'];
    }

    // 3. Check if Products exist, if not, add them
    $stmt = $conn->query("SELECT COUNT(*) FROM products");
    $prodCount = $stmt->fetchColumn();

    if ($prodCount == 0 || (isset($_GET['reset']) && $_GET['reset'] == '1')) {
        echo "<p>Adding sample products...</p>";
        $products = [
            ['Basmati Rice (5kg)', 'Rice', 1250.00, 50],
            ['Iodized Salt (1kg)', 'Salt', 25.00, 100],
            ['White Sugar (1kg)', 'Sugar', 95.00, 75],
            ['Sunflower Oil (1L)', 'Cooking Oil', 210.00, 40],
            ['Wheat Flour (2kg)', 'Flour', 180.00, 30],
            ['Fresh Tomatoes (1kg)', 'Vegetables', 80.00, 20],
            ['Red Apples (1kg)', 'Fruits', 250.00, 15],
            ['Onions (1kg)', 'Vegetables', 60.00, 25]
        ];

        // Also add some items for the special requested categories if they exist
        $products[] = ['Premium Dog Food (1kg)', 'Dog', 450.00, 20];
        $products[] = ['Cat Treats (500g)', 'Cats', 350.00, 25];

        $stmt = $conn->prepare("INSERT INTO products (name, category_id, price, stock, image) VALUES (?, ?, ?, ?, 'placeholder.png')");
        foreach ($products as $p) {
            $name = $p[0];
            $catName = strtolower($p[1]);
            $price = $p[2];
            $stock = $p[3];
            
            // Auto-create category if it doesn't exist (for Dog/Cats)
            if (!isset($catMap[$catName])) {
                $insCat = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
                $insCat->execute([ucfirst($catName)]);
                $catMap[$catName] = $conn->lastInsertId();
                echo "<p>Created missing category: <strong>" . ucfirst($catName) . "</strong></p>";
            }

            $stmt->execute([$name, $catMap[$catName], $price, $stock]);
        }
        echo "<p style='color: green;'>Sample products added successfully!</p>";
    } else {
        echo "<p>Products already exist.</p>";
    }

    echo "<h3>Seeding complete! <a href='index.php'>Go to Homepage</a></h3>";
    echo "<p><a href='seed.php?reset=1' style='color: red;' onclick='return confirm(\"This will delete ALL existing products and categories. Are you sure?\")'>Reset Database (Clear everything and start fresh)</a></p>";
    echo "<p style='color: #777;'>Note: Please delete seed.php after use for security.</p>";

} catch (PDOException $e) {
    echo "<p style='color: red;'>Error seeding database: " . $e->getMessage() . "</p>";
}
?>
