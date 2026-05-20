<?php
/**
 * Customer Dashboard - No Database Queries
 * DEKOMARTNEPAL E-commerce Website
 * 
 * Simple dashboard without any database operations to avoid mysqli errors
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = 'Please login to access your dashboard.';
    header('Location: login.php');
    exit();
}

// Get user info from session (no database queries)
$user_name = $_SESSION['full_name'] ?? $_SESSION['user_name'] ?? $_SESSION['username'] ?? 'User';
$user_email = $_SESSION['email'] ?? 'user@example.com';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - DEKOMARTNEPAL</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, #2E7D32, #43A047);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .dashboard-header h1 {
            margin: 0 0 0.5rem 0;
            font-size: 2rem;
        }
        
        .dashboard-header p {
            margin: 0;
            opacity: 0.9;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
        }
        
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .feature-title {
            font-size: 1.3rem;
            color: #2E7D32;
            margin-bottom: 1rem;
        }
        
        .feature-description {
            color: #666;
            margin-bottom: 1.5rem;
        }
        
        .feature-btn {
            background: #2E7D32;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            transition: background 0.3s;
            display: inline-block;
        }
        
        .feature-btn:hover {
            background: #1B5E20;
        }
        
        .welcome-section {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .welcome-section h2 {
            color: #2E7D32;
            margin-bottom: 1rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #2E7D32;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        
        .info-section {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 10px;
            text-align: center;
            margin-top: 2rem;
        }
        
        @media (max-width: 768px) {
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
            
            .dashboard-header {
                padding: 1.5rem;
            }
            
            .dashboard-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <?php require_once "includes/header.php"; ?>
    
    <div class="dashboard-container">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <h1>Welcome, <?php echo htmlspecialchars($user_name); ?>! 👋</h1>
            <p><?php echo htmlspecialchars($user_email); ?> • Your personal dashboard</p>
        </div>
        
        <!-- Welcome Section -->
        <div class="welcome-section">
            <h2>🎉 Welcome to Your Dashboard</h2>
            <p>Manage your account, enjoy shopping, and explore our amazing products. Your one-stop destination for all your shopping needs!</p>
        </div>
        
        <!-- Quick Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">🛍️</div>
                <div class="stat-number">100+</div>
                <div class="stat-label">Products Available</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🎯</div>
                <div class="stat-number">24/7</div>
                <div class="stat-label">Customer Support</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🚚</div>
                <div class="stat-number">Fast</div>
                <div class="stat-label">Delivery Service</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">⭐</div>
                <div class="stat-number">Top</div>
                <div class="stat-label">Quality Products</div>
            </div>
        </div>
        
        <!-- Features Grid -->
        <div class="features-grid">
            <!-- Shopping Feature -->
            <div class="feature-card">
                <div class="feature-icon">🛍️</div>
                <h3 class="feature-title">Shop Products</h3>
                <p class="feature-description">Browse our wide range of high-quality products and find exactly what you need.</p>
                <a href="index.php" class="feature-btn">Start Shopping</a>
            </div>
            
            <!-- Cart Feature -->
            <div class="feature-card">
                <div class="feature-icon">🛒</div>
                <h3 class="feature-title">Shopping Cart</h3>
                <p class="feature-description">View and manage items in your shopping cart before checkout.</p>
                <a href="cart.php" class="feature-btn">View Cart</a>
            </div>
            
            <!-- Profile Feature -->
            <div class="feature-card">
                <div class="feature-icon">👤</div>
                <h3 class="feature-title">My Profile</h3>
                <p class="feature-description">Manage your personal information, address, security settings, and view your shopping statistics.</p>
                <a href="profile.php" class="feature-btn">Manage Profile</a>
            </div>
            
            <!-- Orders Feature -->
            <div class="feature-card">
                <div class="feature-icon">📦</div>
                <h3 class="feature-title">My Orders</h3>
                <p class="feature-description">View your order history and track current shipments.</p>
                <a href="my_orders.php" class="feature-btn">View Orders</a>
            </div>
            
            <!-- Logout Feature -->
            <div class="feature-card">
                <div class="feature-icon">🚪</div>
                <h3 class="feature-title">Logout</h3>
                <p class="feature-description">Securely logout from your account.</p>
                <a href="logout.php" class="feature-btn" style="background: #dc3545;" onclick="return confirm('Are you sure you want to logout?');">Logout</a>
            </div>
        </div>
        
        <!-- Info Section -->
        <div class="info-section">
            <h3 style="color: #2E7D32; margin-bottom: 1rem;">🌟 Happy Shopping!</h3>
            <p style="color: #666; margin-bottom: 1.5rem;">
                Your account is active and ready for shopping. Enjoy exclusive deals, personalized recommendations, and a seamless shopping experience!
            </p>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="index.php" style="background: #2E7D32; color: white; padding: 1rem 2rem; text-decoration: none; border-radius: 5px;">
                    🛍️ Browse Products
                </a>
                <a href="cart.php" style="background: #1976D2; color: white; padding: 1rem 2rem; text-decoration: none; border-radius: 5px;">
                    🛒 View Cart
                </a>
                <a href="contact.php" style="background: #FF9800; color: white; padding: 1rem 2rem; text-decoration: none; border-radius: 5px;">
                    📞 Get Help
                </a>
            </div>
        </div>
    </div>
    
    <?php require_once "includes/footer.php"; ?>
</body>
</html>
