<?php
require_once 'includes/db_connection.php';
require_once 'includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "All fields are required.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            redirect('index.php');
        } else {
            $error = "Invalid email or password.";
        }
    }
}

include 'includes/header.php';
?>

<div class="auth-container">
    <h2>User Login</h2>
    
    <?php if ($error): ?>
        <div class="error-msg"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <div class="password-group">
                <input type="password" id="password" name="password" required>
                <i class="fas fa-eye toggle-password"></i>
            </div>
        </div>
        
        <button type="submit" name="login" class="btn-primary">Login</button>
        <p style="margin-top: 1rem; text-align: center;">Don't have an account? <a href="register.php" style="color: #3498db;">Register here</a></p>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
