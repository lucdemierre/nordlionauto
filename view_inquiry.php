<?php
// view_inquiry.php — detail page for a single row in `inquiries`
session_start();
require_once 'php/db.php';
require_once 'php/session_check.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userRole   = $isLoggedIn ? ($_SESSION['user_role'] ?? '') : '';
if (!$isLoggedIn || $userRole !== 'vc') {
  header("Location: login.html");
  exit;
}

$uid = (int)$_SESSION['user_id'];
$id  = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
  http_response_code(400);
  echo "Invalid inquiry id.";
  exit;
}

function h($s){ return htmlspecialchars((string)($s ?? ''), ENT_QUOTES, 'UTF-8'); }
function clean_title($title){ return preg_replace('/^\s*inquiry\s*:\s*/i', '', (string)$title); }
function badgeClass($status){
  $s = strtolower((string)$status);
  $map = [
    'new'         => 'badge-new',
    'pending'     => 'badge-pending',
    'in_progress' => 'badge-progress',
    'reviewed'    => 'badge-progress',
    'fulfilled'   => 'badge-resolved',
    'resolved'    => 'badge-resolved',
    'rejected'    => 'badge-closed',
    'closed'      => 'badge-closed',
  ];
  return $map[$s] ?? 'badge-muted';
}
function load_image_pool($dir = 'img'){
  $files = glob($dir.'/*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE);
  $pool = [];
  foreach ($files as $f){
    $b = strtolower(basename($f));
    if (strpos($b,'linkedin') !== false) continue;
    if (strpos($b,'insta') !== false || strpos($b,'instagram') !== false) continue;
    if (strpos($b,'logo') !== false) continue;
    if (preg_match('/^luc/i', $b)) continue;
    if (preg_match('/wallpaper-(218|114|113)\b/i', $b)) continue;
    if (strpos($b,'pagani') !== false) continue;
    $pool[] = $f;
  }
  return $pool;
}
function pick_image(array $pool, $seed){
  if (empty($pool)) return 'img/hp-img.jpg';
  $hash = crc32((string)$seed);
  return $pool[$hash % count($pool)];
}
$imagePool = load_image_pool('img');

/* -------- Fetch inquiry (must belong to this VC) -------- */
try {
  $stmt = $pdo->prepare("
    SELECT id, user_id, type, subject, message, status, created_at, updated_at, car_id
    FROM inquiries
    WHERE id = ? AND user_id = ?
    LIMIT 1
  ");
  $stmt->execute([$id, $uid]);
  $inq = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  $inq = false;
}

if (!$inq) {
  http_response_code(404);
  echo "Inquiry not found.";
  exit;
}

/* -------- If linked to a car, fetch basic car info + first image -------- */
$car = null;
$carImg = null;
if (!empty($inq['car_id'])) {
  try {
    $c = $pdo->prepare("SELECT id, name, model, price, status FROM cars WHERE id = ? LIMIT 1");
    $c->execute([(int)$inq['car_id']]);
    $car = $c->fetch(PDO::FETCH_ASSOC);
    if ($car) {
      $ci = $pdo->prepare("SELECT image_path FROM car_images WHERE car_id = ? LIMIT 1");
      $ci->execute([$car['id']]);
      $carImg = $ci->fetchColumn() ?: null;
    }
  } catch (Exception $e) { /* ignore */ }
}

/* -------- Presentation helpers -------- */
$title     = clean_title($inq['subject'] ?: ucfirst($inq['type']).' Inquiry');
$typeLabel = ucfirst((string)$inq['type']);
$status    = (string)$inq['status'];
$datePretty= $inq['created_at'] ? date('d M Y', strtotime($inq['created_at'])) : '';
$imgHero   = $carImg ?: pick_image($imagePool, 'inq-'.$inq['id'].'-'.$inq['type']);
$bodyHtml  = nl2br(h($inq['message']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= h($title) ?> | NordLion International</title>
  <link rel="icon" type="image/x-icon" href="img/logo-2.png">
  <link rel="stylesheet" href="css/vc_inquiry.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:wght@400;500;600;700&family=Lato:wght@300;400;500;700&display=swap" rel="stylesheet">
  <style>
    .about-hero{
      background:
        linear-gradient(rgba(15,44,89,.75), rgba(15,44,89,.75)),
        url("img/image (2).jpeg");
      background-size: cover; background-position: center 55%;
      color:#fff; text-align:center; padding:160px 0 100px; margin-top:80px;
    }
    .view-wrap{ max-width: 1100px; margin: 24px auto 80px; padding: 0 20px; }
    .card{
      display:grid; grid-template-columns: 1.2fr 1.8fr; gap:24px;
      background:#fff; border:1px solid rgba(0,0,0,.08); border-radius:16px; overflow:hidden;
      box-shadow: 0 8px 28px rgba(0,0,0,.08);
    }
    .card .media{ background:#000; min-height: 320px; }
    .card .media img{ width:100%; height:100%; object-fit:cover; display:block; }
    .card .body{ padding:22px; }
    .breadcrumbs{ margin: 10px 0 18px; font-size: 14px; }
    .breadcrumbs a{ color:#0F2C59; text-decoration:none; }
    h1.title{ margin:0 0 8px; font-size: 28px; color:#0F2C59; }
    .meta{ display:flex; gap:8px; flex-wrap:wrap; align-items:center; margin-bottom: 10px; }
    .badge{ font-size:12px; font-weight:700; text-transform:uppercase; padding:6px 8px; border-radius:999px; background:#eef2f7; border:1px solid rgba(0,0,0,.08); color:#0f172a; }
    .badge-new{ background:#ecfeff; color:#0e7490; border-color:#a5f3fc; }
    .badge-pending{ background:#f1f5f9; color:#475569; border-color:#cbd5e1; }
    .badge-progress{ background:#fff7ed; color:#b45309; border-color:#fed7aa; }
    .badge-resolved{ background:#ecfdf5; color:#047857; border-color:#a7f3d0; }
    .badge-closed{ background:#f5f3ff; color:#6d28d9; border-color:#ddd6fe; }
    .subtle{ color:#6b7280; font-size: 13px; }
    .divider{ height:1px; background:rgba(0,0,0,.06); margin:16px 0; }
    .kv{ margin: 6px 0; }
    .kv .k{ font-weight:700; color:#0f172a; }
    .actions{ margin-top: 16px; display:flex; gap:10px; }
    .btn{ display:inline-block; padding:10px 14px; border-radius: 10px; text-decoration:none; font-weight:700; }
    .btn-primary{ background:#0F2C59; color:#fff; }
    .btn-outline{ border:1px solid rgba(0,0,0,.2); color:#0f172a; background:#fff; }
    @media (max-width: 900px){
      .card{ grid-template-columns: 1fr; }
      .card .media{ min-height: 220px; }
    }
  </style>
</head>
<body>
  <!-- Header -->
  <header id="header">
    <div class="header-container">
      <a href="index.php" class="logo">
        <img src="img/logo-2.png" alt="NordLion Logo">
        <span class="logo-text">NordLion International</span>
      </a>
      <nav>
        <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle menu">&#9776;</button>
        <ul id="nav-menu">
          <li><a href="index.php">Home</a></li>
          <li><a href="onmarket.php">On-Market</a></li>
          <li><a href="offmarket.php">Off-Market</a></li>
          <li><a href="contact.php">Contact</a></li>
          <?php if ($isLoggedIn): ?><li><a href="logout.php">Logout</a></li><?php else: ?><li><a href="login.html">Login</a></li><?php endif; ?>
        </ul>
      </nav>
    </div>
  </header>

  <!-- Hero -->
  <section class="about-hero">
    <div class="container">
      <h1>Inquiry</h1>
      <p class="subtitle">Details & context</p>
    </div>
  </section>

  <!-- Content -->
  <main class="view-wrap">
    <div class="breadcrumbs">
      <a href="vc_inquiries.php">&larr; Back to all inquiries</a>
    </div>

    <article class="card">
      <div class="media">
        <img src="<?= h($imgHero) ?>" alt="">
      </div>
      <div class="body">
        <div class="meta">
          <span class="badge">Inquiry</span>
          <span class="badge">Type: <?= h($typeLabel) ?></span>
          <span class="badge <?= h(badgeClass($status)) ?>"><?= h(ucwords(str_replace('_',' ',$status))) ?></span>
          <?php if ($datePretty): ?><span class="subtle">📅 <?= h($datePretty) ?></span><?php endif; ?>
        </div>

        <h1 class="title"><?= h($title) ?></h1>

        <?php if ($car): ?>
          <div class="kv">
            <span class="k">Related vehicle:</span>
            <a href="car_details.php?id=<?= (int)$car['id'] ?>" target="_blank" rel="noopener">
              <?= h(trim(($car['name'] ?? '').' '.($car['model'] ?? ''))) ?>
            </a>
            <?php if (!empty($car['price'])): ?>
              <span class="subtle"> — €<?= number_format((float)$car['price']) ?></span>
            <?php endif; ?>
          </div>
        <?php elseif (!empty($inq['car_id'])): ?>
          <div class="kv"><span class="k">Related vehicle ID:</span> <?= (int)$inq['car_id'] ?></div>
        <?php endif; ?>

        <div class="divider"></div>

        <div class="kv"><span class="k">Message:</span></div>
        <p style="margin:8px 0 0; line-height:1.7;"><?= $bodyHtml ?></p>

        <div class="actions">
          <a class="btn btn-primary" href="vc_inquiries.php">Back</a>
          <?php if ($car): ?>
            <a class="btn btn-outline" href="car_details.php?id=<?= (int)$car['id'] ?>" target="_blank" rel="noopener">Open Vehicle</a>
          <?php endif; ?>
        </div>
      </div>
    </article>
  </main>

  <!-- Footer -->
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
    // Header scroll
    const header = document.getElementById('header');
    const onScroll = () => { if (window.scrollY > 10) header.classList.add('scrolled'); else header.classList.remove('scrolled'); };
    document.addEventListener('scroll', onScroll); onScroll();

    // Mobile menu
    const btn = document.getElementById('mobileMenuBtn');
    const menu = document.getElementById('nav-menu');
    if (btn && menu) btn.addEventListener('click', () => menu.classList.toggle('active'));
  </script>
</body>
</html>
