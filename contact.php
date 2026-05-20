 <?php
require_once 'includes/db_connection.php';
require_once 'includes/functions.php';
include 'includes/header.php';

$success = "";
$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);
    
    try {
        $stmt = $conn->prepare("INSERT INTO messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $subject, $message]);
        $success = "Thank you, $name! Your message has been sent successfully. We will get back to you soon.";
    } catch (PDOException $e) {
        $error = "Error sending message: " . $e->getMessage();
    }
}
?>

<div class="contact-section" style="max-width: 800px; margin: 0 auto; display: flex; gap: 2rem; flex-wrap: wrap;">
    <!-- Contact Form -->
    <div style="flex: 2; background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
        <h2 style="color: #2ecc71; margin-bottom: 1.5rem;">Contact Us</h2>
        
        <?php if ($success): ?>
            <div class="success-msg" style="background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 1rem;"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error-msg" style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 1rem;"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="contact.php" method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Subject</label>
                <input type="text" name="subject" required>
            </div>
            <div class="form-group">
                <label>Your Message</label>
                <textarea name="message" required rows="5" style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 4px;"></textarea>
            </div>
            <button type="submit" name="send_message" class="btn-primary">Send Message</button>
        </form>
    </div>

    <!-- Contact Info -->
    <div style="flex: 1; background: #34495e; color: white; padding: 2rem; border-radius: 8px; height: fit-content;">
        <h3>Get in Touch</h3>
        <ul style="margin-top: 1.5rem; list-style: none;">
            <li style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 1rem;">
                <i class="fas fa-map-marker-alt" style="color: #2ecc71; font-size: 1.2rem;"></i>
                <span>Kathmandu, Nepal</span>
            </li>
            <li style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 1rem;">
                <i class="fas fa-phone" style="color: #2ecc71; font-size: 1.2rem;"></i>
                <span>+977-9818163312</span>
            </li>
            <li style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 1rem;">
                <i class="fas fa-envelope" style="color: #2ecc71; font-size: 1.2rem;"></i>
                <span>dekomartnepal@gmail.com</span>
            </li>
        </ul>
        <div style="margin-top: 2rem;">
            <h3>Working Hours</h3>
            <p style="margin-top: 0.5rem; color: #bdc3c7;">Mon - Sat: 9:00 AM - 8:00 PM<br>Sun: 10:00 AM - 5:00 PM</p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
