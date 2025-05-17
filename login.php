<?php
require_once('auth/db.php');
session_start();

// Process forgot password form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && !isset($_POST['password'])) {
    $email = trim($_POST['email']);
    
    // Check if email exists in database
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Generate unique token
        $token = bin2hex(random_bytes(32));
        
        // Set expiration time (24 hours from now)
        $expires_at = date('Y-m-d H:i:s', time() + 86400);
        
        // Delete any existing tokens for this email
        $stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        // Store new token
        $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $token, $expires_at);
        $stmt->execute();
        
        // For localhost testing - display the reset link instead of sending email
        $reset_link = "http://localhost/your-project-folder/reset-password.php?token=" . $token;
        
        // Success message with link for localhost testing
        $forgot_success = "Reset link generated. In production, this would be emailed to the user.<br>
                          <a href='$reset_link' class='alert-link'>Click here to reset password</a>";
        
        /* 
        // Production code for sending email
        $to = $email;
        $subject = "Password Reset for HatidGawa";
        $message = "
            <html>
            <head>
                <title>Password Reset</title>
            </head>
            <body>
                <h2>Reset Your Password</h2>
                <p>Click the link below to reset your password:</p>
                <p><a href='$reset_link'>Reset Password</a></p>
                <p>This link will expire in 24 hours.</p>
                <p>If you did not request this reset, please ignore this email.</p>
            </body>
            </html>
        ";
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: noreply@hatidgawa.com" . "\r\n";
        
        mail($to, $subject, $message, $headers);
        
        $forgot_success = "A password reset link has been sent to your email address.";
        */
    } else {
        $forgot_error = "Email not found in our records.";
    }
    
    $stmt->close();
}

// Rest of your login processing code goes here...
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - HatidGawa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/modal.css">
    <link rel="icon" href="assets/images/logo.svg">
</head>
<body>


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
                    <a href="login.php" class="btn btn-outline active">Log In</a>
                    <a href="register.php" class="btn btn-primary">Sign Up</a>
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
                    <a href="login.php" class="btn btn-outline w-100 mb-2 active">Log In</a>
                    <a href="register.php" class="btn btn-primary w-100">Sign Up</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Login Section -->
    <section class="py-8">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card shadow-lg animate-fadeIn">
                        <div class="card-header text-center p-4">
                            <h2 class="card-title">Welcome Back</h2>
                            <p class="card-subtitle">Log in to your HatidGawa account</p>
                        </div>
                        <div class="card-body p-5">
                            <form id="login-form" method="POST" action="auth/login.php">
                                <div class="form-group">
                                    <label for="login-email" class="form-label">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-envelope"></i>
                                        </span>
                                        <input type="email" id="login-email" name="email" class="form-control" placeholder="your@email.com" required>
                                    </div>
                                </div>
                            
                                <div class="form-group">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label for="login-password" class="form-label">Password</label>
                                        <a href="#" class="text-sm" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">Forgot Password?</a>
                                    </div>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                        <input type="password" id="login-password" name="password" class="form-control" placeholder="yourpassword" required>
                                        <button type="button" class="btn btn-ghost toggle-password">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            
                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" id="remember-me" class="form-check-input">
                                        <label for="remember-me" class="form-check-label">Remember me</label>
                                    </div>
                                </div>
                            
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-sign-in-alt me-2"></i>
                                        Log In
                                    </button>
                                </div>
                            </form>
                            
                            
                            <div class="text-center mt-4">
                                <p class="mb-3">Or log in with</p>
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
                            <p class="mb-0">Don't have an account? <a href="register.php">Sign up</a> for free</p>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
        // Make sure your form has the correct action
document.getElementById('forgot-password-form').setAttribute('action', 'login.php');
    </script>

    <!-- Forgot Password Modal -->
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <form id="forgot-password-form" method="POST" action="auth/forgot_password.php">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="forgotPasswordModalLabel">Forgot Password</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <p>Enter your email address and we'll send you a link to reset your password.</p>
              <div class="form-group mb-3">
                <label for="forgot-email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="forgot-email" name="email" required>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Send Reset Link</button>
            </div>
          </div>
        </form>
      </div>
    </div>
</body>
</html>
