<?php
// offmarket_inquiries_admin.php
session_start();
require_once 'php/db.php';
require_once 'php/session_check.php'; // sets $isLoggedIn, $isAdmin, $isVC

// Allow admin (add VC too if you want)
if (!$isLoggedIn || !$isAdmin) {
    http_response_code(403);
    echo "Access denied.";
    exit;
}

// These must match your table ENUM exactly
$offStatuses = ['pending','reviewed','closed'];

// Handle inline status updates (POST to THIS PAGE)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id  = isset($_POST['update_off_id']) ? (int)$_POST['update_off_id'] : 0;
    $new = trim($_POST['new_status_off'] ?? '');

    if ($id <= 0) {
        header("Location: offmarket_inquiries_admin.php?err=bad_id");
        exit;
    }
    if (!in_array($new, $offStatuses, true)) {
        header("Location: offmarket_inquiries_admin.php?err=bad_status");
        exit;
    }

    try {
        $u = $pdo->prepare("UPDATE offmarket_inquiries SET status = ?, updated_at = NOW() WHERE id = ?");
        $u->execute([$new, $id]);

        // optional: verify a row changed
        // if ($u->rowCount() === 0) { header("Location: offmarket_inquiries_admin.php?err=not_updated"); exit; }

        header("Location: offmarket_inquiries_admin.php?updated=1");
        exit;
    } catch (PDOException $e) {
        header("Location: offmarket_inquiries_admin.php?err=db");
        exit;
    }
}

// Fetch list
$stmt = $pdo->query("
    SELECT oi.*, u.name AS user_name, u.email
    FROM offmarket_inquiries oi
    JOIN users u ON oi.user_id = u.id
    ORDER BY oi.created_at DESC
");
$inquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Off-Market Inquiries | NordLion International</title>
  <link rel="icon" type="image/x-icon" href="img/logo-2.png">
  <link rel="stylesheet" href="css/onmarket.css" />
  <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:wght@400;600&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
  <style>
    .status-badge{display:inline-block;padding:4px 10px;border-radius:999px;font-size:.85rem;font-weight:700}
    .s-pending{background:#fff3cd;color:#7a5c00}
    .s-reviewed{background:#e7f1ff;color:#0a3c78}
    .s-closed{background:#ececec;color:#333}
    .request-card .card-details p{margin:.25rem 0;}
    .card-actions{display:flex;gap:8px;align-items:center;margin-top:10px}
    .card-actions select{padding:6px;border:1px solid #ddd;border-radius:6px}
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
        <li><a href="team.php">Our Team</a></li>
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

<main class="section-padding" style="max-width:1000px;margin:100px auto 0;padding:40px 20px;">
  <h1 class="section-heading">Off-Market Inquiries</h1>
  <p class="subtitle" style="text-align:center; margin-bottom:30px;">Review exclusive off-market inquiries. Update statuses inline.</p>

  <div class="featured-grid">
    <?php if (!$inquiries): ?>
      <div class="car-card request-card"><div class="card-details"><h3 class="card-title">No off-market inquiries yet.</h3></div></div>
    <?php else: ?>
      <?php foreach ($inquiries as $i): ?>
        <div class="car-card request-card">
          <div class="card-details">
            <h3 class="card-title"><?php echo h($i['vehicle']); ?></h3>
            <p class="meta">From: <?php echo h($i['user_name']); ?> (<?php echo h($i['email']); ?>)</p>
            <p>Investment Type: <?php echo h($i['investment_type']); ?></p>
            <p><?php echo nl2br(h($i['message'])); ?></p>
            <div style="margin-top:8px;">
              <span class="status-badge s-<?php echo h($i['status']); ?>"><?php echo h(ucwords($i['status'])); ?></span>
              <span class="meta" style="margin-left:8px;">Created: <?php echo h($i['created_at']); ?></span>
            </div>

            <div class="card-actions">
              <form method="post" action="offmarket_inquiries_admin.php">
                <input type="hidden" name="update_off_id" value="<?php echo h($i['id']); ?>">
                <select name="new_status_off" onchange="this.form.submit()">
                  <?php foreach ($offStatuses as $s): ?>
                    <option value="<?php echo h($s); ?>" <?php echo ($i['status']===$s?'selected':''); ?>>
                      <?php echo h(ucwords($s)); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </form>
              <a class="btn-ghost" href="mailto:info@nordlion.com?subject=Off-Market%20Inquiry%20-%20<?php echo rawurlencode($i['vehicle']); ?>">Email</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
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
</body>
</html>
