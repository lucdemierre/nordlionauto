<?php
// vc_dashboard.php — SAME LAYOUT AS ADMIN, NO INQUIRIES BLOCK
session_start();
require_once 'php/db.php';
require_once 'php/session_check.php';

// Auth
$isLoggedIn = isset($_SESSION['user_id']);
$userName   = $isLoggedIn ? ($_SESSION['user_name'] ?? '') : '';
$userRole   = $isLoggedIn ? ($_SESSION['user_role'] ?? '') : '';
if (!$isLoggedIn || $userRole !== 'vc') {
    header("Location: login.html");
    exit;
}

// First name (same as admin)
$firstName = '';
if (!empty($userName)) {
    $parts = explode(' ', $userName);
    $firstName = $parts[0];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>VC Dashboard | NordLion International</title>
  <link rel="icon" type="image/x-icon" href="img/logo-2.png">

  <!-- EXACT same stylesheet as admin -->
  <link rel="stylesheet" href="css/dashboard.css">

  <!-- Fonts (same as admin) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:wght@400;500;600;700&family=Lato:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body>

  <!-- HEADER (matches admin) -->
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
    <!-- HERO (same as admin) -->
    <section class="hero" style="background-image: url('img/mtc-03.jpg');">
      <div class="hero-content">
        <h1 class="hero-title">Welcome to Your Dashboard, <?php echo $firstName ? htmlspecialchars($firstName) : 'User'; ?>!</h1>
      </div>
    </section>

    <!-- TOOLS GRID (same structure/classes as admin) -->
    <section class="featured section-padding" id="vc-dashboard">
        <div class="container">
            <h2 class="section-heading">Your Tools</h2>
            <div class="featured-grid">
                <div class="car-card">
                    <div class="card-image">
                        <img src="img/Wallpaper-245.jpg" alt="Off Market Listings">
                    </div>
                    <div class="card-details">
                        <h3 class="card-title">Off Market Listings</h3>
                        <p class="card-description">Explore exclusive off-market luxury vehicles only available to select investors. Discover rare and limited production models.</p>
                        <a href="offmarket.php" class="btn btn-outline">View Listings</a>
                    </div>
                </div>

                <div class="car-card">
                    <div class="card-image">
                        <img src="img/cc850.jpg" alt="Submit Investment Inquiry">
                    </div>
                    <div class="card-details">
                        <h3 class="card-title">Submit Car Request</h3>
                        <p class="card-description">Looking for a rare hypercar? Submit your criteria and our specialists will prepare tailored investment proposals.</p>
                        <a href="vc_request.php" class="btn btn-outline">Submit Inquiry</a>
                    </div>
                </div>

                <div class="car-card">
                    <div class="card-image">
                        <img src="img/inquire.jpg" alt="Request a Car">
                    </div>
                    <div class="card-details">
                        <h3 class="card-title">Your Profile</h3>
                        <p class="card-description">Need to change personal details? Manage your information and preferences.<br></br></p>
                        <a href="edit_profile.php" class="btn btn-outline">Edit Profile</a>
                    </div>
                </div>

                <div class="car-card">
                    <div class="card-image">
                        <img src="img/mtc-21.jpg" alt="Request a Car">
                    </div>
                    <div class="card-details">
                        <h3 class="card-title">On Market Listings</h3>
                        <p class="card-description">Looking for something quick and easy? See publicly listed inventory currently available.</p>
                        <a href="onmarket.php" class="btn btn-outline">View Listings</a>
                    </div>
                </div>

                <div class="car-card">
                    <div class="card-image">
                        <img src="img/offmarket-hero.jpg" alt="Request a Car">
                    </div>
                    <div class="card-details">
                        <h3 class="card-title">View Inquiries</h3>
                        <p class="card-description">Made an inquiry? Review and track your inquiries and their statuses.</p>
                        <a href="vc_inquiries.php" class="btn btn-outline">View Inquiries</a>
                    </div>
                </div>

                <div class="car-card">
                    <div class="card-image">
                        <img src="img/mtc-33.jpg" alt="Request a Car">
                    </div>
                    <div class="card-details">
                        <h3 class="card-title">Contact Advisory</h3>
                        <p class="card-description">Have some questions? Ask our professional team below!</p>
                        <a href="contact.php" class="btn btn-outline">Contact us</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- No "pending" block here anymore -->

  </main>

  <!-- FOOTER (exact same structure as admin) -->
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

  <!-- JS matching admin behaviors -->
  <script>
    // Header shrink on scroll
    const header = document.getElementById('header');
    const onScroll = () => {
      if (window.scrollY > 10) header.classList.add('scrolled');
      else header.classList.remove('scrolled');
    };
    document.addEventListener('scroll', onScroll);
    onScroll();

    // Mobile menu toggle
    const btn = document.getElementById('mobileMenuBtn');
    const menu = document.getElementById('nav-menu');
    if (btn && menu) {
      btn.addEventListener('click', () => menu.classList.toggle('active'));
    }
  </script>
</body>
</html>
