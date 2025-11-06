<?php
/**
 * Export Tickets API Controller
 * Provides export functionality for tickets in Excel and PDF formats
 */

session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Include required files
require_once '../config/app.php';
require_once '../../model/Ticket.php';

// Get parameters
$format = $_GET['format'] ?? 'csv';
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$type = $_GET['type'] ?? '';
$date = $_GET['date'] ?? '';

try {
    $ticketModel = new Ticket();
    
    // Build filters for data retrieval
    $filters = [];
    $searchQuery = '';
    $params = [];
    
    if (!empty($search)) {
        $searchQuery .= " AND (t.ticket_code LIKE ? OR t.nama LIKE ? OR t.email LIKE ?)";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
    }
    
    if (!empty($status)) {
        $searchQuery .= " AND t.status = ?";
        $params[] = $status;
    }
    
    if (!empty($type)) {
        $searchQuery .= " AND t.jenis_surat = ?";
        $params[] = $type;
    }
    
    if (!empty($date)) {
        $searchQuery .= " AND DATE(t.created_at) = ?";
        $params[] = $date;
    }
    
    // Get filtered tickets data
    $sql = "
        SELECT 
            t.id,
            t.ticket_code,
            t.nama,
            t.npm,
            t.prodi,
            t.jenis_surat,
            t.email,
            t.wa,
            t.status,
            t.created_at,
            t.updated_at
        FROM tickets t
        WHERE 1=1 {$searchQuery}
        ORDER BY t.created_at DESC
    ";
    
    $stmt = $ticketModel->pdo->prepare($sql);
    $stmt->execute($params);
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($tickets)) {
        http_response_code(404);
        echo json_encode(['error' => 'No data found to export']);
        exit;
    }
    
    // Generate export based on format
    if ($format === 'csv') {
        exportToCSV($tickets);
    } elseif ($format === 'pdf') {
        exportToPrintPDF($tickets);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid export format. Use "csv" or "pdf"']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Export failed: ' . $e->getMessage()]);
}

/**
 * Export tickets to CSV format
 */
function exportToCSV($tickets) {
    // Set headers for CSV file download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="tickets-export-' . date('Y-m-d-H-i-s') . '.csv"');
    header('Cache-Control: max-age=0');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8 support in Excel
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // CSV Headers
    fputcsv($output, [
        'No',
        'Nomor Ticket',
        'Nama Lengkap',
        'NPM',
        'Program Studi',
        'Jenis Surat',
        'Email',
        'WhatsApp',
        'Status',
        'Tanggal Dibuat',
        'Terakhir Diupdate'
    ]);
    
    // Data rows
    $no = 1;
    foreach ($tickets as $ticket) {
        fputcsv($output, [
            $no++,
            $ticket['ticket_code'],
            $ticket['nama'],
            $ticket['npm'] ?? '-',
            $ticket['prodi'] ?? '-',
            getLetterTypeName($ticket['jenis_surat']),
            $ticket['email'] ?? '-',
            $ticket['wa'] ?? '-',
            getStatusName($ticket['status']),
            date('d/m/Y H:i', strtotime($ticket['created_at'])),
            date('d/m/Y H:i', strtotime($ticket['updated_at']))
        ]);
    }
    
    fclose($output);
}

/**
 * Export tickets to printable PDF format
 */
function exportToPrintPDF($tickets) {
    // Set headers for HTML output that can be printed as PDF
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: max-age=0');
    
    ob_start();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Laporan Tickets - <?= APP_NAME ?></title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                font-size: 12px;
                margin: 20px;
            }
            .header {
                text-align: center;
                margin-bottom: 30px;
                border-bottom: 2px solid #333;
                padding-bottom: 15px;
            }
            .header h1 {
                color: #333;
                margin: 0;
                font-size: 24px;
            }
            .header p {
                color: #666;
                margin: 5px 0 0 0;
            }
            .info-box {
                background-color: #f8f9fa;
                padding: 10px;
                border-radius: 5px;
                margin-bottom: 20px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }
            th, td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
                font-size: 10px;
            }
            th {
                background-color: #4472C4;
                color: white;
                font-weight: bold;
            }
            tr:nth-child(even) {
                background-color: #f9f9f9;
            }
            .status-submitted { color: #17a2b8; }
            .status-in_review { color: #ffc107; }
            .status-valid { color: #28a745; }
            .status-rejected { color: #dc3545; }
            .status-generated { color: #007bff; }
            .footer {
                margin-top: 30px;
                text-align: center;
                font-size: 10px;
                color: #666;
                border-top: 1px solid #ddd;
                padding-top: 10px;
            }
            @media print {
                body { 
                    margin: 0; 
                    font-size: 10px;
                }
                .no-print { display: none; }
                .header {
                    margin-bottom: 20px;
                    border-bottom: 1px solid #333;
                    padding-bottom: 10px;
                }
                .header h1 {
                    font-size: 18px;
                }
                .info-box {
                    margin-bottom: 15px;
                    page-break-inside: avoid;
                }
                table {
                    page-break-inside: auto;
                }
                tr {
                    page-break-inside: avoid;
                    page-break-after: auto;
                }
                th, td {
                    padding: 4px;
                    font-size: 8px;
                }
                .footer {
                    margin-top: 15px;
                    font-size: 8px;
                }
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>Laporan Tickets</h1>
            <p><?= APP_DESCRIPTION ?></p>
        </div>
        
        <div class="info-box">
            <strong>Informasi Laporan:</strong><br>
            Tanggal Generate: <?php echo date('d/m/Y H:i:s'); ?><br>
            Total Data: <?php echo count($tickets); ?> tickets<br>
            <?php if (!empty($_GET['status'])): ?>
            Filter Status: <?php echo getStatusName($_GET['status']); ?><br>
            <?php endif; ?>
            <?php if (!empty($_GET['type'])): ?>
            Filter Jenis: <?php echo getLetterTypeName($_GET['type']); ?><br>
            <?php endif; ?>
            <?php if (!empty($_GET['date'])): ?>
            Filter Tanggal: <?php echo date('d/m/Y', strtotime($_GET['date'])); ?><br>
            <?php endif; ?>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th style="width: 30px;">No</th>
                    <th style="width: 100px;">Nomor Ticket</th>
                    <th>Nama Lengkap</th>
                    <th style="width: 80px;">NPM</th>
                    <th style="width: 80px;">Prodi</th>
                    <th style="width: 100px;">Jenis Surat</th>
                    <th style="width: 60px;">Status</th>
                    <th style="width: 80px;">Tanggal</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; ?>
                <?php foreach ($tickets as $ticket): ?>
                <tr>
                    <td style="text-align: center;"><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($ticket['ticket_code']); ?></td>
                    <td><?php echo htmlspecialchars($ticket['nama']); ?></td>
                    <td><?php echo htmlspecialchars($ticket['npm'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($ticket['prodi'] ?? '-'); ?></td>
                    <td><?php echo getLetterTypeName($ticket['jenis_surat']); ?></td>
                    <td class="status-<?php echo $ticket['status']; ?>">
                        <?php echo getStatusName($ticket['status']); ?>
                    </td>
                    <td><?php echo date('d/m/Y', strtotime($ticket['created_at'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="footer">
            <p>Dokumen ini digenerate secara otomatis oleh sistem <?= APP_NAME ?> pada <?php echo date('d/m/Y H:i:s'); ?></p>
            <p>© <?php echo date('Y'); ?> <?= APP_NAME ?> - <?= APP_DESCRIPTION ?><br/>Developed by <?= APP_DEV; ?></p>
        </div>
        
        <script>
            // Auto print dialog when page loads
            window.onload = function() {
                setTimeout(function() {
                    window.print();
                    // Close window after printing (optional)
                    window.onafterprint = function() {
                        setTimeout(function() {
                            window.close();
                        }, 1000);
                    };
                }, 500);
            };
        </script>
    </body>
    </html>
    <?php
    
    $html = ob_get_clean();
    echo $html;
}

/**
 * Helper function to get letter type name
 */
function getLetterTypeName($type) {
    $types = [
        'surat_keterangan' => 'Surat Keterangan',
        'surat_pengantar' => 'Surat Pengantar',
        'surat_domisili' => 'Surat Domisili',
        'surat_usaha' => 'Surat Usaha',
        'surat_tidak_mampu' => 'Surat Tidak Mampu'
    ];
    return $types[$type] ?? $type;
}

/**
 * Helper function to get status name
 */
function getStatusName($status) {
    $statuses = [
        'submitted' => 'Submitted',
        'in_review' => 'In Review',
        'valid' => 'Valid',
        'rejected' => 'Rejected',
        'generated' => 'Generated'
    ];
    return $statuses[$status] ?? $status;
}
?>