<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HatidGawa - Community Task Sharing Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="icon" href="assets/images/logo.svg">
</head>
<body>
    <!-- Loading Overlay -->


    <!-- Header -->
    <header>
        <div class="container">
            <nav class="navbar">
                <a href="index.php" class="navbar-brand">
                    <i class="fas fa-hands-helping"></i>
                    HatidGawa
                </a>
                
                <div class="navbar-nav">
                    <a href="index.php" class="nav-link active">Home</a>
                    <a href="about.php" class="nav-link">About</a>
                    <a href="about.php" class="nav-link">Tasks</a>
                    <a href="#how-it-works" class="nav-link">How It Works</a>
                </div>
                
                <div class="dropdown">
                        <button class="btn btn-ghost dropdown-toggle">
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar avatar-sm" id="user-avatar">
                                    <img src="https://randomuser.me/api/portraits/women/12.jpg" alt="User">
                                </div>
                                <span id="username"></span>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </button>
                        <div class="dropdown-menu">
                            <a href="dashboard.php" class="dropdown-item">
                                <i class="fas fa-tachometer-alt"></i>
                                <span>Dashboard</span>
                            </a>
                            <a href="profile.php" class="dropdown-item">
                                <i class="fas fa-user"></i>
                                <span>My Profile</span>
                            </a>
                            <a href="tasks.php" class="dropdown-item">
                                <i class="fas fa-tasks"></i>
                                <span>My Tasks</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item" id="logout-btn">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Log Out</span>
                            </a>
                        </div>
                    </div>
                
                <div class="navbar-actions" id="user-nav" style="display: none;">
                    <div class="dropdown">
                        <button class="btn btn-ghost dropdown-toggle">
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar avatar-sm" id="user-avatar">
                                    <img src="https://randomuser.me/api/portraits/women/12.jpg" alt="User">
                                </div>
                                <span id="user-name">Maria Santos</span>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </button>
                        <div class="dropdown-menu">
                            <a href="dashboard.php" class="dropdown-item">
                                <i class="fas fa-tachometer-alt"></i>
                                <span>Dashboard</span>
                            </a>
                            <a href="profile.php" class="dropdown-item">
                                <i class="fas fa-user"></i>
                                <span>My Profile</span>
                            </a>
                            <a href="tasks-user.php" class="dropdown-item">
                                <i class="fas fa-tasks"></i>
                                <span>My Tasks</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item" id="logout-btn">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Log Out</span>
                            </a>
                        </div>
                    </div>
                    
                    <div class="dropdown">
                        <button class="btn btn-icon btn-ghost notification-badge" id="notification-badge">
                            <i class="fas fa-bell"></i>
                        </button>
                        <div class="dropdown-menu">
                            <div class="p-3 border-bottom">
                                <h5 class="m-0">Notifications</h5>
                            </div>
                            <div id="notification-list">
                                <!-- Notifications will be populated here -->
                            </div>
                            <div class="p-2 text-center border-top">
                                <a href="dashboard.php" class="btn btn-sm btn-outline w-100">View All</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <button class="mobile-menu-btn" id="mobile-menu-btn">
                    <i class="fas fa-bars"></i>
                </button>
            </nav>
            
            <div class="mobile-menu">
                <a href="index.php" class="nav-link active">Home</a>
                <a href="about.php" class="nav-link">About</a>
                <a href="tasks.php" class="nav-link">Tasks</a>
                <a href="#how-it-works" class="nav-link">How It Works</a>
                <div class="mt-4" id="mobile-auth-nav">
                    <a href="login.php" class="btn btn-outline w-100 mb-2">Log In</a>
                    <a href="register.php" class="btn btn-primary w-100">Sign Up</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <div class="hero-content">
                        <h1>Community-Powered Task Sharing Platform</h1>
                        <p>Connect with trusted neighbors to get help with everyday tasks or offer your skills to earn extra income. Safe, secure, and community-focused.</p>
                        <div class="hero-buttons">
                            <a href="register.php" class="btn btn-accent btn-lg">Get Started</a>
                            <a href="#how-it-works" class="btn btn-outline btn-lg text-light">Learn More</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <img src="assets/images/hero.svg" alt="Community Helping" class="hero-image">
    </section>

    <!-- Features Section -->
    <section class="py-8" id="features">
        <div class="container">
            <div class="text-center mb-6">
                <h2 class="text-dark mb-2">Why Choose HatidGawa?</h2>
                <p class="text-gray">Our platform offers unique features designed for your safety and convenience</p>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center p-5">
                            <div class="feature-icon mb-4">
                                <i class="fas fa-shield-alt text-primary fa-3x"></i>
                            </div>
                            <h3 class="card-title">Safe Zones</h3>
                            <p class="card-text">Meet at designated safe zones like barangay halls and community centers for added security.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center p-5">
                            <div class="feature-icon mb-4">
                                <i class="fas fa-user-check text-primary fa-3x"></i>
                            </div>
                            <h3 class="card-title">Verified Users</h3>
                            <p class="card-text">Our verification process ensures you're connecting with trusted community members.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center p-5">
                            <div class="feature-icon mb-4">
                                <i class="fas fa-hand-holding-heart text-primary fa-3x"></i>
                            </div>
                            <h3 class="card-title">Community Focus</h3>
                            <p class="card-text">Build stronger neighborhoods by helping each other with everyday tasks.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="py-8 bg-light" id="how-it-works">
        <div class="container">
            <div class="text-center mb-6">
                <h2 class="text-dark mb-2">How It Works</h2>
                <p class="text-gray">Getting help or offering your skills is simple with HatidGawa</p>
            </div>
            
            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center p-4">
                            <div class="step-number mb-3">1</div>
                            <h4 class="card-title">Create an Account</h4>
                            <p class="card-text">Sign up and complete your profile with your skills and location.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center p-4">
                            <div class="step-number mb-3">2</div>
                            <h4 class="card-title">Post a Task</h4>
                            <p class="card-text">Describe what you need help with, set a budget, and choose a location.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center p-4">
                            <div class="step-number mb-3">3</div>
                            <h4 class="card-title">Connect</h4>
                            <p class="card-text">Review applications and choose a helper, or apply to help others.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center p-4">
                            <div class="step-number mb-3">4</div>
                            <h4 class="card-title">Complete & Rate</h4>
                            <p class="card-text">Complete the task and leave a review to build community trust.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-5">
                <a href="register.php" class="btn btn-primary btn-lg">Get Started Now</a>
            </div>
        </div>
    </section>

    <!-- Popular Tasks Section -->
    <section class="py-8">
        <div class="container">
            <div class="text-center mb-6">
                <h2 class="text-dark mb-2">Popular Tasks</h2>
                <p class="text-gray">Browse some of the most requested services in your community</p>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h3 class="card-title">Grocery Delivery</h3>
                                <span class="badge badge-primary">₱150-300</span>
                            </div>
                            <p class="card-text">Get your groceries delivered to your doorstep by a trusted community member.</p>
                            <div class="task-meta mt-4">
                                <div class="task-meta-item">
                                    <i class="fas fa-tag"></i>
                                    <span>Delivery</span>
                                </div>
                                <div class="task-meta-item">
                                    <i class="fas fa-clock"></i>
                                    <span>1-2 hours</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="tasks.php" class="btn btn-outline w-100">Find Helpers</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h3 class="card-title">Home Repairs</h3>
                                <span class="badge badge-primary">₱300-800</span>
                            </div>
                            <p class="card-text">Get help with minor home repairs from skilled neighbors in your community.</p>
                            <div class="task-meta mt-4">
                                <div class="task-meta-item">
                                    <i class="fas fa-tag"></i>
                                    <span>Repairs</span>
                                </div>
                                <div class="task-meta-item">
                                    <i class="fas fa-clock"></i>
                                    <span>1-3 hours</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="tasks.php" class="btn btn-outline w-100">Find Helpers</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h3 class="card-title">Tutoring</h3>
                                <span class="badge badge-primary">₱200-500</span>
                            </div>
                            <p class="card-text">Connect with knowledgeable tutors for help with schoolwork or learning new skills.</p>
                            <div class="task-meta mt-4">
                                <div class="task-meta-item">
                                    <i class="fas fa-tag"></i>
                                    <span>Education</span>
                                </div>
                                <div class="task-meta-item">
                                    <i class="fas fa-clock"></i>
                                    <span>1-2 hours</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="tasks.php" class="btn btn-outline w-100">Find Helpers</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-5">
                <a href="tasks.php" class="btn btn-outline">View All Tasks</a>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-8 bg-light">
        <div class="container">
            <div class="text-center mb-6">
                <h2 class="text-dark mb-2">What Our Users Say</h2>
                <p class="text-gray">Hear from community members who use HatidGawa</p>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body p-4">
                            <div class="d-flex mb-4">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                            </div>
                            <p class="card-text mb-4">"HatidGawa has been a lifesaver for me as a busy parent. I can get help with errands while supporting people in my community."</p>
                            <div class="d-flex align-items-center">
                                <div class="avatar me-3">
                                    <img src="https://randomuser.me/api/portraits/women/45.jpg" alt="Ana Reyes">
                                </div>
                                <div>
                                    <h5 class="mb-0">Ana Reyes</h5>
                                    <p class="text-gray mb-0">Quezon City</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body p-4">
                            <div class="d-flex mb-4">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                            </div>
                            <p class="card-text mb-4">"I've been able to earn extra income by helping neighbors with tech support and small repairs. The safe zone feature makes everyone feel secure."</p>
                            <div class="d-flex align-items-center">
                                <div class="avatar me-3">
                                    <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Juan Dela Cruz">
                                </div>
                                <div>
                                    <h5 class="mb-0">Juan Dela Cruz</h5>
                                    <p class="text-gray mb-0">Makati City</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body p-4">
                            <div class="d-flex mb-4">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star-half-alt text-warning"></i>
                            </div>
                            <p class="card-text mb-4">"As a senior citizen, I appreciate being able to get help with tasks I can no longer do easily. The verification system gives me peace of mind."</p>
                            <div class="d-flex align-items-center">
                                <div class="avatar me-3">
                                    <img src="https://randomuser.me/api/portraits/men/67.jpg" alt="Carlos Mendoza">
                                </div>
                                <div>
                                    <h5 class="mb-0">Carlos Mendoza</h5>
                                    <p class="text-gray mb-0">Pasig City</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-8 bg-primary text-white">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8 mb-4 mb-md-0">
                    <h2 class="mb-2">Ready to join our community?</h2>
                    <p class="mb-0">Sign up today and start connecting with helpful neighbors in your area.</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="register.php" class="btn btn-accent btn-lg">Get Started</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-5">
                    <div class="footer-brand">
                        <i class="fas fa-hands-helping"></i>
                        HatidGawa
                    </div>
                    <p class="text-gray-light mb-4">A community-powered task sharing platform that connects neighbors for everyday help.</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="btn btn-icon btn-ghost">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="btn btn-icon btn-ghost">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="btn btn-icon btn-ghost">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-md-2 col-6 mb-4">
                    <h5 class="footer-heading">Platform</h5>
                    <ul class="footer-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="tasks.php">Browse Tasks</a></li>
                        <li><a href="#how-it-works">How It Works</a></li>
                    </ul>
                </div>
                
                <div class="col-md-2 col-6 mb-4">
                    <h5 class="footer-heading">Support</h5>
                    <ul class="footer-links">
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Safety Tips</a></li>
                        <li><a href="#">Community Guidelines</a></li>
                        <li><a href="#">Contact Us</a></li>
                    </ul>
                </div>
                
                <div class="col-md-4 mb-4">
                    <h5 class="footer-heading">Stay Updated</h5>
                    <p class="text-gray-light mb-3">Subscribe to our newsletter for the latest updates and features.</p>
                    <form class="d-flex gap-2">
                        <input type="email" class="form-control" placeholder="Your email">
                        <button type="submit" class="btn btn-primary">Subscribe</button>
                    </form>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2023 HatidGawa. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Dark Mode Toggle -->
    <button id="dark-mode-toggle" class="btn btn-icon btn-ghost position-fixed">
        <i class="fas fa-moon"></i>
    </button>

    <script src="js/mockData.js"></script>
    <script src="js/main.js"></script>
    <script>

    </script>
</body>
</html>