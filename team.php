<?php
session_start();

$isLoggedIn = isset($_SESSION['user_id']);
$userName   = $isLoggedIn ? ($_SESSION['user_name'] ?? '') : '';
$userRole   = $isLoggedIn ? ($_SESSION['user_role'] ?? '') : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Our Team - NordLion International</title>
  <link rel="icon" type="image/x-icon" href="img/logo-2.png">
  <link rel="stylesheet" href="css/team.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:wght@400;500;600;700&family=Lato:wght@300;400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    /* Light visual polish in case team.css is minimal */
    .about-hero {
      background: linear-gradient(rgba(15,44,89,.75), rgba(15,44,89,.75)), url('img/egg-2.jpg');
      background-size: cover; background-position: center 55%;
      color: #fff; text-align: center; padding: 160px 0 100px; margin-top: 80px;
    }
    .about-hero h1 { font-size: 3.2rem; margin-bottom: 12px; }
    .about-hero .subtitle { font-size: 1.2rem; letter-spacing: 1.5px; text-transform: uppercase; opacity: .9; }

    .section-padding { padding: 100px 0; }
    .section-heading { font-size: 2.5rem; text-align: center; margin-bottom: 50px; position: relative; padding-bottom: 15px; }
    .section-heading::after { content:""; position:absolute; left:50%; transform:translateX(-50%); bottom:0; width:60px; height:3px; background:#C8A45E; }

    .team-wrap { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
    .team-grid {
      display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 30px;
    }
    .team-card {
      background:#fff; border-radius: 12px; overflow:hidden;
      box-shadow: 0 10px 25px rgba(0,0,0,.08); transition: transform .25s ease, box-shadow .25s ease;
      display:flex; flex-direction:column;
    }
    .team-card:hover { transform: translateY(-8px); box-shadow: 0 18px 35px rgba(0,0,0,.12); }
    .team-photo {
      width:100%; aspect-ratio: 4/3; object-fit: cover;
    }
    .team-body { padding: 24px; display:flex; flex-direction:column; gap:12px; }
    .team-name { margin: 0; font-size: 1.4rem; color:#0F2C59; }
    .team-role { margin: 0; color:#C8A45E; font-weight: 600; letter-spacing:.3px; }
    .team-bio { margin: 0; color:#555; line-height: 1.7; }
    .team-actions { display:flex; align-items:center; gap:10px; margin-top: 6px; }
    .btn-outline {
      border:2px solid #0F2C59; color:#0F2C59; padding:10px 16px; border-radius:6px; text-transform:uppercase;
      font-size:.85rem; letter-spacing:.6px; transition: all .25s ease; text-decoration:none; display:inline-flex; align-items:center; gap:8px;
    }
    .btn-outline:hover { background:#0F2C59; color:#fff; }
    .socials { margin-left:auto; display:flex; gap:10px; }
    .socials a { width:40px; height:40px; display:flex; align-items:center; justify-content:center; border-radius:50%;
      background: rgba(15,44,89,.08); color:#0F2C59; transition: background .25s ease, transform .25s ease;
    }
    .socials a:hover { background:#0F2C59; color:#fff; transform: translateY(-2px); }

    @media (max-width: 576px) {
      .about-hero { padding: 120px 0 80px; }
      .about-hero h1 { font-size: 2.4rem; }
    }
  </style>
</head>
<body>
  <!-- Header / Nav (consistent with other pages) -->
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
          <li><a href="about.php">About Us</a></li>
          <li><a href="team.php" class="active">Our Team</a></li>
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
    <!-- Hero -->
    <section class="about-hero">
      <div class="team-wrap">
        <h1>Our Team</h1>
        <p class="subtitle">Meet the experts behind NordLion International</p>
      </div>
    </section>

    <!-- Team Cards -->
    <section class="section-padding">
      <div class="team-wrap">
        <h2 class="section-heading">Leadership</h2>
        <div class="team-grid">
          <!-- Luc -->
          <article class="team-card">
            <img class="team-photo" src="img/karting.jpeg" alt="Luc Demierre">
            <div class="team-body">
              <h3 class="team-name">Luc Demierre</h3>
              <p class="team-role">Founder &amp; Business Developer</p>
              <p class="team-bio">
                Luc leads NordLion International with a vision for excellence and a passion for automotive rarities.
                His focus on discretion, precision, and tailored service ensures an exceptional experience for every client.
              </p>
              <div class="team-actions">
                <a class="btn-outline" href="mailto:lucdemierre@hotmail.com"><i class="fa-regular fa-envelope"></i> Contact</a>
                <div class="socials">
                  <a href="https://www.linkedin.com/in/luc-demierre-29b301330" target="_blank" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                  <a href="https://www.instagram.com/the_nordlion_international/" target="_blank" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                </div>
              </div>
            </div>
          </article>

          <!-- Eliel -->
          <article class="team-card">
            <img class="team-photo" src="img/team-1.jpg" alt="Eliel Valkama">
            <div class="team-body">
              <h3 class="team-name">Eliel Valkama</h3>
              <p class="team-role">Co-Founder &amp; Lead Evaluator</p>
              <p class="team-bio">
                Eliel brings deep expertise in sports and hypercar evaluation, ensuring every vehicle meets
                uncompromising standards of quality, provenance, and performance.
              </p>
              <div class="team-actions">
                <a class="btn-outline" href="mailto:eliel.valkama@gmail.com"><i class="fa-regular fa-envelope"></i> Contact</a>
                <div class="socials">
                  <a href="https://www.linkedin.com/in/eliel-valkama-5907992aa" target="_blank" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                  <a href="https://www.instagram.com/the_nordlion_international/" target="_blank" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                </div>
              </div>
            </div>
          </article>
        </div>

        <!-- Optional: Advisors or Operations (example block; duplicate/edit as needed) -->
        <!--
        <h2 class="section-heading" style="margin-top:70px;">Advisors</h2>
        <div class="team-grid">
          <article class="team-card">...</article>
          <article class="team-card">...</article>
        </div>
        -->
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
    // Navbar scroll behavior
    window.addEventListener('scroll', function() {
      const header = document.getElementById('header');
      if (window.scrollY > 50) header.classList.add('scrolled'); else header.classList.remove('scrolled');
    });

    // Mobile menu
    document.querySelector('.mobile-menu-btn').addEventListener('click', function() {
      document.getElementById('nav-menu').classList.toggle('active');
    });
  </script>
</body>
</html>
