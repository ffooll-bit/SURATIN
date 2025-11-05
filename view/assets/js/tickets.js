// Tickets management JavaScript
if (typeof ticketsData === 'undefined') {
    var ticketsData = [];
}
if (typeof filteredTickets === 'undefined') {
    var filteredTickets = [];
}
if (typeof currentPage === 'undefined') {
    var currentPage = 1;
}
if (typeof itemsPerPage === 'undefined') {
    var itemsPerPage = 10;
}
if (typeof selectedTickets === 'undefined') {
    var selectedTickets = new Set();
}

// Initialize tickets functionality
function initializeTickets() {
    console.log('Initializing tickets module...');
    
    // Register functions for cleanup
    if (typeof registerGlobalFunction === 'function') {
        registerGlobalFunction('initializeTickets');
        registerGlobalFunction('setupEventListeners');
        registerGlobalFunction('loadTicketsData');
        registerGlobalFunction('applyFilters');
        registerGlobalFunction('saveTicket');
        registerGlobalFunction('renderTicketsTable');
        registerGlobalFunction('updateStatistics');
    }
    
    loadTicketsData();
    setupEventListeners();
}

function setupEventListeners() {
    // Search input with debounce
    let searchTimeout;
    const searchInput = document.getElementById('searchTickets');
    const createForm = document.getElementById('createTicketForm');
    const nikInput = document.getElementById('ticketNik');
    
    if (searchInput) {
        const searchHandler = function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                applyFilters();
            }, 500);
        };
        
        searchInput.addEventListener('input', searchHandler);
        
        // Register cleanup for this event listener
        if (typeof registerEventListener === 'function') {
            registerEventListener(() => {
                searchInput.removeEventListener('input', searchHandler);
            });
        }
        
        // Register timeout for cleanup
        if (typeof registerTimeout === 'function' && searchTimeout) {
            registerTimeout(searchTimeout);
        }
    }

    // Form submission
    if (createForm) {
        const formHandler = function(e) {
            e.preventDefault();
            saveTicket();
        };
        
        createForm.addEventListener('submit', formHandler);
        
        // Register cleanup for this event listener
        if (typeof registerEventListener === 'function') {
            registerEventListener(() => {
                createForm.removeEventListener('submit', formHandler);
            });
        }
    }

    // Auto-format NIK input
    if (nikInput) {
        const nikHandler = function(e) {
            this.value = this.value.replace(/\D/g, '').substring(0, 16);
        };
        
        nikInput.addEventListener('input', nikHandler);
        
        // Register cleanup for this event listener
        if (typeof registerEventListener === 'function') {
            registerEventListener(() => {
                nikInput.removeEventListener('input', nikHandler);
            });
        }
    }
}

// Cleanup function for tickets section
function cleanupTickets() {
    console.log('Cleaning up tickets resources...');
    
    // Reset global variables
    ticketsData = [];
    filteredTickets = [];
    currentPage = 1;
    selectedTickets.clear();
    
    // Reset select all checkbox
    const selectAll = document.getElementById('selectAll');
    if (selectAll) {
        selectAll.checked = false;
        selectAll.indeterminate = false;
    }
    
    // Close any open modals
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        const bsModal = bootstrap.Modal.getInstance(modal);
        if (bsModal) {
            bsModal.hide();
        }
    });
}

async function loadTicketsData() {
    try {
        const response = await fetch('controller/api/tickets.php');
        const result = await response.json();
        
        if (result.success) {
            ticketsData = result.data || [];
            filteredTickets = [...ticketsData];
            updateStatistics();
            renderTicketsTable();
        } else {
            throw new Error(result.message || 'Failed to load tickets');
        }
    } catch (error) {
        console.error('Error loading tickets:', error);
        // Show error message instead of fallback data
        const tbody = document.getElementById('ticketsTableBody');
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-5">
                    <i class="bi bi-exclamation-triangle fs-1 text-danger"></i>
                    <p class="mt-2 text-danger">Gagal memuat data tickets</p>
                    <small class="text-muted">${error.message}</small>
                </td>
            </tr>
        `;
    }
}

function updateStatistics() {
    const stats = ticketsData.reduce((acc, ticket) => {
        acc.total++;
        acc[ticket.status] = (acc[ticket.status] || 0) + 1;
        return acc;
    }, { total: 0, submitted: 0, in_review: 0, valid: 0, rejected: 0, generated: 0 });

    document.getElementById('totalTickets').textContent = stats.total;
    document.getElementById('submittedTickets').textContent = stats.submitted;
    document.getElementById('inReviewTickets').textContent = stats.in_review;
    document.getElementById('generatedTickets').textContent = stats.generated;
}

function renderTicketsTable() {
    const tbody = document.getElementById('ticketsTableBody');
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const pageTickets = filteredTickets.slice(startIndex, endIndex);

    if (pageTickets.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted"></i>
                    <p class="mt-2 text-muted">Tidak ada tickets yang ditemukan</p>
                </td>
            </tr>
        `;
    } else {
        tbody.innerHTML = pageTickets.map(ticket => {
            // Check if this ticket is selected
            const isSelected = selectedTickets.has(ticket.id);
            
            // Generate action buttons based on status
            let actionButtons = `
                <button class="btn btn-sm btn-outline-primary" onclick="showTicketDetail(${ticket.id})" title="Lihat Detail">
                    <i class="bi bi-eye"></i>
                </button>
            `;
            
            // Add status-specific buttons
            if (ticket.status === 'submitted') {
                actionButtons += `
                    <button class="btn btn-sm btn-outline-warning" onclick="updateTicketStatus(${ticket.id}, 'in_review')" title="Review">
                        <i class="bi bi-search"></i>
                    </button>
                `;
            } else if (ticket.status === 'in_review') {
                actionButtons += `
                    <button class="btn btn-sm btn-outline-success" onclick="updateTicketStatus(${ticket.id}, 'valid')" title="Valid">
                        <i class="bi bi-check-circle"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="updateTicketStatus(${ticket.id}, 'rejected')" title="Reject">
                        <i class="bi bi-x-circle"></i>
                    </button>
                `;
            } else if (ticket.status === 'valid') {
                actionButtons += `
                    <button class="btn btn-sm btn-outline-info" onclick="updateTicketStatus(${ticket.id}, 'generated')" title="Generate">
                        <i class="bi bi-file-text"></i>
                    </button>
                `;
            }
            
            // Always add delete button
            actionButtons += `
                <button class="btn btn-sm btn-outline-danger" onclick="deleteTicket(${ticket.id})" title="Hapus">
                    <i class="bi bi-trash"></i>
                </button>
            `;
            
            return `
                <tr>
                    <td class="text-center">
                        <input type="checkbox" class="form-check-input ticket-checkbox" 
                               value="${ticket.id}" onchange="toggleTicketSelection(${ticket.id})"
                               ${isSelected ? 'checked' : ''}>
                    </td>
                    <td>
                        <strong>${ticket.ticket_number}</strong>
                    </td>
                    <td>
                        <div>
                            <strong>${ticket.name}</strong>
                            ${ticket.email ? `<br><small class="text-muted">${ticket.email}</small>` : ''}
                        </div>
                    </td>
                    <td>${getLetterTypeName(ticket.letter_type)}</td>
                    <td class="text-center">
                        <span class="badge status-badge ${getStatusClass(ticket.status)}">
                            ${getStatusName(ticket.status)}
                        </span>
                    </td>
                    <td>
                        <small>${formatDate(ticket.created_at)}</small>
                    </td>
                    <td class="text-center">
                        <div class="btn-group" role="group">
                            ${actionButtons}
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    // Update pagination
    renderPagination();
    updateCounters();
    
    // Update select all state after rendering
    updateSelectAllState();
}

function renderPagination() {
    const totalPages = Math.ceil(filteredTickets.length / itemsPerPage);
    const pagination = document.getElementById('pagination');
    
    if (totalPages <= 1) {
        pagination.innerHTML = '';
        return;
    }

    let paginationHTML = '';
    
    // Previous button
    paginationHTML += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="event.preventDefault(); changePage(${currentPage - 1})">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>
    `;

    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
            paginationHTML += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="event.preventDefault(); changePage(${i})">${i}</a>
                </li>
            `;
        } else if (i === currentPage - 3 || i === currentPage + 3) {
            paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }

    // Next button
    paginationHTML += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="event.preventDefault(); changePage(${currentPage + 1})">
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>
    `;

    pagination.innerHTML = paginationHTML;
}

function changePage(page) {
    const totalPages = Math.ceil(filteredTickets.length / itemsPerPage);
    if (page >= 1 && page <= totalPages) {
        currentPage = page;
        renderTicketsTable();
    }
}

function updateCounters() {
    document.getElementById('showingCount').textContent = filteredTickets.length;
    document.getElementById('totalCount').textContent = ticketsData.length;
}

function applyFilters() {
    const search = document.getElementById('searchTickets').value.toLowerCase();
    const status = document.getElementById('statusFilter').value;
    const type = document.getElementById('typeFilter').value;
    const date = document.getElementById('dateFilter').value;
    const sort = document.getElementById('sortFilter').value;

    filteredTickets = ticketsData.filter(ticket => {
        const matchSearch = !search || 
            ticket.ticket_number.toLowerCase().includes(search) ||
            ticket.name.toLowerCase().includes(search) ||
            (ticket.email && ticket.email.toLowerCase().includes(search));
        
        const matchStatus = !status || ticket.status === status;
        const matchType = !type || ticket.letter_type === type;
        const matchDate = !date || ticket.created_at.startsWith(date);

        return matchSearch && matchStatus && matchType && matchDate;
    });

    // Apply sorting
    filteredTickets.sort((a, b) => {
        switch (sort) {
            case 'created_desc':
                return new Date(b.created_at) - new Date(a.created_at);
            case 'created_asc':
                return new Date(a.created_at) - new Date(b.created_at);
            case 'status_asc':
                return a.status.localeCompare(b.status);
            case 'name_asc':
                return a.name.localeCompare(b.name);
            default:
                return 0;
        }
    });

    // Clean up selections that are no longer in filtered results
    const filteredTicketIds = new Set(filteredTickets.map(t => t.id));
    selectedTickets.forEach(ticketId => {
        if (!filteredTicketIds.has(ticketId)) {
            selectedTickets.delete(ticketId);
        }
    });

    currentPage = 1;
    renderTicketsTable();
}

function clearFilters() {
    document.getElementById('searchTickets').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('typeFilter').value = '';
    document.getElementById('dateFilter').value = '';
    document.getElementById('sortFilter').value = 'created_desc';
    
    filteredTickets = [...ticketsData];
    
    // Clear selections when clearing filters
    selectedTickets.clear();
    
    currentPage = 1;
    renderTicketsTable();
}

// Helper functions
function getLetterTypeName(type) {
    const types = {
        'surat_keterangan': 'Surat Keterangan',
        'surat_pengantar': 'Surat Pengantar', 
        'surat_domisili': 'Surat Domisili',
        'surat_usaha': 'Surat Usaha',
        'surat_tidak_mampu': 'Surat Tidak Mampu'
    };
    return types[type] || type;
}

function getStatusName(status) {
    const statuses = {
        'submitted': 'Submitted',
        'in_review': 'In Review',
        'valid': 'Valid',
        'rejected': 'Rejected',
        'generated': 'Generated'
    };
    return statuses[status] || status;
}

function getStatusClass(status) {
    const classes = {
        'submitted': 'bg-info',
        'in_review': 'bg-warning',
        'valid': 'bg-success',
        'rejected': 'bg-danger',
        'generated': 'bg-primary'
    };
    return classes[status] || 'bg-secondary';
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Ticket selection functions
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    
    if (selectAll.checked) {
        // Select all tickets across all pages
        filteredTickets.forEach(ticket => {
            selectedTickets.add(ticket.id);
        });
    } else {
        // Deselect all tickets
        selectedTickets.clear();
    }
    
    // Update checkboxes on current page
    const checkboxes = document.querySelectorAll('.ticket-checkbox');
    checkboxes.forEach(checkbox => {
        const ticketId = parseInt(checkbox.value);
        checkbox.checked = selectedTickets.has(ticketId);
    });
    
    updateBulkActions();
}

function toggleTicketSelection(ticketId) {
    const checkbox = document.querySelector(`input[value="${ticketId}"]`);
    
    if (checkbox.checked) {
        selectedTickets.add(ticketId);
    } else {
        selectedTickets.delete(ticketId);
    }
    
    updateSelectAllState();
    updateBulkActions();
}

function updateSelectAllState() {
    const selectAll = document.getElementById('selectAll');
    if (!selectAll) return;
    
    const totalFilteredTickets = filteredTickets.length;
    const selectedFilteredTickets = filteredTickets.filter(ticket => 
        selectedTickets.has(ticket.id)
    ).length;
    
    if (selectedFilteredTickets === 0) {
        selectAll.checked = false;
        selectAll.indeterminate = false;
    } else if (selectedFilteredTickets === totalFilteredTickets) {
        selectAll.checked = true;
        selectAll.indeterminate = false;
    } else {
        selectAll.checked = false;
        selectAll.indeterminate = true;
    }
}

// Ticket actions
async function showTicketDetail(ticketId) {
    const ticket = ticketsData.find(t => t.id === ticketId);
    if (!ticket) return;

    const content = document.getElementById('ticketDetailContent');
    content.innerHTML = `
        <div class="row g-3">
            <div class="col-md-6">
                <strong>Nomor Ticket:</strong><br>
                <span class="text-primary">${ticket.ticket_number}</span>
            </div>
            <div class="col-md-6">
                <strong>Status:</strong><br>
                <span class="badge ${getStatusClass(ticket.status)}">${getStatusName(ticket.status)}</span>
            </div>
            <div class="col-md-6">
                <strong>Nama Lengkap:</strong><br>
                ${ticket.name}
            </div>
            <div class="col-md-6">
                <strong>NPM:</strong><br>
                ${ticket.npm || '-'}
            </div>
            <div class="col-md-6">
                <strong>Program Studi:</strong><br>
                ${ticket.prodi || '-'}
            </div>
            <div class="col-md-6">
                <strong>Email:</strong><br>
                ${ticket.email || '-'}
            </div>
            <div class="col-md-6">
                <strong>WhatsApp:</strong><br>
                ${ticket.wa || '-'}
            </div>
            <div class="col-12">
                <strong>Jenis Surat:</strong><br>
                ${getLetterTypeName(ticket.letter_type)}
            </div>
            ${ticket.data ? `
                <div class="col-12">
                    <strong>Data Tambahan:</strong><br>
                    <pre class="bg-light p-2 rounded">${JSON.stringify(ticket.data, null, 2)}</pre>
                </div>
            ` : ''}
            ${ticket.admin_note ? `
                <div class="col-12">
                    <strong>Catatan Admin:</strong><br>
                    <div class="bg-light p-2 rounded">${ticket.admin_note}</div>
                </div>
            ` : ''}
            <div class="col-md-6">
                <strong>Tanggal Dibuat:</strong><br>
                ${formatDate(ticket.created_at)}
            </div>
            <div class="col-md-6">
                <strong>Terakhir Diupdate:</strong><br>
                ${formatDate(ticket.updated_at)}
            </div>
        </div>
    `;

    const modal = new bootstrap.Modal(document.getElementById('ticketDetailModal'));
    modal.show();
}

function showCreateTicketModal() {
    document.getElementById('createTicketModalLabel').textContent = 'Buat Ticket Baru';
    document.getElementById('createTicketForm').reset();
    
    const modal = new bootstrap.Modal(document.getElementById('createTicketModal'));
    modal.show();
}

async function saveTicket() {
    const formData = {
        name: document.getElementById('ticketName').value,
        email: document.getElementById('ticketEmail').value,
        phone: document.getElementById('ticketPhone').value,
        nik: document.getElementById('ticketNik').value,
        letter_type: document.getElementById('ticketType').value,
        purpose: document.getElementById('ticketPurpose').value,
        notes: document.getElementById('ticketNotes').value
    };

    try {
        const response = await fetch('controller/api/tickets.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });

        const result = await response.json();
        
        if (result.success) {
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('createTicketModal')).hide();
            
            // Reload data
            await loadTicketsData();
            
            // Show success message
            showAlert('success', 'Ticket berhasil dibuat!');
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        console.error('Error saving ticket:', error);
        showAlert('danger', 'Gagal menyimpan ticket: ' + error.message);
    }
}

async function updateTicketStatus(ticketId, newStatus) {
    // Get confirmation message based on status
    const statusMessages = {
        'in_review': 'Yakin ingin memindahkan ticket ini ke status "In Review"?',
        'valid': 'Yakin ingin menandai ticket ini sebagai "Valid"?',
        'rejected': 'Yakin ingin menolak ticket ini? Status akan berubah menjadi "Rejected".',
        'generated': 'Yakin ingin generate surat untuk ticket ini?'
    };
    
    const confirmMessage = statusMessages[newStatus] || `Yakin ingin mengubah status ticket ini menjadi "${getStatusName(newStatus)}"?`;
    
    if (!confirm(confirmMessage)) {
        return;
    }
    
    try {
        const response = await fetch(`controller/api/tickets.php?id=${ticketId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ status: newStatus })
        });

        const result = await response.json();
        
        if (result.success) {
            // Update local data
            const ticket = ticketsData.find(t => t.id === ticketId);
            if (ticket) {
                ticket.status = newStatus;
            }
            
            updateStatistics();
            renderTicketsTable();
            
            // Close detail modal if open
            const detailModal = bootstrap.Modal.getInstance(document.getElementById('ticketDetailModal'));
            if (detailModal) {
                detailModal.hide();
            }
            
            showAlert('success', 'Status ticket berhasil diperbarui!');
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        console.error('Error updating ticket status:', error);
        showAlert('danger', 'Gagal memperbarui status: ' + error.message);
    }
}

async function deleteTicket(ticketId) {
    if (!confirm('Yakin ingin menghapus ticket ini?')) return;
    
    try {
        const response = await fetch(`controller/api/tickets.php?id=${ticketId}`, {
            method: 'DELETE'
        });

        const result = await response.json();
        
        if (result.success) {
            // Remove from local data
            ticketsData = ticketsData.filter(t => t.id !== ticketId);
            filteredTickets = filteredTickets.filter(t => t.id !== ticketId);
            
            updateStatistics();
            renderTicketsTable();
            
            showAlert('success', 'Ticket berhasil dihapus!');
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        console.error('Error deleting ticket:', error);
        showAlert('danger', 'Gagal menghapus ticket: ' + error.message);
    }
}

// Bulk actions
async function bulkUpdateStatus(newStatus) {
    if (selectedTickets.size === 0) return;
    
    // Enhanced confirmation message for bulk operations
    const statusMessages = {
        'in_review': `Yakin ingin memindahkan ${selectedTickets.size} tickets ke status "In Review"?`,
        'valid': `Yakin ingin menandai ${selectedTickets.size} tickets sebagai "Valid"?`,
        'rejected': `Yakin ingin menolak ${selectedTickets.size} tickets? Status akan berubah menjadi "Rejected".`,
        'generated': `Yakin ingin generate surat untuk ${selectedTickets.size} tickets?`
    };
    
    const confirmMessage = statusMessages[newStatus] || `Yakin ingin mengubah status ${selectedTickets.size} tickets menjadi "${getStatusName(newStatus)}"?`;
    
    if (!confirm(confirmMessage)) return;
    
    try {
        const ticketIds = Array.from(selectedTickets);
        
        const response = await fetch('controller/api/tickets.php', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ 
                ids: ticketIds,
                status: newStatus 
            })
        });

        const result = await response.json();
        
        if (result.success) {
            // Update local data
            ticketIds.forEach(id => {
                const ticket = ticketsData.find(t => t.id === id);
                if (ticket) {
                    ticket.status = newStatus;
                }
            });
            
            selectedTickets.clear();
            updateStatistics();
            renderTicketsTable();
            updateBulkActions();
            
            showAlert('success', `${ticketIds.length} tickets berhasil diperbarui!`);
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        console.error('Error bulk updating tickets:', error);
        showAlert('danger', 'Gagal memperbarui tickets: ' + error.message);
    }
}

async function bulkDelete() {
    if (selectedTickets.size === 0) return;
    
    if (!confirm(`Yakin ingin menghapus ${selectedTickets.size} tickets? Tindakan ini tidak dapat dibatalkan.`)) return;
    
    try {
        const ticketIds = Array.from(selectedTickets);
        
        const response = await fetch('controller/api/tickets.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ ids: ticketIds })
        });

        const result = await response.json();
        
        if (result.success) {
            // Remove from local data
            ticketsData = ticketsData.filter(t => !ticketIds.includes(t.id));
            filteredTickets = filteredTickets.filter(t => !ticketIds.includes(t.id));
            
            selectedTickets.clear();
            updateStatistics();
            renderTicketsTable();
            updateBulkActions();
            
            showAlert('success', `${ticketIds.length} tickets berhasil dihapus!`);
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        console.error('Error bulk deleting tickets:', error);
        showAlert('danger', 'Gagal menghapus tickets: ' + error.message);
    }
}

// Export functions
function exportTickets(format) {
    const params = new URLSearchParams({
        format: format,
        search: document.getElementById('searchTickets').value,
        status: document.getElementById('statusFilter').value,
        type: document.getElementById('typeFilter').value,
        date: document.getElementById('dateFilter').value
    });
    
    window.open(`controller/api/export-tickets.php?${params.toString()}`, '_blank');
}

// Utility function to show alerts
function showAlert(type, message) {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alert);
    
    // Auto remove after 5 seconds
    const alertTimeout = setTimeout(() => {
        if (alert.parentNode) {
            alert.parentNode.removeChild(alert);
        }
    }, 5000);
    
    // Register timeout for cleanup
    if (typeof registerTimeout === 'function') {
        registerTimeout(alertTimeout);
    }
}
