<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - HatidGawa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
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
                    <a href="index.php" class="nav-link">Home</a>
                    <a href="about.php" class="nav-link">About</a>
                    <a href="tasks.php" class="nav-link">Tasks</a>
                    <a href="index.php#how-it-works" class="nav-link">How It Works</a>
                </div>
                
                <div class="navbar-actions">
                    <a href="login.php" class="btn btn-outline">Log In</a>
                    <a href="register.php" class="btn btn-primary active">Sign Up</a>
                </div>
                
                <button class="mobile-menu-btn" id="mobile-menu-btn">
                    <i class="fas fa-bars"></i>
                </button>
            </nav>
            
            <div class="mobile-menu">
                <a href="index.php" class="nav-link">Home</a>
                <a href="about.php" class="nav-link">About</a>
                <a href="tasks.php" class="nav-link">Tasks</a>
                <a href="index.php#how-it-works" class="nav-link">How It Works</a>
                <div class="mt-4">
                    <a href="login.php" class="btn btn-outline w-100 mb-2">Log In</a>
                    <a href="register.php" class="btn btn-primary w-100 active">Sign Up</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Register Section -->
    <section class="py-8">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card shadow-lg animate-fadeIn">
                        <div class="card-header text-center p-4">
                            <h2 class="card-title">Create Your Account</h2>
                            <p class="card-subtitle">Join the HatidGawa community today</p>
                        </div>
                        <div class="card-body p-5">
                            <form method="POST" action="auth/register.php" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="first-name" class="form-label">First Name</label>
                                            <input type="text" id="first-name" name="first_name" class="form-control" placeholder="First Name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="last-name" class="form-label">Last Name</label>
                                            <input type="text" id="last-name" name="last_name" class="form-control" placeholder="Last Name" required>
                                        </div>
                                    </div>
                                </div>
                            
                                <div class="form-group">
                                    <label for="register-email" class="form-label">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-envelope"></i>
                                        </span>
                                        <input type="email" id="register-email" name="email" class="form-control" placeholder="your@email.com" required>
                                    </div>
                                </div>
                            
                                <div class="form-group">
                                    <label for="phone-number" class="form-label">Phone Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-phone"></i>
                                        </span>
                                        <input type="tel" id="phone-number" name="phone" class="form-control" placeholder="09XX XXX XXXX" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="address" class="form-label">Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </span>
                                        <input type="text" id="address" name="address" class="form-control" placeholder="Enter your address" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="barangay-id" class="form-label">Barangay ID / Valid ID <span class="text-danger">*</span></label>
                                    <input type="file" id="barangay-id" name="barangay_id" class="form-control" accept="image/*,application/pdf" required>
                                    <small class="text-muted">Upload a clear photo or PDF of your Barangay ID or any valid government ID.</small>
                                </div>
                            
                                <div class="form-group">
                                    <label for="register-password" class="form-label">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                        <input type="password" id="register-password" name="password" class="form-control" placeholder="Your Password" required>
                                        <button type="button" class="btn btn-ghost toggle-password">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="password-strength mt-2">
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                        </div>
                                        <small class="text-gray">Password must be at least 8 characters with letters, numbers, and symbols</small>
                                    </div>
                                </div>
                            
                                <div class="form-group">
                                    <label for="confirm-password" class="form-label">Confirm Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                        <input type="password" id="confirm-password" name="confirm_password" class="form-control" placeholder="Your Password" required>
                                        <button type="button" class="btn btn-ghost toggle-password">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            
                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" id="terms-agree" name="terms" class="form-check-input" required>
                                        <label for="terms-agree" class="form-check-label">
                                            I agree to the <a href="terms.php">Terms of Service</a> and <a href="privacy.php">Privacy Policy</a>
                                        </label>
                                    </div>
                                </div>
                            
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-user-plus me-2"></i>
                                        Create Account
                                    </button>
                                </div>
                            </form>
                            
                            
                            <div class="text-center mt-4">
                                <p class="mb-3">Or sign up with</p>
                                <div class="d-flex justify-content-center gap-3">
                                    <button class="btn btn-outline flex-1">
                                        <i class="fab fa-google me-2"></i>
                                        Google
                                    </button>
                                    <button class="btn btn-outline flex-1">
                                        <i class="fab fa-facebook-f me-2"></i>
                                        Facebook
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-center p-4">
                            <p class="mb-0">Already have an account? <a href="login.php">Log in</a></p>
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
                <p>&copy; 2023 HatidGawa. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Dark Mode Toggle -->
    <button id="dark-mode-toggle" class="btn btn-icon btn-ghost position-fixed">
        <i class="fas fa-moon"></i>
    </button>

    <script src="js/main.js"></script>
    <script>


        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const passwordInput = this.previousElementSibling;
                const icon = this.querySelector('i');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });

        // Password strength meter
        const passwordInput = document.getElementById('register-password');
        const progressBar = document.querySelector('.password-strength .progress-bar');
        
        if (passwordInput && progressBar) {
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                
                if (password.length >= 8) strength += 25;
                if (password.match(/[a-z]+/)) strength += 25;
                if (password.match(/[A-Z]+/)) strength += 25;
                if (password.match(/[0-9]+/) || password.match(/[^a-zA-Z0-9]+/)) strength += 25;
                
                progressBar.style.width = strength + '%';
                
                if (strength < 50) {
                    progressBar.className = 'progress-bar bg-danger';
                } else if (strength < 75) {
                    progressBar.className = 'progress-bar bg-warning';
                } else {
                    progressBar.className = 'progress-bar bg-success';
                }
            });
        }
    </script>
</body>
</> 