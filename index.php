<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HatidGawa - Community Task Sharing Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/gabay.css">
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
                
                <div class="navbar-actions" id="auth-nav">
                    <a href="login.php" class="btn btn-outline">Log In</a>
                    <a href="register.php" class="btn btn-primary">Sign Up</a>
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
        <img src="img/barangay.png" alt="Community Helping" class="hero-image">
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
                <p>&copy; 2025 HatidGawa. All rights reserved.</p>
            </div>
        </div>
    </footer>

      <!-- Dark Mode Toggle -->
      <button id="dark-mode-toggle" class="btn btn-icon btn-ghost position-fixed">
    </button>

    <div id="chatbot-toggle" class="chatbot-toggle">
    <div class="chatbot-toggle-avatar">
      <img src="js/images/gabay.png" class="chatbot-avatar">
    </div>
    <div class="chatbot-toggle-pulse"></div>
  </div>
  
  <!-- Chatbot Container -->
  <div id="chatbot-container" class="chatbot-container hidden">
    <div class="chatbot-header">
      <div class="chatbot-header-info">
        <div class="chatbot-avatar-small-container">
          <img src="js/images/gabay.png" class="chatbot-avatar-small">
        </div>
        <div class="chatbot-header-text">
          <span class="chatbot-title">Gabay AI Support</span>
          <span class="chatbot-status">Online</span>
        </div>
      </div>
      <div class="chatbot-header-actions">
        <button id="chatbot-clear" class="chatbot-action-button" title="Clear conversation">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"></path><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path></svg>
        </button>
        <button id="chatbot-close" class="chatbot-action-button" title="Close chat">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
      </div>
    </div>
    
    <div class="chatbot-messages" id="chatbot-messages">
      <div class="message-container">
        <div class="chatbot-message chatbot-message-received">
          <div class="message-content">
            Kumusta! 👋 Ako si Gabay, nandito ako para tumulong. Anong maitutulong ko sa'yo ngayon?
          </div>
          <div class="message-time">12:00 PM</div>
        </div>
      </div>
    </div>
    
    <div id="quick-topics" class="quick-topics">
      <div class="quick-topics-title">Madalas na Tanong:</div>
      <div class="quick-topics-container">
        <button class="quick-topic-button" data-topic="track">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
          Track Order
        </button>
        <button class="quick-topic-button" data-topic="how-to-use">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="M12 5l7 7-7 7"></path></svg>
          How to Use
        </button>
        <button class="quick-topic-button" data-topic="contact">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
          Contact Support
        </button>
        <button class="quick-topic-button" data-topic="issue">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
          Report Issue
        </button>
      </div>
    </div>
    
    <div id="typing-indicator" class="typing-indicator hidden">
      <div class="typing-bubble">
        <div class="typing-dot"></div>
        <div class="typing-dot"></div>
        <div class="typing-dot"></div>
      </div>
      <div class="typing-text">Si Gabay ay nagta-type...</div>
    </div>
    
    <div class="chatbot-input-area">
      <div class="chatbot-input-container">
        <input type="text" id="chatbot-input" placeholder="Type your message...">
      </div>
      <button id="chatbot-send" class="chatbot-send-button" title="Send message">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
      </button>
    </div>
  </div>

    <script src="js/gabay.js"></script>
    <script src="js/main.js"></script>
</body>
</html>