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
    showActivityModal();
}

function showActivityModal() {
    // Check if modal exists, if not create it
    let modal = document.getElementById('activityModal');
    if (!modal) {
        createActivityModal();
        modal = document.getElementById('activityModal');
    }
    
    // Show the modal
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    // Load activity data
    loadAllActivity(1); // Load first page
}

function createActivityModal() {
    const modalHTML = `
        <div class="modal fade" id="activityModal" tabindex="-1" aria-labelledby="activityModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="activityModalLabel">
                            <i class="bi bi-clock-history me-2"></i>All Recent Activity
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                            <div class="d-flex flex-wrap gap-2">
                                <select class="form-select form-select-sm" id="activityStatusFilter" style="width: auto;">
                                    <option value="">All Status</option>
                                    <option value="submitted">Submitted</option>
                                    <option value="in_review">In Review</option>
                                    <option value="valid">Valid</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="generated">Generated</option>
                                </select>
                                <button class="btn btn-sm btn-outline-primary" onclick="loadAllActivity(1)">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                                <!-- Pagination -->
                                <nav aria-label="Activity pagination">
                                    <ul class="pagination pagination-sm justify-content-center mb-0" id="activityPagination">
                                        <!-- Pagination will be generated here -->
                                    </ul>
                                </nav>
                            </div>
                            <small class="text-muted" id="activityCount">Loading...</small>
                        </div>
                        
                        <div id="allActivityList" class="list-group">
                            <!-- Activity items will be loaded here -->
                            <div class="text-center py-4">
                                <div class="spinner-border spinner-border-sm text-muted" role="status"></div>
                                <div class="mt-2 text-muted">Loading all activity...</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="exportActivityData()">
                            <i class="bi bi-download me-1"></i>Export CSV
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
}

function loadAllActivity(page = 1, limit = 5) {
    const container = document.getElementById('allActivityList');
    const countElement = document.getElementById('activityCount');
    const statusFilter = document.getElementById('activityStatusFilter')?.value || '';
    
    // Show loading state
    if (page === 1) {
        container.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border spinner-border-sm text-muted" role="status"></div>
                <div class="mt-2 text-muted">Loading activities...</div>
            </div>
        `;
    }
    
    // Build query parameters
    const params = new URLSearchParams({
        action: 'activity',
        page: page,
        limit: limit
    });
    
    if (statusFilter) {
        params.append('status', statusFilter);
    }
    
    fetch(`./controller/api/dashboard.php?${params.toString()}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(result => {
            if (result.success) {
                renderAllActivity(result.data, result.pagination);
                updateActivityCount(result.pagination);
                updateActivityPagination(result.pagination);
            } else {
                showAllActivityError('Failed to load activity data');
            }
        })
        .catch(error => {
            console.error('Error loading all activity:', error);
            showAllActivityError('Connection error');
        });
}

function renderAllActivity(activities, pagination) {
    const container = document.getElementById('allActivityList');
    
    if (activities.length === 0) {
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="bi bi-inbox fs-2 d-block mb-3 text-muted"></i>
                <h6 class="text-muted">No Activity Found</h6>
                <p class="small text-muted mb-0">No activities match your current filter criteria.</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = activities.map((activity, index) => `
        <div class="list-group-item d-flex align-items-center border-0 ${index > 0 ? 'border-top' : ''}">
            <div class="me-3">
                <div class="bg-${activity.color} bg-opacity-10 rounded-circle p-2">
                    <i class="bi ${activity.icon} text-${activity.color}"></i>
                </div>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fw-medium">${activity.title}</div>
                        <small class="text-muted">${activity.description}</small>
                    </div>
                    <div class="text-end ms-3">
                        <small class="text-muted d-block">${activity.time}</small>
                        ${activity.id ? `<small class="text-primary fw-medium" style="font-size: 0.7rem;">${activity.id}</small>` : ''}
                    </div>
                </div>
                ${activity.admin_note ? `<div class="mt-1"><small class="text-info"><i class="bi bi-sticky me-1"></i>${activity.admin_note}</small></div>` : ''}
            </div>
        </div>
    `).join('');
}

function updateActivityCount(pagination) {
    const countElement = document.getElementById('activityCount');
    if (countElement && pagination) {
        const start = ((pagination.current_page - 1) * pagination.per_page) + 1;
        const end = Math.min(pagination.current_page * pagination.per_page, pagination.total);
        countElement.textContent = `Showing ${start}-${end} of ${pagination.total} activities`;
    }
}

function updateActivityPagination(pagination) {
    const paginationElement = document.getElementById('activityPagination');
    if (!paginationElement || !pagination) return;
    
    const { current_page, last_page, total } = pagination;
    
    if (last_page <= 1) {
        paginationElement.innerHTML = '';
        return;
    }
    
    let paginationHTML = '';
    
    // Previous button
    paginationHTML += `
        <li class="page-item ${current_page <= 1 ? 'disabled' : ''}">
            <button class="page-link" onclick="loadAllActivity(${current_page - 1})" ${current_page <= 1 ? 'disabled' : ''}>
                <i class="bi bi-chevron-left"></i>
            </button>
        </li>
    `;
    
    // Page numbers
    const maxVisiblePages = 3;
    let startPage = Math.max(1, current_page - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(last_page, startPage + maxVisiblePages - 1);
    
    // Adjust start page if we're near the end
    if (endPage - startPage < maxVisiblePages - 1) {
        startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }
    
    // First page and ellipsis
    if (startPage > 1) {
        paginationHTML += `
            <li class="page-item">
                <button class="page-link" onclick="loadAllActivity(1)">1</button>
            </li>
        `;
        if (startPage > 2) {
            paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }
    
    // Visible page numbers
    for (let page = startPage; page <= endPage; page++) {
        paginationHTML += `
            <li class="page-item ${page === current_page ? 'active' : ''}">
                <button class="page-link" onclick="loadAllActivity(${page})">${page}</button>
            </li>
        `;
    }
    
    // Last page and ellipsis
    if (endPage < last_page) {
        if (endPage < last_page - 1) {
            paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
        paginationHTML += `
            <li class="page-item">
                <button class="page-link" onclick="loadAllActivity(${last_page})">${last_page}</button>
            </li>
        `;
    }
    
    // Next button
    paginationHTML += `
        <li class="page-item ${current_page >= last_page ? 'disabled' : ''}">
            <button class="page-link" onclick="loadAllActivity(${current_page + 1})" ${current_page >= last_page ? 'disabled' : ''}>
                <i class="bi bi-chevron-right"></i>
            </button>
        </li>
    `;
    
    paginationElement.innerHTML = paginationHTML;
}

function showAllActivityError(message) {
    const container = document.getElementById('allActivityList');
    if (container) {
        container.innerHTML = `
            <div class="text-center py-4">
                <i class="bi bi-exclamation-triangle text-danger fs-4 d-block mb-2"></i>
                <div class="fw-medium text-danger">Unable to Load Activities</div>
                <small class="text-muted">${message}</small>
                <div class="mt-2">
                    <button class="btn btn-sm btn-outline-primary" onclick="loadAllActivity(1)">
                        <i class="bi bi-arrow-clockwise me-1"></i>Try Again
                    </button>
                </div>
            </div>
        `;
    }
}

function exportActivityData() {
    // Simple CSV export functionality
    const statusFilter = document.getElementById('activityStatusFilter')?.value || '';
    
    const params = new URLSearchParams({
        action: 'export_activity',
        format: 'csv'
    });
    
    if (statusFilter) {
        params.append('status', statusFilter);
    }
    
    // Create download link
    const downloadUrl = `./controller/api/dashboard.php?${params.toString()}`;
    
    // Create temporary link and trigger download
    const link = document.createElement('a');
    link.href = downloadUrl;
    link.download = `activity_export_${new Date().toISOString().split('T')[0]}.csv`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
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
    
    // Register this interval for cleanup
    if (typeof registerInterval === 'function') {
        registerInterval(timer);
    }
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

// Global initialization function for dashboard
function initializeDashboard() {
    console.log('Initializing dashboard...');
    
    // Register this function for cleanup
    if (typeof registerGlobalFunction === 'function') {
        registerGlobalFunction('initializeDashboard');
        registerGlobalFunction('loadDashboardStats');
        registerGlobalFunction('reviewPendingTickets');
        registerGlobalFunction('generateReports');
        registerGlobalFunction('manageTemplates');
        registerGlobalFunction('systemSettings');
        registerGlobalFunction('viewAllActivity');
        registerGlobalFunction('showActivityModal');
        registerGlobalFunction('loadAllActivity');
        registerGlobalFunction('exportActivityData');
    }
    
    if (document.getElementById('dashboard-content')) {
        loadDashboardStats();
        
        // Auto refresh every 30 seconds - register the interval for cleanup
        const refreshInterval = setInterval(() => {
            console.log('Auto-refreshing dashboard...');
            if (document.getElementById('dashboard-content')) {
                loadDashboardStats();
            }
        }, 30000);
        
        // Register interval for cleanup
        if (typeof registerInterval === 'function') {
            registerInterval(refreshInterval);
        }
    }
}

// Cleanup function for dashboard section
function cleanupDashboard() {
    console.log('Cleaning up dashboard resources...');
    
    // Close any open modals
    const activityModal = document.getElementById('activityModal');
    if (activityModal) {
        const bsModal = bootstrap.Modal.getInstance(activityModal);
        if (bsModal) {
            bsModal.hide();
        }
        // Remove modal from DOM
        activityModal.remove();
    }
    
    // Clear any pending fetch requests (if we had a way to track them)
    // This would require implementing an AbortController system
};
