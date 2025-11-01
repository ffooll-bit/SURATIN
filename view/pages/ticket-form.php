<?php
require_once __DIR__ . '/../../controller/config/app.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pengajuan Surat - <?= APP_NAME; ?></title>
    <meta name="description" content="Form pengajuan surat online untuk berbagai keperluan akademik dan administrasi melalui sistem <?= APP_NAME; ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .file-upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 0.375rem;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .file-upload-area:hover {
            border-color: #0d6efd;
            background-color: #f8f9fa;
        }
        .file-upload-area.dragover {
            border-color: #0d6efd;
            background-color: #e7f1ff;
        }
        .file-list {
            margin-top: 1rem;
        }
        .file-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.5rem;
            background-color: #f8f9fa;
            border-radius: 0.375rem;
            margin-bottom: 0.5rem;
        }
        .hero-section {
            background: linear-gradient(135deg, #0d6efd 0%, #0056b3 100%);
            color: white;
            padding: 3rem 0;
        }
        .form-section {
            background-color: #fff;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            margin-top: -2rem;
            position: relative;
            z-index: 10;
        }
        
        /* Mobile Improvements */
        @media (max-width: 576px) {
            .hero-section {
                padding: 2rem 0;
            }
            .hero-section h1 {
                font-size: 2rem;
            }
            .form-section {
                margin-top: -1rem;
                border-radius: 0.5rem;
            }
            .file-upload-area {
                padding: 1.5rem;
            }
            .btn {
                min-height: 44px; /* WCAG touch target */
            }
        }
        
        /* Tablet Improvements */
        @media (min-width: 577px) and (max-width: 991px) {
            .col-md-6 {
                margin-bottom: 1rem;
            }
        }
        
        /* Better focus indicators */
        .form-control:focus,
        .form-select:focus,
        .btn:focus {
            outline: 2px solid #0d6efd;
            outline-offset: 2px;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        
        .file-upload-area:focus-within {
            outline: 2px solid #0d6efd;
            outline-offset: 2px;
        }
        
        /* Environment indicator */
        <?php if (DEBUG_MODE): ?>
        .debug-info {
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 1050;
            font-size: 0.75rem;
        }
        <?php endif; ?>
    </style>
</head>
<body class="bg-light">
    <?php if (DEBUG_MODE): ?>
    <div class="debug-info">
        <span class="badge bg-warning text-dark">
            <i class="bi bi-bug me-1"></i>Debug Mode - <?= APP_VERSION; ?>
        </span>
    </div>
    <?php endif; ?>
    
    <!-- Skip to main content link -->
    <a href="#main-form" class="visually-hidden-focusable">Skip to main form</a>
    
    <!-- Hero Section -->
    <header class="hero-section" role="banner">
        <div class="container">
            <div class="row justify-content-center text-center">
                <div class="col-lg-8">
                    <h1 class="display-5 fw-bold mb-3"><?= APP_NAME; ?></h1>
                    <p class="lead mb-0"><?= APP_DESCRIPTION; ?></p>
                    <p class="mb-0">Ajukan permohonan surat dengan mudah dan cepat</p>
                    <?php if (defined('APP_DEV') && APP_DEV): ?>
                    <small class="d-block mt-2 opacity-75">
                        Dikembangkan oleh <?= APP_DEV; ?>
                    </small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Form -->
    <main class="container" role="main">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <section class="form-section p-4 p-lg-5 mb-5" aria-labelledby="form-title">
                    <form id="ticketForm" novalidate aria-describedby="form-description">
                        <div class="text-center mb-4">
                            <h2 id="form-title" class="h3 mb-2">Form Pengajuan Surat</h2>
                            <p id="form-description" class="text-muted">Lengkapi data di bawah ini dengan benar</p>
                        </div>

                        <!-- Personal Information -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama" name="nama" required>
                                <div class="invalid-feedback">Nama lengkap wajib diisi</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="npm" class="form-label">NPM <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="npm" name="npm" placeholder="Contoh: 2023110001" required>
                                <div class="form-text">Format: 10 digit angka (tahun + nomor)</div>
                                <div class="invalid-feedback">NPM harus berupa 10 digit angka</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="prodi" class="form-label">Program Studi <span class="text-danger">*</span></label>
                                <select class="form-select" id="prodi" name="prodi" required>
                                    <option value="">Pilih Program Studi</option>
                                    <option value="Teknik Informatika">Teknik Informatika</option>
                                    <option value="Sistem Informasi">Sistem Informasi</option>
                                    <option value="Teknik Elektro">Teknik Elektro</option>
                                    <option value="Teknik Mesin">Teknik Mesin</option>
                                    <option value="Teknik Sipil">Teknik Sipil</option>
                                    <option value="Manajemen">Manajemen</option>
                                    <option value="Akuntansi">Akuntansi</option>
                                </select>
                                <div class="invalid-feedback">Program studi wajib dipilih</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="jenisSurat" class="form-label">Jenis Surat <span class="text-danger">*</span></label>
                                <select class="form-select" id="jenisSurat" name="jenis_surat" required>
                                    <option value="">Pilih Jenis Surat</option>
                                    <option value="Surat Keterangan Aktif Kuliah">Surat Keterangan Aktif Kuliah</option>
                                    <option value="Surat Pengantar Magang">Surat Pengantar Magang</option>
                                    <option value="Surat Pengantar Penelitian">Surat Pengantar Penelitian</option>
                                    <option value="Surat Keterangan Lulus">Surat Keterangan Lulus</option>
                                    <option value="Surat Rekomendasi">Surat Rekomendasi</option>
                                </select>
                                <div class="invalid-feedback">Jenis surat wajib dipilih</div>
                            </div>
                        </div>

                        <!-- Dynamic Fields Section -->
                        <div id="dynamicFields" class="mb-4"></div>

                        <!-- Contact Information -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="contoh@email.com" required>
                                <div class="form-text">Email untuk notifikasi status pengajuan</div>
                                <div class="invalid-feedback">Email tidak valid</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="noWa" class="form-label">No. WhatsApp <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="noWa" name="no_wa" placeholder="08123456789" required>
                                <div class="form-text">Nomor WhatsApp aktif untuk notifikasi</div>
                                <div class="invalid-feedback">Nomor WhatsApp wajib diisi</div>
                            </div>
                        </div>

                        <!-- File Upload -->
                        <fieldset class="mb-4">
                            <legend class="form-label">Lampiran Dokumen</legend>
                            <div class="file-upload-area" id="fileUploadArea" role="button" tabindex="0" 
                                 aria-describedby="file-upload-instructions" onclick="document.getElementById('fileInput').click()" 
                                 onkeydown="if(event.key==='Enter'||event.key===' ') document.getElementById('fileInput').click()">
                                <i class="bi bi-cloud-upload fs-1 text-muted mb-3" aria-hidden="true"></i>
                                <h5>Drag & Drop file atau klik untuk upload</h5>
                                <p id="file-upload-instructions" class="text-muted mb-0">
                                    Format: PDF, JPG, PNG | Maksimal 5MB per file
                                </p>
                                <input type="file" id="fileInput" name="attachments[]" multiple 
                                       accept=".pdf,.jpg,.jpeg,.png" class="d-none" 
                                       aria-describedby="file-upload-instructions">
                            </div>
                            <div id="fileList" class="file-list" role="list" aria-live="polite" aria-label="Uploaded files"></div>
                        </fieldset>

                        <!-- Submit Button -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-send me-2"></i>Ajukan Permohonan
                            </button>
                        </div>

                        <div class="text-center mt-3">
                            <small class="text-muted">
                                Dengan mengajukan permohonan, Anda menyetujui bahwa data yang diberikan adalah benar
                            </small>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-light py-3">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <small>
                        © <?= date('Y'); ?> <?= APP_NAME; ?>. <?= APP_DESCRIPTION; ?>
                        <?php if (defined('APP_DEV') && APP_DEV): ?>
                            <br>Developed by <?= APP_DEV; ?>
                        <?php endif; ?>
                    </small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Pengajuan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Pastikan data yang Anda masukkan sudah benar:</p>
                    <div id="summaryContent"></div>
                    <div class="alert alert-info mt-3">
                        <i class="bi bi-info-circle me-2"></i>
                        Setelah pengajuan dikirim, Anda akan mendapat nomor tiket untuk melacak status permohonan.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="confirmSubmit">Ya, Kirim Pengajuan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Configuration from PHP
        const APP_CONFIG = {
            name: '<?= APP_NAME; ?>',
            description: '<?= APP_DESCRIPTION; ?>',
            version: '<?= APP_VERSION; ?>',
            dev: '<?= APP_DEV; ?>',
            debugMode: <?= DEBUG_MODE ? 'true' : 'false'; ?>,
            sessionLifetime: <?= SESSION_LIFETIME; ?>
        };

        // Global variables
        let selectedFiles = [];
        const maxFileSize = 5 * 1024 * 1024; // 5MB
        const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];

        // Dynamic form fields based on letter type
        const dynamicFields = {
            'Surat Pengantar Magang': [
                { label: 'Nama Perusahaan', name: 'nama_perusahaan', type: 'text', required: true },
                { label: 'Alamat Perusahaan', name: 'alamat_perusahaan', type: 'textarea', required: true },
                { label: 'Periode Magang', name: 'periode_magang', type: 'text', placeholder: 'Contoh: Januari - Maret 2024', required: true }
            ],
            'Surat Pengantar Penelitian': [
                { label: 'Topik Penelitian', name: 'topik_penelitian', type: 'text', required: true },
                { label: 'Lokasi Penelitian', name: 'lokasi_penelitian', type: 'text', required: true },
                { label: 'Periode Penelitian', name: 'periode_penelitian', type: 'text', placeholder: 'Contoh: Februari - April 2024', required: true }
            ],
            'Surat Rekomendasi': [
                { label: 'Tujuan Rekomendasi', name: 'tujuan_rekomendasi', type: 'text', placeholder: 'Contoh: Beasiswa, Pekerjaan, dll', required: true },
                { label: 'Nama Institusi/Perusahaan', name: 'nama_institusi', type: 'text', required: true }
            ]
        };

        // Form validation
        function validateForm() {
            const form = document.getElementById('ticketForm');
            let isValid = true;

            // Reset previous validation
            form.classList.remove('was-validated');

            // Validate NPM format
            const npmInput = document.getElementById('npm');
            const npmPattern = /^\d{10}$/;
            if (!npmPattern.test(npmInput.value)) {
                npmInput.setCustomValidity('NPM harus berupa 10 digit angka');
                isValid = false;
            } else {
                npmInput.setCustomValidity('');
            }

            // Validate phone number
            const waInput = document.getElementById('noWa');
            const phonePattern = /^08\d{8,11}$/;
            if (!phonePattern.test(waInput.value)) {
                waInput.setCustomValidity('Nomor WhatsApp harus dimulai dengan 08 dan 10-13 digit');
                isValid = false;
            } else {
                waInput.setCustomValidity('');
            }

            // Check form validity
            if (!form.checkValidity()) {
                isValid = false;
            }

            form.classList.add('was-validated');
            return isValid;
        }

        // Handle dynamic fields
        function updateDynamicFields() {
            const jenisSurat = document.getElementById('jenisSurat').value;
            const container = document.getElementById('dynamicFields');
            
            container.innerHTML = '';
            
            if (dynamicFields[jenisSurat]) {
                container.innerHTML = '<h5 class="mb-3">Informasi Tambahan</h5>';
                
                dynamicFields[jenisSurat].forEach(field => {
                    const div = document.createElement('div');
                    div.className = 'mb-3';
                    
                    let inputElement = '';
                    if (field.type === 'textarea') {
                        inputElement = `<textarea class="form-control" id="${field.name}" name="${field.name}" ${field.required ? 'required' : ''} placeholder="${field.placeholder || ''}"></textarea>`;
                    } else {
                        inputElement = `<input type="${field.type}" class="form-control" id="${field.name}" name="${field.name}" ${field.required ? 'required' : ''} placeholder="${field.placeholder || ''}">`;
                    }
                    
                    div.innerHTML = `
                        <label for="${field.name}" class="form-label">${field.label} ${field.required ? '<span class="text-danger">*</span>' : ''}</label>
                        ${inputElement}
                        <div class="invalid-feedback">${field.label} wajib diisi</div>
                    `;
                    
                    container.appendChild(div);
                });
            }
        }

        // File upload handling
        function setupFileUpload() {
            const uploadArea = document.getElementById('fileUploadArea');
            const fileInput = document.getElementById('fileInput');
            const fileList = document.getElementById('fileList');

            // Click to upload
            uploadArea.addEventListener('click', () => fileInput.click());

            // Drag and drop
            uploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadArea.classList.add('dragover');
            });

            uploadArea.addEventListener('dragleave', () => {
                uploadArea.classList.remove('dragover');
            });

            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadArea.classList.remove('dragover');
                handleFiles(e.dataTransfer.files);
            });

            fileInput.addEventListener('change', (e) => {
                handleFiles(e.target.files);
            });
        }

        function handleFiles(files) {
            Array.from(files).forEach(file => {
                if (validateFile(file)) {
                    selectedFiles.push(file);
                    displayFile(file);
                }
            });
        }

        function validateFile(file) {
            if (!allowedTypes.includes(file.type)) {
                alert(`File ${file.name} tidak didukung. Gunakan format PDF, JPG, atau PNG.`);
                return false;
            }
            
            if (file.size > maxFileSize) {
                alert(`File ${file.name} terlalu besar. Maksimal 5MB.`);
                return false;
            }
            
            return true;
        }

        function displayFile(file) {
            const fileList = document.getElementById('fileList');
            const fileItem = document.createElement('div');
            fileItem.className = 'file-item';
            
            fileItem.innerHTML = `
                <div>
                    <i class="bi bi-file-earmark me-2"></i>
                    <span>${file.name}</span>
                    <small class="text-muted ms-2">(${formatFileSize(file.size)})</small>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFile('${file.name}')">
                    <i class="bi bi-trash"></i>
                </button>
            `;
            
            fileList.appendChild(fileItem);
        }

        function removeFile(fileName) {
            selectedFiles = selectedFiles.filter(file => file.name !== fileName);
            updateFileList();
        }

        function updateFileList() {
            const fileList = document.getElementById('fileList');
            fileList.innerHTML = '';
            selectedFiles.forEach(file => displayFile(file));
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Form submission
        function showConfirmationModal() {
            const formData = new FormData(document.getElementById('ticketForm'));
            const summaryContent = document.getElementById('summaryContent');
            
            let summary = '<ul class="list-unstyled">';
            summary += `<li><strong>Nama:</strong> ${formData.get('nama')}</li>`;
            summary += `<li><strong>NPM:</strong> ${formData.get('npm')}</li>`;
            summary += `<li><strong>Program Studi:</strong> ${formData.get('prodi')}</li>`;
            summary += `<li><strong>Jenis Surat:</strong> ${formData.get('jenis_surat')}</li>`;
            summary += `<li><strong>Email:</strong> ${formData.get('email')}</li>`;
            summary += `<li><strong>WhatsApp:</strong> ${formData.get('no_wa')}</li>`;
            
            if (selectedFiles.length > 0) {
                summary += `<li><strong>Lampiran:</strong> ${selectedFiles.length} file</li>`;
            }
            
            summary += '</ul>';
            summaryContent.innerHTML = summary;
            
            new bootstrap.Modal(document.getElementById('confirmationModal')).show();
        }

        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            setupFileUpload();
            
            document.getElementById('jenisSurat').addEventListener('change', updateDynamicFields);
            
            document.getElementById('ticketForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (validateForm()) {
                    showConfirmationModal();
                }
            });
            
            document.getElementById('confirmSubmit').addEventListener('click', function() {
                // Here you would normally submit to the API
                // For now, we'll show a success message
                const ticketNumber = 'TCK-' + new Date().getFullYear() + 
                                   String(new Date().getMonth() + 1).padStart(2, '0') + 
                                   String(new Date().getDate()).padStart(2, '0') + '-' + 
                                   String(Math.floor(Math.random() * 9999) + 1).padStart(4, '0');
                                   
                alert(`Pengajuan berhasil dikirim!\n\nNomor tiket: ${ticketNumber}\n\nAnda akan menerima notifikasi melalui email dan WhatsApp.`);
                
                // Reset form
                document.getElementById('ticketForm').reset();
                selectedFiles = [];
                updateFileList();
                document.getElementById('dynamicFields').innerHTML = '';
                document.getElementById('ticketForm').classList.remove('was-validated');
                
                bootstrap.Modal.getInstance(document.getElementById('confirmationModal')).hide();
                
                // Redirect to success page after a moment
                setTimeout(() => {
                    window.location.href = 'index.php?page=success&ticket=' + ticketNumber;
                }, 2000);
            });

            // Debug info if in development mode
            if (APP_CONFIG.debugMode) {
                console.log('App Config:', APP_CONFIG);
                console.log(`${APP_CONFIG.name} v${APP_CONFIG.version} - Form ready`);
            }
        });
    </script>
</body>
</html>