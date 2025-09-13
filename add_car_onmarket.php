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

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'vc', 'mop'])) {
    header("Location: login.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Car to On-Market | NordLion International</title>
  <link rel="icon" href="img/logo-2.png" type="image/x-icon">
  <link rel="stylesheet" href="css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:wght@400;500;600;700&family=Lato:wght@300;400;500;700&display=swap" rel="stylesheet">
  <style>
    .image-entry {
      border: 1px solid #ccc;
      padding: 15px;
      margin-bottom: 15px;
      position: relative;
      border-radius: 6px;
    }

    .image-entry textarea {
      width: 100%;
      margin-top: 10px;
    }

    .image-entry .remove-btn {
      position: absolute;
      top: 8px;
      right: 8px;
      background: transparent;
      border: none;
      font-size: 1.2rem;
      cursor: pointer;
      color: #c00;
    }

    .form-group label {
      display: block;
      margin-bottom: 6px;
      font-weight: 600;
    }

    .form-group input, .form-group textarea {
      width: 100%;
      padding: 8px;
      font-size: 1rem;
      margin-bottom: 15px;
      border-radius: 4px;
      border: 1px solid #ccc;
    }

    .btn-secondary {
      background-color: #eee;
      border: 1px solid #aaa;
      padding: 8px 12px;
      border-radius: 4px;
      cursor: pointer;
    }

    .btn-secondary:hover {
      background-color: #ddd;
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

  <main class="section-padding" style="max-width: 800px; margin: auto;">
    <h1 class="section-heading">Add Car to On-Market</h1>
    <form action="php/submit_car.php" method="POST" enctype="multipart/form-data" class="form">
      <div class="form-group">
        <label for="name">Car Make</label>
        <input type="text" name="name" id="name" required>
      </div>

      <div class="form-group">
        <label for="model">Model</label>
        <input type="text" name="model" id="model" required>
      </div>

      <div class="form-group">
        <label for="price">Price (€)</label>
        <input type="number" name="price" id="price" required>
      </div>

      <div class="form-group">
        <label for="year">Year</label>
        <input type="number" name="year" id="year" required>
      </div>

      <div class="form-group">
        <label for="mileage">Mileage (km)</label>
        <input type="number" name="mileage" id="mileage" required>
      </div>

      <div class="form-group">
        <label for="int_colour">Interior Colour</label>
        <input type="text" name="int_colour" id="int_colour" required>
      </div>

      <div class="form-group">
        <label for="ext_colour">Exterior Colour</label>
        <input type="text" name="ext_colour" id="ext_colour" required>
      </div>

      <div class="form-group">
        <label for="description">Description</label>
        <textarea name="description" id="description" rows="5" required></textarea>
      </div>

      <div class="form-group">
        <label>Upload Images & Captions</label>
        <div id="image-list"></div>
        <button type="button" onclick="addImageInput()" class="btn-secondary">Add Image</button>
      </div>

      <button type="submit" class="btn btn-primary">Submit Car</button>
    </form>
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
    function addImageInput() {
      const container = document.getElementById('image-list');

      const wrapper = document.createElement('div');
      wrapper.className = 'image-entry';

      const fileInput = document.createElement('input');
      fileInput.type = 'file';
      fileInput.name = 'images[]';
      fileInput.accept = 'image/*';
      fileInput.required = true;

      const caption = document.createElement('textarea');
      caption.name = 'captions[]';
      caption.rows = 2;
      caption.placeholder = 'Enter caption for this image';
      caption.required = true;

      const removeBtn = document.createElement('button');
      removeBtn.type = 'button';
      removeBtn.className = 'remove-btn';
      removeBtn.innerText = '❌';
      removeBtn.onclick = () => wrapper.remove();

      wrapper.appendChild(fileInput);
      wrapper.appendChild(caption);
      wrapper.appendChild(removeBtn);

      container.appendChild(wrapper);
    }
  </script>
</body>
</html>
