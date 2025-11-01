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
            
            <!-- Server Time Display -->
            <div class="card bg-dark bg-opacity-25 border-0 mt-3">
                <div class="card-body text-white text-center py-2">
                    <div class="d-flex align-items-center justify-content-center mb-1">
                        <i class="bi bi-clock me-2" aria-hidden="true"></i>
                        <small class="text-white-75">Server Time</small>
                    </div>
                    <div id="serverTime" class="fw-bold" style="font-size: 0.9rem;">
                        <?= date('H:i:s'); ?>
                    </div>
                    <small id="serverDate" class="text-white-50" style="font-size: 0.75rem;">
                        <?= date('d M Y'); ?>
                    </small>
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
    </div>

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

        // Advanced Section Management System
        let currentSection = 'dashboard';
        let sectionManager = null;

        // Section lifecycle manager
        class SectionManager {
            constructor() {
                this.currentSection = null;
                this.loadedScripts = new Map(); // Map of section -> Set of loaded scripts
                this.scriptElements = new Map(); // Map of script src -> DOM element
                this.sectionIntervals = new Map(); // Map of section -> Set of intervals
                this.sectionTimeouts = new Map(); // Map of section -> Set of timeouts
                this.sectionEventListeners = new Map(); // Map of section -> Array of cleanup functions
                this.globalFunctions = new Map(); // Map of section -> Set of function names
                this.isTransitioning = false;
            }

            // Register interval for current section
            addInterval(intervalId) {
                if (!this.currentSection) return;
                if (!this.sectionIntervals.has(this.currentSection)) {
                    this.sectionIntervals.set(this.currentSection, new Set());
                }
                this.sectionIntervals.get(this.currentSection).add(intervalId);
            }

            // Register timeout for current section
            addTimeout(timeoutId) {
                if (!this.currentSection) return;
                if (!this.sectionTimeouts.has(this.currentSection)) {
                    this.sectionTimeouts.set(this.currentSection, new Set());
                }
                this.sectionTimeouts.get(this.currentSection).add(timeoutId);
            }

            // Register event listener cleanup function for current section
            addEventListener(cleanupFunction) {
                if (!this.currentSection) return;
                if (!this.sectionEventListeners.has(this.currentSection)) {
                    this.sectionEventListeners.set(this.currentSection, []);
                }
                this.sectionEventListeners.get(this.currentSection).push(cleanupFunction);
            }

            // Register global function for current section
            addGlobalFunction(functionName) {
                if (!this.currentSection) return;
                if (!this.globalFunctions.has(this.currentSection)) {
                    this.globalFunctions.set(this.currentSection, new Set());
                }
                this.globalFunctions.get(this.currentSection).add(functionName);
            }

            // Load script for section
            async loadScript(src, section) {
                if (!this.loadedScripts.has(section)) {
                    this.loadedScripts.set(section, new Set());
                }

                if (this.loadedScripts.get(section).has(src)) {
                    return Promise.resolve();
                }

                return new Promise((resolve, reject) => {
                    const script = document.createElement('script');
                    script.src = src;
                    script.dataset.section = section;
                    script.onload = () => {
                        this.loadedScripts.get(section).add(src);
                        this.scriptElements.set(src, script);
                        resolve();
                    };
                    script.onerror = reject;
                    document.head.appendChild(script);
                });
            }

            // Cleanup section completely
            cleanupSection(section) {
                console.log(`Cleaning up section: ${section}`);

                // Clear intervals
                if (this.sectionIntervals.has(section)) {
                    this.sectionIntervals.get(section).forEach(intervalId => {
                        clearInterval(intervalId);
                    });
                    this.sectionIntervals.delete(section);
                }

                // Clear timeouts
                if (this.sectionTimeouts.has(section)) {
                    this.sectionTimeouts.get(section).forEach(timeoutId => {
                        clearTimeout(timeoutId);
                    });
                    this.sectionTimeouts.delete(section);
                }

                // Remove event listeners
                if (this.sectionEventListeners.has(section)) {
                    this.sectionEventListeners.get(section).forEach(cleanupFn => {
                        try {
                            cleanupFn();
                        } catch (e) {
                            console.warn('Error during event listener cleanup:', e);
                        }
                    });
                    this.sectionEventListeners.delete(section);
                }

                // Remove global functions
                if (this.globalFunctions.has(section)) {
                    this.globalFunctions.get(section).forEach(funcName => {
                        try {
                            delete window[funcName];
                        } catch (e) {
                            window[funcName] = undefined;
                        }
                    });
                    this.globalFunctions.delete(section);
                }

                // Remove scripts
                if (this.loadedScripts.has(section)) {
                    this.loadedScripts.get(section).forEach(src => {
                        if (this.scriptElements.has(src)) {
                            const script = this.scriptElements.get(src);
                            if (script.parentNode) {
                                script.parentNode.removeChild(script);
                            }
                            this.scriptElements.delete(src);
                        }
                    });
                    this.loadedScripts.delete(section);
                }

                // Call section-specific cleanup if available
                const cleanupFunctionName = `cleanup${section.charAt(0).toUpperCase() + section.slice(1)}`;
                if (typeof window[cleanupFunctionName] === 'function') {
                    try {
                        window[cleanupFunctionName]();
                    } catch (e) {
                        console.warn(`Error during ${cleanupFunctionName}:`, e);
                    }
                }
            }

            // Switch to new section
            async switchToSection(newSection) {
                if (this.isTransitioning) {
                    console.warn('Section transition already in progress');
                    return;
                }

                if (this.currentSection === newSection) {
                    console.log('Already on section:', newSection);
                    return;
                }

                this.isTransitioning = true;

                try {
                    // Cleanup current section
                    if (this.currentSection) {
                        this.cleanupSection(this.currentSection);
                    }

                    // Set new section
                    this.currentSection = newSection;

                    // Update UI
                    this.updateNavigation(newSection);
                    
                    // Load new section
                    await this.loadSectionContent(newSection);

                } catch (error) {
                    console.error('Error during section transition:', error);
                } finally {
                    this.isTransitioning = false;
                }
            }

            updateNavigation(section) {
                // Update active nav link
                document.querySelectorAll('.sidebar .nav-link').forEach(link => {
                    link.classList.remove('active');
                });
                
                const activeLink = document.querySelector(`[href="#${section}"]`);
                if (activeLink) {
                    activeLink.classList.add('active');
                }
                
                // Update page title
                const titleElement = document.querySelector('.navbar-brand');
                if (titleElement) {
                    titleElement.textContent = section.charAt(0).toUpperCase() + section.slice(1);
                }
            }

            async loadSectionContent(section) {
                const container = document.getElementById('content-container');
                if (!container) return;

                // Show loading state
                container.innerHTML = `
                    <div class="d-flex justify-content-center align-items-center" style="min-height: 300px;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                `;

                try {
                    const sectionConfig = this.getSectionConfig(section);
                    
                    // Load script first
                    if (sectionConfig.script) {
                        await this.loadScript(sectionConfig.script, section);
                    }

                    // Load HTML content
                    const response = await fetch(sectionConfig.template);
                    if (!response.ok) {
                        throw new Error(`Failed to load template: ${response.statusText}`);
                    }
                    
                    const html = await response.text();
                    container.innerHTML = html;

                    // Initialize section
                    if (sectionConfig.initFunction && typeof window[sectionConfig.initFunction] === 'function') {
                        // Add initialization function to global functions for cleanup
                        this.addGlobalFunction(sectionConfig.initFunction);
                        
                        // Wait a bit for DOM to settle
                        setTimeout(() => {
                            try {
                                window[sectionConfig.initFunction]();
                            } catch (e) {
                                console.error(`Error initializing section ${section}:`, e);
                            }
                        }, 100);
                    }

                } catch (error) {
                    console.error(`Error loading section ${section}:`, error);
                    container.innerHTML = `
                        <div class="alert alert-danger" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Error loading ${section} content. Please try again.
                        </div>
                    `;
                }
            }

            getSectionConfig(section) {
                const configs = {
                    'dashboard': {
                        script: './view/assets/js/dashboard.js',
                        template: './view/components/dashboard-content.html',
                        initFunction: 'initializeDashboard'
                    },
                    'tickets': {
                        script: './view/assets/js/tickets.js',
                        template: './view/components/tickets-content.html',
                        initFunction: 'initializeTickets'
                    },
                    'templates': {
                        script: './view/assets/js/templates.js',
                        template: './view/components/templates-content.html',
                        initFunction: 'initializeTemplates'
                    },
                    'settings': {
                        script: './view/assets/js/settings.js',
                        template: './view/components/settings-content.html',
                        initFunction: 'initializeSettings'
                    }
                };

                return configs[section] || {
                    template: './view/components/not-found.html',
                    initFunction: null
                };
            }
        }

        // Initialize section manager
        sectionManager = new SectionManager();

        // Enhanced showSection function
        function showSection(section) {
            if (sectionManager) {
                sectionManager.switchToSection(section);
                currentSection = section;
            }
        }

        // Global helper functions for sections to register their resources
        window.registerInterval = function(intervalId) {
            if (sectionManager) {
                sectionManager.addInterval(intervalId);
            }
            return intervalId;
        };

        window.registerTimeout = function(timeoutId) {
            if (sectionManager) {
                sectionManager.addTimeout(timeoutId);
            }
            return timeoutId;
        };

        window.registerEventListener = function(cleanupFunction) {
            if (sectionManager) {
                sectionManager.addEventListener(cleanupFunction);
            }
        };

        window.registerGlobalFunction = function(functionName) {
            if (sectionManager) {
                sectionManager.addGlobalFunction(functionName);
            }
        };
        
        // Legacy function - now handled by SectionManager
        function loadSectionContent(section) {
            if (sectionManager) {
                sectionManager.loadSectionContent(section);
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

        // Server time management
        let serverTimeOffset = 0; // Difference between server and client time
        let serverTimeInterval;
        let syncAttempts = 0;
        const maxSyncAttempts = 3;
        
        function syncServerTime() {
            const clientTime = new Date();
            
            fetch('controller/api/server-time.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const serverTime = new Date(data.timestamp * 1000);
                        const currentClientTime = new Date();
                        
                        // Calculate offset between server and client
                        serverTimeOffset = serverTime.getTime() - currentClientTime.getTime();
                        
                        // Update display immediately
                        updateTimeDisplay();
                        
                        syncAttempts++;
                        
                        // Sync a few more times for accuracy
                        if (syncAttempts <= maxSyncAttempts) {
                            setTimeout(syncServerTime, 500);
                        } else {
                            // Start local time updates after syncing
                            startLocalTimeUpdate();
                        }
                    }
                })
                .catch(error => {
                    console.error('Error syncing server time:', error);
                    syncAttempts++;
                    
                    if (syncAttempts < maxSyncAttempts) {
                        // Retry sync after 3 seconds
                        setTimeout(syncServerTime, 3000);
                    } else {
                        // Fallback to client time if all syncs fail
                        serverTimeOffset = 0;
                        startLocalTimeUpdate();
                    }
                });
        }
        
        function updateTimeDisplay() {
            const now = new Date(Date.now() + serverTimeOffset);
            
            // Format time as HH:MM:SS
            const timeString = now.toLocaleTimeString('id-ID', {
                hour12: false,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            
            // Format date
            const dateString = now.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
            
            document.getElementById('serverTime').textContent = timeString;
            document.getElementById('serverDate').textContent = dateString;
        }
        
        function startLocalTimeUpdate() {
            // Update display every second using local time + offset
            serverTimeInterval = setInterval(updateTimeDisplay, 1000);
        }
        
        function stopServerTimeUpdate() {
            if (serverTimeInterval) {
                clearInterval(serverTimeInterval);
                serverTimeInterval = null;
            }
        }
        
        function startServerTimeSync() {
            syncAttempts = 0;
            syncServerTime(); // Start initial sync
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadAdminInfo();
            
            // Initialize section manager and load default section
            if (sectionManager) {
                showSection('dashboard'); // Load dashboard by default
            }
            
            startServerTimeSync(); // Start server time sync
        });

        // Clean up on page unload
        window.addEventListener('beforeunload', function() {
            stopServerTimeUpdate();
        });
    </script>
</body>
</html>
