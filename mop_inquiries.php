<?php
// mop_inquiries.php — Same as VC, but for MOPs and without Off-Market
session_start();
require_once 'php/db.php';
require_once 'php/session_check.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userRole   = $isLoggedIn ? ($_SESSION['user_role'] ?? '') : '';
// Accept either 'mop' or 'mops' just in case your role string varies
if (!$isLoggedIn || !in_array($userRole, ['mop','mops'], true)) {
  header("Location: login.html");
  exit;
}

$uid = (int)$_SESSION['user_id'];

/* --------------------------- Controls --------------------------- */
$q       = trim($_GET['q'] ?? '');
$bucket  = trim($_GET['bucket'] ?? '');   // '', 'car', 'gen' (no 'off' for MOP)
$statusF = trim($_GET['status'] ?? '');
$sort    = trim($_GET['sort'] ?? 'newest');

$allowedSort = ['newest','oldest','title_az','title_za'];
if (!in_array($sort, $allowedSort, true)) $sort = 'newest';

/* --------------------------- Helpers ---------------------------- */
function h($s){ return htmlspecialchars((string)($s ?? ''), ENT_QUOTES, 'UTF-8'); }

// Strip “Inquiry:” prefix from titles (case-insensitive)
function clean_title($title){
  return preg_replace('/^\s*inquiry\s*:\s*/i', '', (string)$title);
}

// Load & filter a pool of images from /img with exclusions
function load_image_pool($dir = 'img'){
  $files = glob($dir.'/*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE);
  $pool = [];
  foreach ($files as $f){
    $base = strtolower(basename($f));
    // Exclusions: linkedin, insta/instagram, logo, verticals (218/114/113), pagani, any starting with luc
    if (strpos($base,'linkedin') !== false) continue;
    if (strpos($base,'insta') !== false || strpos($base,'instagram') !== false) continue;
    if (strpos($base,'logo') !== false) continue;
    if (preg_match('/^luc/i', $base)) continue;
    if (preg_match('/wallpaper-(218|114|113)\b/i', $base)) continue;
    if (strpos($base,'pagani') !== false) continue;
    $pool[] = $f;
  }
  return $pool;
}

// Stable pick from pool based on a seed
function pick_image(array $pool, $seed){
  if (empty($pool)) return 'img/hp-img.jpg'; // final fallback
  $hash = crc32((string)$seed);
  return $pool[$hash % count($pool)];
}

// Prefer the first image of a car when we have car_id
function car_first_image(PDO $pdo, int $car_id): ?string {
  static $cache = [];
  if ($car_id <= 0) return null;
  if (array_key_exists($car_id, $cache)) return $cache[$car_id];

  try {
    $s = $pdo->prepare("SELECT image_path FROM car_images WHERE car_id = ? LIMIT 1");
    $s->execute([$car_id]);
    $img = $s->fetchColumn();
    if ($img) {
      // normalize to a web path (support stored filenames or relative paths)
      $img = ltrim((string)$img, './');
      if (
        strpos($img, 'uploads/') !== 0 &&
        strpos($img, 'img/') !== 0 &&
        substr($img, 0, 1) !== '/'
      ) {
        $img = 'uploads/' . basename($img);
      }
      return $cache[$car_id] = $img;
    }
  } catch (Exception $e) { /* ignore; fallback below */ }

  return $cache[$car_id] = null;
}

$imagePool = load_image_pool('img');

/* ---------------------- Fetch from 2 tables --------------------- */
function safeFetch(PDO $pdo, string $sql, array $params) {
  try { $stmt = $pdo->prepare($sql); $stmt->execute($params); return $stmt->fetchAll(PDO::FETCH_ASSOC); }
  catch (Exception $e){ return []; }
}

$carRequests = safeFetch(
  $pdo,
  "SELECT id, vehicle_name, vehicle_type, budget, details, status, created_at
   FROM car_requests WHERE user_id = ? ORDER BY created_at DESC",
  [$uid]
);

$generalInquiries = safeFetch(
  $pdo,
  "SELECT id, type, subject, message, status, created_at, car_id
   FROM inquiries WHERE user_id = ? ORDER BY created_at DESC",
  [$uid]
);

// NOTE: No off-market fetch here for MOP users.

/* ---------------------- Normalize to 1 list --------------------- */
$items = [];

// car_requests (no car_id; use pool image)
foreach ($carRequests as $r) {
  $title = clean_title($r['vehicle_name'] ?: 'Car Request');
  $sub   = $r['vehicle_type'] ? ('Type: '.$r['vehicle_type']) : '';
  $msg   = (string)($r['details'] ?? '');
  if (!empty($r['budget'])) $msg = 'Budget: '.number_format((float)$r['budget'], 0).' • '.$msg;

  $items[] = [
    'id'         => (int)$r['id'],
    'bucket'     => 'car',
    'title'      => $title,
    'subtitle'   => $sub,
    'message'    => $msg,
    'status'     => (string)($r['status'] ?? ''),
    'created_at' => (string)($r['created_at'] ?? ''),
    'image'      => pick_image($imagePool, 'car-'.$r['id']),
    'open_url'   => 'view_car_request.php?id='.(int)$r['id'],
  ];
}

// inquiries (prefer car image when type=car & car_id valid)
foreach ($generalInquiries as $g) {
  $type = strtolower($g['type'] ?? 'general');
  $rawTitle = $g['subject'] ?: (ucfirst($type).' Inquiry');
  $title    = clean_title($rawTitle);
  $sub      = $type ? ('Type: '.ucfirst($type)) : '';

  // choose image
  $img = null;
  if ($type === 'car' && !empty($g['car_id'])) {
    $img = car_first_image($pdo, (int)$g['car_id']);
  }
  if (!$img) {
    $img = pick_image($imagePool, 'gen-'.$g['id'].'-'.$type);
  }

  $items[] = [
    'id'         => (int)$g['id'],
    'bucket'     => 'gen',
    'title'      => $title,
    'subtitle'   => $sub,
    'message'    => (string)($g['message'] ?? ''),
    'status'     => (string)($g['status'] ?? ''),
    'created_at' => (string)($g['created_at'] ?? ''),
    'image'      => $img,
    'open_url'   => 'view_inquiry.php?id='.(int)$g['id'],
  ];
}

/* ------------------ Filter (search/category/status) ------------- */
$norm = static function($s){ return mb_strtolower(trim((string)$s)); };

$items = array_values(array_filter($items, function($it) use ($q, $bucket, $statusF, $norm){
  if ($bucket !== '' && $it['bucket'] !== $bucket) return false; // only 'car' or 'gen'
  if ($statusF !== '' && $norm($it['status']) !== $norm($statusF)) return false;
  if ($q !== '') {
    $needle = $norm($q);
    $hay = $norm($it['title'].' '.$it['subtitle'].' '.$it['message']);
    if (mb_strpos($hay, $needle) === false) return false;
  }
  return true;
}));

/* --------------------------- Sorting ---------------------------- */
usort($items, function($a,$b) use ($sort){
  if ($sort === 'oldest')   return strtotime($a['created_at']) <=> strtotime($b['created_at']);
  if ($sort === 'title_az') return strcasecmp($a['title'], $b['title']);
  if ($sort === 'title_za') return strcasecmp($b['title'], $a['title']);
  return strtotime($b['created_at']) <=> strtotime($a['created_at']); // newest
});

/* ----------------------- Badge styling map ---------------------- */
function badgeClass($status) {
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Your Inquiries | NordLion International</title>
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
        <h1>Your Inquiries</h1>
        <p class="subtitle">All requests in one place — search, filter, and sort</p>
      </div>
    </section>

    <section class="section-padding" style="margin-top:20px;">
      <div class="container">
        <form class="toolbar" method="get" action="mop_inquiries.php">
          <div class="field" style="flex:1 1 260px;">
            🔎 <input type="text" name="q" value="<?= h($q) ?>" placeholder="Search title or message...">
          </div>
          <div class="field">
            🗂️
            <select name="bucket" aria-label="Filter by category">
              <option value="">All categories</option>
              <option value="car" <?= $bucket==='car'?'selected':'' ?>>Car Requests</option>
              <option value="gen" <?= $bucket==='gen'?'selected':'' ?>>Inquiries</option>
              <!-- No Off-Market option for MOPs -->
            </select>
          </div>
          <div class="field">
            ✅
            <select name="status" aria-label="Filter by status">
              <option value="">All status</option>
              <?php foreach (['new','pending','in_progress','reviewed','fulfilled','resolved','rejected','closed'] as $s): ?>
                <option value="<?= h($s) ?>" <?= strtolower($statusF)===$s?'selected':'' ?>><?= h(ucwords(str_replace('_',' ',$s))) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            ↕️
            <select name="sort" aria-label="Sort">
              <option value="newest"   <?= $sort==='newest'?'selected':'' ?>>Newest first</option>
              <option value="oldest"   <?= $sort==='oldest'?'selected':'' ?>>Oldest first</option>
              <option value="title_az" <?= $sort==='title_az'?'selected':'' ?>>Title A–Z</option>
              <option value="title_za" <?= $sort==='title_za'?'selected':'' ?>>Title Z–A</option>
            </select>
          </div>
          <button class="btn" type="submit">Apply</button>
          <?php if ($q || $bucket || $statusF || $sort!=='newest'): ?>
            <a class="btn secondary" href="mop_inquiries.php">Reset</a>
          <?php endif; ?>
        </form>

        <?php if (empty($items)): ?>
          <p style="text-align:center; margin-top:26px;">No inquiries match your filters.</p>
        <?php else: ?>
          <div class="grid">
            <?php foreach ($items as $it):
              $datePretty = $it['created_at'] ? date('d M Y', strtotime($it['created_at'])) : '';
              $bucketLabel = ['car'=>'Car Request','gen'=>'Inquiry'][$it['bucket']] ?? 'Inquiry';
              $excerpt = trim(mb_substr($it['message'] ?? '', 0, 180));
            ?>
              <article class="card">
                <img class="thumb" src="<?= h($it['image']) ?>" alt="">
                <div class="body">
                  <div class="meta">
                    <span class="badge"><?= h($bucketLabel) ?></span>
                    <?php if (!empty($it['subtitle'])): ?><span class="badge"><?= h($it['subtitle']) ?></span><?php endif; ?>
                    <?php if (!empty($it['status'])): ?><span class="badge <?= h(badgeClass($it['status'])) ?>"><?= h(ucwords(str_replace('_',' ', $it['status']))) ?></span><?php endif; ?>
                    <span class="subtle">📅 <?= h($datePretty) ?></span>
                  </div>
                  <h3 class="title"><?= h($it['title']) ?></h3>
                  <?php if ($excerpt): ?>
                    <p class="excerpt"><?= h($excerpt) ?><?= mb_strlen($it['message'] ?? '') > 180 ? '…' : '' ?></p>
                  <?php endif; ?>
                  <div class="actions">
                    <a class="btn-primary" href="<?= h($it['open_url']) ?>">Open</a>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
          <p class="subtle" style="text-align:center; margin-top:10px;">
            Showing <?= count($items) ?> result<?= count($items)===1?'':'s' ?>.
          </p>
        <?php endif; ?>
      </div>
    </section>
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
