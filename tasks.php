
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tasks - HatidGawa</title>
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
                    <a href="tasks.php" class="nav-link active">Tasks</a>
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
                <a href="about.php" class="nav-link">About</a>
                <a href="tasks.php" class="nav-link active">Tasks</a>
                <a href="index.php#how-it-works" class="nav-link">How It Works</a>
                <div class="mt-4" id="mobile-auth-nav">
                    <a href="login.php" class="btn btn-outline w-100 mb-2">Log In</a>
                    <a href="register.php" class="btn btn-primary w-100">Sign Up</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Tasks Hero Section -->
    <section class="hero hero-sm bg-primary text-white">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <div class="hero-content">
                        <h1>Browse Tasks</h1>
                        <p>Find tasks that need help or post your own task to get assistance from the community.</p>
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-center justify-content-end">
                    <button class="btn btn-accent btn-lg" id="post-task-btn">
                        <i class="fas fa-plus me-2"></i>
                        Post a Task
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Tasks Section -->
    <section class="py-6">
        <div class="container">
            <div class="row">
                <!-- Filters Sidebar -->
                <div class="col-lg-3 mb-4">
                    <div class="card sticky-top" style="top: 100px;">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Filters</h4>
                        </div>
                        <div class="card-body">
                            <form id="filter-form">
                                <div class="form-group">
                                    <label for="search-tasks" class="form-label">Search</label>
                                    <div class="input-group">
                                        <input type="text" id="search-tasks" class="form-control" placeholder="Search tasks...">
                                        <button type="button" class="btn btn-primary">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Categories</label>
                                    <div class="form-check">
                                        <input type="checkbox" id="category-delivery" class="form-check-input" value="delivery">
                                        <label for="category-delivery" class="form-check-label">Delivery</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" id="category-repairs" class="form-check-input" value="repairs">
                                        <label for="category-repairs" class="form-check-label">Home Repairs</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" id="category-tutoring" class="form-check-input" value="tutoring">
                                        <label for="category-tutoring" class="form-check-label">Tutoring</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" id="category-cleaning" class="form-check-input" value="cleaning">
                                        <label for="category-cleaning" class="form-check-label">Cleaning</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" id="category-errands" class="form-check-input" value="errands">
                                        <label for="category-errands" class="form-check-label">Errands</label>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="location-filter" class="form-label">Location</label>
                                    <select id="location-filter" class="form-control">
                                        <option value="">All Locations</option>
                                        <option value="makati">Makati City</option>
                                        <option value="manila">Manila City</option>
                                        <option value="quezon">Quezon City</option>
                                        <option value="pasig">Pasig City</option>
                                        <option value="taguig">Taguig City</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Price Range</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="number" id="price-min" class="form-control" placeholder="Min">
                                        <span>-</span>
                                        <input type="number" id="price-max" class="form-control" placeholder="Max">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Task Status</label>
                                    <div class="form-check">
                                        <input type="radio" id="status-all" name="status" class="form-check-input" value="all" checked>
                                        <label for="status-all" class="form-check-label">All Tasks</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="radio" id="status-open" name="status" class="form-check-input" value="open">
                                        <label for="status-open" class="form-check-label">Open Tasks</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="radio" id="status-assigned" name="status" class="form-check-input" value="assigned">
                                        <label for="status-assigned" class="form-check-label">Assigned Tasks</label>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                                    <button type="reset" class="btn btn-outline">Reset Filters</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Tasks List -->
                <div class="col-lg-9">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h3 class="mb-0">Available Tasks</h3>
                            <p class="text-gray mb-0">Showing <span id="task-count">12</span> tasks</p>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <label for="sort-tasks" class="form-label mb-0">Sort by:</label>
                            <select id="sort-tasks" class="form-control form-control-sm" style="width: auto;">
                                <option value="newest">Newest First</option>
                                <option value="oldest">Oldest First</option>
                                <option value="price-high">Price: High to Low</option>
                                <option value="price-low">Price: Low to High</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row" id="tasks-container">
                        <!-- Task cards will be populated here -->
                        
                        <!-- Sample Task Card 1 -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h3 class="card-title">Grocery Delivery</h3>
                                        <span class="badge badge-primary">₱200</span>
                                    </div>
                                    <p class="card-text">Need someone to pick up groceries from SM Megamall and deliver to my home in Mandaluyong. List will be provided.</p>
                                    <div class="task-meta mt-4">
                                        <div class="task-meta-item">
                                            <i class="fas fa-tag"></i>
                                            <span>Delivery</span>
                                        </div>
                                        <div class="task-meta-item">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span>Mandaluyong City</span>
                                        </div>
                                        <div class="task-meta-item">
                                            <i class="fas fa-calendar-alt"></i>
                                            <span>May 15, 2023</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            <img src="https://randomuser.me/api/portraits/women/45.jpg" alt="Task Poster">
                                        </div>
                                        <span>Ana Reyes</span>
                                    </div>
                                    <a href="#" class="btn btn-primary btn-sm view-task-btn" data-task-id="1">View Details</a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sample Task Card 2 -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h3 class="card-title">Fix Leaking Faucet</h3>
                                        <span class="badge badge-primary">₱350</span>
                                    </div>
                                    <p class="card-text">Need someone with plumbing experience to fix a leaking faucet in my kitchen. Tools will be provided if needed.</p>
                                    <div class="task-meta mt-4">
                                        <div class="task-meta-item">
                                            <i class="fas fa-tag"></i>
                                            <span>Home Repairs</span>
                                        </div>
                                        <div class="task-meta-item">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span>Makati City</span>
                                        </div>
                                        <div class="task-meta-item">
                                            <i class="fas fa-calendar-alt"></i>
                                            <span>May 16, 2023</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Task Poster">
                                        </div>
                                        <span>Juan Dela Cruz</span>
                                    </div>
                                    <a href="#" class="btn btn-primary btn-sm view-task-btn" data-task-id="2">View Details</a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sample Task Card 3 -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h3 class="card-title">Math Tutoring</h3>
                                        <span class="badge badge-primary">₱400</span>
                                    </div>
                                    <p class="card-text">Looking for a tutor to help my high school student with Algebra. Need 2 hours of tutoring twice a week.</p>
                                    <div class="task-meta mt-4">
                                        <div class="task-meta-item">
                                            <i class="fas fa-tag"></i>
                                            <span>Tutoring</span>
                                        </div>
                                        <div class="task-meta-item">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span>Quezon City</span>
                                        </div>
                                        <div class="task-meta-item">
                                            <i class="fas fa-calendar-alt"></i>
                                            <span>May 17, 2023</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            <img src="https://randomuser.me/api/portraits/men/67.jpg" alt="Task Poster">
                                        </div>
                                        <span>Carlos Mendoza</span>
                                    </div>
                                    <a href="#" class="btn btn-primary btn-sm view-task-btn" data-task-id="3">View Details</a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sample Task Card 4 -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h3 class="card-title">House Cleaning</h3>
                                        <span class="badge badge-primary">₱500</span>
                                    </div>
                                    <p class="card-text">Need help cleaning a 2-bedroom apartment. Tasks include dusting, vacuuming, mopping, and bathroom cleaning.</p>
                                    <div class="task-meta mt-4">
                                        <div class="task-meta-item">
                                            <i class="fas fa-tag"></i>
                                            <span>Cleaning</span>
                                        </div>
                                        <div class="task-meta-item">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span>Pasig City</span>
                                        </div>
                                        <div class="task-meta-item">
                                            <i class="fas fa-calendar-alt"></i>
                                            <span>May 18, 2023</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            <img src="https://randomuser.me/api/portraits/women/22.jpg" alt="Task Poster">
                                        </div>
                                        <span>Maria Santos</span>
                                    </div>
                                    <a href="#" class="btn btn-primary btn-sm view-task-btn" data-task-id="4">View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-5">
                        <nav aria-label="Page navigation">
                            <ul class="pagination">
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" aria-label="Previous">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item">
                                    <a class="page-link" href="#" aria-label="Next">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Task Detail Modal -->
    <div class="modal" id="task-detail-modal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="task-detail-title">Task Details</h4>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="task-detail-content">
                                <h3 id="task-detail-name">Grocery Delivery</h3>
                                <div class="d-flex align-items-center mb-4">
                                    <span class="badge badge-primary me-3" id="task-detail-price">₱200</span>
                                    <div class="task-meta-item me-3">
                                        <i class="fas fa-tag"></i>
                                        <span id="task-detail-category">Delivery</span>
                                    </div>
                                    <div class="task-meta-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span id="task-detail-location">Mandaluyong City</span>
                                    </div>
                                </div>
                                
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">Description</h5>
                                    </div>
                                    <div class="card-body">
                                        <p id="task-detail-description">Need someone to pick up groceries from SM Megamall and deliver to my home in Mandaluyong. List will be provided.</p>
                                    </div>
                                </div>
                                
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">Requirements</h5>
                                    </div>
                                    <div class="card-body">
                                        <ul id="task-detail-requirements">
                                            <li>Must have transportation</li>
                                            <li>Must be able to carry grocery bags</li>
                                            <li>Must be available on the specified date</li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Meeting Location</h5>
                                    </div>
                                    <div class="card-body">
                                        <p id="task-detail-meeting">SM Megamall, EDSA corner Doña Julia Vargas Ave, Ortigas Center, Mandaluyong</p>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-shield-alt text-success me-2"></i>
                                            <span class="text-success">Safe Zone Location</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Task Details</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-gray">Status:</span>
                                        <span class="badge badge-success" id="task-detail-status">Open</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-gray">Date Posted:</span>
                                        <span id="task-detail-date-posted">May 12, 2023</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-gray">Due Date:</span>
                                        <span id="task-detail-due-date">May 15, 2023</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-gray">Estimated Time:</span>
                                        <span id="task-detail-time">1-2 hours</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Posted By</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar me-3" id="task-detail-poster-avatar">
                                            <img src="https://randomuser.me/api/portraits/women/45.jpg" alt="Task Poster">
                                        </div>
                                        <div>
                                            <h5 class="mb-0" id="task-detail-poster-name">Ana Reyes</h5>
                                            <div class="d-flex align-items-center">
                                                <div class="rating me-1">
                                                    <i class="fas fa-star text-warning"></i>
                                                    <i class="fas fa-star text-warning"></i>
                                                    <i class="fas fa-star text-warning"></i>
                                                    <i class="fas fa-star text-warning"></i>
                                                    <i class="fas fa-star-half-alt text-warning"></i>
                                                </div>
                                                <span>(4.5)</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-gray">Member Since:</span>
                                        <span>Jan 2023</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-gray">Tasks Posted:</span>
                                        <span>12</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-gray">Verification:</span>
                                        <span class="text-success">
                                            <i class="fas fa-check-circle"></i> Verified
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary" id="apply-task-btn">
                                    <i class="fas fa-hand-paper me-2"></i>
                                    Apply for Task
                                </button>
                                <button class="btn btn-outline">
                                    <i class="fas fa-bookmark me-2"></i>
                                    Save Task
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Post Task Modal -->
    <div class="modal" id="post-task-modal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Post a New Task</h4>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="post-task-form">
                        <div class="form-group">
                            <label for="task-title" class="form-label">Task Title</label>
                            <input type="text" id="task-title" class="form-control" placeholder="Enter a clear title for your task" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="task-category" class="form-label">Category</label>
                                    <select id="task-category" class="form-control" required>
                                        <option value="" selected disabled>Select a category</option>
                                        <option value="delivery">Delivery</option>
                                        <option value="repairs">Home Repairs</option>
                                        <option value="tutoring">Tutoring</option>
                                        <option value="cleaning">Cleaning</option>
                                        <option value="errands">Errands</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="task-price" class="form-label">Budget (₱)</label>
                                    <input type="number" id="task-price" class="form-control" placeholder="Enter your budget" min="50" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="task-description" class="form-label">Description</label>
                            <textarea id="task-description" class="form-control" rows="5" placeholder="Describe the task in detail" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="task-requirements" class="form-label">Requirements</label>
                            <textarea id="task-requirements" class="form-control" rows="3" placeholder="List any specific requirements or skills needed"></textarea>
                            <small class="text-gray">Enter each requirement on a new line</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="task-location" class="form-label">Location</label>
                                    <select id="task-location" class="form-control" required>
                                        <option value="" selected disabled>Select a location</option>
                                        <option value="makati">Makati City</option>
                                        <option value="manila">Manila City</option>
                                        <option value="quezon">Quezon City</option>
                                        <option value="pasig">Pasig City</option>
                                        <option value="taguig">Taguig City</option>
                                        <option value="mandaluyong">Mandaluyong City</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="task-due-date" class="form-label">Due Date</label>
                                    <input type="date" id="task-due-date" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="task-meeting-location" class="form-label">Meeting Location</label>
                            <input type="text" id="task-meeting-location" class="form-control" placeholder="Enter a specific meeting location">
                        </div>
                        
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" id="safe-zone" class="form-check-input">
                                <label for="safe-zone" class="form-check-label">
                                    This is a designated Safe Zone (e.g., barangay hall, community center)
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Estimated Time Required</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="number" id="task-time-min" class="form-control" placeholder="Min" min="1" required>
                                <span>-</span>
                                <input type="number" id="task-time-max" class="form-control" placeholder="Max" min="1" required>
                                <select id="task-time-unit" class="form-control">
                                    <option value="hours">Hours</option>
                                    <option value="days">Days</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" id="terms-agree-task" class="form-check-input" required>
                                <label for="terms-agree-task" class="form-check-label">
                                    I agree to the <a href="#">Task Posting Guidelines</a> and <a href="#">Community Standards</a>
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="submit-task-btn">
                        <i class="fas fa-paper-plane me-2"></i>
                        Post Task
                    </button>
                </div>
            </div>
        </div>
    </div>

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
    <script src="js/data.js"></script>
    <script src="auth.js"></script>
    <script src="js/main.js"></script>
    <script>


        // Task Detail Modal
        document.querySelectorAll('.view-task-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const taskId = this.getAttribute('data-task-id');
                // In a real app, you would fetch task details from the server
                // For now, we'll just show the modal
                const modal = document.getElementById('task-detail-modal');
                modal.classList.add('show');
                modal.style.display = 'block';
            });
        });

        // Post Task Modal
        document.getElementById('post-task-btn').addEventListener('click', function() {
            const modal = document.getElementById('post-task-modal');
            modal.classList.add('show');
            modal.style.display = 'block';
        });

        // Close Modals
        document.querySelectorAll('.btn-close, [data-dismiss="modal"]').forEach(button => {
            button.addEventListener('click', function() {
                const modal = this.closest('.modal');
                modal.classList.remove('show');
                modal.style.display = 'none';
            });
        });

        // Submit Task Form
        document.getElementById('submit-task-btn').addEventListener('click', function() {
            alert('Task posted successfully! In a real app, this would save the task to the database.');
            const modal = document.getElementById('post-task-modal');
            modal.classList.remove('show');
            modal.style.display = 'none';
        });

        // Apply for Task
        document.getElementById('apply-task-btn').addEventListener('click', function() {
            alert('Application submitted! In a real app, this would notify the task poster.');
        });
    </script>
</body>
</>