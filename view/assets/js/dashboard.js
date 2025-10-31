/**
 * Dashboard JavaScript Functions
 */

// Dashboard-specific functions
function reviewPendingTickets() {
    showSection('tickets');
    const statusFilter = document.getElementById('statusFilter');
    if (statusFilter) {
        statusFilter.value = 'in_review';
        if (typeof filterTickets === 'function') {
            filterTickets();
        }
    }
}

function generateReports() {
    alert('Generate Reports - Feature coming soon!');
}

function manageTemplates() {
    showSection('templates');
}

function systemSettings() {
    showSection('settings');
}

function viewAllActivity() {
    alert('View All Activity - Feature coming soon!');
}

// Load dashboard statistics
function loadDashboardStats() {
    console.log('Loading dashboard stats...');
    
    // Show loading state
    showStatsLoading();
    
    fetch('./controller/api/dashboard.php?action=stats')
        .then(response => {
            console.log('Stats response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(result => {
            console.log('Stats result:', result);
            if (result.success) {
                const stats = result.data;
                updateStatsElements(stats);
                
                // Load today's summary
                loadTodaySummary();
                
                // Load recent activity
                loadRecentActivity();
            } else {
                console.error('Failed to load dashboard stats:', result.error);
                showStatsError();
            }
        })
        .catch(error => {
            console.error('Error loading dashboard stats:', error);
            showStatsError();
        });
}

function showStatsLoading() {
    const loadingHTML = '<div class="spinner-border spinner-border-sm text-muted"></div>';
    ['totalTickets', 'pendingTickets', 'completedTickets', 'weeklyTickets'].forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.innerHTML = loadingHTML;
        }
    });
}

function updateStatsElements(stats) {
    // Animate numbers counting up
    animateCounter('totalTickets', stats.total_tickets);
    animateCounter('pendingTickets', stats.pending_tickets);
    animateCounter('completedTickets', stats.completed_tickets);
    animateCounter('weeklyTickets', stats.weekly_tickets);
}

function animateCounter(elementId, targetValue) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const currentValue = parseInt(element.textContent) || 0;
    const increment = targetValue > currentValue ? 1 : -1;
    const stepTime = Math.abs(Math.floor(200 / (targetValue - currentValue))) || 10;
    
    let current = currentValue;
    
    const timer = setInterval(() => {
        current += increment;
        element.textContent = current;
        
        if ((increment === 1 && current >= targetValue) || 
            (increment === -1 && current <= targetValue)) {
            element.textContent = targetValue;
            clearInterval(timer);
        }
    }, stepTime);
}

function showStatsError() {
    ['totalTickets', 'pendingTickets', 'completedTickets', 'weeklyTickets'].forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.innerHTML = '<span class="text-danger">--</span>';
        }
    });
}

// Load today's activity summary
function loadTodaySummary() {
    fetch('./controller/api/dashboard.php?action=today_summary')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(result => {
            console.log('Today summary result:', result);
            
            if (result.success) {
                const summary = result.data;
                updateTodayActivity(summary);
            } else {
                console.error('API returned error:', result.error);
                showTodayActivityError('Failed to load data');
            }
        })
        .catch(error => {
            console.error('Error loading today summary:', error);
            showTodayActivityError('Connection error');
        });
}

function updateTodayActivity(summary) {
    const activityContainer = document.querySelector('[data-today-activity]');
    if (activityContainer) {
        activityContainer.innerHTML = `
            <div class="d-flex justify-content-between mb-2">
                <span class="small">New Tickets</span>
                <span class="badge bg-primary">${summary.new_tickets}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span class="small">Approved</span>
                <span class="badge bg-success">${summary.approved}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span class="small">Generated</span>
                <span class="badge bg-info">${summary.generated}</span>
            </div>
            <div class="d-flex justify-content-between">
                <span class="small">Rejected</span>
                <span class="badge bg-danger">${summary.rejected}</span>
            </div>
        `;
    }
}

function showTodayActivityError(message) {
    const activityContainer = document.querySelector('[data-today-activity]');
    if (activityContainer) {
        activityContainer.innerHTML = `
            <div class="text-center text-danger">
                <i class="bi bi-exclamation-triangle"></i><br>
                <small>${message}</small>
            </div>
        `;
    }
}

// Load recent activity
function loadRecentActivity() {
    console.log('Loading recent activity...');
    
    fetch('./controller/api/dashboard.php?action=activity&limit=5')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(result => {
            console.log('Recent activity result:', result);
            if (result.success) {
                const activities = result.data;
                updateRecentActivity(activities);
            } else {
                console.error('Failed to load recent activity:', result.error);
                showRecentActivityError('Failed to load recent activity');
            }
        })
        .catch(error => {
            console.error('Error loading recent activity:', error);
            showRecentActivityError('Connection error');
        });
}

function updateRecentActivity(activities) {
    const container = document.getElementById('recentActivityList');
    if (!container) return;
    
    if (activities.length === 0) {
        container.innerHTML = `
            <div class="list-group-item text-center text-muted py-5">
                <i class="bi bi-inbox fs-2 d-block mb-3 text-muted"></i>
                <h6 class="text-muted">No Recent Activity</h6>
                <p class="small mb-0">Activity will appear here when tickets are created or updated.</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = activities.map(activity => `
        <div class="list-group-item d-flex align-items-center border-0">
            <div class="me-3">
                <div class="bg-${activity.color} bg-opacity-10 rounded-circle p-2">
                    <i class="bi ${activity.icon} text-${activity.color}"></i>
                </div>
            </div>
            <div class="flex-grow-1">
                <div class="fw-medium">${activity.title}</div>
                <small class="text-muted">${activity.description}</small>
            </div>
            <div class="text-end">
                <small class="text-muted d-block">${activity.time}</small>
                ${activity.id ? `<small class="text-primary" style="font-size: 0.7rem;">${activity.id}</small>` : ''}
            </div>
        </div>
    `).join('');
}

function showRecentActivityError(message) {
    const container = document.getElementById('recentActivityList');
    if (!container) return;
    
    container.innerHTML = `
        <div class="list-group-item text-center text-danger py-4">
            <i class="bi bi-exclamation-triangle fs-4 d-block mb-2"></i>
            <div class="fw-medium">Unable to Load Activity</div>
            <small>${message}</small>
            <div class="mt-2">
                <button class="btn btn-sm btn-outline-primary" onclick="loadRecentActivity()">
                    <i class="bi bi-arrow-clockwise me-1"></i>Try Again
                </button>
            </div>
        </div>
    `;
}

// Initialize dashboard
function initializeDashboard() {
    console.log('Initializing dashboard...');
    if (document.getElementById('dashboard-content')) {
        loadDashboardStats();
        // Auto refresh every 30 seconds
        setInterval(() => {
            console.log('Auto-refreshing dashboard...');
            loadDashboardStats();
        }, 30000);
    }
}

// Global initialization function for dashboard
window.initializeDashboard = initializeDashboard;
