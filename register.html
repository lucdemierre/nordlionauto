<?php
session_start();
require_once 'php/db.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] : '';
$userRole = $isLoggedIn ? $_SESSION['user_role'] : '';


// Get first name if user is logged in
$firstName = '';
if ($isLoggedIn && !empty($userName)) {
    $nameParts = explode(' ', $userName);
    $firstName = $nameParts[0];
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $inquiry_type = $_POST['inquiry_type'] ?? '';
    $message = $_POST['message'] ?? '';
    $user_id = $_SESSION['user_id'] ?? null;
}


$is_logged_in = isset($_SESSION['user_id']);

// Database connection
require_once 'php/db.php';

// Fetch inquiry types from database
$inquiry_types = [];
try {
    $stmt = $pdo->query("SELECT DISTINCT type, subject FROM inquiries WHERE status = 'new' ORDER BY subject");
} catch (PDOException $e) {
    // If there's an error, use default types as fallback
    $inquiry_types = [
        ['type' => 'car', 'subject' => 'Car Inquiry'],
        ['type' => 'jet', 'subject' => 'Jet Inquiry'],
        ['type' => 'investment', 'subject' => 'Investment Opportunity'],
        ['type' => 'general', 'subject' => 'General Inquiry'],
        ['type' => 'other', 'subject' => 'Other']
    ];
}




// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'] ?? null;         // must be set if user_id is NOT NULL in DB
    $message = $_POST['message'] ?? '';
    $type    = 'general';
    $subject = 'General Inquiry';

    try {
        $stmt = $pdo->prepare("
            INSERT INTO inquiries (user_id, type, subject, message, status)
            VALUES (?, ?, ?, ?, 'new')
        ");
        $stmt->execute([$user_id, $type, $subject, $message]);

        $success_message = "Thank you for your inquiry. We'll get back to you soon!";
    } catch (PDOException $e) {
        $error_message = "There was an error submitting your inquiry. Please try again.";
    }
}



// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userName   = $isLoggedIn ? ($_SESSION['user_name'] ?? '') : '';
$userRole   = $isLoggedIn ? ($_SESSION['user_role'] ?? '') : '';


// Default values
$email = '';
$firstName = '';

// If logged in, try to get email from session or database
if ($isLoggedIn) {
    if (!empty($_SESSION['user_email'])) {
        $email = $_SESSION['user_email'];
    } else {
        // Optional: fetch from database if not stored in session
        $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $dbEmail = $stmt->fetchColumn();
        if ($dbEmail) {
            $email = $dbEmail;
            $_SESSION['user_email'] = $dbEmail; // cache for next time
        }
    }
}

// If form was submitted, keep the posted email instead
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? $email;
}

// Get first name from full name if available
if (!empty($userName)) {
    $nameParts = explode(' ', $userName);
    $firstName = $nameParts[0];
}



?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register | NordLion International</title>
  <link rel="icon" type="image/x-icon" href="img/logo-2.png">
  <link rel="stylesheet" href="css/login.css">
  <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:wght@400;500;600;700&family=Lato:wght@300;400;500;700&display=swap" rel="stylesheet">
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>
  <header id="header">
        <div class="header-container">
            <a href="index.php" class="logo">
                <img src="img/logo-2.png" alt="NordLion Logo">
                <span class="logo-text">NordLion International</span>
            </a>
            <nav>
                <ul id="nav-menu">
                    <li><a href="index.php" class="active">Home</a></li>
                    <li><a href="onmarket.php">Cars</a></li>
                    <li><a href="offmarket.php">Off Market</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="team.php">Our Team</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <?php if ($isLoggedIn): ?>
                        <?php if ($userRole === 'admin'): ?>
                            <li><a href="dashboard.php">Admin Panel</a></li>
                        <?php elseif ($userRole === 'vc'): ?>
                            <li><a href="vc_dashboard.php">VC Panel</a></li>
                        <?php endif; ?>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.html">Login</a></li>
                    <?php endif; ?>
                </ul>
                <button class="mobile-menu-btn" aria-label="Toggle menu">
                    <i class="fas fa-bars"></i>
                </button>
            </nav>
        </div>
    </header>

  <div class="login-container">
    <div class="login-form">
      <h2>Register</h2>
      <form action="php/register.php" method="POST">
        <div class="form-group">
          <label for="name">Full Name</label>
          <input type="text" id="name" name="name" class="form-input" required>
        </div>
        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" class="form-input" required>
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" class="form-input" required>
        </div>
        <button type="submit" class="btn btn-primary login-btn">Register</button>
      </form>
      <div class="forgot-password">
        <a href="login.html">Already have an account? Login here</a>
      </div>
    </div>
  </div>

  <footer class="footer" style="background-color: #0F2C59;">
        <div class="container">
        <div class="footer-container">
            <div class="footer-brand">
            <div class="footer-logo">
                <div class="social-icons">
                <a href="https://www.instagram.com/the_nordlion_international/" target="_blank"><img src="img/insta.png" alt="Instagram"></a>
                <a href="https://www.linkedin.com/company/nordlion-international/?viewAsMember=true" target="_blank"><img src="img/linkedin.png" alt="LinkedIn"></a>
                </div>
                <img src="img/logo-2.png" alt="NordLion Logo">
                <span class="footer-logo-text">NordLion International</span>
            </div>
            <p class="footer-text">Excellence in luxury vehicle brokerage.</p>
            </div>

            <div class="footer-links">
            <h4 class="footer-heading">Quick Links</h4>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="onmarket.php">Cars</a></li>
                <li><a href="offmarket.php">Off Market</a></li>
                <li><a href="about.php">About Us</a></li>
                <li><a href="team.php">Our Team</a></li>
                <li><a href="contact.php">Contact</a></li>
                    <?php if ($isLoggedIn): ?>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.html">Login</a></li>
                <?php endif; ?>
            </ul>
            </div>
            <div class="footer-links">
            <h4 class="footer-heading">Services</h4>
            <ul>
                <li><a href="onmarket.php">Vehicle Acquisition</a></li>
                <li><a href="about.php">About Us</a></li>
                <li><a href="offmarket.php">Off-Market Access</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
            </div>

            <div class="footer-links">
            <h4 class="footer-heading">Legal</h4>
            <ul>
                <li><a href="privacy.php">Privacy Policy</a></li>
                <li><a href="terms.php">Terms of Service</a></li>
                <li><a href="cookie.php">Cookie Policy</a></li>
            </ul>
            </div>
        </div>

        <div class="copyright">
            <p>&copy; 2025 NordLion International. All rights reserved.</p>
        </div>
        </div>
    </footer>

  <script src="js/main.js"></script>
  <script>
    // Add scroll behavior for navbar
    window.addEventListener('scroll', function() {
      const header = document.getElementById('header');
      if (window.scrollY > 50) {
        header.classList.add('scrolled');
      } else {
        header.classList.remove('scrolled');
      }
    });
  </script>
</body>
</html>
