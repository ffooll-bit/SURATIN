<?php
require_once __DIR__ . '/../../controller/config/app.php';

// Simple session check (in real app, this would be more robust)
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php?page=admin&action=login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - <?= APP_NAME; ?></title>
    <meta name="description" content="Dashboard admin untuk mengelola sistem <?= APP_NAME; ?>">
    <link href="view/assets/bootstrap-5.3.8/css/bootstrap.min.css" rel="stylesheet">
    <link href="view/assets/bootstrap-icons-1.13.1/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #0d6efd 0%, #0056b3 100%);
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            z-index: 1050;
        }
        .sidebar.show {
            transform: translateX(0);
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            margin: 0.25rem 1rem;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }
        .main-content {
            margin-left: 0;
            min-height: 100vh;
            background: #f8f9fa;
        }
        @media (min-width: 992px) {
            .sidebar {
                position: fixed;
                transform: translateX(0);
            }
            .main-content {
                margin-left: 250px;
            }
        }
        .navbar-brand {
            font-weight: bold;
        }
        .stats-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
        }
        .stats-card:hover {
            transform: translateY(-2px);
        }
        .stats-icon {
            width: 3rem;
            height: 3rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        .ticket-table {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .status-badge {
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
        }
        .filter-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.1);
        }
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1040;
        }
        .sidebar-overlay.show {
            display: block;
        }
        
        /* Mobile Improvements */
        @media (max-width: 576px) {
            .container-fluid {
                padding-left: 1rem;
                padding-right: 1rem;
            }
            .stats-card .card-body {
                padding: 1rem;
            }
            .stats-icon {
                width: 2.5rem;
                height: 2.5rem;
                font-size: 1rem;
            }
            .navbar-brand {
                font-size: 1.1rem;
            }
            .btn {
                min-height: 44px; /* WCAG touch target */
            }
            .dropdown-menu {
                min-width: 280px;
                max-width: calc(100vw - 2rem);
            }
        }
        
        /* Tablet Improvements */
        @media (min-width: 577px) and (max-width: 991px) {
            .sidebar .card {
                margin-bottom: 0.5rem;
            }
        }
        
        /* Better focus indicators */
        .btn:focus,
        .nav-link:focus,
        .form-control:focus,
        .dropdown-toggle:focus {
            outline: 2px solid #0d6efd;
            outline-offset: 2px;
        }
    </style>
</head>
<body>
    <!-- Skip to main content link -->
    <a href="#main-content" class="visually-hidden-focusable">Skip to main content</a>
    
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar" role="navigation" aria-label="Main navigation">
        <div class="p-4">
            <h4 class="text-white mb-0">
                <i class="bi bi-shield-check me-2" aria-hidden="true"></i>
                <?= APP_NAME; ?>
            </h4>
            <small class="text-white-50">Admin Panel</small>
        </div>
        
        <ul class="nav nav-pills flex-column" role="menubar">
            <li class="nav-item" role="none">
                <a class="nav-link active" href="#dashboard" role="menuitem" aria-current="page">
                    <i class="bi bi-speedometer2 me-2" aria-hidden="true"></i>Dashboard
                </a>
            </li>
            <li class="nav-item" role="none">
                <a class="nav-link" href="#tickets" role="menuitem">
                    <i class="bi bi-ticket-detailed me-2" aria-hidden="true"></i>Tickets
                </a>
            </li>
            <li class="nav-item" role="none">
                <a class="nav-link" href="#templates" role="menuitem">
                    <i class="bi bi-file-earmark-text me-2" aria-hidden="true"></i>Templates
                </a>
            </li>
            <li class="nav-item" role="none">
                <a class="nav-link" href="#settings" role="menuitem">
                    <i class="bi bi-gear me-2" aria-hidden="true"></i>Pengaturan
                </a>
            </li>
            <li class="nav-item mt-3" role="none">
                <a class="nav-link text-warning" href="#" onclick="logout()" role="menuitem">
                    <i class="bi bi-box-arrow-right me-2" aria-hidden="true"></i>Logout
                </a>
            </li>
        </ul>
        
        <div class="mt-auto p-4">
            <div class="card bg-primary bg-opacity-25 border-0">
                <div class="card-body text-white text-center">
                    <i class="bi bi-person-circle fs-1 mb-2"></i>
                    <h6 class="card-title" id="adminName">Administrator</h6>
                    <small id="adminRole">Admin</small>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navigation -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm" role="banner">
            <div class="container-fluid p-2">
                <button class="btn btn-outline-primary d-lg-none me-3" onclick="toggleSidebar()" 
                        aria-label="Toggle sidebar navigation" aria-expanded="false" aria-controls="sidebar">
                    <i class="bi bi-list" aria-hidden="true"></i>
                </button>
                
                <span class="navbar-brand mb-0 h1 p-2">Dashboard</span>
                
                <div class="ms-auto d-flex align-items-center">
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary position-relative me-3" type="button" 
                                data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
                            <i class="bi bi-bell" aria-hidden="true"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                  aria-label="3 new notifications">
                                3
                            </span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-label="Notification list">
                            <li><h6 class="dropdown-header">Notifikasi</h6></li>
                            <li><a class="dropdown-item" href="#">Ticket baru: TCK-20241029-0015</a></li>
                            <li><a class="dropdown-item" href="#">Ticket baru: TCK-20241029-0014</a></li>
                            <li><a class="dropdown-item" href="#">Ticket baru: TCK-20241029-0013</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Dashboard Content -->
        <main id="main-content" class="container-fluid p-4" role="main">
            <!-- Content will be loaded here dynamically -->
            <div id="content-container">
                <!-- Default: Load dashboard content -->
            </div>
        </main>

    <!-- Scripts -->
    <script src="view/assets/bootstrap-5.3.8/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle sidebar
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }

        // Load admin info
        function loadAdminInfo() {
            const adminSession = localStorage.getItem('adminSession');
            if (adminSession) {
                const admin = JSON.parse(adminSession);
                document.getElementById('adminName').textContent = admin.name;
                document.getElementById('adminRole').textContent = admin.role === 'super' ? 'Super Admin' : 'Admin';
            } else {
                // Redirect to login if no session
                window.location.href = 'index.php?page=admin&action=login';
            }
        }

        function logout() {
            if (confirm('Yakin ingin logout?')) {
                // Clear localStorage
                localStorage.removeItem('adminSession');
                localStorage.removeItem('rememberAdmin');
                
                // Call logout API to clear server session
                fetch('controller/api/auth.php', {
                    method: 'DELETE'
                })
                .then(() => {
                    window.location.href = 'index.php?page=admin&action=logout';
                })
                .catch(() => {
                    // Fallback if API fails
                    window.location.href = 'index.php?page=admin&action=logout';
                });
            }
        }

        // Content management
        let currentSection = 'dashboard';
        
        function showSection(section) {
            // Update active nav link
            document.querySelectorAll('.sidebar .nav-link').forEach(link => {
                link.classList.remove('active');
            });
            document.querySelector(`[href="#${section}"]`).classList.add('active');
            
            // Update page title
            document.querySelector('.navbar-brand').textContent = section.charAt(0).toUpperCase() + section.slice(1);
            
            // Load content
            loadSectionContent(section);
            currentSection = section;
        }
        
        // Content-specific script management
        const loadedScripts = new Set();
        
        function loadScript(src) {
            return new Promise((resolve, reject) => {
                if (loadedScripts.has(src)) {
                    resolve();
                    return;
                }
                
                const script = document.createElement('script');
                script.src = src;
                script.onload = () => {
                    loadedScripts.add(src);
                    resolve();
                };
                script.onerror = reject;
                document.head.appendChild(script);
            });
        }
        
        function executeScriptsInContainer(container) {
            const scripts = container.querySelectorAll('script');
            const promises = [];
            
            scripts.forEach(script => {
                if (script.src) {
                    // External script
                    promises.push(loadScript(script.src));
                } else {
                    // Inline script
                    promises.push(new Promise(resolve => {
                        const newScript = document.createElement('script');
                        newScript.textContent = script.textContent;
                        document.head.appendChild(newScript);
                        resolve();
                    }));
                }
                script.remove();
            });
            
            return Promise.all(promises);
        }
        
        function loadSectionContent(section) {
            const container = document.getElementById('content-container');
            
            switch(section) {
                case 'dashboard':
                    // Load dashboard-specific script first
                    loadScript('./view/assets/js/dashboard.js')
                        .then(() => {
                            return fetch('./view/components/dashboard-content.html');
                        })
                        .then(response => response.text())
                        .then(html => {
                            container.innerHTML = html;
                            // Execute any inline scripts in the loaded content
                            return executeScriptsInContainer(container);
                        })
                        .then(() => {
                            // Initialize dashboard after everything is loaded
                            setTimeout(() => {
                                if (typeof initializeDashboard === 'function') {
                                    initializeDashboard();
                                } else {
                                    console.error('initializeDashboard function not found');
                                }
                            }, 100);
                        })
                        .catch(error => {
                            console.error('Error loading dashboard:', error);
                            container.innerHTML = '<div class="alert alert-danger">Error loading dashboard content</div>';
                        });
                    break;
                    
                case 'tickets':
                    // Load tickets-specific script when implemented
                    fetch('./view/components/tickets-content.html')
                        .then(response => response.text())
                        .then(html => {
                            container.innerHTML = html;
                            return executeScriptsInContainer(container);
                        })
                        .then(() => {
                            // Initialize tickets functionality
                            if (typeof initializeTickets === 'function') {
                                initializeTickets();
                            }
                        })
                        .catch(error => {
                            console.error('Error loading tickets:', error);
                            container.innerHTML = '<div class="alert alert-info">Tickets section - Coming soon!</div>';
                        });
                    break;
                    
                case 'templates':
                    // Load templates-specific script when implemented
                    fetch('./view/components/templates-content.html')
                        .then(response => response.text())
                        .then(html => {
                            container.innerHTML = html;
                            return executeScriptsInContainer(container);
                        })
                        .then(() => {
                            // Initialize templates functionality
                            if (typeof initializeTemplates === 'function') {
                                initializeTemplates();
                            }
                        })
                        .catch(error => {
                            console.error('Error loading templates:', error);
                            container.innerHTML = '<div class="alert alert-info">Templates section - Coming soon!</div>';
                        });
                    break;
                    
                case 'settings':
                    // Load settings-specific script when implemented
                    fetch('./view/components/settings-content.html')
                        .then(response => response.text())
                        .then(html => {
                            container.innerHTML = html;
                            return executeScriptsInContainer(container);
                        })
                        .then(() => {
                            // Initialize settings functionality
                            if (typeof initializeSettings === 'function') {
                                initializeSettings();
                            }
                        })
                        .catch(error => {
                            console.error('Error loading settings:', error);
                            container.innerHTML = '<div class="alert alert-info">Settings section - Coming soon!</div>';
                        });
                    break;
                    
                default:
                    container.innerHTML = '<div class="alert alert-warning">Section not found</div>';
            }
        }
        
        // Update navigation event listeners
        document.querySelectorAll('.sidebar .nav-link[href^="#"]:not([onclick])').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const section = this.getAttribute('href').substring(1);
                showSection(section);
            });
        });

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadAdminInfo();
            showSection('dashboard'); // Load dashboard by default
        });
    </script>
</body>
</html>
