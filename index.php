<?php

/**
 * SURATIN - Sistem Urus Surat Terintegrasi
 * Entry Point / Landing Page
 * 
 * @author SURATIN Development Team
 * @version 1.0
 */

// Include app configuration
require_once __DIR__ . '/controller/config/app.php';

// Start session
session_start();

// Basic configuration using constants from app.php
$config = [
    'app_name' => APP_NAME,
    'app_description' => APP_DESCRIPTION,
    'version' => APP_VERSION,
    'base_url' => '/SURATIN',
    'admin_path' => '/admin',
];

// Simple routing
$page = $_GET['page'] ?? 'home';
$action = $_GET['action'] ?? null;

// Handle basic routing
switch ($page) {
    case 'ticket':
        include 'view/pages/ticket-form.html';
        exit;
    case 'status':
        include 'view/pages/check-status.html';
        exit;
    case 'success':
        include 'view/pages/success.html';
        exit;
    case 'admin':
        if ($action === 'login') {
            include 'view/pages/admin-login.html';
        } elseif ($action === 'logout') {
            // Handle logout
            session_unset();
            session_destroy();
            header('Location: ?page=home');
        } else {
            // Check if admin is logged in (basic session check)
            if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
                include 'view/pages/admin-dashboard.html';
            } else {
                header('Location: ?page=admin&action=login');
            }
        }
        exit;
    case 'home':
    default:
        // Show landing page
        break;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - <?= APP_DESCRIPTION ?></title>

    <!-- Bootstrap CSS Local -->
    <link href="view/assets/bootstrap-5.3.8/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons Local -->
    <link href="view/assets/bootstrap-icons-1.13.1/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #64748b;
            --success-color: #16a34a;
            --warning-color: #d97706;
            --danger-color: #dc2626;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .hero-section {
            padding: 80px 0;
            color: white;
        }

        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
        }

        .btn-outline-light {
            border: 2px solid white;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
        }

        .stats-section {
            background: white;
            border-radius: 15px;
            padding: 40px;
            margin: 60px 0;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .stat-item {
            text-align: center;
            padding: 20px;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .footer {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            padding: 40px 0;
            margin-top: 60px;
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: rgba(255,255,255,0.1);">
        <div class="container">
            <a class="navbar-brand fw-bold fs-4" href="?page=home">
                <i class="bi bi-envelope-check me-2"></i>
                <?= APP_NAME ?>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="?page=home">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?page=ticket">Buat Surat</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?page=status">Cek Status</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?page=admin&action=login">Admin</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">
                        <?= APP_DESCRIPTION ?>
                    </h1>
                    <p class="lead mb-4">
                        Platform digital yang memudahkan proses pengurusan surat-menyurat secara online.
                        Cepat, efisien, dan terintegrasi untuk kebutuhan administrasi modern.
                    </p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="?page=ticket" class="btn btn-primary btn-lg">
                            <i class="bi bi-plus-circle me-2"></i>
                            Buat Surat Baru
                        </a>
                        <a href="?page=status" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-search me-2"></i>
                            Cek Status Surat
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <i class="bi bi-file-earmark-text" style="font-size: 15rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="bi bi-lightning-charge text-white fs-3"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Proses Cepat</h4>
                        <p class="text-muted">
                            Pengajuan surat dapat diproses dengan cepat melalui sistem digital yang terintegrasi.
                        </p>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="bi bi-shield-check text-white fs-3"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Aman & Terpercaya</h4>
                        <p class="text-muted">
                            Data Anda aman dengan sistem keamanan berlapis dan backup otomatis.
                        </p>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="bi bi-graph-up text-white fs-3"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Tracking Real-time</h4>
                        <p class="text-muted">
                            Pantau progress pengajuan surat Anda secara real-time kapan saja.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="container">
        <div class="stats-section">
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="stat-item">
                        <div class="stat-number">150+</div>
                        <div class="text-muted">Surat Diproses</div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="stat-item">
                        <div class="stat-number">98%</div>
                        <div class="text-muted">Tingkat Kepuasan</div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="stat-item">
                        <div class="stat-number">24/7</div>
                        <div class="text-muted">Layanan Online</div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="stat-item">
                        <div class="stat-number">5 Menit</div>
                        <div class="text-muted">Rata-rata Proses</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center text-white">
                    <h2 class="fw-bold mb-4">Siap Untuk Memulai?</h2>
                    <p class="lead mb-4">
                        Bergabunglah dengan ribuan pengguna yang telah merasakan kemudahan pengurusan surat digital.
                    </p>
                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        <a href="?page=ticket" class="btn btn-light btn-lg">
                            <i class="bi bi-plus-circle me-2"></i>
                            Buat Surat Sekarang
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <h5 class="fw-bold mb-3">
                        <i class="bi bi-envelope-check me-2"></i>
                        <?= APP_NAME ?>
                    </h5>
                    <p class="mb-3">
                        <?= APP_DESCRIPTION ?>
                    </p>
                    <p class="small">
                        Version <?= APP_VERSION ?> &copy; <?= date('Y') ?> <?= APP_DEV ?>
                    </p>
                </div>
                <div class="col-lg-3">
                    <h6 class="fw-bold mb-3">Menu Cepat</h6>
                    <ul class="list-unstyled">
                        <li><a href="?page=ticket" class="text-white-50 text-decoration-none">Buat Surat</a></li>
                        <li><a href="?page=status" class="text-white-50 text-decoration-none">Cek Status</a></li>
                        <li><a href="?page=admin" class="text-white-50 text-decoration-none">Admin Panel</a></li>
                    </ul>
                </div>
                <div class="col-lg-3">
                    <h6 class="fw-bold mb-3">Bantuan</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white-50 text-decoration-none">Panduan Penggunaan</a></li>
                        <li><a href="#" class="text-white-50 text-decoration-none">Kontak Support</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Local -->
    <script src="view/assets/bootstrap-5.3.8/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
        // Simple page tracking
        document.addEventListener('DOMContentLoaded', function() {
            console.log('<?= APP_NAME ?> v<?= APP_VERSION ?> - Landing Page Loaded');

            // Add smooth scrolling to anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Add loading states to buttons
            document.querySelectorAll('.btn').forEach(button => {
                button.addEventListener('click', function() {
                    if (this.href && !this.href.includes('#')) {
                        this.innerHTML = '<i class="bi bi-spinner spinner-border spinner-border-sm me-2"></i> Memuat...';
                    }
                });
            });
        });
    </script>
</body>

</html>