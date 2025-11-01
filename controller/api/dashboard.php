<?php
/**
 * Dashboard API Controller
 * Provides statistics and activity data for admin dashboard
 */

session_start();

header('Content-Type: application/json');

// Include app configuration
require_once '../config/app.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Include required files
require_once '../../model/Ticket.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $ticketModel = new Ticket();
    
    switch ($method) {
        case 'GET':
            $action = $_GET['action'] ?? 'stats';
            
            switch ($action) {
                case 'stats':
                    // Get dashboard statistics
                    $stats = $ticketModel->getStatistics();
                    
                    echo json_encode([
                        'success' => true,
                        'data' => [
                            'total_tickets' => $stats['total'],
                            'pending_tickets' => $stats['in_review'] + $stats['submitted'],
                            'completed_tickets' => $stats['generated'],
                            'weekly_tickets' => $stats['this_week'],
                            'today_new' => $stats['today'],
                            'by_status' => $stats['by_status'],
                            'breakdown' => [
                                'submitted' => $stats['submitted'],
                                'in_review' => $stats['in_review'],
                                'valid' => $stats['valid'],
                                'rejected' => $stats['rejected'],
                                'generated' => $stats['generated']
                            ]
                        ]
                    ]);
                    break;
                    
                case 'activity':
                    // Get recent activity with pagination
                    $page = intval($_GET['page'] ?? 1);
                    $limit = intval($_GET['limit'] ?? 10);
                    $statusFilter = $_GET['status'] ?? '';
                    
                    // Build filters
                    $filters = [];
                    if (!empty($statusFilter)) {
                        $filters['status'] = $statusFilter;
                    }
                    
                    // Get paginated tickets
                    $result = $ticketModel->getAll($filters, $page, $limit);
                    
                    if ($result['success']) {
                        $formattedActivities = [];
                        foreach ($result['data'] as $ticket) {
                            $timeAgo = timeAgo($ticket['updated_at']);
                            $statusInfo = getStatusInfo($ticket['status']);
                            
                            $formattedActivities[] = [
                                'id' => $ticket['ticket_code'],
                                'title' => getActivityTitle($ticket),
                                'description' => "{$ticket['jenis_surat']} - {$ticket['nama']}",
                                'time' => $timeAgo,
                                'icon' => $statusInfo['icon'],
                                'color' => $statusInfo['color'],
                                'status' => $ticket['status'],
                                'admin_note' => $ticket['admin_note'],
                                'created_at' => $ticket['created_at'],
                                'updated_at' => $ticket['updated_at']
                            ];
                        }
                        
                        echo json_encode([
                            'success' => true,
                            'data' => $formattedActivities,
                            'pagination' => [
                                'current_page' => $result['page'],
                                'per_page' => $result['limit'],
                                'total' => $result['total'],
                                'last_page' => $result['total_pages']
                            ]
                        ]);
                    } else {
                        echo json_encode(['success' => false, 'error' => $result['error']]);
                    }
                    break;
                    
                case 'today_summary':
                    // Get today's activity summary
                    $today = date('Y-m-d');
                    
                    // Get all tickets created today
                    $filters = ['date_from' => $today, 'date_to' => $today];
                    $todayTickets = $ticketModel->getAll($filters, 1, 1000); // Increase limit
                    
                    $summary = [
                        'new_tickets' => 0,
                        'approved' => 0,
                        'generated' => 0,
                        'rejected' => 0
                    ];
                    
                    // Count new tickets created today
                    $newToday = $ticketModel->getAll(['date_from' => $today, 'date_to' => $today], 1, 1000);
                    $summary['new_tickets'] = count($newToday['data']);
                    
                    // Get tickets updated today (status changes)
                    $stmt = $ticketModel->pdo->prepare("
                        SELECT status, COUNT(*) as count 
                        FROM tickets 
                        WHERE DATE(updated_at) = ? AND DATE(created_at) != DATE(updated_at)
                        GROUP BY status
                    ");
                    $stmt->execute([$today]);
                    $updatedToday = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
                    
                    $summary['approved'] = $updatedToday['valid'] ?? 0;
                    $summary['generated'] = $updatedToday['generated'] ?? 0;
                    $summary['rejected'] = $updatedToday['rejected'] ?? 0;
                    
                    echo json_encode([
                        'success' => true,
                        'data' => $summary,
                        'debug' => [
                            'today' => $today,
                            'total_today_tickets' => count($todayTickets['data']),
                            'updated_counts' => $updatedToday
                        ]
                    ]);
                    break;
                    
                case 'export_activity':
                    // Export activity data as CSV
                    $statusFilter = $_GET['status'] ?? '';
                    $format = $_GET['format'] ?? 'csv';
                    
                    $filters = [];
                    if (!empty($statusFilter)) {
                        $filters['status'] = $statusFilter;
                    }
                    
                    // Get all activities (no pagination for export)
                    $result = $ticketModel->getAll($filters, 1, 1000);
                    
                    if ($result['success'] && $format === 'csv') {
                        header('Content-Type: text/csv');
                        header('Content-Disposition: attachment; filename="activity_export_' . date('Y-m-d') . '.csv"');
                        
                        $output = fopen('php://output', 'w');
                        
                        // CSV headers
                        fputcsv($output, [
                            'Ticket Code',
                            'Name',
                            'Letter Type',
                            'Status',
                            'Admin Note',
                            'Created Date',
                            'Updated Date'
                        ]);
                        
                        // CSV data
                        foreach ($result['data'] as $ticket) {
                            fputcsv($output, [
                                $ticket['ticket_code'],
                                $ticket['nama'],
                                $ticket['jenis_surat'],
                                $ticket['status'],
                                $ticket['admin_note'] ?? '',
                                $ticket['created_at'],
                                $ticket['updated_at']
                            ]);
                        }
                        
                        fclose($output);
                        exit;
                    } else {
                        echo json_encode(['success' => false, 'error' => 'Export failed']);
                    }
                    break;

                default:
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid action']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
}

/**
 * Helper function to calculate time ago using app timezone
 * Uses the getTimeAgo() function from app.php
 */
function timeAgo($datetime) {
    return getTimeAgo($datetime);
}

/**
 * Helper function to get status information
 */
function getStatusInfo($status) {
    $statusMap = [
        'submitted' => ['icon' => 'bi-plus-circle', 'color' => 'primary'],
        'in_review' => ['icon' => 'bi-eye', 'color' => 'warning'],
        'valid' => ['icon' => 'bi-check-circle', 'color' => 'success'],
        'rejected' => ['icon' => 'bi-x-circle', 'color' => 'danger'],
        'generated' => ['icon' => 'bi-file-check', 'color' => 'success']
    ];
    
    return $statusMap[$status] ?? ['icon' => 'bi-circle', 'color' => 'secondary'];
}

/**
 * Helper function to get activity title
 */
function getActivityTitle($activity) {
    $isNewTicket = strtotime($activity['created_at']) === strtotime($activity['updated_at']);
    
    switch ($activity['status']) {
        case 'submitted':
            return $isNewTicket ? "New ticket submitted" : "Ticket resubmitted";
        case 'in_review':
            return "Ticket under review";
        case 'valid':
            return "Ticket approved";
        case 'rejected':
            return "Ticket rejected";
        case 'generated':
            return "Letter generated for {$activity['ticket_code']}";
        default:
            return "Ticket updated";
    }
}
?>
