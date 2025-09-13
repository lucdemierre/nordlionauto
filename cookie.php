<?php
// cookie.php
session_start();
require_once 'php/db.php';
// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userName   = $isLoggedIn ? ($_SESSION['user_name'] ?? '') : '';
$userRole   = $isLoggedIn ? ($_SESSION['user_role'] ?? '') : '';

$firstName = '';
if ($isLoggedIn && !empty($userName)) {
    $nameParts = explode(' ', $userName);
    $firstName = $nameParts[0];
}
$is_logged_in = isset($_SESSION['user_id']);
$email = '';
$firstName = '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Cookie Policy | NordLion International</title>
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
        <h1>Cookie Policy</h1>
        <p class="subtitle">Last Updated: <?= date('F j, Y') ?></p>
      </div>
    </section>

    <section class="section-padding">
      <div class="container">

        <div class="policy-section">
          <h2>1. Introduction</h2>
          <p>This Cookie Policy explains how NordLion International (“we”, “our”, “us”) uses cookies and similar technologies on our website and online services. By using our website, you agree to the use of cookies as described in this policy.</p>
        </div>

        <div class="policy-section">
          <h2>2. What Are Cookies?</h2>
          <p>Cookies are small text files placed on your device when you visit a website. They help us enhance your browsing experience, understand website usage, and deliver personalized content and services.</p>
        </div>

        <div class="policy-section">
          <h2>3. Types of Cookies We Use</h2>
          <ul>
            <li><strong>Essential Cookies:</strong> Necessary for the website to function, such as logging in and managing your account.</li>
            <li><strong>Performance Cookies:</strong> Collect information about how visitors use our site, allowing us to improve usability and performance.</li>
            <li><strong>Functionality Cookies:</strong> Remember your preferences and settings to provide a customized experience.</li>
            <li><strong>Advertising & Analytics Cookies:</strong> Used to deliver relevant ads and measure the effectiveness of our marketing campaigns.</li>
          </ul>
        </div>

        <div class="policy-section">
          <h2>4. How We Use Cookies</h2>
          <p>We use cookies to:</p>
          <ul>
            <li>Enable secure login and account management;</li>
            <li>Analyze traffic and user interactions to improve our website;</li>
            <li>Personalize content and vehicle recommendations;</li>
            <li>Support marketing, social media, and advertising initiatives.</li>
          </ul>
        </div>

        <div class="policy-section">
          <h2>5. Third-Party Cookies</h2>
          <p>We may allow trusted third parties (such as analytics providers and advertising networks) to place cookies on your device. These third parties may collect information about your browsing activity across websites and over time.</p>
        </div>

        <div class="policy-section">
          <h2>6. Managing Cookies</h2>
          <p>You can control or delete cookies through your browser settings. Please note that disabling certain cookies may limit functionality and affect your experience on our website.</p>
        </div>

        <div class="policy-section">
          <h2>7. Updates to This Policy</h2>
          <p>We may update this Cookie Policy from time to time. Any changes will be posted on this page with the updated “Last Updated” date. Continued use of our website constitutes acceptance of the updated policy.</p>
        </div>

        <div class="policy-section">
          <h2>8. Contact Us</h2>
          <p>If you have questions about our use of cookies, please contact us:</p>
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
