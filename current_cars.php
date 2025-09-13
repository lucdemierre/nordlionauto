<?php
// contact.php
session_start();

// ---- Auth flags (used by header) ----
$isLoggedIn = isset($_SESSION['user_id']);
$userName   = $isLoggedIn ? ($_SESSION['user_name'] ?? '') : '';
$userRole   = $isLoggedIn ? ($_SESSION['user_role'] ?? '') : '';

// ---- DB ----
require_once 'php/db.php';

// ---- Inquiry type options (no DB call needed) ----
$INQUIRY_TYPES = [
  ['value' => 'general',    'label' => 'General Inquiry'],
  ['value' => 'car',        'label' => 'Car Inquiry'],
  ['value' => 'jet',        'label' => 'Jet Inquiry'],
  ['value' => 'investment', 'label' => 'Investment Opportunity'],
  ['value' => 'other',      'label' => 'Other'],
];

// ---- Prefill email for logged-in users ----
$email = '';
if ($isLoggedIn) {
    if (!empty($_SESSION['user_email'])) {
        $email = $_SESSION['user_email'];
    } else {
        try {
            $s = $pdo->prepare("SELECT email FROM users WHERE id = ? LIMIT 1");
            $s->execute([$_SESSION['user_id']]);
            $dbEmail = $s->fetchColumn();
            if ($dbEmail) {
                $email = $dbEmail;
                $_SESSION['user_email'] = $dbEmail; // cache
            }
        } catch (PDOException $e) {
            // ignore quietly; user can still type an email
        }
    }
}

// ---- POST handling ----
$success_message = null;
$error_message   = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Trim + basic sanitize
    $name         = trim($_POST['name'] ?? '');
    $emailPosted  = trim($_POST['email'] ?? '');
    $inquiry_type = trim($_POST['inquiry_type'] ?? '');
    $message      = trim($_POST['message'] ?? '');
    $user_id      = $_SESSION['user_id'] ?? null;

    // Prefer posted email if present
    if ($emailPosted !== '') {
        $email = $emailPosted;
    }

    // Validation
    if (!$user_id) {
        $error_message = "Please log in before sending an inquiry.";
    } elseif ($name === '' || $email === '' || $inquiry_type === '' || $message === '') {
        $error_message = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } else {
        // subject is REQUIRED by your table; derive it from type
        $subject = ucfirst($inquiry_type) . " Inquiry";

        // Optional: associate a car (your schema allows NULL)
        $car_id = null;

        try {
            $stmt = $pdo->prepare("
                INSERT INTO inquiries (user_id, type, subject, message, status, car_id)
                VALUES (?, ?, ?, ?, 'new', ?)
            ");
            $stmt->execute([$user_id, $inquiry_type, $subject, $message, $car_id]);
            $success_message = "Thank you for your inquiry. We'll get back to you soon!";
        } catch (PDOException $e) {
            error_log('contact.php INSERT failed: ' . $e->getMessage());
            $error_message = "There was an error submitting your inquiry. Please try again.";
        }
    }
}

// Convenience: first name (for greetings if you ever need it)
$firstName = '';
if (!empty($userName)) {
    $parts = explode(' ', $userName);
    $firstName = $parts[0];
}
require 'php/db.php';


// Fetch all cars from the database
$stmt = $pdo->query("SELECT * FROM cars ORDER BY id DESC");
$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Current Cars | NordLion Dashboard</title>
  <link rel="stylesheet" href="css/style.css">
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

  <main class="jet-main">
    <section class="jet-overview">
      <h1 class="jet-title" style="margin-top:50px;">Manage Current Cars</h1>
    </section>

    <?php if (count($cars) === 0): ?>
      <p style="text-align: center;">No cars available.</p>
    <?php else: ?>
    <section class="features-grid">
      <?php foreach ($cars as $car): ?>
        <div class="feature-card">
          <h3 class="feature-title"><?= htmlspecialchars($car['name']) . ' ' . htmlspecialchars($car['model']) ?></h3>
          <p class="feature-description">Price: €<?= number_format($car['price']) ?><br>Year: <?= htmlspecialchars($car['year']) ?></p>
          <a href="edit_car.php?id=<?= $car['id'] ?>" class="btn btn-outline">Edit</a>
          <a href="delete_car.php?id=<?= $car['id'] ?>" class="btn btn-primary" onclick="return confirm('Are you sure you want to delete this car? This cannot be undone.');">Delete</a>
        </div>
      <?php endforeach; ?>
    </section>
    <?php endif; ?>
  </main>

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
</body>
</html>
