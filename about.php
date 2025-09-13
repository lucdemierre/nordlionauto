<?php
session_start();

// If you don’t need DB here, you can omit this.
// require_once 'php/db.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userName   = $isLoggedIn ? ($_SESSION['user_name'] ?? '') : '';
$userRole   = $isLoggedIn ? ($_SESSION['user_role'] ?? '') : '';


?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>About Us - NordLion International</title>
  <link rel="icon" type="image/x-icon" href="img/logo-2.png">
  <link rel="stylesheet" href="css/about.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:wght@400;500;600;700&family=Lato:wght@300;400;500;700&display=swap" rel="stylesheet">
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>
  <!-- Header and Navigation (same format as other pages) -->
  <header id="header">
    <div class="header-container">
      <a href="index.php" class="logo">
        <img src="img/logo-2.png" alt="NordLion Logo">
        <span class="logo-text">NordLion International</span>
      </a>
      <nav>
        <ul id="nav-menu">
          <li><a href="index.php">Home</a></li>
          <li><a href="onmarket.php">Cars</a></li>
          <li><a href="offmarket.php">Off Market</a></li>
          <li><a href="about.php" class="active">About Us</a></li>
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
        <h1>About NordLion International</h1>
        <p class="subtitle">Excellence in Luxury Vehicle Brokerage</p>
      </div>
    </section>

    <section class="about-content section-padding">
      <div class="container">
        <div class="about-grid">
          <div class="about-text">
            <h2>Our Mission</h2>
            <p>At NordLion International, we bridge the gap between exceptional vehicles and discerning collectors. Founded with a passion for automotive excellence, we specialize in sourcing and curating the world's most prestigious vehicles for our private clients.</p>

            <h2>Our Approach</h2>
            <p>We believe in a personalized approach to luxury vehicle brokerage. Every vehicle in our portfolio undergoes rigorous evaluation to ensure it meets our exacting standards. Our commitment to quality and authenticity sets us apart in the luxury automotive market.</p>
          </div>
          <div class="about-image">
            <img src="img/pagani.jpg" alt="NordLion Excellence">
          </div>
        </div>

        <div class="services-section">
          <h2>Our Services</h2>
          <div class="services-grid">
            <div class="service-card">
              <h3>Vehicle Sourcing</h3>
              <p>We source exceptional vehicles from around the world, ensuring each meets our rigorous standards for quality and authenticity.</p>
            </div>
            <div class="service-card">
              <h3>Private Sales</h3>
              <p>Our off-market listings provide exclusive access to rare and unique vehicles for our verified clients.</p>
            </div>
            <div class="service-card">
              <h3>Client Verification</h3>
              <p>We maintain a secure and private marketplace, ensuring all transactions are conducted with trusted partners.</p>
            </div>
          </div>
        </div>

        <div class="about-grid" style="margin-top: 80px;">
          <div class="about-image">
            <img src="img/valkama.png" alt="Aydemir Project">
          </div>
          <div class="about-text">
            <h2>The Valkama Project</h2>
            <p>The Valkama Project represents NordLion International’s evolution from a premier luxury car brokerage into a creator of automotive icons. While NordLion has built its reputation on sourcing and delivering the world’s most exclusive vehicles, Valkama takes this vision further by crafting hypercars that break boundaries of speed, design, and innovation.</p>

            <p>Through Valkama, we are no longer just connecting clients to luxury—we are engineering the future of it. Each hypercar is conceived as a masterpiece of technology and artistry, blending precision engineering with bespoke customization. This reflects the same values that drive our brokerage—exclusivity, excellence, and a relentless pursuit of perfection—but on an even more ambitious stage.</p>

            <p>By uniting our expertise in the global luxury vehicle market with groundbreaking automotive design, Valkama establishes NordLion as not only a trusted broker but also a pioneer shaping the next era of hypercars. Our mission is to ensure that every Valkama creation redefines possibility, inspiring a new standard of performance and prestige.</p>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- Footer (same look as other pages) -->
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
    // Navbar scroll behavior
    window.addEventListener('scroll', function() {
      const header = document.getElementById('header');
      if (window.scrollY > 50) {
        header.classList.add('scrolled');
      } else {
        header.classList.remove('scrolled');
      }
    });

    // Mobile menu
    document.querySelector('.mobile-menu-btn').addEventListener('click', function() {
      document.getElementById('nav-menu').classList.toggle('active');
    });
  </script>
</body>
</html>
