<?php
// terms.php
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
  <title>Terms of Service | NordLion International</title>
  <link rel="icon" type="image/x-icon" href="img/logo-2.png">
  <link rel="stylesheet" href="css/vc_inquiry.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:wght@400;500;600;700&family=Lato:wght@300;400;500;700&display=swap" rel="stylesheet">
  <style>
    .about-hero{
      background:
        linear-gradient(rgba(15,44,89,.75), rgba(15,44,89,.75)),
        url("img/image.jpeg");
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
        <h1>Terms of Service</h1>
        <p class="subtitle">Last Updated: <?= date('F j, Y') ?></p>
      </div>
    </section>

    <section class="section-padding">
      <div class="container">
        <div class="policy-section">
          <h2>1. Acceptance of Terms</h2>
          <p>By accessing or using the NordLion International website and services (“Services”), you agree to be bound by these Terms of Service (“Terms”) and our Privacy Policy. If you do not agree, you must not use our Services.</p>
        </div>

        <div class="policy-section">
          <h2>2. Eligibility</h2>
          <p>You must be at least 18 years old (or the legal age of majority in your jurisdiction) to use our Services. By using our Services, you represent and warrant that you meet this requirement.</p>
        </div>

        <div class="policy-section">
          <h2>3. Accounts</h2>
          <p>When creating an account, you must provide accurate and complete information. You are responsible for maintaining the confidentiality of your login credentials and for all activities under your account.</p>
        </div>

        <div class="policy-section">
          <h2>4. Permitted Use</h2>
          <p>You agree not to:</p>
          <ul>
            <li>Use the Services for unlawful purposes;</li>
            <li>Interfere with or disrupt the Services or servers;</li>
            <li>Attempt to gain unauthorized access to other accounts or systems;</li>
            <li>Copy, distribute, or exploit the Services without authorization.</li>
          </ul>
        </div>

        <div class="policy-section">
          <h2>5. Intellectual Property</h2>
          <p>All content, trademarks, logos, and materials provided through our Services are the property of NordLion International or its licensors and are protected under applicable copyright, trademark, and intellectual property laws. Unauthorized use is strictly prohibited.</p>
        </div>

        <div class="policy-section">
          <h2>6. Transactions & Payments</h2>
          <p>All transactions conducted through our Services are subject to review and approval. You agree to provide accurate billing information and authorize us (and our third-party payment providers) to process charges in accordance with applicable agreements.</p>
        </div>

        <div class="policy-section">
          <h2>7. Disclaimer of Warranties</h2>
          <p>Our Services are provided “as is” and “as available.” We make no warranties, express or implied, regarding the reliability, accuracy, or availability of the Services. Your use of the Services is at your own risk.</p>
        </div>

        <div class="policy-section">
          <h2>8. Limitation of Liability</h2>
          <p>To the maximum extent permitted by law, NordLion International and its affiliates shall not be liable for any indirect, incidental, consequential, or punitive damages, including lost profits, arising out of or related to your use of the Services.</p>
        </div>

        <div class="policy-section">
          <h2>9. Indemnification</h2>
          <p>You agree to indemnify and hold harmless NordLion International, its affiliates, and their respective officers, directors, and employees from any claims, damages, liabilities, and expenses arising out of your use of the Services or violation of these Terms.</p>
        </div>

        <div class="policy-section">
          <h2>10. Termination</h2>
          <p>We reserve the right to suspend or terminate your account and access to the Services at our sole discretion, without notice, for conduct that violates these Terms or is otherwise harmful to our interests.</p>
        </div>

        <div class="policy-section">
          <h2>11. Governing Law</h2>
          <p>These Terms shall be governed by and construed under the laws of [Insert Jurisdiction], without regard to its conflict of law principles. Any disputes shall be resolved exclusively in the courts of [Insert Jurisdiction].</p>
        </div>

        <div class="policy-section">
          <h2>12. Changes to Terms</h2>
          <p>We may update these Terms from time to time. Any changes will be posted on this page with the updated “Last Updated” date. Your continued use of the Services constitutes acceptance of the updated Terms.</p>
        </div>

        <div class="policy-section">
          <h2>13. Contact Us</h2>
          <p>If you have questions about these Terms, please contact us:</p>
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
