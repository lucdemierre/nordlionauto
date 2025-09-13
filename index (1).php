<?php
session_start();
require_once 'php/db.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] : '';
$userRole = $isLoggedIn ? $_SESSION['user_role'] : '';


// Get first name if user is logged in
$firstName = '';
if ($isLoggedIn && !empty($userName)) {
    $nameParts = explode(' ', $userName);
    $firstName = $nameParts[0];
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $inquiry_type = $_POST['inquiry_type'] ?? '';
    $message = $_POST['message'] ?? '';
    $user_id = $_SESSION['user_id'] ?? null;
}


$is_logged_in = isset($_SESSION['user_id']);

// Database connection
require_once 'php/db.php';

// Fetch inquiry types from database
$inquiry_types = [];
try {
    $stmt = $pdo->query("SELECT DISTINCT type, subject FROM inquiries WHERE status = 'new' ORDER BY subject");
} catch (PDOException $e) {
    // If there's an error, use default types as fallback
    $inquiry_types = [
        ['type' => 'car', 'subject' => 'Car Inquiry'],
        ['type' => 'jet', 'subject' => 'Jet Inquiry'],
        ['type' => 'investment', 'subject' => 'Investment Opportunity'],
        ['type' => 'general', 'subject' => 'General Inquiry'],
        ['type' => 'other', 'subject' => 'Other']
    ];
}




// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'] ?? null;         // must be set if user_id is NOT NULL in DB
    $message = $_POST['message'] ?? '';
    $type    = 'general';
    $subject = 'General Inquiry';

    try {
        $stmt = $pdo->prepare("
            INSERT INTO inquiries (user_id, type, subject, message, status)
            VALUES (?, ?, ?, ?, 'new')
        ");
        $stmt->execute([$user_id, $type, $subject, $message]);

        $success_message = "Thank you for your inquiry. We'll get back to you soon!";
    } catch (PDOException $e) {
        $error_message = "There was an error submitting your inquiry. Please try again.";
    }
}



// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userName   = $isLoggedIn ? ($_SESSION['user_name'] ?? '') : '';
$userRole   = $isLoggedIn ? ($_SESSION['user_role'] ?? '') : '';


// Default values
$email = '';
$firstName = '';

// If logged in, try to get email from session or database
if ($isLoggedIn) {
    if (!empty($_SESSION['user_email'])) {
        $email = $_SESSION['user_email'];
    } else {
        // Optional: fetch from database if not stored in session
        $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $dbEmail = $stmt->fetchColumn();
        if ($dbEmail) {
            $email = $dbEmail;
            $_SESSION['user_email'] = $dbEmail; // cache for next time
        }
    }
}

// If form was submitted, keep the posted email instead
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? $email;
}

// Get first name from full name if available
if (!empty($userName)) {
    $nameParts = explode(' ', $userName);
    $firstName = $nameParts[0];
}



?>
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
</head>
<body>
    <!-- Header and Navigation -->
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

    <!-- Hero Section -->
    <section class="hero" id="home">
        <img src="img/hp-img.jpg" alt="Luxury Hypercar" class="hero-image">
        <div class="hero-content">
            <?php if ($isLoggedIn): ?>
                <h1 class="hero-heading">Welcome, <?php echo htmlspecialchars($firstName); ?>!</h1>
            <?php else: ?>
                <h1 class="hero-heading">NordLion International</h1>
            <?php endif; ?>
            <p class="hero-subheading">Exceptional Luxury Vehicles & Private Jets</p>
            <a href="contact.php" class="btn btn-outline-invert">Contact Us</a>
        </div>
    </section>

    <!-- Featured Cars or VC Dashboard Section based on login status -->
    <?php if ($isLoggedIn && $userRole === 'vc'): ?>
    <!-- VC Dashboard in Featured Section Position - Only shown for VC users -->
    <section class="featured section-padding" id="vc-dashboard">
        <div class="container">
            <h2 class="section-heading">VC Dashboard</h2>
            <div class="featured-grid">
                <div class="car-card">
                    <div class="card-image">
                        <img src="img/Wallpaper-245.jpg" alt="Off Market Listings">
                    </div>
                    <div class="card-details">
                        <h3 class="card-title">Off Market Listings</h3>
                        <p class="card-description">Explore exclusive off-market luxury vehicles only available to select investors. Discover rare and limited production models.</p>
                        <a href="offmarket.php" class="btn btn-outline">View Listings</a>
                    </div>
                </div>

                <div class="car-card">
                    <div class="card-image">
                        <img src="img/cc850.jpg" alt="Submit Investment Inquiry">
                    </div>
                    <div class="card-details">
                        <h3 class="card-title">Submit Investment Inquiry</h3>
                        <p class="card-description">Interested in investing in luxury vehicles? Submit your criteria and our specialists will prepare tailored investment proposals.</p>
                        <a href="contact.php" class="btn btn-outline">Submit Inquiry</a>
                    </div>
                </div>

                <div class="car-card">
                    <div class="card-image">
                        <img src="img/inquire.jpg" alt="Request a Car">
                    </div>
                    <div class="card-details">
                        <h3 class="card-title">Request a Car</h3>
                        <p class="card-description">Looking for a specific vehicle? Let us know your requirements and our global network will help you find it.</p>
                        <a href="vc_request.php" class="btn btn-outline">Make Request</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php else: ?>
    <!-- Standard Featured Cars Section for non-VC users -->
    <section class="featured section-padding" id="cars">
        <div class="container">
            <h2 class="section-heading">Featured Vehicles</h2>
            <div class="featured-grid">
                <div class="car-card">
                    <div class="card-image">
                        <img src="img/cc850.jpg" alt="Koenigsegg CC850">
                    </div>
                    <div class="card-details">
                        <h3 class="card-title">Koenigsegg CC850</h3>
                        <p class="card-price">€3,650,000</p>
                        <p class="card-description">Limited production hybrid hypercar with revolutionary transmission technology.</p>
                        <a href="car_details.php?id=4" class="btn btn-outline">View Details</a>
                    </div>
                </div>

                <div class="car-card">
                    <div class="card-image">
                        <img src="img/huayra.png" alt="Pagani Huayra Imola">
                    </div>
                    <div class="card-details">
                        <h3 class="card-title">Pagani Huayra Imola</h3>
                        <p class="card-price">€5,100,000</p>
                        <p class="card-description">Track-focused hypercar with 827 horsepower, limited to just 5 units worldwide.</p>
                        <a href="car_details.php?id=5" class="btn btn-outline">View Details</a>
                    </div>
                </div>

                <div class="car-card">
                    <div class="card-image">
                        <img src="img/agera.png" alt="Koenigsegg Agera One:1">
                    </div>
                    <div class="card-details">
                        <h3 class="card-title">Koenigsegg Agera One:1</h3>
                        <p class="card-price">€6,800,000</p>
                        <p class="card-description">The world's first megacar with a perfect 1:1 power-to-weight ratio.</p>
                        <a href="car_details.php?id=6" class="btn btn-outline">View Details</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($isLoggedIn && ($userRole === 'mop' || $userRole === '')): ?>
    <!-- Member Exclusive Section - Only shown for regular members -->
    <section class="section-padding" style="background-color: #f8f9fa;">
        <div class="container">
            <h2 class="section-heading">Member Exclusive</h2>
            <div class="dashboard-grid">
                <div class="card">
                    <h3>Your Inquiries</h3>
                    <p>View and manage your vehicle inquiries and requests.</p>
                    <a href="mop_inquiries.php" class="btn btn-primary">View Inquiries</a>
                </div>
                
                <div class="card">
                    <h3>View On Market</h3>
                    <p>Access NordLion's current stock.</p>
                    <a href="onmarket.php" class="btn btn-primary">View On Market</a>
                </div>
                
                <div class="card">
                    <h3>Your Profile</h3>
                    <p>Update your personal information and preferences.</p>
                    <a href="edit_profile.php" class="btn btn-primary">Edit Profile</a>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($isLoggedIn && $userRole === 'admin'): ?>
    <!-- Admin Quick Tools - Only shown for admins -->
    <section class="section-padding" style="background-color: #f8f9fa;">
        <div class="container">
            <h2 class="section-heading">Admin Quick Tools</h2>
            <div class="dashboard-grid">
                <div class="card">
                    <h3>Pending Listings</h3>
                    <p>Manage vehicle submissions awaiting approval.</p>
                    <a href="dashboard.php#pending-section" class="btn btn-primary">View Pending</a>
                </div>
                
                <div class="card">
                    <h3>Add Vehicles</h3>
                    <p>Add new cars to on-market or off-market inventory.</p>
                    <a href="add_car_onmarket.php" class="btn btn-primary">Add Vehicle</a>
                </div>
                
                <div class="card">
                    <h3>Off Market Inquiry</h3>
                    <p>View Off Market Inquiries. <br></br></p>
                    <a href="offmarket_inquiries_admin.php" class="btn btn-primary">View Inquiries</a>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- VC Dashboard has been moved to the featured cars position -->

    <!-- About Section -->
    <section class="about section-padding" id="about">
        <div class="container">
            <div class="about-container">
                <div class="about-image">
                    <img src="img/luc-1.jpg" alt="NordLion International Office">
                </div>
                <div class="about-content">
                    <h2>About NordLion International</h2>
                    <p>Founded in 2020, NordLion International has established itself as a premier luxury vehicle brokerage, connecting discerning clients with extraordinary automobiles and private aircraft.</p>
                    <p>Based in London, with a global network of partners, we specialize in rare and limited-production vehicles that represent the pinnacle of automotive engineering and design.<br></br></p>
                    <a href="team.php" class="btn btn-primary">Meet Our Team</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services section-padding">
        <div class="container">
            <h2 class="section-heading">Our Services</h2>
            <div class="services-grid">
                <a href="onmarket.php" class="service-card">
                    <div class="service-icon">🚗</div>
                    <h3 class="service-title">Vehicle Acquisition</h3>
                    <p class="service-description">Expert sourcing of rare and limited production hypercars and luxury vehicles based on your specific requirements.</p>
                </a>
                
                
                <a href="offmarket.php" class="service-card">
                    <div class="service-icon">🔒</div>
                    <h3 class="service-title">Off-Market Access</h3>
                    <p class="service-description">Gain exclusive access to vehicles and aircraft not available to the general public through our private network.</p>
                </a>
                
                <a href="contact.php" class="service-card">
                    <div class="service-icon">📊</div>
                    <h3 class="service-title">Investment Consulting</h3>
                    <p class="service-description">Expert guidance on automotive investments, including rare and limited-production vehicles with appreciation potential.</p>
                </a>
            </div>
        </div>
    </section>

    

    

    <!-- Contact Section -->
    <section class="contact section-padding" id="contact">
        <div class="container">
            <h2 class="section-heading">Contact Us</h2>
            
            <?php if ($isLoggedIn): ?>
            <!-- Two-column layout with form for logged-in users -->
            <div class="contact-container">
                <div class="contact-form">
                    <form action="index.php" method="POST">
                        <input type="hidden" name="inquiry_type" value="general">
                        <input type="hidden" name="subject" value="General Inquiry">
                        <div class="form-group">
                            <label for="name">Your Name</label>
                            <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($userName); ?>">
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="text" id="email" name="email" required value="<?php echo htmlspecialchars($email); ?>">
                        </div>


                        <div class="form-group">
                            <label for="message">Your Message</label>
                            <textarea id="message" name="message" rows="5" required></textarea>
                        </div>

                        
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>
                
                <div class="contact-info">
                    <h3 class="contact-heading">Get In Touch</h3>
                    <p class="contact-text">Interested in our services or have a specific vehicle in mind? Our team is ready to assist you with any inquiries.</p>
                    
                    <div class="contact-items-wrapper">
                        <div class="contact-item">
                            <span class="contact-icon">📍</span>
                            <span class="contact-detail">London, United Kingdom</span>
                        </div>
                        
                        <div class="contact-item">
                            <span class="contact-icon">📱</span>
                            <span class="contact-detail">+44 20 1234 5678</span>
                        </div>
                        
                        <div class="contact-item">
                            <span class="contact-icon">✉️</span>
                            <span class="contact-detail">info@nordlioninternational.com</span>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <!-- SIMPLIFIED LAYOUT FOR LOGGED OUT USERS -->
            <div style="width: 100%; display: flex; justify-content: center; align-items: center;">
            <div class="contact-center" style="text-align: center; margin: 0 auto; max-width: 800px;">

                <p style="font-size: 1.1rem; line-height: 1.5; margin-bottom: 25px;">Interested in our services or have a specific vehicle in mind? Our team is ready to assist you with any inquiries.</p>
                
                <div class="contact-items" style="margin-top: 0;">
                    <div class="centered-item" style="font-size: 1.15rem; margin-bottom: 4px; padding: 0;">
                        <span class="icon" style="font-size: 1.4rem; min-width: 30px;">📍</span>
                        <span>London, United Kingdom - Singapore, Singapore - Turku, Finland</span>
                    </div>
                    
                    <div class="centered-item" style="font-size: 1.15rem; margin-bottom: 4px; padding: 0;">
                        <span class="icon" style="font-size: 1.4rem; min-width: 30px;">📱</span>
                        <span>+44 7947 977474</span>
                    </div>
                    
                    <div class="centered-item" style="font-size: 1.15rem; margin-bottom: 4px; padding: 0;">
                        <span class="icon" style="font-size: 1.4rem; min-width: 30px;">✉️</span>
                        <span>lucdemierre@hotmail.com - eliel.valkama@gmail.com</span>
                    </div>

                    <div class="centered-item" style="font-size: 1.15rem; margin-bottom: 0; padding: 0;">
                        <span class="icon" style="font-size: 1.4rem; min-width: 30px;">⏰</span>
                        <span>Mon-Sat: 4:00 PM - 10:00 PM GMT</span>
                    </div>
                </div>
            </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

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

    <script src="scripts.js"></script>
    <script>
        // Add scroll behavior for navbar
        window.addEventListener('scroll', function() {
            const header = document.getElementById('header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
        
        // Mobile menu functionality
        document.querySelector('.mobile-menu-btn').addEventListener('click', function() {
            document.getElementById('nav-menu').classList.toggle('active');
        });
    </script>
</body>
</html>
