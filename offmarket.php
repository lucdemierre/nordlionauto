<?php
session_start();
require_once 'php/db.php';
require_once 'php/session_check.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userName   = $isLoggedIn ? ($_SESSION['user_name'] ?? '') : '';
$userRole   = $isLoggedIn ? ($_SESSION['user_role'] ?? '') : '';

$firstName = '';
if ($isLoggedIn && !empty($userName)) {
    $nameParts = explode(' ', $userName);
    $firstName = $nameParts[0];
}

if (!$isLoggedIn) {
    header("Location: login.html");
    exit;
}

// ROLE-GATED FETCH (Admin + VC only see off-market + form)
if ($userRole === 'admin' || $userRole === 'vc') {
    $stmt = $pdo->prepare("SELECT id, name, model, price, description FROM cars WHERE status = 'offmarket'");
    $stmt->execute();
    $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif ($userRole === 'mop') {
    echo "<script>
            alert('Hi " . htmlspecialchars($firstName, ENT_QUOTES) . ", this page is reserved for Admin and VC users.');
            window.location.href = 'index.php';
          </script>";
    exit;
} else {
    header("Location: login.html");
    exit;
}

// Optional debug: visit offmarket.php?debug=1
$debug = isset($_GET['debug']) && $_GET['debug'] == '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Off-Market Vehicles | NordLion International</title>
  <link rel="icon" type="image/x-icon" href="img/logo-2.png">
  <link rel="stylesheet" href="css/onmarket.css" />
  <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:wght@400;500;600;700&family=Lato:wght@300;400;500;700&display=swap" rel="stylesheet">
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
  <style>
    .exclusive-badge { position:absolute; top:10px; right:10px; background:#000; color:gold; padding:5px 10px; border-radius:3px; font-size:.8rem; font-weight:700; letter-spacing:1px; }
    .car-card { position:relative; }
    .muted { color:#6c757d; }
    .sticky-sidebar { position:sticky; top:100px; align-self:flex-start; }
    .container-flex { display:flex; gap:40px; align-items:flex-start; max-width:1200px; margin:100px auto 0; padding:40px 20px; }
    .debug { font-size:12px; color:#666; margin-top:8px; }
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
          <li><a href="team.html">Our Team</a></li>
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

  <main class="section-padding container-flex">
    <!-- Cars -->
    <section style="flex:2;">
      <h2 class="section-heading">Off-Market Vehicles — Exclusive Listings</h2>
      <p class="intro-text" style="margin-bottom:30px;">Exclusive private listings available to Admin/VC only.</p>

      <?php if (empty($cars)): ?>
        <div class="muted" style="font-size:1rem; padding:16px; background:#f8f9fa; border:1px solid #eee; border-radius:6px;">
          No current off-market vehicles.
        </div>
      <?php else: ?>
        <div class="featured-grid" id="car-list">
          <?php foreach ($cars as $car): ?>
            <?php
              $imageStmt = $pdo->prepare("SELECT image_path FROM car_images WHERE car_id = ? LIMIT 1");
              $imageStmt->execute([$car['id']]);
              $image = $imageStmt->fetchColumn();
            ?>
            <div class="car-card">
              <div class="exclusive-badge">EXCLUSIVE</div>
              <div class="card-image">
                <a href="car_details.php?id=<?php echo (int)$car['id']; ?>&mode=offmarket">
                  <img src="<?php echo htmlspecialchars($image ?: 'img/default-car.jpg'); ?>" alt="<?php echo htmlspecialchars(($car['name'] ?? '') . ' ' . ($car['model'] ?? '')); ?>">
                </a>
              </div>
              <div class="card-details">
                <h3 class="card-title">
                  <a href="car_details.php?id=<?php echo (int)$car['id']; ?>&mode=offmarket" style="color:inherit; text-decoration:none;">
                    <?php echo htmlspecialchars(($car['name'] ?? '') . ' ' . ($car['model'] ?? '')); ?>
                  </a>
                </h3>
                <p class="card-price">€<?php echo number_format((float)$car['price']); ?></p>
                <p class="card-description"><?php echo htmlspecialchars($car['description'] ?? ''); ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if ($debug): ?>
        <div class="debug">debug: role=<?php echo htmlspecialchars($userRole); ?>, offmarket_count=<?php echo count($cars); ?></div>
      <?php endif; ?>
    </section>

    <!-- Conditional Inquiry Sidebar (only when cars exist) -->
    <?php if (!empty($cars)): ?>
    <aside class="sticky-sidebar" style="flex:1; background:#f8f9fa; padding:30px; border-radius:5px; box-shadow:0 5px 15px rgba(0,0,0,0.1);">
      <h3 style="margin-bottom:20px; color: var(--primary-color);">Interested in an Off-Market Vehicle?</h3>
      <p style="margin-bottom:20px; font-size:0.95rem;">Select a vehicle and send a private inquiry.</p>
      <form action="php/offmarket_inquiry.php" method="POST">
        <input type="hidden" name="user_id" value="<?php echo (int)$_SESSION['user_id']; ?>">
        <div class="form-group">
          <label for="vehicle" class="form-label">Vehicle of Interest</label>
          <select id="vehicle" name="car_id" class="form-input" required>
            <option value="">Select a vehicle</option>
            <?php foreach ($cars as $car): ?>
              <option value="<?php echo (int)$car['id']; ?>">
                <?php echo htmlspecialchars(($car['name'] ?? '') . ' ' . ($car['model'] ?? '')); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="message" class="form-label">Your Inquiry</label>
          <textarea id="message" name="message" class="form-textarea" rows="5" required></textarea>
        </div>
        <div class="form-group">
          <label for="investment_type" class="form-label">Investment Type</label>
          <select id="investment_type" name="investment_type" class="form-input" required>
            <option value="">Select Investment Type</option>
            <option value="personal">Personal Collection</option>
            <option value="fund">Investment Fund</option>
            <option value="group">Group Investment</option>
            <option value="resale">Purchase for Resale</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;">Submit Private Inquiry</button>
      </form>
    </aside>
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

  <script>
    window.addEventListener('scroll', function() {
      const header = document.getElementById('header');
      if (window.scrollY > 50) header.classList.add('scrolled'); else header.classList.remove('scrolled');
    });
    document.querySelector('.mobile-menu-btn').addEventListener('click', function() {
      document.getElementById('nav-menu').classList.toggle('active');
    });
  </script>
</body>
</html>
