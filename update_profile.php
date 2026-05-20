<?php
/**
 * Update Profile
 * DEKOMARTNEPAL E-commerce Website
 * 
 * Handles customer profile updates
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/database.php';

// Check if user is logged in
if (!is_logged_in()) {
    $_SESSION['error_message'] = 'Please login to update your profile.';
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    
    // Get form data
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $phone_number = sanitize_input($_POST['phone_number'] ?? '');
    $address = sanitize_input($_POST['address'] ?? '');
    
    // Validate required fields
    if (empty($full_name) || empty($email) || empty($phone_number) || empty($address)) {
        $_SESSION['error_message'] = 'All fields are required.';
        redirect('customer_dashboard.php');
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = 'Please enter a valid email address.';
        redirect('customer_dashboard.php');
    }
    
    // Check if email is already taken by another user
    $stmt = prepared_query("SELECT user_id FROM users WHERE email = ? AND user_id != ?", "si", [$email, $user_id]);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        $_SESSION['error_message'] = 'Email address is already in use by another account.';
        redirect('customer_dashboard.php');
    }
    
    // Update user profile
    $stmt = prepared_query("
        UPDATE users 
        SET full_name = ?, email = ?, phone_number = ?, address = ?, updated_at = NOW()
        WHERE user_id = ?
    ", "ssssi", [$full_name, $email, $phone_number, $address, $user_id]);
    
    if ($stmt) {
        // Update session variables
        $_SESSION['full_name'] = $full_name;
        $_SESSION['email'] = $email;
        
        $_SESSION['success_message'] = 'Profile updated successfully!';
    } else {
        $_SESSION['error_message'] = 'Failed to update profile. Please try again.';
    }
}

redirect('customer_dashboard.php');
?>
