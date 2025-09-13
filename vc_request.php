<?php
session_start();
require_once 'php/db.php';
require_once 'php/session_check.php';

// Check if user is logged in and is a VC
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $isLoggedIn ? $_SESSION['user_role'] : '';
$isVC = ($isLoggedIn && $userRole === 'vc');

// If not logged in as VC, redirect to login
if (!$isVC) {
    header("Location: login.html?message=" . urlencode("You need to be logged in as a VC to access this page."));
    exit;
}

// Get user name if logged in
$userName = $isLoggedIn ? $_SESSION['user_name'] : '';
$userId = $_SESSION['user_id'];

// Handle form submission
$successMessage = '';
$errorMessage = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $vehicleName = $_POST['vehicle_name'] ?? '';
    $vehicleType = $_POST['vehicle_type'] ?? '';
    $budget = $_POST['budget'] ?? '';
    $details = $_POST['details'] ?? '';
    
    // Validate required fields
    if (empty($vehicleName) || empty($vehicleType) || empty($budget) || empty($details)) {
        $errorMessage = "Please fill in all required fields.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO car_requests (user_id, vehicle_name, vehicle_type, budget, details, status, created_at) 
                                VALUES (?, ?, ?, ?, ?, 'pending', NOW())");
            $stmt->execute([$userId, $vehicleName, $vehicleType, $budget, $details]);
            
            $successMessage = "Your vehicle request has been submitted. A specialist will contact you shortly.";
        } catch (PDOException $e) {
            $errorMessage = "There was an error submitting your request. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request a Car | NordLion International</title>
    <link rel="icon" type="image/x-icon" href="img/logo-2.png">
    <link rel="stylesheet" href="css/vc_request.css">
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
                    <li><a href="index.php"">Home</a></li>
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
    <section class="about-hero" style="background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('img/wallpaper-200.jpg'); background-size: cover; background-position: center; height: 70vh; display: flex; align-items: center; justify-content: center;">
        <div class="container" style="text-align: center;">
            <h1 style="color: #fff; font-family: 'EB Garamond', serif; font-size: 4rem; margin-bottom: 1.5rem;">Request a Specific Vehicle</h1>
            <p style="color: #fff; font-family: 'Lato', sans-serif; font-size: 1rem; letter-spacing: 2px; text-transform: uppercase; max-width: 800px; margin: 0 auto;">ACCESS OUR GLOBAL NETWORK FOR RARE AND EXCLUSIVE VEHICLES</p>
        </div>
    </section>

    <!-- Request Form Section -->
    <section class="contact section-padding">
        <div class="container" style="max-width: 1000px; margin: 0 auto;">
            <div style="text-align: center; margin-bottom: 40px;">
                <h2 style="font-family: 'EB Garamond', serif; font-size: 2.5rem; margin-bottom: 15px;">Get In Touch</h2>
                <p style="max-width: 600px; margin: 0 auto; color: #666;">Interested in our services or have a specific vehicle in mind? Our team is ready to assist you with any inquiries.</p>
            </div>
            
            <?php if ($successMessage): ?>
                <div class="success-message" style="background-color: #4CAF50; color: white; padding: 15px; border-radius: 5px; margin: 20px auto; text-align: center; max-width: 800px;">
                    <?php echo htmlspecialchars($successMessage); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($errorMessage): ?>
                <div class="error-message" style="background-color: #f44336; color: white; padding: 15px; border-radius: 5px; margin: 20px auto; text-align: center; max-width: 800px;">
                    <?php echo htmlspecialchars($errorMessage); ?>
                </div>
            <?php endif; ?>
            
            <div class="form-container" style="max-width: 600px; margin: 0 auto;">
                <form action="vc_request.php" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
                    <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                        
                    <div>
                        <label for="vehicle_name" style="display: block; font-weight: 500; margin-bottom: 8px;">Vehicle Name</label>
                        <input type="text" id="vehicle_name" name="vehicle_name" required placeholder="e.g., Ferrari 458 Italia" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Lato', sans-serif;">
                    </div>
                    
                    <div>
                        <label for="vehicle_type" style="display: block; font-weight: 500; margin-bottom: 8px;">Vehicle Type</label>
                        <select id="vehicle_type" name="vehicle_type" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Lato', sans-serif; background-color: white;">
                            <option value="">Select Vehicle Type</option>
                            <option value="Sports Car">Sports Car</option>
                            <option value="Supercar">Supercar</option>
                            <option value="Hypercar">Hypercar</option>
                            <option value="Luxury Sedan">Luxury Sedan</option>
                            <option value="Luxury SUV">Luxury SUV</option>
                            <option value="Classic Car">Classic Car</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label for="budget" style="display: block; font-weight: 500; margin-bottom: 8px;">Budget (€)</label>
                        <input type="text" id="budget" name="budget" required placeholder="e.g., 1,500,000" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Lato', sans-serif;">
                    </div>

                    <div>
                        <label for="details" style="display: block; font-weight: 500; margin-bottom: 8px;">Specific Requirements</label>
                        <textarea id="details" name="details" rows="5" required placeholder="Please provide any specific details about the vehicle you're looking for, such as condition, mileage, year, color, special features, etc." style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Lato', sans-serif;"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; background-color: #0F2C59; color: white; border: none; border-radius: 4px; font-family: 'Lato', sans-serif; font-weight: 500; cursor: pointer; text-transform: uppercase; letter-spacing: 1px; margin-top: 10px;">Submit Request</button>
                </form>
            </div>
            
            <div style="max-width: 600px; margin: 40px auto 0; text-align: center;">
                <h3 style="font-family: 'EB Garamond', serif; font-size: 1.8rem; margin-bottom: 20px;">How It Works</h3>
                
                <div class="process-steps" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 30px;">
                    <div style="text-align: center; padding: 15px; border: 1px solid #eee; border-radius: 5px;">
                        <div style="font-size: 1.5rem; margin-bottom: 10px; color: #0F2C59;">1</div>
                        <p style="font-size: 0.9rem;">Submit your vehicle request</p>
                    </div>
                    
                    <div style="text-align: center; padding: 15px; border: 1px solid #eee; border-radius: 5px;">
                        <div style="font-size: 1.5rem; margin-bottom: 10px; color: #0F2C59;">2</div>
                        <p style="font-size: 0.9rem;">Our team reviews your requirements</p>
                    </div>
                    
                    <div style="text-align: center; padding: 15px; border: 1px solid #eee; border-radius: 5px;">
                        <div style="font-size: 1.5rem; margin-bottom: 10px; color: #0F2C59;">3</div>
                        <p style="font-size: 0.9rem;">We contact you within 24 hours</p>
                    </div>
                    
                    <div style="text-align: center; padding: 15px; border: 1px solid #eee; border-radius: 5px;">
                        <div style="font-size: 1.5rem; margin-bottom: 10px; color: #0F2C59;">4</div>
                        <p style="font-size: 0.9rem;">We source your perfect vehicle</p>
                    </div>
                </div>
                
                <div style="display: flex; justify-content: center; align-items: center; margin-bottom: 20px; gap: 15px;">
                    <span style="display: inline-block; width: 8px; height: 8px; background-color: #0F2C59; border-radius: 50%;"></span>
                    <span style="display: inline-block; width: 8px; height: 8px; background-color: #0F2C59; border-radius: 50%;"></span>
                    <span style="display: inline-block; width: 8px; height: 8px; background-color: #0F2C59; border-radius: 50%;"></span>
                </div>
                
                <p style="color: #666; font-style: italic; font-size: 0.9rem;">For urgent inquiries, please call <span style="color: #0F2C59; font-weight: 500;">+44 7947 977474</span></p>
            </div>
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
