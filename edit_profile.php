<?php
// edit_profile.php
session_start();
require_once 'php/db.php';
require_once 'php/session_check.php';

if (!$isLoggedIn) {
    header("Location: login.html");
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($name && $email) {
        if ($password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
            $stmt->execute([$name, $email, $hash, $user_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $stmt->execute([$name, $email, $user_id]);
        }
        $success = "Profile updated successfully.";
    } else {
        $error = "Name and Email cannot be empty.";
    }
}

// Fetch current info
$stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    echo "User not found.";
    exit;
}

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Profile | NordLion International</title>
  <link rel="icon" type="image/x-icon" href="img/logo-2.png">
  <link rel="stylesheet" href="css/onmarket.css">
  <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:wght@400;600&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
  <style>
    .form-group label { display:block; font-weight:600; margin-bottom:6px; }
    .form-group input { width:100%; padding:10px; border:1px solid #ccc; border-radius:6px; margin-bottom:15px; }
    .form-feedback { margin-bottom:20px; font-weight:600; }
    .form-feedback.success { color:green; }
    .form-feedback.error { color:#c00; }
    main { max-width:600px; margin:100px auto; padding:40px 20px; background:#fff; border-radius:8px; box-shadow:0 4px 10px rgba(0,0,0,0.05); }
    h1.section-heading { text-align:center; margin-bottom:20px; }
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
        <li><a href="onmarket.php">Cars</a></li>
        <?php if ($isVC): ?><li><a href="offmarket.php">Off-Market</a></li><?php endif; ?>
        <li><a href="offmarket.php">Off Market</a></li>
        <li><a href="about.php">About Us</a></li>
        <li><a href="team.php">Team</a></li>
        <li><a href="contact.php">Contact</a></li>
        <?php if ($isLoggedIn): ?>
          <?php if ($isAdmin): ?><li><a href="dashboard.php">Admin Panel</a></li>
          <?php elseif ($isVC): ?><li><a href="vc_dashboard.php">VC Panel</a></li><?php endif; ?>
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
  <h1 class="section-heading">Edit Profile</h1>

  <?php if (!empty($success)): ?>
    <p class="form-feedback success"><?php echo h($success); ?></p>
  <?php endif; ?>
  <?php if (!empty($error)): ?>
    <p class="form-feedback error"><?php echo h($error); ?></p>
  <?php endif; ?>

  <form method="post" action="edit_profile.php">
    <div class="form-group">
      <label for="name">Full Name</label>
      <input type="text" name="name" id="name" value="<?php echo h($user['name']); ?>" required>
    </div>

    <div class="form-group">
      <label for="email">Email Address</label>
      <input type="email" name="email" id="email" value="<?php echo h($user['email']); ?>" required>
    </div>

    <div class="form-group">
      <label for="password">New Password (leave blank to keep current)</label>
      <input type="password" name="password" id="password">
    </div>

    <button type="submit" class="btn btn-primary" style="width:100%;">Save Changes</button>
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
  window.addEventListener('scroll', function () {
    const header = document.getElementById('header');
    if (window.scrollY > 50) header.classList.add('scrolled');
    else header.classList.remove('scrolled');
  });
</script>
</body>
</html>
