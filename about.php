<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - HatidGawa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/gabay.css">
    <link rel="icon" href="assets/images/logo.svg">
</head>
<body>
   <!-- Loading Overlay 
    <div id="loading-overlay" class="loading-overlay">
        <div class="spinner">
            <i class="fas fa-circle-notch fa-spin"></i>
        </div>
    </div>-->

    <!-- Header -->
    <header>
        <div class="container">
            <nav class="navbar">
                <a href="index.php" class="navbar-brand">
                    <i class="fas fa-hands-helping"></i>
                    HatidGawa
                </a>
                
                <div class="navbar-nav">
                    <a href="index.php" class="nav-link">Home</a>
                    <a href="about.php" class="nav-link active">About</a>
                    <a href="tasks.php" class="nav-link">Tasks</a>
                    <a href="index.php#how-it-works" class="nav-link">How It Works</a>
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
                <a href="index.php" class="nav-link">Home</a>
                <a href="about.php" class="nav-link active">About</a>
                <a href="tasks.php" class="nav-link">Tasks</a>
                <a href="index.php#how-it-works" class="nav-link">How It Works</a>
                <div class="mt-4" id="mobile-auth-nav">
                    <a href="login.php" class="btn btn-outline w-100 mb-2">Log In</a>
                    <a href="register.php" class="btn btn-primary w-100">Sign Up</a>
                </div>
            </div>
        </div>
    </header>

    <!-- About Hero Section -->
    <section class="hero hero-sm bg-primary text-white">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <div class="hero-content">
                        <h1>About HatidGawa</h1>
                        <p>Learn about our mission to build stronger communities through task sharing and mutual support.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Story Section -->
    <section class="py-8">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 mb-5 mb-md-0">
                    <img src="img/IMG_20250426_171845_418.jpg" alt="Our Story" class="img-fluid rounded-lg shadow-lg animate-fadeIn">
                </div>
                <div class="col-md-6">
                    <h2 class="mb-4">Our Story</h2>
                    <p class="mb-4">HatidGawa was born from a simple observation: in our busy modern lives, we often don't know our neighbors, yet we all need help sometimes. We saw communities where people lived side by side but rarely connected.</p>
                    <p class="mb-4">Founded in 2025 by a group of young advocates, HatidGawa aims to revive the Filipino "bayanihan" spirit in urban settings. We believe that by helping each other with everyday tasks, we can build stronger, more resilient communities.</p>
                    <p>Our platform connects people who need help with those who have the skills and time to assist, all while prioritizing safety, trust, and community building.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission & Values Section -->
    <section class="py-8 bg-light">
        <div class="container">
            <div class="text-center mb-6">
                <h2 class="text-dark mb-2">Our Mission & Values</h2>
                <p class="text-gray">The principles that guide everything we do</p>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center p-5">
                            <div class="feature-icon mb-4">
                                <i class="fas fa-handshake text-primary fa-3x"></i>
                            </div>
                            <h3 class="card-title">Community First</h3>
                            <p class="card-text">We believe in the power of local connections. Our platform is designed to strengthen neighborhoods by encouraging face-to-face interactions and mutual support.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center p-5">
                            <div class="feature-icon mb-4">
                                <i class="fas fa-shield-alt text-primary fa-3x"></i>
                            </div>
                            <h3 class="card-title">Safety & Trust</h3>
                            <p class="card-text">We prioritize user safety through verification processes, safe meeting zones, and community ratings. Trust is the foundation of our platform.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center p-5">
                            <div class="feature-icon mb-4">
                                <i class="fas fa-users text-primary fa-3x"></i>
                            </div>
                            <h3 class="card-title">Inclusivity</h3>
                            <p class="card-text">We're building a platform for everyone. HatidGawa welcomes users of all backgrounds, ages, and abilities to participate in our community.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="py-8">
        <div class="container">
            <div class="text-center mb-6">
                <h2 class="text-dark mb-2">Meet Our Team</h2>
                <p class="text-gray">The passionate people behind HatidGawa</p>
            </div>
            
            <div class="row">
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center p-4">
                            <div class="avatar avatar-lg mb-3">
                                <img src="https://scontent.fceb2-2.fna.fbcdn.net/v/t39.30808-6/476803872_1682456516036697_5141040908219090642_n.jpg?_nc_cat=111&ccb=1-7&_nc_sid=f727a1&_nc_eui2=AeF1Pp5g0lvfOpaU4onfw21fAniHkC6Mk88CeIeQLoyTzxtoqZJW4BtRSulyIWc9mwivAdWpZDQmvpsKZyFT5Q11&_nc_ohc=k1Gk5X2nDcgQ7kNvwGJzeDu&_nc_oc=Adl-9quF0r__Pgx4ypn9MBFRH03bCKNYyxXk5Py7OrpWINTrObr_gTbhY81QRDb8kdw&_nc_zt=23&_nc_ht=scontent.fceb2-2.fna&_nc_gid=wSX0aZS2r2fUjdgu9ZmS4g&oh=00_AfGmKwEPQ6OG9Cm8GLBXV-_GUuEFu2dnyIMX6EoucUy7yA&oe=68125F28" alt="Juan Dela Cruz">
                            </div>
                            <h4 class="card-title mb-1">Vincent B. Layon<br><br></h4>
                            <p class="text-primary mb-3">Front-end Developer & Web Designer</p>
                            <p class="card-text">2nd year BSIT Student in SUNN.</p>
                            <div class="d-flex justify-content-center gap-2 mt-3">
                                <a href="mailto:vincentlayonuser@gmail.com" class="btn btn-icon btn-sm btn-ghost">
                                    <i class="fa-solid fa-at"></i>
                                </a>
                                <a href="https://web.facebook.com/VincentLayonuser" class="btn btn-icon btn-sm btn-ghost">
                                    <i class="fa-brands fa-facebook"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center p-4">
                            <div class="avatar avatar-lg mb-3">
                                <img src="https://scontent.fceb2-1.fna.fbcdn.net/v/t39.30808-6/462322132_122105189258552456_4473636246314490166_n.jpg?_nc_cat=106&ccb=1-7&_nc_sid=6ee11a&_nc_eui2=AeEHVSv141veLSbJxxKJq-deFBs6WiXrRYIUGzpaJetFgkZEBiQjYtA2LCUxw3Hezykaps5Pm5Bc0sf0LQp2kjEF&_nc_ohc=K2Ml71cKUnsQ7kNvwGS_OH7&_nc_oc=Admy3zZ80V6hUHehlDooZEJi4o9CSFrCVs10EQbTZ_j1uEtzIxR0Kvu2_mDFdZBcves&_nc_zt=23&_nc_ht=scontent.fceb2-1.fna&_nc_gid=Z79H3FBx4LG3q0zZlfr4DQ&oh=00_AfH-Wsel-ijU9imYgJLUV0IbMXPH-QOIR-1T0TLT_V9VPQ&oe=68126A71" alt="Maria Santos">
                            </div>
                            <h4 class="card-title mb-1">Charles Donuel C.<br>Mag-alasin</h4>
                            <p class="text-primary mb-3">UI/UX Developer & Project Manager</p>
                            <p class="card-text">2nd year BSIT Student in SUNN</p>
                            <div class="d-flex justify-content-center gap-2 mt-3">
                                <a href="https://charles-web.netlify.app" class="btn btn-icon btn-sm btn-ghost">
                                    <i class="fa-solid fa-earth-asia"></i>
                                </a>
                                <a href="https://web.facebook.com/charles.mag.alasin.2024" class="btn btn-icon btn-sm btn-ghost">
                                    <i class="fa-brands fa-facebook"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center p-4">
                            <div class="avatar avatar-lg mb-3">
                                <img src="https://scontent.fceb2-2.fna.fbcdn.net/v/t39.30808-6/489846629_2669376966740966_2820453978099975592_n.jpg?_nc_cat=111&ccb=1-7&_nc_sid=a5f93a&_nc_eui2=AeEk8FOI0o-xYbDyUbefFArgNIgOlcXvhUQ0iA6Vxe-FRMHDiyxH2iuHb91t398IA3qTGIsonD4DZhIyJh1KV4Wy&_nc_ohc=psae1gUqiw0Q7kNvwGWCP0B&_nc_oc=Adl4qy2XXUTr-aRJTkMvYZDa660Bzwc9xILlkOL1d6IUNYpNC05VSqxoFeYt6ixefi0&_nc_zt=23&_nc_ht=scontent.fceb2-2.fna&_nc_gid=-oYt_vJ1z9mcxmL4Dgrc5A&oh=00_AfGtM4PBnqei94UNu0N2oNq9LTZnw_IuMfN7-yUPh_fNMw&oe=68128B7C" alt="Carlos Mendoza">
                            </div>
                            <h4 class="card-title mb-1">Eliezer Santillan<br><br></h4>
                            <p class="text-primary mb-3">Back-end Developer & Database Administrator</p>
                            <p class="card-text">Adiktus</p>
                            <div class="d-flex justify-content-center gap-2 mt-3">
                                <a href="https://eliezer-santillan.netlify.app" class="btn btn-icon btn-sm btn-ghost">
                                    <i class="fa-solid fa-earth-asia"></i>
                                </a>
                                <a href="https://web.facebook.com/httpselie" class="btn btn-icon btn-sm btn-ghost">
                                    <i class="fa-brands fa-facebook"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center p-4">
                            <div class="avatar avatar-lg mb-3">
                                <img src="https://scontent.fceb6-1.fna.fbcdn.net/v/t1.6435-9/64880529_10218976795320844_4215254854764855296_n.jpg?_nc_cat=100&ccb=1-7&_nc_sid=f727a1&_nc_eui2=AeFyLYkmWzKgEjt_KxlFLjLsTQV7ocREGzJNBXuhxEQbMo8_Chtti9JoD6BCeWT3VNQ1ESRPgRg1_5xFzNQY801_&_nc_ohc=qwHnF_BXU5gQ7kNvwGecoGk&_nc_oc=Adl-yyJ9i1xk3A39TYAQu45yexF55wCjB64OxWI0JjvKQFl78RdEFrIiUuufKEXXsTA&_nc_zt=23&_nc_ht=scontent.fceb6-1.fna&_nc_gid=ZBrxAgDXn0a02N2dwua-yQ&oh=00_AfF21_g6qcuNg9kNfFepIp0fBtR-ORY9kEQNrTRGeeXOQg&oe=68342348" alt="Ana Reyes">
                            </div>
                            <h4 class="card-title mb-1">Ms. Dada Sabete Pehid<br><br></h4>
                            <p class="text-primary mb-3">Superviser</p>
                            <p class="card-text">Community engagement specialist focused on building trust and safety.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Impact Section -->
    <section class="py-8 bg-light">
        <div class="container">
            <div class="text-center mb-6">
                <h2 class="text-dark mb-2">Our Impact</h2>
                <p class="text-gray">How HatidGawa is making a difference</p>
            </div>
            
            <div class="row text-center">
                <div class="col-md-3 col-6 mb-4">
                    <div class="impact-stat">
                        <div class="impact-number">10,000+</div>
                        <div class="impact-label">Registered Users</div>
                    </div>
                </div>
                
                <div class="col-md-3 col-6 mb-4">
                    <div class="impact-stat">
                        <div class="impact-number">25,000+</div>
                        <div class="impact-label">Tasks Completed</div>
                    </div>
                </div>
                
                <div class="col-md-3 col-6 mb-4">
                    <div class="impact-stat">
                        <div class="impact-number">50+</div>
                        <div class="impact-label">Communities Served</div>
                    </div>
                </div>
                
                <div class="col-md-3 col-6 mb-4">
                    <div class="impact-stat">
                        <div class="impact-number">₱5M+</div>
                        <div class="impact-label">Income Generated</div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-5">
                <div class="col-md-8 mx-auto">
                    <div class="card">
                        <div class="card-body p-5">
                            <h3 class="card-title text-center mb-4">Success Stories</h3>
                            
                            <div class="testimonial mb-5">
                                <p class="testimonial-text">"HatidGawa helped me find reliable assistance for my elderly mother when I couldn't be there. The community verification system gave us peace of mind, and now we have a regular helper who's become like family."</p>
                                <div class="d-flex align-items-center mt-3">
                                    <div class="avatar me-3">
                                        <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="Testimonial">
                                    </div>
                                    <div>
                                        <h5 class="mb-0">Lorna Pascual</h5>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="testimonial">
                                <p class="testimonial-text">"As a college student, I've been able to earn extra income by helping neighbors with errands and tutoring. HatidGawa has connected me with opportunities I wouldn't have found otherwise, and I've met amazing people in my community."</p>
                                <div class="d-flex align-items-center mt-3">
                                    <div class="avatar me-3">
                                        <img src="https://randomuser.me/api/portraits/men/42.jpg" alt="Testimonial">
                                    </div>
                                    <div>
                                        <h5 class="mb-0">Miguel Ramos</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="py-8 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="card shadow-lg">
                        <div class="card-body p-0">
                            <div class="row">
                                <div class="col-md-5 bg-primary text-white p-5">
                                    <h3 class="mb-4">Get In Touch</h3>
                                    <p class="mb-5">Have questions about HatidGawa? We'd love to hear from you. Reach out to our team using the contact form or through our contact information.</p>
                                    
                                    <div class="contact-info">
                                        <div class="d-flex mb-4">
                                            <div class="icon me-3">
                                                <i class="fas fa-map-marker-alt"></i>
                                            </div>
                                            <div>
                                                <h5 class="mb-1">Address</h5>
                                                <p class="mb-0">Barangay Old Sagay, Sagay City, Negros Occidental</p>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex mb-4">
                                            <div class="icon me-3">
                                                <i class="fas fa-envelope"></i>
                                            </div>
                                            <div>
                                                <h5 class="mb-1">Email</h5>
                                                <p class="mb-0">contact@hatidgawa.com</p>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex">
                                            <div class="icon me-3">
                                                <i class="fas fa-phone"></i>
                                            </div>
                                            <div>
                                                <h5 class="mb-1">Phone</h5>
                                                <p class="mb-0">0922-122-6051</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-7 p-5">
                                    <h3 class="mb-4">Send Us a Message</h3>
                                    <form id="contact-form">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="contact-name" class="form-label">Your Name</label>
                                                    <input type="text" id="contact-name" class="form-control" placeholder="Your Name" required>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="contact-email" class="form-label">Email Address</label>
                                                    <input type="email" id="contact-email" class="form-control" placeholder="Your Email" required>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="contact-subject" class="form-label">Subject</label>
                                            <input type="text" id="contact-subject" class="form-control" placeholder="Subject" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="contact-message" class="form-label">Message</label>
                                            <textarea id="contact-message" class="form-control" rows="5" placeholder="Your Message" required></textarea>
                                        </div>
                                        
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-paper-plane me-2"></i>
                                                Send Message
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
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
                        <li><a href="index.php#how-it-works">How It Works</a></li>
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
        <i class="fas fa-moon"></i>
    </button>
  <!-- Chatbot Toggle Button -->
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
      <div class="chat-day-divider">
        <span>Today</span>
      </div>
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
        <button class="quick-topic-button" data-topic="delivery">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="M12 5l7 7-7 7"></path></svg>
          Delivery Status
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
        <button id="chatbot-attach" class="chatbot-input-action" title="Attach file">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
        </button>
      </div>
      <button id="chatbot-send" class="chatbot-send-button" title="Send message">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
      </button>
    </div>
  </div>
    <script src="js/mockData.js"></script>
    <script src="js/main.js"></script>
    <script>
        // Hide loading overlay when page is loaded
        window.addEventListener('load', function() {
            const loadingOverlay = document.getElementById('loading-overlay');
            if (loadingOverlay) {
                loadingOverlay.classList.add('hidden');
                setTimeout(() => {
                    loadingOverlay.style.display = 'none';
                }, 500);
            }
        });
    </script>
</body>
</html>
