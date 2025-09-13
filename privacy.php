<?php
// privacy.php
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
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Privacy Policy | NordLion International</title>
  <link rel="icon" type="image/x-icon" href="img/logo-2.png">
  <link rel="stylesheet" href="css/vc_inquiry.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:wght@400;500;600;700&family=Lato:wght@300;400;500;700&display=swap" rel="stylesheet">
  <style>
    .about-hero{
      background:
        linear-gradient(rgba(15,44,89,.75), rgba(15,44,89,.75)),
        url("img/egg-1.jpg");
      background-size: cover; background-position: center 55%;
      color:#fff; text-align:center; padding:160px 0 100px; margin-top:80px;
    }
    .policy-section{ margin-bottom:40px; }
    .policy-section h2{ margin-bottom:12px; }
    .policy-section p{ margin-bottom:10px; line-height:1.6; }
  </style>
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

  <main>
    <section class="about-hero">
      <div class="container">
        <h1>Privacy Policy</h1>
        <p class="subtitle">Last Updated: <?= date('F j, Y') ?></p>
      </div>
    </section>

    <section class="section-padding">
      <div class="container">
        <div class="policy-section">
          <h2>1. Introduction</h2>
          <p>NordLion International (“Company”, “we”, “our”, or “us”) is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our website, services, and platform.</p>
        </div>

        <div class="policy-section">
          <h2>2. Information We Collect</h2>
          <p><strong>Personal Information:</strong> name, email address, phone number, payment details, and account credentials provided during registration or transactions.</p>
          <p><strong>Non-Personal Information:</strong> browser type, IP address, device information, usage data, and cookies.</p>
        </div>

        <div class="policy-section">
          <h2>3. How We Use Your Information</h2>
          <p>We may use collected information to:</p>
          <ul>
            <li>Provide, maintain, and improve our services;</li>
            <li>Process transactions and send confirmations;</li>
            <li>Respond to inquiries and customer service requests;</li>
            <li>Send promotional communications (where permitted by law);</li>
            <li>Comply with legal obligations and enforce our terms.</li>
          </ul>
        </div>

        <div class="policy-section">
          <h2>4. Cookies and Tracking</h2>
          <p>We use cookies, web beacons, and similar technologies to improve user experience, analyze trends, and personalize content. You can manage cookies in your browser settings, but some features may not function properly if disabled.</p>
        </div>

        <div class="policy-section">
          <h2>5. Sharing of Information</h2>
          <p>We do not sell or rent personal information. We may share information with:</p>
          <ul>
            <li>Trusted third-party service providers (e.g., payment processors, hosting providers);</li>
            <li>Business partners for joint marketing (with consent);</li>
            <li>Legal authorities when required by law or to protect our rights.</li>
          </ul>
        </div>

        <div class="policy-section">
          <h2>6. Data Retention</h2>
          <p>We retain personal information only as long as necessary for the purposes described in this policy, unless a longer retention period is required by law.</p>
        </div>

        <div class="policy-section">
          <h2>7. Your Rights</h2>
          <p>Depending on your jurisdiction, you may have the right to:</p>
          <ul>
            <li>Access, correct, or delete your personal data;</li>
            <li>Withdraw consent at any time;</li>
            <li>Object to processing or request data portability;</li>
            <li>Lodge a complaint with a data protection authority.</li>
          </ul>
        </div>

        <div class="policy-section">
          <h2>8. Security</h2>
          <p>We implement reasonable technical and organizational measures to protect your data. However, no system is 100% secure, and we cannot guarantee absolute security of your information.</p>
        </div>

        <div class="policy-section">
          <h2>9. International Transfers</h2>
          <p>Your data may be transferred and processed outside your country of residence. We ensure such transfers comply with applicable data protection laws.</p>
        </div>

        <div class="policy-section">
          <h2>10. Children’s Privacy</h2>
          <p>Our services are not directed to children under 16. We do not knowingly collect data from minors. If you believe a child has provided us data, please contact us.</p>
        </div>

        <div class="policy-section">
          <h2>11. Updates to This Policy</h2>
          <p>We may update this Privacy Policy from time to time. Changes will be posted here with an updated “Last Updated” date. Continued use of our services means acceptance of the revised policy.</p>
        </div>

        <div class="policy-section">
          <h2>12. Contact Us</h2>
          <p>If you have questions or concerns about this Privacy Policy or our practices, please contact us at:</p>
          <p><strong>Email:</strong> info@nordlion.com<br>
             <strong>Address:</strong> NordLion International, [Insert Office Address Here]</p>
        </div>
      </div>
    </section>
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

  <script>
    const header = document.getElementById('header');
    const onScroll = () => { if (window.scrollY > 10) header.classList.add('scrolled'); else header.classList.remove('scrolled'); };
    document.addEventListener('scroll', onScroll); onScroll();
  </script>
</body>
</html>
