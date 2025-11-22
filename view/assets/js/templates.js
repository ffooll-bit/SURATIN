/**
 * Templates Management Module
 * Handles template CRUD operations, file upload, placeholder mapping, and form configuration
 */

// Use IIFE to avoid global scope pollution and redeclaration errors
(function() {
    'use strict';

    // Module-scoped state (not global)
    let templatesData = [];
    let currentPage = 1;
    let totalPages = 1;
    let currentTemplate = null;
    let currentStep = 1;
    let uploadedFile = null;
    let placeholders = [];
    let formFields = [];
    let editingFieldId = null;

    // Bootstrap modal instances
    let createTemplateModal = null;
    let formPreviewModal = null;
    let editFieldModal = null;
    let viewTemplateModal = null;

    /**
     * Initialize Templates Section
     */
    window.initializeTemplates = function() {
        console.log('Initializing Templates section...');
        
        // Initialize Bootstrap modals
        initializeModals();
        
        // Load templates data
        loadTemplates();
        
        // Setup event listeners
        setupEventListeners();
        
        // Setup file upload handlers
        setupFileUpload();
        
        // Register cleanup function
        window.registerGlobalFunction('initializeTemplates');
        window.registerGlobalFunction('showCreateTemplateModal');
        window.registerGlobalFunction('nextStep');
        window.registerGlobalFunction('previousStep');
        window.registerGlobalFunction('removeFile');
        window.registerGlobalFunction('addCustomField');
        window.registerGlobalFunction('saveFormConfiguration');
        window.registerGlobalFunction('saveFieldConfiguration');
        window.registerGlobalFunction('editTemplate');
        window.registerGlobalFunction('toggleTemplateStatus');
        window.registerGlobalFunction('viewTemplate');
        window.registerGlobalFunction('deleteTemplate');
        window.registerGlobalFunction('updatePlaceholderMapping');
    };

    /**
     * Initialize Bootstrap Modals
     */
    function initializeModals() {
    const createModalEl = document.getElementById('createTemplateModal');
    const formPreviewModalEl = document.getElementById('formPreviewModal');
    const editFieldModalEl = document.getElementById('editFieldModal');
    const viewTemplateModalEl = document.getElementById('viewTemplateModal');
    
    if (createModalEl) {
        createTemplateModal = new bootstrap.Modal(createModalEl);
    }
    
    if (formPreviewModalEl) {
        formPreviewModal = new bootstrap.Modal(formPreviewModalEl);
    }
    
    if (editFieldModalEl) {
        editFieldModal = new bootstrap.Modal(editFieldModalEl);
    }
    
    if (viewTemplateModalEl) {
        viewTemplateModal = new bootstrap.Modal(viewTemplateModalEl);
    }
}

    /**
     * Setup Event Listeners
     */
    function setupEventListeners() {
    // Search templates
    const searchInput = document.getElementById('searchTemplates');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(function(e) {
            searchTemplates(e.target.value);
        }, 300));
    }
    
    // Number format preview update
    const numberFormatInputs = ['numberFormat', 'startNumber', 'numberPadding'];
    numberFormatInputs.forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            input.addEventListener('input', updateNumberPreview);
        }
    });
    
    // Field type change in edit modal
    const editFieldType = document.getElementById('editFieldType');
    if (editFieldType) {
        editFieldType.addEventListener('change', function() {
            const optionsContainer = document.getElementById('editFieldOptionsContainer');
            if (this.value === 'select') {
                optionsContainer.style.display = 'block';
            } else {
                optionsContainer.style.display = 'none';
            }
        });
    }
    
    // Register event listener cleanup
    window.registerEventListener(() => {
        if (searchInput) {
            searchInput.removeEventListener('input', searchTemplates);
        }
    });
}

    /**
     * Setup File Upload Handlers
     */
    function setupFileUpload() {
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('templateFile');
    
    if (!dropZone || !fileInput) return;
    
    // Click to select file
    dropZone.addEventListener('click', (e) => {
        if (e.target.tagName !== 'BUTTON' && e.target.tagName !== 'I') {
            fileInput.click();
        }
    });
    
    // File input change
    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            handleFileSelect(this.files[0]);
        }
    });
    
    // Drag and drop events
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('drag-over');
    });
    
    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('drag-over');
    });
    
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        
        if (e.dataTransfer.files.length > 0) {
            const file = e.dataTransfer.files[0];
            if (file.name.endsWith('.docx')) {
                fileInput.files = e.dataTransfer.files;
                handleFileSelect(file);
            } else {
                alert('Hanya file .docx yang diperbolehkan!');
            }
        }
    });
}

    /**
     * Handle File Selection
     */
    function handleFileSelect(file) {
    // Validate file
    if (!file.name.endsWith('.docx')) {
        alert('Hanya file .docx yang diperbolehkan!');
        return;
    }
    
    if (file.size > 5 * 1024 * 1024) { // 5MB
        alert('Ukuran file maksimal 5MB!');
        return;
    }
    
    uploadedFile = file;
    
    // Show file preview
    const filePreview = document.getElementById('filePreview');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    
    if (filePreview && fileName && fileSize) {
        fileName.textContent = file.name;
        fileSize.textContent = formatFileSize(file.size);
        filePreview.classList.remove('d-none');
    }
}

    /**
     * Remove Selected File
     */
    window.removeFile = function() {
    uploadedFile = null;
    
    const fileInput = document.getElementById('templateFile');
    const filePreview = document.getElementById('filePreview');
    
    if (fileInput) fileInput.value = '';
    if (filePreview) filePreview.classList.add('d-none');
}

    /**
     * Format File Size
     */
    function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

    /**
     * Show Create Template Modal
     */
    window.showCreateTemplateModal = function() {
    // Reset form
    resetUploadForm();
    
    // Show modal
    if (createTemplateModal) {
        createTemplateModal.show();
    }
}

    /**
     * Reset Upload Form
     */
    function resetUploadForm() {
    currentStep = 1;
    uploadedFile = null;
    placeholders = [];
    
    // Reset form
    const form = document.getElementById('uploadTemplateForm');
    if (form) form.reset();
    
    // Hide file preview
    const filePreview = document.getElementById('filePreview');
    if (filePreview) filePreview.classList.add('d-none');
    
    // Show step 1
    showUploadStep(1);
}

    /**
     * Navigate to Next Step
     */
    window.nextStep = function() {
    if (validateCurrentStep()) {
        if (currentStep === 1) {
            // Process uploaded file to extract placeholders
            processUploadedFile();
        } else if (currentStep === 2) {
            // Move to placeholder mapping
            showUploadStep(3);
        } else if (currentStep === 3) {
            // Show form preview modal
            showFormPreview();
        }
    }
}

    /**
     * Navigate to Previous Step
     */
    window.previousStep = function() {
    if (currentStep > 1) {
        showUploadStep(currentStep - 1);
    }
}

    /**
     * Validate Current Step
     */
    function validateCurrentStep() {
    switch (currentStep) {
        case 1:
            if (!uploadedFile) {
                alert('Silakan upload file template terlebih dahulu!');
                return false;
            }
            return true;
            
        case 2:
            const templateName = document.getElementById('templateName');
            const letterType = document.getElementById('letterType');
            const numberFormat = document.getElementById('numberFormat');
            
            if (!templateName || !templateName.value.trim()) {
                alert('Nama template harus diisi!');
                return false;
            }
            
            if (!letterType || !letterType.value.trim()) {
                alert('Jenis surat harus diisi!');
                return false;
            }
            
            if (!numberFormat || !numberFormat.value.trim()) {
                alert('Format penomoran harus diisi!');
                return false;
            }
            
            return true;
            
        case 3:
            // Validate that all placeholders are mapped
            const unmappedPlaceholders = placeholders.filter(p => !p.mappingType);
            if (unmappedPlaceholders.length > 0) {
                alert(`Masih ada ${unmappedPlaceholders.length} placeholder yang belum dimapping!`);
                return false;
            }
            return true;
            
        default:
            return true;
    }
}

    /**
     * Show Upload Step
     */
    function showUploadStep(step) {
    currentStep = step;
    
    // Hide all steps
    for (let i = 1; i <= 3; i++) {
        const stepEl = document.getElementById(`uploadStep${i}`);
        if (stepEl) {
            stepEl.classList.add('d-none');
        }
    }
    
    // Show current step
    const currentStepEl = document.getElementById(`uploadStep${step}`);
    if (currentStepEl) {
        currentStepEl.classList.remove('d-none');
    }
    
    // Update progress bar
    const progressBar = document.getElementById('uploadProgressBar');
    if (progressBar) {
        const progress = (step / 3) * 100;
        progressBar.style.width = `${progress}%`;
    }
    
    // Update step labels
    for (let i = 1; i <= 3; i++) {
        const label = document.getElementById(`step${i}Label`);
        if (label) {
            if (i < step) {
                label.className = 'text-success fw-bold';
            } else if (i === step) {
                label.className = 'text-primary fw-bold';
            } else {
                label.className = 'text-muted';
            }
        }
    }
    
    // Update buttons
    const btnPrev = document.getElementById('btnPrevStep');
    const btnNext = document.getElementById('btnNextStep');
    
    if (btnPrev) {
        btnPrev.style.display = step > 1 ? 'block' : 'none';
    }
    
    if (btnNext) {
        if (step === 3) {
            btnNext.innerHTML = 'Lanjut ke Preview<i class="bi bi-eye ms-2"></i>';
        } else {
            btnNext.innerHTML = 'Selanjutnya<i class="bi bi-arrow-right ms-2"></i>';
        }
    }
}

    /**
     * Process Uploaded File (Simulate placeholder extraction)
     */
    function processUploadedFile() {
    // In real implementation, this would process the .docx file
    // For now, simulate with sample placeholders
    
    // Show loading
    showLoading('Memproses file template...');
    
    setTimeout(() => {
        // Simulate extracted placeholders
        placeholders = [
            { name: 'NAMA_MAHASISWA', mappingType: null, fieldConfig: null },
            { name: 'NIM', mappingType: null, fieldConfig: null },
            { name: 'PROGRAM_STUDI', mappingType: null, fieldConfig: null },
            { name: 'NOMOR_SURAT', mappingType: null, fieldConfig: null },
            { name: 'TANGGAL_SURAT', mappingType: null, fieldConfig: null },
            { name: 'SEMESTER', mappingType: null, fieldConfig: null },
            { name: 'TAHUN_AKADEMIK', mappingType: null, fieldConfig: null }
        ];
        
        hideLoading();
        showUploadStep(2);
    }, 1500);
}

    /**
     * Update Number Format Preview
     */
    function updateNumberPreview() {
    const numberFormat = document.getElementById('numberFormat');
    const startNumber = document.getElementById('startNumber');
    const numberPadding = document.getElementById('numberPadding');
    const preview = document.getElementById('numberPreview');
    
    if (!numberFormat || !startNumber || !numberPadding || !preview) return;
    
    const format = numberFormat.value || '{NO}/SKA/FT/{BULAN}/{TAHUN}';
    const num = parseInt(startNumber.value) || 1;
    const padding = parseInt(numberPadding.value) || 4;
    
    const paddedNum = String(num).padStart(padding, '0');
    const now = new Date();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const year = now.getFullYear();
    
    let result = format
        .replace('{NO}', paddedNum)
        .replace('{BULAN}', month)
        .replace('{TAHUN}', year);
    
    preview.textContent = result;
}

    /**
     * Show Form Preview
     */
    function showFormPreview() {
    // Generate placeholder mapping UI
    generatePlaceholderMapping();
    
    showUploadStep(3);
}

    /**
     * Generate Placeholder Mapping UI
     */
    function generatePlaceholderMapping() {
    const container = document.getElementById('placeholderMappingContainer');
    if (!container) return;
    
    container.innerHTML = '';
    
    placeholders.forEach((placeholder, index) => {
        const item = document.createElement('div');
        item.className = 'placeholder-item';
        item.innerHTML = `
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <span class="placeholder-badge">\${${placeholder.name}}</span>
                </div>
            </div>
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Tipe Mapping</label>
                    <select class="form-select" onchange="updatePlaceholderMapping(${index}, this.value)">
                        <option value="">-- Pilih Tipe --</option>
                        <option value="auto">Auto Generate (Sistem)</option>
                        <option value="manual">Input Manual (Formulir)</option>
                    </select>
                </div>
                <div class="col-md-6" id="autoTypeContainer${index}" style="display: none;">
                    <label class="form-label">Tipe Auto Generate</label>
                    <select class="form-select" id="autoType${index}">
                        <option value="letter_number">Nomor Surat</option>
                        <option value="date">Tanggal Surat</option>
                        <option value="month">Bulan</option>
                        <option value="year">Tahun</option>
                        <option value="current_date">Tanggal Sekarang</option>
                    </select>
                </div>
                <div class="col-12" id="manualFieldContainer${index}" style="display: none;">
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Field ini akan muncul di formulir dan harus diisi manual
                    </div>
                </div>
            </div>
        `;
        
        container.appendChild(item);
    });
}

    /**
     * Update Placeholder Mapping
     */
    window.updatePlaceholderMapping = function(index, type) {
    if (placeholders[index]) {
        placeholders[index].mappingType = type;
        
        const autoContainer = document.getElementById(`autoTypeContainer${index}`);
        const manualContainer = document.getElementById(`manualFieldContainer${index}`);
        
        if (autoContainer && manualContainer) {
            if (type === 'auto') {
                autoContainer.style.display = 'block';
                manualContainer.style.display = 'none';
            } else if (type === 'manual') {
                autoContainer.style.display = 'none';
                manualContainer.style.display = 'block';
                
                // Create default field config
                placeholders[index].fieldConfig = {
                    label: placeholder[index].name.replace(/_/g, ' '),
                    type: 'text',
                    required: true,
                    placeholder: '',
                    preprocessing: []
                };
            } else {
                autoContainer.style.display = 'none';
                manualContainer.style.display = 'none';
            }
        }
    }
}

    /**
     * Add Custom Field (not mapped to placeholder)
     */
    window.addCustomField = function() {
    alert('Fitur tambah custom field akan segera diimplementasikan');
}

    /**
     * Save Form Configuration
     */
    window.saveFormConfiguration = function() {
    alert('Fitur save form configuration akan segera diimplementasikan');
}

    /**
     * Save Field Configuration
     */
    window.saveFieldConfiguration = function() {
    alert('Fitur save field configuration akan segera diimplementasikan');
}

    /**
     * Edit Template
     */
    window.editTemplate = function() {
    alert('Fitur edit template akan segera diimplementasikan');
}

    /**
     * Toggle Template Status
     */
    window.toggleTemplateStatus = function() {
    alert('Fitur toggle status akan segera diimplementasikan');
}

    /**
     * View Template Details
     */
    window.viewTemplate = function(templateId) {
    console.log('View template:', templateId);
    alert('Fitur view template akan segera diimplementasikan');
}

    /**
     * Delete Template
     */
    window.deleteTemplate = function(templateId) {
    if (confirm('Yakin ingin menghapus template ini?')) {
        console.log('Delete template:', templateId);
        alert('Fitur delete template akan segera diimplementasikan');
    }
}

    /**
     * Load Templates Data
     */
    function loadTemplates(page = 1) {
    currentPage = page;
    
    // Show loading
    const tbody = document.getElementById('templatesTableBody');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-2 mb-0">Memuat data template...</p>
                </td>
            </tr>
        `;
    }
    
    // Simulate API call with sample data
    setTimeout(() => {
        const sampleData = [
            {
                id: 1,
                name: 'Surat Keterangan Aktif Kuliah',
                letter_type: 'Surat Keterangan',
                status: 'active',
                updated_at: '2025-11-20 10:30:00'
            },
            {
                id: 2,
                name: 'Surat Permohonan Cuti Akademik',
                letter_type: 'Surat Permohonan',
                status: 'active',
                updated_at: '2025-11-18 14:15:00'
            },
            {
                id: 3,
                name: 'Surat Keterangan Lulus',
                letter_type: 'Surat Keterangan',
                status: 'inactive',
                updated_at: '2025-11-15 09:00:00'
            }
        ];
        
        templatesData = sampleData;
        renderTemplatesTable();
    }, 800);
}

    /**
     * Render Templates Table
     */
    function renderTemplatesTable() {
    const tbody = document.getElementById('templatesTableBody');
    if (!tbody) return;
    
    if (templatesData.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                    <p class="text-muted mb-0">Belum ada template</p>
                    <button class="btn btn-sm btn-primary mt-3" onclick="showCreateTemplateModal()">
                        <i class="bi bi-plus-circle me-2"></i>Buat Template Pertama
                    </button>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = '';
    
    templatesData.forEach(template => {
        const row = document.createElement('tr');
        
        const statusClass = template.status === 'active' ? 'status-active' : 'status-inactive';
        const statusText = template.status === 'active' ? 'Aktif' : 'Non-aktif';
        const statusIcon = template.status === 'active' ? 'check-circle' : 'x-circle';
        
        row.innerHTML = `
            <td>
                <div class="d-flex align-items-center">
                    <i class="bi bi-file-earmark-text fs-4 text-primary me-3"></i>
                    <div>
                        <div class="fw-bold">${escapeHtml(template.name)}</div>
                    </div>
                </div>
            </td>
            <td>${escapeHtml(template.letter_type)}</td>
            <td>
                <span class="status-badge ${statusClass}">
                    <i class="bi bi-${statusIcon} me-1"></i>${statusText}
                </span>
            </td>
            <td>${formatDateTime(template.updated_at)}</td>
            <td class="text-center">
                <div class="btn-group btn-group-sm" role="group">
                    <button class="btn btn-outline-primary" onclick="viewTemplate(${template.id})" title="Lihat Detail">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-outline-danger" onclick="deleteTemplate(${template.id})" title="Hapus">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        `;
        
        tbody.appendChild(row);
    });
}

    /**
     * Search Templates
     */
    function searchTemplates(query) {
    console.log('Search templates:', query);
    // In real implementation, this would filter the templates
    // For now, just log the search query
}

    /**
     * Format DateTime
     */
    function formatDateTime(datetime) {
    const date = new Date(datetime);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

    /**
     * Escape HTML
     */
    function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

    /**
     * Show Loading Overlay
     */
    function showLoading(message = 'Loading...') {
    // Create loading overlay if doesn't exist
    let overlay = document.getElementById('loadingOverlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'loadingOverlay';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        `;
        overlay.innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-light" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-white mt-3" id="loadingMessage">${message}</p>
            </div>
        `;
        document.body.appendChild(overlay);
    } else {
        overlay.style.display = 'flex';
        const messageEl = document.getElementById('loadingMessage');
        if (messageEl) messageEl.textContent = message;
    }
}

    /**
     * Hide Loading Overlay
     */
    function hideLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.style.display = 'none';
    }
}

    /**
     * Debounce Function
     */
    function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

    /**
     * Cleanup Function (called when leaving section)
     */
    window.cleanupTemplates = function() {
        console.log('Cleaning up Templates section...');
        
        // Close all modals
        if (createTemplateModal) {
            createTemplateModal.hide();
        }
        if (formPreviewModal) {
            formPreviewModal.hide();
        }
        if (editFieldModal) {
            editFieldModal.hide();
        }
        if (viewTemplateModal) {
            viewTemplateModal.hide();
        }
        
        // Clear state
        templatesData = [];
        currentTemplate = null;
        uploadedFile = null;
        placeholders = [];
        formFields = [];
    };

})(); // End of IIFE
