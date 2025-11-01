<?php
require_once __DIR__ . '/../../controller/config/app.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Status Pengajuan - <?= APP_NAME; ?></title>
    <meta name="description" content="Cek status pengajuan surat melalui sistem <?= APP_NAME; ?> dengan memasukkan nomor ticket atau email">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #0d6efd 0%, #0056b3 100%);
            color: white;
            padding: 4rem 0;
        }
        .search-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 2rem rgba(0, 0, 0, 0.1);
            margin-top: -2rem;
            position: relative;
            z-index: 10;
        }
        .status-card {
            border-radius: 1rem;
            box-shadow: 0 0.25rem 1rem rgba(0, 0, 0, 0.1);
            border: none;
        }
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-weight: 600;
        }
        .timeline-item {
            padding: 1rem 0;
            border-left: 2px solid #e9ecef;
            margin-left: 1rem;
            padding-left: 2rem;
            position: relative;
        }
        .timeline-item::before {
            content: '';
            width: 12px;
            height: 12px;
            background: #e9ecef;
            border-radius: 50%;
            position: absolute;
            left: -7px;
            top: 1.5rem;
        }
        .timeline-item.completed::before {
            background: #198754;
        }
        .timeline-item.active::before {
            background: #ffc107;
        }
        .timeline-item.active {
            border-left-color: #ffc107;
        }
        .timeline-item.completed {
            border-left-color: #198754;
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

    <!-- Hero Section -->
    <header class="hero-section">
        <div class="container">
            <div class="row justify-content-center text-center">
                <div class="col-lg-8">
                    <h1 class="display-5 fw-bold mb-3">Cek Status Pengajuan</h1>
                    <p class="lead mb-0">Lacak status pengajuan surat Anda</p>
                    <p class="mb-0">Gunakan nomor tiket atau email untuk melihat progress pengajuan</p>
                </div>
            </div>
        </div>
    </header>

    <!-- Search Form -->
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="search-card p-4 p-lg-5 mb-5">
                    <form id="statusForm" novalidate>
                        <div class="text-center mb-4">
                            <h2 class="h3 mb-2">Cek Status Pengajuan</h2>
                            <p class="text-muted">Masukkan nomor tiket atau email yang digunakan saat pengajuan</p>
                        </div>

                        <!-- Search Options -->
                        <div class="mb-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="searchType" id="searchByTicket" value="ticket" checked>
                                        <label class="form-check-label" for="searchByTicket">
                                            <i class="bi bi-ticket-perforated me-2"></i>Nomor Tiket
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="searchType" id="searchByEmail" value="email">
                                        <label class="form-check-label" for="searchByEmail">
                                            <i class="bi bi-envelope me-2"></i>Email
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ticket Number Input -->
                        <div id="ticketInput" class="mb-3">
                            <label for="ticketNumber" class="form-label">Nomor Tiket</label>
                            <input type="text" class="form-control form-control-lg" id="ticketNumber" 
                                   placeholder="Contoh: TCK-20241029-0001" pattern="TCK-\d{8}-\d{4}">
                            <div class="form-text">Format: TCK-YYYYMMDD-XXXX</div>
                            <div class="invalid-feedback">Format nomor tiket tidak valid</div>
                        </div>

                        <!-- Email Input -->
                        <div id="emailInput" class="mb-3 d-none">
                            <label for="emailAddress" class="form-label">Email</label>
                            <input type="email" class="form-control form-control-lg" id="emailAddress" 
                                   placeholder="contoh@email.com">
                            <div class="form-text">Email yang digunakan saat pengajuan</div>
                            <div class="invalid-feedback">Email tidak valid</div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-search me-2"></i>Cek Status
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Status Result -->
        <div id="statusResult" class="row justify-content-center d-none">
            <div class="col-lg-8">
                <div class="status-card card mb-5">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Detail Pengajuan</h4>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <h5 id="resultTicketNumber" class="mb-2"></h5>
                                <p id="resultSubmissionDate" class="text-muted mb-1"></p>
                                <p id="resultSubmitterName" class="mb-0"></p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <span id="resultStatus" class="status-badge"></span>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong>Jenis Surat:</strong>
                                <span id="resultLetterType"></span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Program Studi:</strong>
                                <span id="resultStudyProgram"></span>
                            </div>
                        </div>

                        <!-- Status Timeline -->
                        <h6 class="mt-4 mb-3">Progress Pengajuan</h6>
                        <div id="statusTimeline">
                            <!-- Timeline items will be inserted here -->
                        </div>

                        <!-- Actions -->
                        <div class="row g-3 mt-4">
                            <div class="col-md-6">
                                <a href="index.php?page=ticket" class="btn btn-outline-primary w-100">
                                    <i class="bi bi-plus-circle me-2"></i>Ajukan Surat Baru
                                </a>
                            </div>
                            <div class="col-md-6">
                                <button type="button" class="btn btn-secondary w-100" onclick="resetSearch()">
                                    <i class="bi bi-arrow-left me-2"></i>Cek Tiket Lain
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- No Result -->
        <div id="noResult" class="row justify-content-center d-none">
            <div class="col-lg-6">
                <div class="text-center py-5">
                    <i class="bi bi-search text-muted" style="font-size: 4rem;"></i>
                    <h4 class="mt-3">Data Tidak Ditemukan</h4>
                    <p class="text-muted mb-4">Nomor tiket atau email tidak ditemukan dalam sistem</p>
                    <button type="button" class="btn btn-primary" onclick="resetSearch()">
                        <i class="bi bi-arrow-left me-2"></i>Coba Lagi
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h6><?= APP_NAME; ?></h6>
                    <p class="mb-0"><?= APP_DESCRIPTION; ?></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <small>
                        © <?= date('Y'); ?> <?= APP_NAME; ?>. All rights reserved.
                        <?php if (defined('APP_DEV') && APP_DEV): ?>
                            <br>Developed by <?= APP_DEV; ?>
                        <?php endif; ?>
                    </small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Configuration from PHP
        const APP_CONFIG = {
            name: '<?= APP_NAME; ?>',
            description: '<?= APP_DESCRIPTION; ?>',
            version: '<?= APP_VERSION; ?>',
            dev: '<?= APP_DEV; ?>',
            debugMode: <?= DEBUG_MODE ? 'true' : 'false'; ?>
        };

        // Mock data for demonstration
        const mockTickets = {
            'TCK-20241029-0001': {
                ticketNumber: 'TCK-20241029-0001',
                submissionDate: '29 Oktober 2024, 14:30',
                submitterName: 'Ahmad Nur Fauzi',
                letterType: 'Surat Keterangan Aktif Kuliah',
                studyProgram: 'Teknik Informatika',
                status: 'processing',
                statusText: 'Sedang Diproses',
                timeline: [
                    { step: 'Pengajuan Diterima', date: '29 Okt 2024, 14:30', status: 'completed', description: 'Pengajuan berhasil masuk ke sistem' },
                    { step: 'Verifikasi Data', date: '30 Okt 2024, 09:15', status: 'completed', description: 'Data dan dokumen telah diverifikasi' },
                    { step: 'Sedang Diproses', date: '31 Okt 2024, 10:00', status: 'active', description: 'Surat sedang diproses oleh bagian akademik' },
                    { step: 'Siap Diambil', date: '', status: 'pending', description: 'Surat siap untuk diambil' }
                ]
            },
            'TCK-20241028-0002': {
                ticketNumber: 'TCK-20241028-0002',
                submissionDate: '28 Oktober 2024, 10:15',
                submitterName: 'Siti Nurhaliza',
                letterType: 'Surat Pengantar Magang',
                studyProgram: 'Sistem Informasi',
                status: 'ready',
                statusText: 'Siap Diambil',
                timeline: [
                    { step: 'Pengajuan Diterima', date: '28 Okt 2024, 10:15', status: 'completed', description: 'Pengajuan berhasil masuk ke sistem' },
                    { step: 'Verifikasi Data', date: '28 Okt 2024, 15:30', status: 'completed', description: 'Data dan dokumen telah diverifikasi' },
                    { step: 'Sedang Diproses', date: '29 Okt 2024, 08:45', status: 'completed', description: 'Surat telah selesai diproses' },
                    { step: 'Siap Diambil', date: '29 Okt 2024, 16:20', status: 'completed', description: 'Surat siap untuk diambil di bagian akademik' }
                ]
            }
        };

        // Email to ticket mapping for demo
        const emailToTicket = {
            'ahmad.fauzi@email.com': 'TCK-20241029-0001',
            'siti.nurhaliza@email.com': 'TCK-20241028-0002'
        };

        // Toggle search input fields
        function toggleSearchInput() {
            const searchType = document.querySelector('input[name="searchType"]:checked').value;
            const ticketInput = document.getElementById('ticketInput');
            const emailInput = document.getElementById('emailInput');

            if (searchType === 'ticket') {
                ticketInput.classList.remove('d-none');
                emailInput.classList.add('d-none');
                document.getElementById('ticketNumber').required = true;
                document.getElementById('emailAddress').required = false;
            } else {
                ticketInput.classList.add('d-none');
                emailInput.classList.remove('d-none');
                document.getElementById('ticketNumber').required = false;
                document.getElementById('emailAddress').required = true;
            }
        }

        // Validate form
        function validateForm() {
            const form = document.getElementById('statusForm');
            const searchType = document.querySelector('input[name="searchType"]:checked').value;
            
            let isValid = true;

            if (searchType === 'ticket') {
                const ticketNumber = document.getElementById('ticketNumber').value;
                const ticketPattern = /^TCK-\d{8}-\d{4}$/;
                
                if (!ticketPattern.test(ticketNumber)) {
                    document.getElementById('ticketNumber').setCustomValidity('Format nomor tiket tidak valid');
                    isValid = false;
                } else {
                    document.getElementById('ticketNumber').setCustomValidity('');
                }
            } else {
                const email = document.getElementById('emailAddress').value;
                if (!email || !/\S+@\S+\.\S+/.test(email)) {
                    document.getElementById('emailAddress').setCustomValidity('Email tidak valid');
                    isValid = false;
                } else {
                    document.getElementById('emailAddress').setCustomValidity('');
                }
            }

            form.classList.add('was-validated');
            return isValid;
        }

        // Display status result
        function displayResult(ticket) {
            const statusColors = {
                'submitted': 'bg-info text-white',
                'processing': 'bg-warning text-dark',
                'ready': 'bg-success text-white',
                'completed': 'bg-success text-white',
                'rejected': 'bg-danger text-white'
            };

            // Populate result fields
            document.getElementById('resultTicketNumber').textContent = ticket.ticketNumber;
            document.getElementById('resultSubmissionDate').textContent = `Diajukan: ${ticket.submissionDate}`;
            document.getElementById('resultSubmitterName').textContent = `Pengaju: ${ticket.submitterName}`;
            document.getElementById('resultLetterType').textContent = ticket.letterType;
            document.getElementById('resultStudyProgram').textContent = ticket.studyProgram;
            
            const statusBadge = document.getElementById('resultStatus');
            statusBadge.textContent = ticket.statusText;
            statusBadge.className = `status-badge ${statusColors[ticket.status] || 'bg-secondary text-white'}`;

            // Build timeline
            const timeline = document.getElementById('statusTimeline');
            timeline.innerHTML = '';

            ticket.timeline.forEach(item => {
                const timelineItem = document.createElement('div');
                timelineItem.className = `timeline-item ${item.status}`;
                
                timelineItem.innerHTML = `
                    <h6 class="mb-1">${item.step}</h6>
                    <p class="text-muted mb-1 small">${item.date || 'Menunggu'}</p>
                    <p class="mb-0 small">${item.description}</p>
                `;
                
                timeline.appendChild(timelineItem);
            });

            // Show result and hide form
            document.getElementById('statusResult').classList.remove('d-none');
            document.querySelector('.search-card').classList.add('d-none');
            document.getElementById('noResult').classList.add('d-none');
        }

        // Show no result message
        function showNoResult() {
            document.getElementById('noResult').classList.remove('d-none');
            document.querySelector('.search-card').classList.add('d-none');
            document.getElementById('statusResult').classList.add('d-none');
        }

        // Reset search
        function resetSearch() {
            document.getElementById('statusResult').classList.add('d-none');
            document.getElementById('noResult').classList.add('d-none');
            document.querySelector('.search-card').classList.remove('d-none');
            document.getElementById('statusForm').reset();
            document.getElementById('statusForm').classList.remove('was-validated');
            toggleSearchInput();
        }

        // Handle form submission
        function handleFormSubmit(e) {
            e.preventDefault();

            if (!validateForm()) {
                return;
            }

            const searchType = document.querySelector('input[name="searchType"]:checked').value;
            let searchValue;
            let ticketNumber;

            if (searchType === 'ticket') {
                ticketNumber = document.getElementById('ticketNumber').value.toUpperCase();
            } else {
                const email = document.getElementById('emailAddress').value.toLowerCase();
                ticketNumber = emailToTicket[email];
            }

            // Simulate API call delay
            const submitButton = document.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mencari...';
            submitButton.disabled = true;

            setTimeout(() => {
                submitButton.innerHTML = originalText;
                submitButton.disabled = false;

                if (ticketNumber && mockTickets[ticketNumber]) {
                    displayResult(mockTickets[ticketNumber]);
                } else {
                    showNoResult();
                }
            }, 1500);
        }

        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Radio button change
            document.querySelectorAll('input[name="searchType"]').forEach(radio => {
                radio.addEventListener('change', toggleSearchInput);
            });

            // Form submission
            document.getElementById('statusForm').addEventListener('submit', handleFormSubmit);

            // Initialize form state
            toggleSearchInput();

            // Check if ticket is passed via URL parameter
            const urlParams = new URLSearchParams(window.location.search);
            const ticketParam = urlParams.get('ticket');
            if (ticketParam) {
                document.getElementById('ticketNumber').value = ticketParam;
                // Auto-submit if valid ticket format
                if (/^TCK-\d{8}-\d{4}$/.test(ticketParam)) {
                    document.getElementById('statusForm').dispatchEvent(new Event('submit'));
                }
            }

            // Check localStorage for recent tickets
            if (typeof(Storage) !== "undefined") {
                const userTickets = JSON.parse(localStorage.getItem('userTickets') || '[]');
                if (userTickets.length > 0 && !ticketParam) {
                    // Optionally pre-fill with most recent ticket
                    const mostRecent = userTickets[userTickets.length - 1];
                    document.getElementById('ticketNumber').value = mostRecent.code;
                }
            }

            // Debug info if in development mode
            if (APP_CONFIG.debugMode) {
                console.log('App Config:', APP_CONFIG);
                console.log('Available mock tickets:', Object.keys(mockTickets));
                console.log('Email mappings:', emailToTicket);
            }
        });
    </script>
</body>
</html>