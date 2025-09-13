<?php
// admin_users.php (autosave, pill role selector)
require_once __DIR__ . '/php/db.php';
require_once __DIR__ . '/php/session_check.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo "Forbidden";
    exit;
}

function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
$VALID_ROLES = ['admin','vc','mop'];

// CSRF
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
$csrf = $_SESSION['csrf'];

// AJAX handler for role update (no page reload)
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['ajax'] ?? '') === '1') {
    header('Content-Type: application/json');
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        echo json_encode(['ok'=>false,'msg'=>'Invalid CSRF token.']); exit;
    }
    $userId = (int)($_POST['user_id'] ?? 0);
    $newRole = $_POST['role'] ?? '';
    if (!in_array($newRole,$VALID_ROLES,true)) {
        echo json_encode(['ok'=>false,'msg'=>'Invalid role.']); exit;
    }
    if ($userId === (int)$_SESSION['user_id']) {
        echo json_encode(['ok'=>false,'msg'=>"You can't change your own role."]); exit;
    }
    $stmt = $pdo->prepare("UPDATE users SET role = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->execute([$newRole,$userId]);
    echo json_encode(['ok'=>true,'msg'=>'Role updated.']); exit;
}

// fetch users
$stmt = $pdo->query("SELECT id,name,email,role,created_at,updated_at FROM users ORDER BY id ASC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

function role_class(string $role): string {
    return 'role-' . preg_replace('/[^a-z]/','', strtolower($role));
}
?>
<!DOCTYPE html>
<html lang="en">
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NordLion International - Luxury Vehicle Brokerage</title>
    <link rel="icon" type="image/x-icon" href="img/logo-2.png">
    <link rel="stylesheet" href="css/<?php echo $isLoggedIn ? 'mop_dashboard.css' : 'index.css'; ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:wght@400;500;600;700&family=Lato:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
  <style>
    /* Layout polish */
    .page-wrap {max-width: 1200px; margin: 120px auto 60px; padding: 0 20px;}
    .page-head {display:flex; justify-content:space-between; align-items:end; margin-bottom:24px;}
    .muted {opacity:.75;}
    table.admin-table {width:100%; border-collapse:separate; border-spacing:0; background:#fff;
      box-shadow: 0 5px 15px rgba(0,0,0,.06); border-radius:10px; overflow:hidden;}
    table.admin-table th, table.admin-table td {padding:14px 16px; border-bottom:1px solid #eee; text-align:left;}
    table.admin-table th {background:#f8f9fa; color:#0F2C59; font-weight:700; letter-spacing:.3px;}
    table.admin-table tr:last-child td {border-bottom:none;}
    .actions {display:flex; gap:10px; align-items:center; flex-wrap:wrap;}

    /* Pill select (looks like a chip, but is a native select) */
    .role-select.pill {
      appearance: none; -webkit-appearance: none; -moz-appearance: none;
      border: 1px solid rgba(15,44,89,.15);
      background: #fff;
      padding: 10px 36px 10px 14px;
      border-radius: 999px;
      font-weight: 700;
      letter-spacing: .3px;
      box-shadow: 0 3px 8px rgba(0,0,0,.06);
      cursor: pointer;
      transition: box-shadow .2s ease, border-color .2s ease;
    }
    .role-select.pill:focus {outline:none; border-color: rgba(15,44,89,.35); box-shadow: 0 5px 16px rgba(0,0,0,.12);}
    .role-select.pill[disabled]{opacity:.6; cursor:not-allowed;}

    /* color tints per role (fallbacks; main colors added to index.css below) */
    .role-admin-bg { background: rgba(200,164,94,.12); border-color: rgba(200,164,94,.35); color:#8a6a2f; }
    .role-vc-bg    { background: rgba(15,44,89,.08);   border-color: rgba(15,44,89,.25);   color:#0F2C59; }
    .role-mop-bg   { background: rgba(108,117,125,.12);border-color: rgba(108,117,125,.25);color:#495057; }

    .badge {display:inline-block; padding:6px 9px; border-radius:999px; font-size:.8rem; background:#e9ecef; color:#495057;}
    .btn-chip {display:inline-block; padding:10px 14px; border-radius:999px; font-weight:700; border:1px solid transparent;
      cursor:pointer; transition:transform .05s ease, box-shadow .2s ease;}
    .btn-chip:hover {transform: translateY(-1px);}
    .btn-chip.danger {background:#dc3545; color:#fff; box-shadow:0 4px 10px rgba(220,53,69,.25);}
    .btn-chip.danger:focus {outline:none; box-shadow:0 0 0 3px rgba(220,53,69,.25);}

    /* tiny toast */
    .toast {
      position: fixed; right: 18px; bottom: 18px; z-index: 9999;
      padding: 10px 14px; background: #0F2C59; color: #fff; border-radius: 10px;
      box-shadow: 0 6px 20px rgba(0,0,0,.2); opacity: 0; transform: translateY(10px);
      transition: opacity .25s ease, transform .25s ease;
      font-size: .95rem;
    }
    .toast.show { opacity: 1; transform: translateY(0); }
    .toast.err { background:#dc3545; }
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

  <main class="page-wrap">
    <div class="page-head">
      <h1 class="section-heading" style="margin:0;">Users</h1>
      <div class="muted"><?= count($users) ?> total</div>
    </div>

    <div class="table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width:70px;">ID</th>
            <th style="width:220px;">Name</th>
            <th>Email</th>
            <th style="width:160px;">Role</th>
            <th style="width:180px;">Created</th>
            <th style="width:180px;">Updated</th>
            <th style="width:280px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): 
            $isSelf = ((int)$u['id'] === (int)$_SESSION['user_id']);
            $bgClass = 'role-'.$u['role'].'-bg';
          ?>
            <tr data-row-id="<?= h($u['id']) ?>">
              <td><?= h($u['id']) ?></td>
              <td><?= h($u['name']) ?></td>
              <td><?= h($u['email']) ?></td>
              <td>
                <?php if ($isSelf): ?>
                  <span class="badge">Self</span>
                <?php else: ?>
                  <select
                    class="role-select pill <?= h($bgClass) ?>"
                    data-user-id="<?= h($u['id']) ?>"
                    data-csrf="<?= h($csrf) ?>"
                    aria-label="Change role for <?= h($u['name']) ?>"
                  >
                    <?php foreach ($VALID_ROLES as $r): ?>
                      <option value="<?= h($r) ?>" <?= $u['role']===$r?'selected':'' ?>>
                        <?= h(strtoupper($r)) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                <?php endif; ?>
              </td>
              <td><?= h($u['created_at']) ?></td>
              <td class="updated-cell"><?= h($u['updated_at']) ?></td>
              <td class="actions">
                <?php if (!$isSelf): ?>
                  <form method="post" action="admin_users.php" onsubmit="return confirm('Delete this user? This cannot be undone.');" style="margin:0;">
                    <input type="hidden" name="csrf" value="<?= $csrf ?>">
                    <input type="hidden" name="action" value="delete_user">
                    <input type="hidden" name="user_id" value="<?= h($u['id']) ?>">
                    <button type="submit" class="btn-chip danger">Delete</button>
                  </form>
                <?php else: ?>
                  <span class="badge">Owner</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($users)): ?>
            <tr><td colspan="7" class="muted">No users found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
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


  <div id="toast" class="toast"></div>

  <script>
    const toastEl = document.getElementById('toast');
    let toastTimer = null;
    function showToast(msg, isErr=false){
      toastEl.textContent = msg;
      toastEl.classList.remove('err');
      if (isErr) toastEl.classList.add('err');
      toastEl.classList.add('show');
      clearTimeout(toastTimer);
      toastTimer = setTimeout(()=> toastEl.classList.remove('show'), 2000);
    }

    // dynamic background class based on role value
    function applyRoleTint(selectEl){
      selectEl.classList.remove('role-admin-bg','role-vc-bg','role-mop-bg');
      const v = selectEl.value;
      if (v==='admin') selectEl.classList.add('role-admin-bg');
      else if (v==='vc') selectEl.classList.add('role-vc-bg');
      else selectEl.classList.add('role-mop-bg');
    }

    document.querySelectorAll('.role-select.pill').forEach(sel=>{
      applyRoleTint(sel);
      sel.addEventListener('change', async (e)=>{
        const el = e.currentTarget;
        applyRoleTint(el);

        const userId = el.dataset.userId;
        const csrf = el.dataset.csrf;
        const role = el.value;

        el.disabled = true; // prevent double submits
        try {
          const res = await fetch('admin_users.php', {
            method: 'POST',
            headers: {'Accept': 'application/json'},
            body: new URLSearchParams({
              ajax: '1',
              csrf: csrf,
              user_id: userId,
              role: role
            })
          });
          const data = await res.json();
          if (data.ok) {
            showToast('Role updated.');
            // Update updated_at quickly by reloading cell via little trick (optional)
            const row = el.closest('tr');
            const updatedCell = row.querySelector('.updated-cell');
            if (updatedCell) updatedCell.textContent = new Date().toISOString().slice(0,19).replace('T',' ');
          } else {
            showToast(data.msg || 'Error', true);
          }
        } catch(err) {
          showToast('Network error', true);
        } finally {
          el.disabled = false;
        }
      });
    });

    // sticky header behavior
    window.addEventListener('scroll', function () {
      const header = document.getElementById('header');
      if (window.scrollY > 50) header.classList.add('scrolled'); else header.classList.remove('scrolled');
    });
    document.querySelector('.mobile-menu-btn')?.addEventListener('click', function() {
      document.getElementById('nav-menu').classList.toggle('active');
    });
  </script>
</body>
</html>
