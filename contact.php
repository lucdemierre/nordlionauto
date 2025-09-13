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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Contact Us - NordLion International</title>
  <link rel="icon" type="image/x-icon" href="img/logo-2.png">
  <link rel="stylesheet" href="css/contact.css">
  <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:wght@400;600&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
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

<main>
  <section class="about-hero">
    <div class="container">
      <h1>Contact Us</h1>
      <p class="subtitle">Get in Touch with Our Team</p>
    </div>
  </section>

  <section class="contact section-padding">
    <div class="container">

      <?php if ($isLoggedIn): ?>
        <div class="contact-container">
          <div class="contact-info">
            <h3 class="contact-heading">Get In Touch</h3>
            <p class="contact-text">
              Interested in our services or have a specific vehicle in mind?
              Our team is ready to assist you with any inquiries.
            </p>

            <div class="contact-item">
              <span class="contact-icon">📍</span>
              <span class="contact-detail">London, United Kingdom — Singapore, Singapore — Turku, Finland</span>
            </div>
            <div class="contact-item">
              <span class="contact-icon">📱</span>
              <span class="contact-detail">+44 7947 977474</span>
            </div>
            <div class="contact-item">
              <span class="contact-icon">✉️</span>
              <span class="contact-detail">lucdemierre@hotmail.com — eliel.valkama@gmail.com</span>
            </div>
            <div class="contact-item">
              <span class="contact-icon">⏰</span>
              <span class="contact-detail">Mon–Sat: 4:00 PM – 10:00 PM GMT</span>
            </div>
          </div>

          <div class="contact-form">
            <form id="inquiry-form" action="contact.php" method="POST" novalidate>
              <?php if ($success_message): ?>
                <div class="alert alert-success" style="background:#d4edda;color:#155724;padding:15px;border-radius:4px;margin-bottom:20px;">
                  <?php echo htmlspecialchars($success_message); ?>
                </div>
              <?php endif; ?>
              <?php if ($error_message): ?>
                <div class="alert alert-danger" style="background:#f8d7da;color:#721c24;padding:15px;border-radius:4px;margin-bottom:20px;">
                  <?php echo htmlspecialchars($error_message); ?>
                </div>
              <?php endif; ?>

              <div class="form-group">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" id="name" name="name" required
                       value="<?php echo htmlspecialchars($userName); ?>">
              </div>

              <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" id="email" name="email" required
                       value="<?php echo htmlspecialchars($email); ?>">
              </div>

              <div class="form-group">
                <label for="inquiry_type" class="form-label">Inquiry Type</label>
                <select id="inquiry_type" name="inquiry_type" class="form-input" required>
                  <option value="">Select an inquiry type</option>
                  <?php foreach ($INQUIRY_TYPES as $t): ?>
                    <option value="<?php echo htmlspecialchars($t['value']); ?>">
                      <?php echo htmlspecialchars($t['label']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="form-group">
                <label for="message" class="form-label">Message</label>
                <textarea id="message" name="message" class="form-textarea" required></textarea>
              </div>

              <button type="submit" class="btn btn-primary">Send Message</button>
            </form>
          </div>
        </div>
      <?php else: ?>
        <div class="contact-center">
          <h3>Get In Touch</h3>
          <p>Interested in our services or have a specific vehicle in mind? Our team is ready to assist you with any inquiries.</p>
          <div class="contact-items">
            <div class="centered-item"><span class="icon">📍</span>London, United Kingdom — Singapore, Singapore — Turku, Finland</div>
            <div class="centered-item"><span class="icon">📱</span>+44 7947 977474</div>
            <div class="centered-item"><span class="icon">✉️</span>lucdemierre@hotmail.com — eliel.valkama@gmail.com</div>
            <div class="centered-item"><span class="icon">⏰</span>Mon–Sat: 4:00 PM – 10:00 PM GMT</div>
          </div>
        </div>
      <?php endif; ?>

      <!-- Offices -->
      <div class="office-locations" style="margin-top:80px;">
        <h2 class="section-heading">Our Offices</h2>
        <div class="locations-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:30px;margin-top:40px;">
          <div class="location-card" style="background:#fff;padding:30px;border-radius:10px;box-shadow:var(--box-shadow);">
            <h3 style="color:var(--primary-color);margin-bottom:15px;">London Office</h3>
            <p style="margin-bottom:10px;">Kensington, London</p>
            <p style="margin-bottom:10px;">United Kingdom</p>
            <p style="color:var(--secondary-color);">+44 7947 977474</p>
          </div>
          <div class="location-card" style="background:#fff;padding:30px;border-radius:10px;box-shadow:var(--box-shadow);">
            <h3 style="color:var(--primary-color);margin-bottom:15px;">Finland Office</h3>
            <p style="margin-bottom:10px;">Turku</p>
            <p style="margin-bottom:10px;">Finland</p>
            <p style="color:var(--secondary-color);">+358 40 0186049</p>
          </div>
          <div class="location-card" style="background:#fff;padding:30px;border-radius:10px;box-shadow:var(--box-shadow);">
            <h3 style="color:var(--primary-color);margin-bottom:15px;">Singapore Office</h3>
            <p style="margin-bottom:10px;">Singapore</p>
            <p style="margin-bottom:10px;">Singapore</p>
            <p style="color:var(--secondary-color);">+65 8333 0905 (Whatsapp)</p>
          </div>
        </div>
      </div>

      <!-- Hours -->
      <div class="business-hours" style="margin-top:80px;text-align:center;">
        <h2 class="section-heading">Business Hours</h2>
        <div class="hours-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:30px;margin-top:40px;">
          <div class="hours-card" style="background:#fff;padding:30px;border-radius:10px;box-shadow:var(--box-shadow);">
            <h3 style="color:var(--primary-color);margin-bottom:15px;">London Office</h3>
            <p>Monday - Friday: 4:00 PM - 10:00 PM GMT</p>
            <p>Saturday: By Appointment</p>
            <p>Sunday: Closed</p>
          </div>
          <div class="hours-card" style="background:#fff;padding:30px;border-radius:10px;box-shadow:var(--box-shadow);">
            <h3 style="color:var(--primary-color);margin-bottom:15px;">Finland Office</h3>
            <p>Monday - Friday: 4:00 AM - 10:00 PM CET</p>
            <p>Saturday: By Appointment</p>
            <p>Sunday: Closed</p>
          </div>
          <div class="hours-card" style="background:#fff;padding:30px;border-radius:10px;box-shadow:var(--box-shadow);">
            <h3 style="color:var(--primary-color);margin-bottom:15px;">Singapore Office</h3>
            <p>Monday - Friday: 9:00 AM - 10:00 PM GST</p>
            <p>Saturday: 12:00 PM - 2:00 AM GST</p>
            <p>Sunday: Closed</p>
          </div>
        </div>
      </div>

      <!-- FAQ -->
      <div class="faq-section" style="margin-top:80px;">
        <h2 class="section-heading">Frequently Asked Questions</h2>
        <div class="faq-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:30px;margin-top:40px;">
          <div class="faq-item" style="background:#fff;padding:30px;border-radius:10px;box-shadow:var(--box-shadow);">
            <h3 style="color:var(--primary-color);margin-bottom:15px;">How quickly can I expect a response?</h3>
            <p>We typically respond within 24 hours during business days. For urgent matters, please call our office.</p>
          </div>
          <div class="faq-item" style="background:#fff;padding:30px;border-radius:10px;box-shadow:var(--box-shadow);">
            <h3 style="color:var(--primary-color);margin-bottom:15px;">Do you offer virtual consultations?</h3>
            <p>Yes, we offer virtual consultations via video call for clients who prefer remote meetings or are in different time zones.</p>
          </div>
          <div class="faq-item" style="background:#fff;padding:30px;border-radius:10px;box-shadow:var(--box-shadow);">
            <h3 style="color:var(--primary-color);margin-bottom:15px;">What information should I prepare?</h3>
            <p>Please have your contact details and any specifics about the vehicle or service you're interested in.</p>
          </div>
        </div>
      </div>

    </div>
  </section>
</main>

<footer class="footer" style="background-color:#0F2C59;">
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
// Header scroll effect
window.addEventListener('scroll', function () {
  const header = document.getElementById('header');
  if (window.scrollY > 50) header.classList.add('scrolled');
  else header.classList.remove('scrolled');
});
// Mobile menu
document.querySelector('.mobile-menu-btn')?.addEventListener('click', () => {
  document.getElementById('nav-menu')?.classList.toggle('active');
});
</script>
</body>
</html>
