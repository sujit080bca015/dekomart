<?php
/**
 * Comprehensive Customer Profile
 * DEKOMARTNEPAL E-commerce Website
 * 
 * Advanced profile management with multiple features
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = 'Please login to access your profile.';
    header('Location: login.php');
    exit();
}

// Include database connection
require_once 'includes/db_connection.php';

$user_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $postal_code = trim($_POST['postal_code'] ?? '');
        
        if (!empty($full_name) && !empty($email)) {
            try {
                $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, address = ?, city = ?, postal_code = ? WHERE user_id = ?");
                $stmt->execute([$full_name, $email, $phone, $address, $city, $postal_code, $user_id]);
                
                // Update session
                $_SESSION['full_name'] = $full_name;
                $_SESSION['email'] = $email;
                
                $success_msg = "Profile updated successfully!";
            } catch (Exception $e) {
                $error_msg = "Error updating profile. Please try again.";
            }
        } else {
            $error_msg = "Name and email are required.";
        }
    }
    
    // Handle password change
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (!empty($current_password) && !empty($new_password) && !empty($confirm_password)) {
            if ($new_password === $confirm_password) {
                try {
                    // Verify current password
                    $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
                    $stmt->execute([$user_id]);
                    $user = $stmt->fetch();
                    
                    if ($user && password_verify($current_password, $user['password'])) {
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                        $stmt->execute([$hashed_password, $user_id]);
                        
                        $success_msg = "Password changed successfully!";
                    } else {
                        $error_msg = "Current password is incorrect.";
                    }
                } catch (Exception $e) {
                    $error_msg = "Error changing password. Please try again.";
                }
            } else {
                $error_msg = "New passwords do not match.";
            }
        } else {
            $error_msg = "All password fields are required.";
        }
    }
}

// Get user data
$user_data = [];
try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user_data = $stmt->fetch();
} catch (Exception $e) {
    $user_data = [];
}

// Get order statistics
$order_stats = [];
try {
    $stmt = $conn->prepare("SELECT COUNT(*) as total_orders, SUM(total_amount) as total_spent FROM orders WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $order_stats = $stmt->fetch();
} catch (Exception $e) {
    $order_stats = ['total_orders' => 0, 'total_spent' => 0];
}

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - DEKOMARTNEPAL</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .profile-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .profile-header {
            background: linear-gradient(135deg, #2E7D32, #43A047);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .profile-card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .profile-card h3 {
            color: #2E7D32;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            font-weight: bold;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 1rem;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #2E7D32;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #2E7D32;
            color: white;
        }
        
        .btn-primary:hover {
            background: #1B5E20;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #2E7D32;
        }
        
        .stat-label {
            color: #666;
            margin-top: 0.5rem;
        }
        
        .message {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 5px;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .back-link {
            text-align: center;
            margin-top: 2rem;
        }
        
        .back-link a {
            color: #2E7D32;
            text-decoration: none;
            font-weight: bold;
        }
        
        @media (max-width: 768px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <!-- Profile Header -->
        <div class="profile-header">
            <h1>👤 My Profile</h1>
            <p>Manage your account information and preferences</p>
        </div>
        
        <!-- Messages -->
        <?php if (!empty($success_msg)): ?>
            <div class="message success"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error_msg)): ?>
            <div class="message error"><?php echo $error_msg; ?></div>
        <?php endif; ?>
        
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $order_stats['total_orders'] ?? 0; ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">Rs. <?php echo number_format($order_stats['total_spent'] ?? 0, 2); ?></div>
                <div class="stat-label">Total Spent</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo date('Y-m-d', strtotime($user_data['created_at'] ?? 'now')); ?></div>
                <div class="stat-label">Member Since</div>
            </div>
        </div>
        
        <!-- Profile Forms -->
        <div class="profile-grid">
            <!-- Personal Information -->
            <div class="profile-card">
                <h3>📝 Personal Information</h3>
                <form method="POST">
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user_data['full_name'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Delivery Address</label>
                        <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user_data['address'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($user_data['city'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="postal_code">Postal Code</label>
                        <input type="text" id="postal_code" name="postal_code" value="<?php echo htmlspecialchars($user_data['postal_code'] ?? ''); ?>">
                    </div>
                    
                    <button type="submit" name="update_profile" class="btn btn-primary">💾 Update Profile</button>
                </form>
            </div>
            
            <!-- Security Settings -->
            <div class="profile-card">
                <h3>🔒 Security Settings</h3>
                <form method="POST">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" name="change_password" class="btn btn-danger">🔑 Change Password</button>
                </form>
            </div>
        </div>
        
        <!-- Back to Dashboard -->
        <div class="back-link">
            <a href="customer_dashboard.php">← Back to Dashboard</a>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
