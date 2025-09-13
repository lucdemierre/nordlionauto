<?php
require 'php/db.php';
require_once 'php/session_check.php';

// Fetch approved cars
$stmt = $pdo->prepare("SELECT * FROM jets WHERE status = 'approved'");
$stmt->execute();
$cars = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>On-Market Vehicles | NordLion International</title>
  <link rel="icon" type="image/x-icon" href="img/logo-2.png">
  <link rel="stylesheet" href="css/onmarket.css" />
  <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:wght@400;500;600;700&family=Lato:wght@300;400;500;700&display=swap" rel="stylesheet">
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
  <style>
    .sticky-sidebar {
      position: sticky;
      top: 100px;
      align-self: flex-start;
    }
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
          <li><a href="index.php">Home</a></li>
          <li><a href="onmarket.php" class="active">Cars</a></li>
          <?php if ($isVC): ?>
          <li><a href="offmarket.php">Off-Market</a></li>
          <?php endif; ?>
          <li><a href="jets.html">Jets</a></li>
          <li><a href="about.html">About Us</a></li>
          <li><a href="team.html">Our Team</a></li>
          <li><a href="contact.php">Contact</a></li>
          <?php if ($isLoggedIn): ?>
            <?php if ($isAdmin): ?>
              <li><a href="dashboard.php">Admin Panel</a></li>
            <?php elseif ($isVC): ?>
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

  <main class="section-padding" style="display: flex; gap: 40px; align-items: flex-start; max-width: 1200px; margin: 100px auto 0; padding: 40px 20px;">
    <!-- Cars List -->
    <section style="flex: 2;">
      <h2 class="section-heading">On-Market Vehicles</h2>
      <div class="featured-grid" id="car-list">
        <?php foreach ($cars as $car): ?>
          <?php
            // Fetch first image if using car_images table
            $imageStmt = $pdo->prepare("SELECT image_path FROM car_images WHERE car_id = ? LIMIT 1");
            $imageStmt->execute([$car['id']]);
            $image = $imageStmt->fetchColumn();
          ?>
          <div class="car-card">
            <div class="card-image">
              <a href="car_details.php?id=<?php echo $car['id']; ?>">
                <img src="<?php echo htmlspecialchars($image ?: 'img/default-car.jpg'); ?>" alt="<?php echo htmlspecialchars($car['name'] . ' ' . $car['model']); ?>">
              </a>
            </div>
            <div class="card-details">
              <h3 class="card-title">
                <a href="car_details.php?id=<?php echo $car['id']; ?>" style="color: inherit; text-decoration: none;">
                  <?php echo htmlspecialchars($car['name'] . ' ' . $car['model']); ?>
                </a>
              </h3>
              <p class="card-price">€<?php echo number_format($car['price']); ?></p>
              <p class="card-description"><?php echo htmlspecialchars($car['description']); ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- Contact Sidebar -->
    <aside class="sticky-sidebar" style="flex: 1; background-color: #f8f9fa; padding: 30px; border-radius: 5px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
      <h3 style="margin-bottom: 20px; color: var(--primary-color);">Interested in a Vehicle?</h3>
      <p style="margin-bottom: 20px; font-size: 0.95rem;">Use this quick form to inquire about any listed vehicle. A NordLion representative will get in touch with you privately.</p>
      <form action="php/car_inquiry.php" method="POST">
        <?php if ($isLoggedIn): ?>
          <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
        <?php else: ?>
        <div class="form-group">
          <label for="name" class="form-label">Full Name</label>
          <input type="text" id="name" name="name" class="form-input" required>
        </div>
        <div class="form-group">
          <label for="email" class="form-label">Email</label>
          <input type="email" id="email" name="email" class="form-input" required>
        </div>
        <?php endif; ?>
        <div class="form-group">
          <label for="vehicle" class="form-label">Vehicle of Interest</label>
          <select id="vehicle" name="vehicle" class="form-input" required>
            <option value="">Select a vehicle</option>
            <?php foreach ($cars as $car): ?>
              <option value="<?php echo htmlspecialchars($car['id']); ?>">
                <?php echo htmlspecialchars($car['name'] . ' ' . $car['model']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="message" class="form-label">Your Message</label>
          <textarea id="message" name="message" class="form-textarea" rows="4" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%;">Send Inquiry</button>
      </form>
    </aside>
  </main>

  <footer class="footer" style="background-color: #0F2C59;">
        <div class="container">
        <div class="footer-container">
            <div class="footer-brand">
            <div class="footer-logo">
                <div class="social-icons">
                <a href="https://www.instagram.com/the_nordlion_international/" target="_blank"><img src="img/insta.png" alt="Instagram"></a>
                <a href="https://www.linkedin.com/company/nordlion-international/" target="_blank"><img src="img/linkedin.png" alt="LinkedIn"></a>
                </div>
                <img src="img/logo-2.png" alt="NordLion Logo">
                <span class="footer-logo-text">NordLion International</span>
            </div>
            <p class="footer-text">Excellence in luxury vehicle brokerage</p>
            </div>

            <div class="footer-links">
            <h4 class="footer-heading">Quick Links</h4>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="onmarket.php">Cars</a></li>
                <li><a href="offmarket.php">Off Market</a></li>
                <li><a href="about.php">About Us</a></li>
                <li><a href="contact.php">Contact</a></li>
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
                <li><a href="#">Terms of Service</a></li>
                <li><a href="#">Cookie Policy</a></li>
            </ul>
            </div>
        </div>

        <div class="copyright">
            <p>&copy; 2025 NordLion International. All rights reserved.</p>
        </div>
        </div>
    </footer>

  <script>
    window.addEventListener('scroll', function () {
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
