// App state to manage the application
let appState = {
    currentUser: null,
    isLoggedIn: false,
    currentPage: 'home',
    tasks: [],
    notifications: [],
    users: [],
    safeZones: [],
    activeModal: null,
    activeTab: 'all-tasks',
    searchQuery: '',
    filterCategory: 'all',
    filterUrgency: 'all',
    filterLocation: 'all',
    darkMode: false
};


function updateUI() {
    // Update navigation based on login status
    updateNavigation();
    
    // Show the current page
    showPage(appState.currentPage);
    
    // Update task list if on tasks page
    if (appState.currentPage === 'tasks' || appState.currentPage === 'dashboard') {
        updateTaskList();
    }
    
    // Update notification count
    updateNotificationCount();
}

function updateNavigation() {
    const authNav = document.getElementById('auth-nav');
    const userNav = document.getElementById('user-nav');
    
    if (appState.isLoggedIn) {
        authNav.style.display = 'none';
        userNav.style.display = 'flex';
        
        // Update user info in nav
        const userAvatar = document.getElementById('user-avatar');
        const userName = document.getElementById('user-name');
        
        userAvatar.src = appState.currentUser.profilePic;
        userName.textContent = appState.currentUser.name;
    } else {
        authNav.style.display = 'flex';
        userNav.style.display = 'none';
    }
}

function showPage(pageName) {
    // Hide all pages
    const pages = document.querySelectorAll('.page-container');
    pages.forEach(page => {
        page.classList.remove('active');
    });
    
    // Show the requested page
    const activePage = document.getElementById(`${pageName}-page`);
    if (activePage) {
        activePage.classList.add('active');
        activePage.scrollTop = 0; // Scroll to top when changing pages
    }
    
    // Update active nav link
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('data-page') === pageName) {
            link.classList.add('active');
        }
    });
    
    // Update sidebar links if logged in
    if (appState.isLoggedIn) {
        const sidebarLinks = document.querySelectorAll('.sidebar-link');
        sidebarLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('data-page') === pageName) {
                link.classList.add('active');
            }
        });
    }
    
    // Close mobile menu if open
    const mobileMenu = document.querySelector('.mobile-menu');
    if (mobileMenu && mobileMenu.classList.contains('show')) {
        mobileMenu.classList.remove('show');
    }
    
    // Update app state
    appState.currentPage = pageName;
}

function updateTaskList() {
    const taskListContainer = document.getElementById('task-list');
    if (!taskListContainer) return;
    
    // Filter tasks based on current filters
    let filteredTasks = appState.tasks;
    
    // Filter by category
    if (appState.filterCategory !== 'all') {
        filteredTasks = filteredTasks.filter(task => task.category === appState.filterCategory);
    }
    
    // Filter by urgency
    if (appState.filterUrgency !== 'all') {
        filteredTasks = filteredTasks.filter(task => task.urgency === appState.filterUrgency);
    }
    
    // Filter by location
    if (appState.filterLocation !== 'all') {
        filteredTasks = filteredTasks.filter(task => task.location === appState.filterLocation);
    }
    
    // Filter by search query
    if (appState.searchQuery) {
        const query = appState.searchQuery.toLowerCase();
        filteredTasks = filteredTasks.filter(task => 
            task.title.toLowerCase().includes(query) || 
            task.description.toLowerCase().includes(query)
        );
    }
    
    // Filter by active tab
    if (appState.activeTab === 'my-tasks') {
        filteredTasks = filteredTasks.filter(task => task.requesterId === appState.currentUser.id);
    } else if (appState.activeTab === 'my-applications') {
        filteredTasks = filteredTasks.filter(task => 
            task.helperId === appState.currentUser.id || 
            (task.applicants && task.applicants.includes(appState.currentUser.id))
        );
    }
    
    // Clear the container
    taskListContainer.innerHTML = '';
    
    // Add tasks to the container
    if (filteredTasks.length === 0) {
        taskListContainer.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-search fa-3x text-gray mb-3"></i>
                <p>No tasks found matching your criteria.</p>
                <button class="btn btn-outline mt-3" onclick="resetFilters()">Reset Filters</button>
            </div>
        `;
        return;
    }
    
    filteredTasks.forEach(task => {
        // Find requester info
        const requester = appState.users.find(user => user.id === task.requesterId);
        
        // Determine task card class based on status
        let taskCardClass = 'task-card';
        if (task.status === 'Pending') taskCardClass += ' pending';
        if (task.status === 'Accepted') taskCardClass += ' accepted';
        if (task.status === 'Completed') taskCardClass += ' completed';
        if (task.urgency === 'Urgent') taskCardClass += ' urgent';
        
        // Create task card HTML
        const taskCard = document.createElement('div');
        taskCard.className = `card ${taskCardClass} mb-3 animate-fadeIn`;
        taskCard.innerHTML = `
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <h3 class="card-title">${task.title}</h3>
                    <span class="badge ${task.urgency === 'Urgent' ? 'badge-danger' : 'badge-primary'}">${task.urgency}</span>
                </div>
                <p class="mb-3">${task.description}</p>
                <div class="task-meta">
                    <div class="task-meta-item">
                        <i class="fas fa-tag"></i>
                        <span>${task.category}</span>
                    </div>
                    <div class="task-meta-item">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>₱${task.payment}</span>
                    </div>
                    <div class="task-meta-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>${task.location === 'Safe Zone' ? 
                            `<span class="safe-zone-tag"><i class="fas fa-shield-alt"></i> ${task.safeZone}</span>` : 
                            'Home Task'}</span>
                    </div>
                    <div class="task-meta-item">
                        <i class="fas fa-clock"></i>
                        <span>${formatDate(task.createdAt)}</span>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar avatar-sm">
                        <img src="${requester.profilePic}" alt="${requester.name}">
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-1">
                            <span class="text-dark">${requester.name}</span>
                            ${requester.isVerified ? '<span class="verified-badge"><i class="fas fa-check-circle"></i> Verified</span>' : ''}
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <i class="fas fa-star text-warning"></i>
                            <span>${requester.rating}</span>
                        </div>
                    </div>
                </div>
                <div>
                    ${getTaskActionButton(task)}
                </div>
            </div>
        `;
        
        taskListContainer.appendChild(taskCard);
    });
}

function getTaskActionButton(task) {
    // Different buttons based on task status and user role
    if (task.requesterId === appState.currentUser.id) {
        // Current user is the requester
        switch (task.status) {
            case 'Pending':
                return `<button class="btn btn-outline btn-sm" onclick="viewTaskDetails(${task.id})">View Details</button>`;
            case 'Waiting for Review':
                return `<button class="btn btn-primary btn-sm" onclick="reviewApplicants(${task.id})">Review Applicants</button>`;
            case 'Accepted':
                return `<button class="btn btn-success btn-sm" onclick="markTaskComplete(${task.id})">Mark Complete</button>`;
            case 'Completed':
                return `<button class="btn btn-outline btn-sm" onclick="viewTaskDetails(${task.id})">View Details</button>`;
            default:
                return `<button class="btn btn-outline btn-sm" onclick="viewTaskDetails(${task.id})">View Details</button>`;
        }
    } else if (task.helperId === appState.currentUser.id) {
        // Current user is the helper
        switch (task.status) {
            case 'Accepted':
                return `<button class="btn btn-primary btn-sm" onclick="viewTaskDetails(${task.id})">View Details</button>`;
            case 'Completed':
                return `<button class="btn btn-outline btn-sm" onclick="viewTaskDetails(${task.id})">View Details</button>`;
            default:
                return `<button class="btn btn-outline btn-sm" onclick="viewTaskDetails(${task.id})">View Details</button>`;
        }
    } else {
        // Current user is neither
        if (task.status === 'Pending') {
            // Check if user already applied
            if (task.applicants && task.applicants.includes(appState.currentUser.id)) {
                return `<button class="btn btn-secondary btn-sm" disabled>Applied</button>`;
            } else {
                return `<button class="btn btn-primary btn-sm" onclick="applyForTask(${task.id})">Apply</button>`;
            }
        } else {
            return `<button class="btn btn-outline btn-sm" onclick="viewTaskDetails(${task.id})">View Details</button>`;
        }
    }
}

function updateNotificationCount() {
    const notificationBadge = document.getElementById('notification-badge');
    if (!notificationBadge) return;
    
    // Count unread notifications
    const unreadCount = appState.notifications.filter(n => !n.isRead && n.userId === appState.currentUser.id).length;
    
    if (unreadCount > 0) {
        notificationBadge.classList.add('notification-badge');
    } else {
        notificationBadge.classList.remove('notification-badge');
    }
}

function populateNotifications() {
    // Populate notification dropdown
    const notificationList = document.getElementById('notification-list');
    if (notificationList) {
        notificationList.innerHTML = '';
        
        const userNotifications = appState.notifications
            .filter(n => n.userId === appState.currentUser.id)
            .slice(0, 3); // Show only 3 most recent
        
        if (userNotifications.length === 0) {
            notificationList.innerHTML = `
                <div class="p-3 text-center">
                    <p class="text-gray">No notifications yet</p>
                </div>
            `;
        } else {
            userNotifications.forEach(notification => {
                const notificationItem = document.createElement('div');
                notificationItem.className = 'notification-item';
                
                let iconClass = 'fas fa-bell';
                if (notification.type === 'application') iconClass = 'fas fa-user-plus';
                if (notification.type === 'completion') iconClass = 'fas fa-check-circle';
                if (notification.type === 'system') iconClass = 'fas fa-info-circle';
                
                notificationItem.innerHTML = `
                    <div class="avatar avatar-sm">
                        <i class="${iconClass}"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-title">${notification.title}</div>
                        <div class="notification-text">${notification.message}</div>
                        <div class="notification-time">${notification.time}</div>
                    </div>
                `;
                
                notificationList.appendChild(notificationItem);
            });
        }
    }
    
    // Populate dashboard notifications
    const dashboardNotifications = document.getElementById('dashboard-notifications');
    if (dashboardNotifications) {
        dashboardNotifications.innerHTML = '';
        
        const userNotifications = appState.notifications
            .filter(n => n.userId === appState.currentUser.id)
            .slice(0, 3); // Show only 3 most recent
        
        if (userNotifications.length === 0) {
            dashboardNotifications.innerHTML = `
                <div class="p-3 text-center">
                    <p class="text-gray">No notifications yet</p>
                </div>
            `;
        } else {
            userNotifications.forEach(notification => {
                const notificationItem = document.createElement('div');
                notificationItem.className = 'notification-item';
                
                let iconClass = 'fas fa-bell';
                if (notification.type === 'application') iconClass = 'fas fa-user-plus';
                if (notification.type === 'completion') iconClass = 'fas fa-check-circle';
                if (notification.type === 'system') iconClass = 'fas fa-info-circle';
                
                notificationItem.innerHTML = `
                    <div class="avatar avatar-sm">
                        <i class="${iconClass}"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-title">${notification.title}</div>
                        <div class="notification-text">${notification.message}</div>
                        <div class="notification-time">${notification.time}</div>
                    </div>
                `;
                
                dashboardNotifications.appendChild(notificationItem);
            });
        }
    }
}

function populateLeaderboard() {
    const leaderboardList = document.getElementById('leaderboard-list');
    if (!leaderboardList) return;
    
    // Sort users by rating
    const topUsers = [...appState.users]
        .filter(user => user.isVerified)
        .sort((a, b) => b.rating - a.rating)
        .slice(0, 3); // Top 3 users
    
    leaderboardList.innerHTML = '';
    
    topUsers.forEach((user, index) => {
        const leaderboardItem = document.createElement('div');
        leaderboardItem.className = 'leaderboard-item';
        
        leaderboardItem.innerHTML = `
            <div class="leaderboard-rank">${index + 1}</div>
            <div class="avatar">
                <img src="${user.profilePic}" alt="${user.name}">
            </div>
            <div class="leaderboard-info">
                <div class="d-flex align-items-center gap-1">
                    <span class="text-dark">${user.name}</span>
                    <span class="verified-badge"><i class="fas fa-check-circle"></i> Verified</span>
                </div>
                <div>
                    ${user.skills.map(skill => `<span class="skill-tag">${skill}</span>`).join('')}
                </div>
            </div>
            <div class="leaderboard-score">
                <i class="fas fa-star text-warning"></i> ${user.rating}
            </div>
        `;
        
        leaderboardList.appendChild(leaderboardItem);
    });
}

function setupEventListeners() {
    // Navigation links
    document.querySelectorAll('[data-page]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const pageName = this.getAttribute('data-page');
            showPage(pageName);
        });
    });
    
    // Mobile menu toggle
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            const mobileMenu = document.querySelector('.mobile-menu');
            mobileMenu.classList.toggle('show');
        });
    }
    
    // Login form
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            // Simulate login
            appState.currentUser = mockData.users[0];
            appState.isLoggedIn = true;
            updateUI();
            showPage('dashboard');
            showAlert('Login successful! Welcome back, ' + appState.currentUser.name, 'success');
        });
    }
    
    // Signup form
    const signupForm = document.getElementById('signup-form');
    if (signupForm) {
        signupForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate password match
            const password = document.getElementById('signup-password').value;
            const confirmPassword = document.getElementById('signup-confirm-password').value;
            
            if (password !== confirmPassword) {
                showAlert('Passwords do not match!', 'danger');
                return;
            }
            
            // Simulate signup
            const name = document.getElementById('signup-name').value;
            appState.currentUser = {
                ...mockData.users[0],
                name: name
            };
            appState.isLoggedIn = true;
            updateUI();
            showPage('dashboard');
            showAlert('Account created successfully! Welcome, ' + name, 'success');
        });
    }
    
    // Logout button
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            // Simulate logout
            appState.currentUser = null;
            appState.isLoggedIn = false;
            updateUI();
            showPage('home');
            showAlert('You have been logged out successfully', 'success');
        });
    }
    
    // Task filter buttons
    document.querySelectorAll('[data-filter-category]').forEach(btn => {
        btn.addEventListener('click', function() {
            appState.filterCategory = this.getAttribute('data-filter-category');
            updateTaskList();
            
            // Update active button
            document.querySelectorAll('[data-filter-category]').forEach(b => b.classList.remove('btn-primary'));
            document.querySelectorAll('[data-filter-category]').forEach(b => b.classList.add('btn-outline'));
            this.classList.remove('btn-outline');
            this.classList.add('btn-primary');
        });
    });
    
    // Task tabs
    document.querySelectorAll('[data-tab]').forEach(tab => {
        tab.addEventListener('click', function() {
            appState.activeTab = this.getAttribute('data-tab');
            updateTaskList();
            
            // Update active tab
            document.querySelectorAll('[data-tab]').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    });
    
    // Search form
    const searchForm = document.getElementById('task-search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const searchInput = document.getElementById('task-search');
            appState.searchQuery = searchInput.value;
            updateTaskList();
        });
    }
    
    // Create task form
    const createTaskForm = document.getElementById('create-task-form');
    if (createTaskForm) {
        createTaskForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form values
            const title = document.getElementById('task-title').value;
            const description = document.getElementById('task-description').value;
            const category = document.getElementById('task-category').value;
            const payment = document.getElementById('task-payment').value;
            const urgency = document.getElementById('task-urgency').value;
            const location = document.getElementById('task-location').value;
            
            // Create new task
            const newTask = {
                id: appState.tasks.length + 1,
                title,
                description,
                category,
                payment: parseInt(payment),
                urgency,
                location,
                status: 'Pending',
                createdAt: new Date().toISOString(),
                requesterId: appState.currentUser.id,
                helperId: null
            };
            
            // Add safe zone if selected
            if (location === 'Safe Zone') {
                const safeZone = document.getElementById('task-safe-zone').value;
                newTask.safeZone = safeZone;
                newTask.isPublicSafeZone = true;
            } else {
                newTask.address = appState.currentUser.address;
                newTask.isPublicSafeZone = false;
            }
            
            // Add to tasks
            appState.tasks.unshift(newTask);
            
            // Reset form
            createTaskForm.reset();
            
            // Close modal
            closeModal('create-task-modal');
            
            // Update task list
            updateTaskList();
            
            // Show success message
            showAlert('Task created successfully!', 'success');
        });
    }
    
    // Modal close buttons
    document.querySelectorAll('.modal-close').forEach(btn => {
        btn.addEventListener('click', function() {
            const modalId = this.closest('.modal-backdrop').id;
            closeModal(modalId);
        });
    });
    
    // Modal backdrops
    document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
        backdrop.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this.id);
            }
        });
    });
    
    // Task location change
    const taskLocation = document.getElementById('task-location');
    if (taskLocation) {
        taskLocation.addEventListener('change', function() {
            const safeZoneField = document.getElementById('safe-zone-field');
            if (this.value === 'Safe Zone') {
                safeZoneField.style.display = 'block';
            } else {
                safeZoneField.style.display = 'none';
            }
        });
    }
    
    // Rating stars
    document.querySelectorAll('.rating-star').forEach(star => {
        star.addEventListener('click', function() {
            const rating = parseInt(this.getAttribute('data-rating'));
            const ratingInput = document.getElementById('rating-input');
            ratingInput.value = rating;
            
            // Update stars
            document.querySelectorAll('.rating-star').forEach(s => {
                if (parseInt(s.getAttribute('data-rating')) <= rating) {
                    s.classList.add('active');
                } else {
                    s.classList.remove('active');
                }
            });
        });
    });
    
    // Dropdown toggles
    document.querySelectorAll('.dropdown').forEach(dropdown => {
        dropdown.addEventListener('click', function(e) {
            e.stopPropagation();
            const dropdownMenu = this.querySelector('.dropdown-menu');
            dropdownMenu.classList.toggle('show');
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
            menu.classList.remove('show');
        });
    });
    
    // Urgency radio buttons
    document.querySelectorAll('input[name="urgency"]').forEach(radio => {
        radio.addEventListener('change', function() {
            appState.filterUrgency = this.value;
            updateTaskList();
        });
    });
    
    // Location radio buttons
    document.querySelectorAll('input[name="location"]').forEach(radio => {
        radio.addEventListener('change', function() {
            appState.filterLocation = this.value;
            updateTaskList();
        });
    });
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('show');
        appState.activeModal = modalId;
        
        // Add body class to prevent scrolling
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
        appState.activeModal = null;
        
        // Remove body class to allow scrolling
        document.body.style.overflow = '';
    }
}

function showAlert(message, type = 'info') {
    // Create alert element
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.style.position = 'fixed';
    alert.style.top = '20px';
    alert.style.right = '20px';
    alert.style.zIndex = '9999';
    alert.style.maxWidth = '300px';
    alert.style.animation = 'fadeIn 0.3s ease';
    
    alert.innerHTML = `
        <div class="d-flex justify-content-between align-items-center">
            <span>${message}</span>
            <button type="button" class="btn-close" onclick="this.parentElement.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    // Add to body
    document.body.appendChild(alert);
    
    // Remove after 5 seconds
    setTimeout(() => {
        alert.style.animation = 'fadeOut 0.3s ease';
        setTimeout(() => {
            alert.remove();
        }, 300);
    }, 5000);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffTime = Math.abs(now - date);
    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays === 0) {
        return 'Today';
    } else if (diffDays === 1) {
        return 'Yesterday';
    } else if (diffDays < 7) {
        return `${diffDays} days ago`;
    } else {
        return date.toLocaleDateString();
    }
}

function resetFilters() {
    appState.filterCategory = 'all';
    appState.filterUrgency = 'all';
    appState.filterLocation = 'all';
    appState.searchQuery = '';
    
    // Reset UI
    document.querySelectorAll('[data-filter-category]').forEach(btn => {
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-outline');
    });
    
    document.querySelector('[data-filter-category="all"]').classList.remove('btn-outline');
    document.querySelector('[data-filter-category="all"]').classList.add('btn-primary');
    
    // Reset radio buttons
    document.getElementById('urgency-all').checked = true;
    document.getElementById('location-all').checked = true;
    
    const searchInput = document.getElementById('task-search');
    if (searchInput) searchInput.value = '';
    
    updateTaskList();
}

// Task-specific functions
function viewTaskDetails(taskId) {
    const task = appState.tasks.find(t => t.id === taskId);
    if (!task) return;
    
    const taskDetailsModal = document.getElementById('task-details-modal');
    const modalBody = taskDetailsModal.querySelector('.modal-body');
    
    // Find requester and helper
    const requester = appState.users.find(u => u.id === task.requesterId);
    const helper = task.helperId ? appState.users.find(u => u.id === task.helperId) : null;
    
    // Create modal content
    modalBody.innerHTML = `
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <h3 class="modal-title">${task.title}</h3>
                <span class="badge ${task.urgency === 'Urgent' ? 'badge-danger' : 'badge-primary'}">${task.urgency}</span>
            </div>
            <p>${task.description}</p>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <h4 class="text-primary mb-2">Task Details</h4>
                <ul class="feature-list">
                    <li><strong>Category:</strong> ${task.category}</li>
                    <li><strong>Payment:</strong> ₱${task.payment}</li>
                    <li><strong>Status:</strong> ${task.status}</li>
                    <li><strong>Posted:</strong> ${formatDate(task.createdAt)}</li>
                    <li><strong>Location:</strong> ${task.isPublicSafeZone ? 
                        `<span class="safe-zone-tag"><i class="fas fa-shield-alt"></i> ${task.safeZone}</span>` : 
                        'Home Task'}</li>
                </ul>
            </div>
            
            <div class="col-md-6">
                <h4 class="text-primary mb-2">Requester</h4>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div class="avatar">
                        <img src="${requester.profilePic}" alt="${requester.name}">
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-1">
                            <span class="text-dark">${requester.name}</span>
                            ${requester.isVerified ? '<span class="verified-badge"><i class="fas fa-check-circle"></i> Verified</span>' : ''}
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <i class="fas fa-star text-warning"></i>
                            <span>${requester.rating}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        ${helper ? `
        <div class="mb-4">
            <h4 class="text-primary mb-2">Helper</h4>
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="avatar">
                    <img src="${helper.profilePic}" alt="${helper.name}">
                </div>
                <div>
                    <div class="d-flex align-items-center gap-1">
                        <span class="text-dark">${helper.name}</span>
                        ${helper.isVerified ? '<span class="verified-badge"><i class="fas fa-check-circle"></i> Verified</span>' : ''}
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <i class="fas fa-star text-warning"></i>
                        <span>${helper.rating}</span>
                    </div>
                </div>
            </div>
        </div>
        ` : ''}
        
        ${task.status === 'Accepted' ? `
        <div class="mb-4">
            <h4 class="text-primary mb-2">Magic Word</h4>
            <p class="mb-2">Use this word to confirm identity when meeting:</p>
            <div class="magic-word">${task.magicWord || 'BLUESKY'}</div>
            <p class="text-gray">Share this word only with the other party.</p>
        </div>
        ` : ''}
        
        ${task.status === 'Accepted' ? `
        <div class="mb-4">
            <button class="emergency-btn w-100" onclick="triggerEmergency(${task.id})">
                <i class="fas fa-exclamation-triangle"></i>
                Emergency Button
            </button>
            <p class="text-center text-gray mt-2">Press in case of emergency to alert barangay officials.</p>
        </div>
        ` : ''}
    `;
    
    // Open modal
    openModal('task-details-modal');
}

function applyForTask(taskId) {
    const task = appState.tasks.find(t => t.id === taskId);
    if (!task) return;
    
    // Add current user as applicant
    if (!task.applicants) {
        task.applicants = [];
    }
    
    if (!task.applicants.includes(appState.currentUser.id)) {
        task.applicants.push(appState.currentUser.id);
    }
    
    // Update task status
    task.status = 'Waiting for Review';
    
    // Add notification for requester
    const newNotification = {
        id: appState.notifications.length + 1,
        userId: task.requesterId,
        title: 'New Task Application',
        message: `${appState.currentUser.name} has applied to your "${task.title}" task.`,
        time: 'Just now',
        isRead: false,
        type: 'application'
    };
    
    appState.notifications.unshift(newNotification);
    
    // Update UI
    updateTaskList();
    
    // Show success message
    showAlert('Application submitted successfully!', 'success');
}

function reviewApplicants(taskId) {
    const task = appState.tasks.find(t => t.id === taskId);
    if (!task || !task.applicants || task.applicants.length === 0) return;
    
    const reviewModal = document.getElementById('review-applicants-modal');
    const modalBody = reviewModal.querySelector('.modal-body');
    
    // Create modal content
    modalBody.innerHTML = `
        <h4 class="mb-3">Applicants for "${task.title}"</h4>
        <div id="applicants-list" class="mb-4">
            ${task.applicants.map(applicantId => {
                const applicant = appState.users.find(u => u.id === applicantId);
                return `
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar">
                                        <img src="${applicant.profilePic}" alt="${applicant.name}">
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="text-dark">${applicant.name}</span>
                                            ${applicant.isVerified ? '<span class="verified-badge"><i class="fas fa-check-circle"></i> Verified</span>' : ''}
                                        </div>
                                        <div class="d-flex align-items-center gap-1">
                                            <i class="fas fa-star text-warning"></i>
                                            <span>${applicant.rating}</span>
                                            <span class="text-gray">(${applicant.completedTasks} tasks)</span>
                                        </div>
                                    </div>
                                </div>
                                <button class="btn btn-primary btn-sm" onclick="acceptApplicant(${task.id}, ${applicant.id})">Accept</button>
                            </div>
                            
                            <div class="mt-3">
                                <p class="mb-2"><strong>Skills:</strong></p>
                                <div>
                                    ${applicant.skills.map(skill => `<span class="skill-tag">${skill}</span>`).join('')}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('')}
        </div>
    `;
    
    // Open modal
    openModal('review-applicants-modal');
}

function acceptApplicant(taskId, applicantId) {
    const task = appState.tasks.find(t => t.id === taskId);
    if (!task) return;
    
    // Update task
    task.helperId = applicantId;
    task.status = 'Accepted';
    
    // Generate magic word
    const magicWords = ['BLUESKY', 'SUNSHINE', 'RAINBOW', 'MOUNTAIN', 'OCEAN', 'FOREST', 'EAGLE', 'DOLPHIN'];
    task.magicWord = magicWords[Math.floor(Math.random() * magicWords.length)];
    
    // Add notification for helper
    const newNotification = {
        id: appState.notifications.length + 1,
        userId: applicantId,
        title: 'Task Application Accepted',
        message: `Your application for "${task.title}" has been accepted. Magic word: ${task.magicWord}`,
        time: 'Just now',
        isRead: false,
        type: 'acceptance'
    };
    
    appState.notifications.unshift(newNotification);
    
    // Close modal
    closeModal('review-applicants-modal');
    
    // Update UI
    updateTaskList();
    updateNotificationCount();
    populateNotifications();
    
    // Show success message
    showAlert('Applicant accepted successfully!', 'success');
}

function markTaskComplete(taskId) {
    const task = appState.tasks.find(t => t.id === taskId);
    if (!task) return;
    
    // Update task
    task.status = 'Completed';
    
    // Open rating modal
    const ratingModal = document.getElementById('rating-modal');
    const modalTitle = ratingModal.querySelector('.modal-title');
    const ratingForm = document.getElementById('rating-form');
    
    // Find helper
    const helper = appState.users.find(u => u.id === task.helperId);
    
    // Update modal content
    modalTitle.textContent = `Rate ${helper.name}`;
    ratingForm.setAttribute('data-task-id', taskId);
    ratingForm.setAttribute('data-user-id', helper.id);
    
    // Reset rating
    document.querySelectorAll('.rating-star').forEach(star => star.classList.remove('active'));
    document.getElementById('rating-input').value = '';
    document.getElementById('rating-comment').value = '';
    
    // Open modal
    openModal('rating-modal');
}

function submitRating(event) {
    event.preventDefault();
    
    const form = event.target;
    const taskId = parseInt(form.getAttribute('data-task-id'));
    const userId = parseInt(form.getAttribute('data-user-id'));
    
    const rating = parseInt(document.getElementById('rating-input').value);
    const comment = document.getElementById('rating-comment').value;
    
    if (!rating) {
        showAlert('Please select a rating.', 'warning');
        return;
    }
    
    // Create review
    const newReview = {
        id: mockData.reviews.length + 1,
        taskId,
        reviewerId: appState.currentUser.id,
        revieweeId: userId,
        rating,
        comment,
        createdAt: new Date().toISOString()
    };
    
    // Add to reviews
    mockData.reviews.push(newReview);
    
    // Update user rating
    const user = appState.users.find(u => u.id === userId);
    const userReviews = mockData.reviews.filter(r => r.revieweeId === userId);
    const totalRating = userReviews.reduce((sum, review) => sum + review.rating, 0);
    user.rating = (totalRating / userReviews.length).toFixed(1);
    
    // Add notification for helper
    const task = appState.tasks.find(t => t.id === taskId);
    const newNotification = {
        id: appState.notifications.length + 1,
        userId: userId,
        title: 'New Rating Received',
        message: `${appState.currentUser.name} has rated you ${rating} stars for "${task.title}".`,
        time: 'Just now',
        isRead: false,
        type: 'rating'
    };
    
    appState.notifications.unshift(newNotification);
    
    // Close modal
    closeModal('rating-modal');
    
    // Update UI
    updateTaskList();
    updateNotificationCount();
    populateNotifications();
    
    // Show success message
    showAlert('Rating submitted successfully!', 'success');
}

function triggerEmergency(taskId) {
    const task = appState.tasks.find(t => t.id === taskId);
    if (!task) return;
    
    // Close task details modal
    closeModal('task-details-modal');
    
    // Show emergency alert
    showAlert('Emergency alert sent to barangay officials. Help is on the way!', 'danger');
    
    // Add notification for both parties
    const requester = appState.users.find(u => u.id === task.requesterId);
    const helper = appState.users.find(u => u.id === task.helperId);
    
    const emergencyNotification = {
        id: appState.notifications.length + 1,
        userId: requester.id,
        title: 'Emergency Alert Sent',
        message: `Emergency alert for task "${task.title}" has been sent to barangay officials.`,
        time: 'Just now',
        isRead: false,
        type: 'emergency'
    };
    
    appState.notifications.unshift(emergencyNotification);
    
    if (helper) {
        const helperNotification = {
            id: appState.notifications.length + 1,
            userId: helper.id,
            title: 'Emergency Alert Sent',
            message: `Emergency alert for task "${task.title}" has been sent to barangay officials.`,
            time: 'Just now',
            isRead: false,
            type: 'emergency'
        };
        
        appState.notifications.unshift(helperNotification);
    }
    
    // Update notification count
    updateNotificationCount();
    populateNotifications();
}

function checkDarkModePreference() {
    // Check if user prefers dark mode
    const prefersDarkMode = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    // Set dark mode based on preference
    if (prefersDarkMode) {
        toggleDarkMode();
    }
}

function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
    appState.darkMode = !appState.darkMode;
}

// Add dark mode toggle to the UI
document.addEventListener('DOMContentLoaded', function() {
    // Create dark mode toggle button
    const darkModeToggle = document.createElement('button');
    darkModeToggle.className = 'btn btn-icon btn-ghost position-fixed';
    darkModeToggle.style.bottom = '20px';
    darkModeToggle.style.right = '20px';
    darkModeToggle.style.zIndex = '999';
    darkModeToggle.innerHTML = '<i class="fas fa-moon"></i>';
    darkModeToggle.title = 'Toggle Dark Mode';
    
    darkModeToggle.addEventListener('click', function() {
        toggleDarkMode();
        
        // Update icon
        const icon = this.querySelector('i');
        if (appState.darkMode) {
            icon.className = 'fas fa-sun';
        } else {
            icon.className = 'fas fa-moon';
        }
    });
    
    document.body.appendChild(darkModeToggle);
});

// Add these functions to the existing js/main.js file

// Function to toggle sidebar on mobile
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    
    if (sidebar) {
        sidebar.classList.toggle('expanded');
        if (sidebarToggle) {
            sidebarToggle.classList.toggle('active');
        }
    }
}

// Function to check for dark mode preference
function checkDarkModePreference() {
    // Check if user prefers dark mode
    const prefersDarkMode = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    // Check if user has previously set a preference
    const savedPreference = localStorage.getItem('darkMode');
    
    // Set dark mode based on saved preference or system preference
    if (savedPreference === 'true' || (savedPreference === null && prefersDarkMode)) {
        toggleDarkMode();
    }
}

// Function to toggle dark mode
function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
    appState.darkMode = !appState.darkMode;
    
    // Save preference to localStorage
    localStorage.setItem('darkMode', appState.darkMode);
    
    // Update dark mode toggle icon
    const darkModeToggle = document.getElementById('dark-mode-toggle');
    if (darkModeToggle) {
        const icon = darkModeToggle.querySelector('i');
        if (appState.darkMode) {
            icon.className = 'fas fa-sun';
        } else {
            icon.className = 'fas fa-moon';
        }
    }
}

// Function to handle scroll effects
function handleScroll() {
    const header = document.querySelector('header');
    if (window.scrollY > 50) {
        header.classList.add('scrolled');
    } else {
        header.classList.remove('scrolled');
    }
}

// Add these to the document.addEventListener('DOMContentLoaded', function() {...}) block

// Create dark mode toggle button
const darkModeToggle = document.createElement('button');
darkModeToggle.id = 'dark-mode-toggle';
darkModeToggle.className = 'btn btn-icon btn-ghost position-fixed';
darkModeToggle.style.bottom = '20px';
darkModeToggle.style.right = '20px';
darkModeToggle.style.zIndex = '999';
darkModeToggle.style.backgroundColor = 'rgba(255, 255, 255, 0.2)';
darkModeToggle.style.backdropFilter = 'blur(4px)';
darkModeToggle.style.boxShadow = 'var(--shadow)';
darkModeToggle.innerHTML = '<i class="fas fa-moon"></i>';
darkModeToggle.title = 'Toggle Dark Mode';

darkModeToggle.addEventListener('click', function() {
    toggleDarkMode();
});

document.body.appendChild(darkModeToggle);

// Add sidebar toggle for mobile
if (window.innerWidth <= 768) {
    const appContainer = document.querySelector('.app-container');
    if (appContainer) {
        const sidebar = appContainer.querySelector('.sidebar');
        if (sidebar) {
            const sidebarToggle = document.createElement('div');
            sidebarToggle.className = 'sidebar-toggle';
            sidebarToggle.innerHTML = '<span>Menu</span><i class="fas fa-chevron-down"></i>';
            sidebarToggle.addEventListener('click', toggleSidebar);
            
            appContainer.insertBefore(sidebarToggle, sidebar);
        }
    }
}

// Add scroll event listener
window.addEventListener('scroll', handleScroll);

// Add smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (href !== '#') {
            e.preventDefault();
            document.querySelector(href).scrollIntoView({
                behavior: 'smooth'
            });
        }
    });
});

// Enhance dropdown behavior
document.querySelectorAll('.dropdown').forEach(dropdown => {
    dropdown.addEventListener('click', function(e) {
        e.stopPropagation();
        
        // Close all other dropdowns first
        document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
            if (!this.contains(menu)) {
                menu.classList.remove('show');
            }
        });
        
        const dropdownMenu = this.querySelector('.dropdown-menu');
        dropdownMenu.classList.toggle('show');
    });
});

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.dropdown')) {
        document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
            menu.classList.remove('show');
        });
    }
});

// Add animation to cards
document.querySelectorAll('.card').forEach(card => {
    card.classList.add('animate-fadeIn');
});