<?php
require_once __DIR__ . '/../../controller/config/app.php';

// Get ticket from URL parameter or use mock data
$ticket_code = $_GET['ticket'] ?? 'TCK-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Berhasil - <?= APP_NAME; ?></title>
    <meta name="description" content="Pengajuan surat berhasil dikirim melalui sistem <?= APP_NAME; ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .success-section {
            background: linear-gradient(135deg, #198754 0%, #146c43 100%);
            color: white;
            min-height: 60vh;
            display: flex;
            align-items: center;
            padding-bottom: 5rem;
        }
        .ticket-code {
            font-size: 2.5rem;
            font-weight: bold;
            letter-spacing: 2px;
            background: rgba(255, 255, 255, 0.2);
            padding: 1rem 2rem;
            border-radius: 1rem;
            border: 2px dashed rgba(255, 255, 255, 0.5);
        }
        .info-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 2rem rgba(0, 0, 0, 0.1);
            margin-top: -1rem;
            position: relative;
            z-index: 10;
        }
        .process-step {
            padding: 1rem;
            margin-bottom: 0.75rem;
            border-radius: 0.5rem;
            border-left: 4px solid #dee2e6;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
        }
        .process-step.active {
            border-left-color: #198754;
            background-color: #d1e7dd;
        }
        .process-step.completed {
            border-left-color: #198754;
            background-color: #d1e7dd;
        }
        .icon-success {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            font-size: 3rem;
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

    <!-- Success Section -->
    <section class="success-section">
        <div class="container">
            <div class="row justify-content-center text-center">
                <div class="col-lg-8">
                    <div class="icon-success">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <h1 class="display-5 fw-bold mb-4">Pengajuan Berhasil Dikirim!</h1>
                    <p class="lead mb-4">
                        Terima kasih telah menggunakan <?= APP_NAME; ?>. Pengajuan surat Anda telah diterima dan akan diproses segera.
                    </p>
                    <div class="ticket-code mb-4">
                        <?= htmlspecialchars($ticket_code); ?>
                    </div>
                    <p class="mb-4">
                        Simpan nomor tiket di atas untuk melacak status pengajuan Anda.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Information Card -->
    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="info-card p-4">
                    <h3 class="text-center mb-4">Proses Selanjutnya</h3>
                    
                    <!-- Process Steps -->
                    <div class="process-step completed">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-success me-3 fs-4"></i>
                            <div>
                                <h5 class="mb-1">Pengajuan Diterima</h5>
                                <p class="mb-0 text-muted">Pengajuan Anda telah masuk ke sistem dan siap diproses</p>
                            </div>
                        </div>
                    </div>

                    <div class="process-step active">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-clock-fill text-warning me-3 fs-4"></i>
                            <div>
                                <h5 class="mb-1">Dalam Proses Verifikasi</h5>
                                <p class="mb-0 text-muted">Tim admin sedang memverifikasi data dan dokumen Anda</p>
                                <small class="text-success">Estimasi: 1-2 hari kerja</small>
                            </div>
                        </div>
                    </div>

                    <div class="process-step">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-file-earmark-text me-3 fs-4 text-muted"></i>
                            <div>
                                <h5 class="mb-1">Surat Diproses</h5>
                                <p class="mb-0 text-muted">Surat sedang disiapkan oleh bagian terkait</p>
                                <small class="text-muted">Estimasi: 2-3 hari kerja</small>
                            </div>
                        </div>
                    </div>

                    <div class="process-step">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-square me-3 fs-4 text-muted"></i>
                            <div>
                                <h5 class="mb-1">Siap Diambil</h5>
                                <p class="mb-0 text-muted">Surat siap diambil atau dikirim sesuai pilihan Anda</p>
                            </div>
                        </div>
                    </div>

                    <!-- Notification Info -->
                    <div class="alert alert-info mt-4">
                        <h6><i class="bi bi-bell me-2"></i>Notifikasi</h6>
                        <p class="mb-0">
                            Anda akan menerima notifikasi melalui email dan WhatsApp setiap kali ada perubahan status pengajuan.
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row g-3 mt-3">
                        <div class="col-md-6">
                            <a href="index.php?page=status" class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-search me-2"></i>Cek Status Pengajuan
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="index.php?page=ticket" class="btn btn-outline-primary btn-lg w-100">
                                <i class="bi bi-plus-circle me-2"></i>Ajukan Surat Lain
                            </a>
                        </div>
                    </div>

                    <!-- Contact Info -->
                    <div class="text-center mt-4 pt-4 border-top">
                        <h6><?= APP_NAME; ?></h6>
                        <p class="mb-0"><?= APP_DESCRIPTION; ?></p>
                        <?php if (defined('APP_DEV') && APP_DEV): ?>
                        <small class="text-muted d-block mt-2">Dikembangkan oleh <?= APP_DEV; ?></small>
                        <?php endif; ?>
                    </div>
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

        // Copy ticket code to clipboard
        function copyTicketCode() {
            const ticketCode = '<?= htmlspecialchars($ticket_code); ?>';
            navigator.clipboard.writeText(ticketCode).then(() => {
                // Show toast notification (you can implement this)
                console.log('Ticket code copied to clipboard');
            });
        }

        // Auto-copy ticket code on page load (optional)
        document.addEventListener('DOMContentLoaded', function() {
            // Add click handler to ticket code
            document.querySelector('.ticket-code').addEventListener('click', copyTicketCode);
            
            // Add tooltip or visual indicator
            document.querySelector('.ticket-code').setAttribute('title', 'Klik untuk menyalin');
            document.querySelector('.ticket-code').style.cursor = 'pointer';

            // Debug info if in development mode
            if (APP_CONFIG.debugMode) {
                console.log('App Config:', APP_CONFIG);
                console.log('Ticket:', '<?= htmlspecialchars($ticket_code); ?>');
            }

            // Simulate real-time updates (for demo purposes)
            if (APP_CONFIG.debugMode) {
                setTimeout(() => {
                    console.log('Simulated status update notification');
                }, 5000);
            }
        });

        // Store ticket in localStorage for later reference
        if (typeof(Storage) !== "undefined") {
            const tickets = JSON.parse(localStorage.getItem('userTickets') || '[]');
            tickets.push({
                code: '<?= htmlspecialchars($ticket_code); ?>',
                date: new Date().toISOString(),
                status: 'submitted'
            });
            localStorage.setItem('userTickets', JSON.stringify(tickets));
        }
    </script>
</body>
</html>