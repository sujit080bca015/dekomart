<?php
require_once 'includes/db_connection.php';
require_once 'includes/functions.php';

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Backend Validation
    if (empty($full_name) || empty($email) || empty($phone) || empty($address) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!validateAlphabetsOnly($full_name)) {
        $error = "Full Name must contain alphabets only.";
    } elseif (!validateEmail($email)) {
        $error = "Please enter a valid email address.";
    } elseif (!validatePhone($phone)) {
        $error = "Phone number must be exactly 10 digits.";
    } elseif (!validateAlphabetsOnly($address)) {
        $error = "Address must contain alphabets only (no numbers).";
    } elseif (!validatePassword($password)) {
        $error = "Password must be at least 8 characters long and include both letters and numbers.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email is already registered.";
        } else {
            // Hash password and insert into database
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, address, password) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$full_name, $email, $phone, $address, $hashed_password])) {
                $success = "Registration successful! You can now <a href='login.php'>login</a>.";
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="auth-container">
    <h2>User Registration</h2>
    
    <?php if ($error): ?>
        <div class="error-msg"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success-msg"><?php echo $success; ?></div>
    <?php endif; ?>

    <form id="register-form" action="register.php" method="POST">
        <div class="form-group">
            <label for="full_name">Full Name</label>
            <input type="text" id="full_name" name="full_name" required value="<?php echo isset($full_name) ? $full_name : ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required value="<?php echo isset($email) ? $email : ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="text" id="phone" name="phone" required maxlength="10" value="<?php echo isset($phone) ? $phone : ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="address">Address (Alphabets only)</label>
            <input type="text" id="address" name="address" required value="<?php echo isset($address) ? $address : ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <div class="password-group">
                <input type="password" id="password" name="password" required>
                <i class="fas fa-eye toggle-password"></i>
            </div>
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <div class="password-group">
                <input type="password" id="confirm_password" name="confirm_password" required>
                <i class="fas fa-eye toggle-password"></i>
            </div>
        </div>
        
        <button type="submit" name="register" class="btn-primary">Register</button>
        <p style="margin-top: 1rem; text-align: center;">Already have an account? <a href="login.php" style="color: #3498db;">Login here</a></p>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
