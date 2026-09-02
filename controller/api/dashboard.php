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
require_once '../../model/TicketLog.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $ticketModel = new Ticket();
    $ticketLogModel = new TicketLog();
    
    switch ($method) {
        case 'GET':
            $action = $_GET['action'] ?? 'stats';
            
            switch ($action) {
                case 'stats':
                    // Get dashboard statistics
                    $stats = $ticketModel->getStatistics();
                    $logStats = $ticketLogModel->getStatistics();
                    
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
                            ],
                            'admin_activity' => [
                                'total_logs' => $logStats['total'],
                                'today_logs' => $logStats['today'],
                                'week_logs' => $logStats['this_week'],
                                'active_admins' => $logStats['active_admins'],
                                'by_action' => $logStats['by_action']
                            ]
                        ]
                    ]);
                    break;
                    
                case 'activity':
                    // Get recent activity with proper pagination
                    $page = intval($_GET['page'] ?? 1);
                    $limit = intval($_GET['limit'] ?? 10);
                    $statusFilter = $_GET['status'] ?? '';
                    
                    // Build filters
                    $filters = [];
                    if (!empty($statusFilter)) {
                        $filters['status'] = $statusFilter;
                    }

                    // Order by updated_at descending
                    $orderBy = 'updated_at DESC';
                    
                    // Get paginated tickets with logs
                    $result = $ticketModel->getAll($filters, $page, $limit, $orderBy);

                    if ($result['success']) {
                        $formattedActivities = [];
                        foreach ($result['data'] as $ticket) {
                            // Get latest log for this ticket to show in activity
                            $latestLog = $ticketModel->ticketLog->getLatestByTicketId($ticket['id']);
                            
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
                                'latest_note' => $latestLog['note'] ?? null,
                                'admin_name' => $latestLog['admin_name'] ?? null,
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
                    
                case 'recent_activity':
                    // Get limited recent activity for dashboard widget (no pagination)
                    $limit = intval($_GET['limit'] ?? 5);
                    
                    // Get recent activity from tickets (includes latest log info)
                    $recentActivities = $ticketModel->getRecentActivity($limit);

                    $formattedActivities = [];
                    foreach ($recentActivities as $activity) {
                        $timeAgo = timeAgo($activity['updated_at']);
                        $statusInfo = getStatusInfo($activity['status']);
                        
                        $formattedActivities[] = [
                            'id' => $activity['ticket_code'],
                            'title' => getActivityTitle($activity),
                            'description' => "{$activity['jenis_surat']} - {$activity['nama']}",
                            'time' => $timeAgo,
                            'icon' => $statusInfo['icon'],
                            'color' => $statusInfo['color'],
                            'status' => $activity['status'],
                            'latest_note' => $activity['latest_note'],
                            'admin_name' => $activity['admin_name'],
                            'created_at' => $activity['created_at'],
                            'updated_at' => $activity['updated_at']
                        ];
                    }
                    
                    echo json_encode([
                        'success' => true,
                        'data' => $formattedActivities
                    ]);
                    break;
                    
                case 'recent_logs':
                    // Get recent logs across all tickets
                    $limit = intval($_GET['limit'] ?? 10);
                    $recentLogs = $ticketLogModel->getRecentLogs($limit);
                    
                    $formattedLogs = [];
                    foreach ($recentLogs as $log) {
                        $timeAgo = timeAgo($log['created_at']);
                        $statusInfo = getStatusInfo($log['action']);
                        
                        $formattedLogs[] = [
                            'id' => $log['ticket_code'],
                            'title' => getLogTitle($log),
                            'description' => "{$log['jenis_surat']} - {$log['ticket_name']}",
                            'time' => $timeAgo,
                            'icon' => $statusInfo['icon'],
                            'color' => $statusInfo['color'],
                            'action' => $log['action'],
                            'note' => $log['note'],
                            'admin_name' => $log['admin_name'] ?? 'Sistem',
                            'created_at' => $log['created_at']
                        ];
                    }
                    
                    echo json_encode([
                        'success' => true,
                        'data' => $formattedLogs
                    ]);
                    break;
                    
                case 'today_summary':
                    // Get today's activity summary using ticket logs
                    $today = date('Y-m-d');
                    
                    $summary = [
                        'new_tickets' => 0,
                        'approved' => 0,
                        'generated' => 0,
                        'rejected' => 0,
                        'in_review' => 0
                    ];
                    
                    // Get logs created today grouped by action
                    $stmt = $ticketLogModel->pdo->prepare("
                        SELECT action, COUNT(*) as count 
                        FROM ticket_logs 
                        WHERE DATE(created_at) = ?
                        GROUP BY action
                    ");
                    $stmt->execute([$today]);
                    $todayLogs = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
                    
                    $summary['new_tickets'] = $todayLogs['submitted'] ?? 0;
                    $summary['in_review'] = $todayLogs['in_review'] ?? 0;
                    $summary['approved'] = $todayLogs['valid'] ?? 0;
                    $summary['generated'] = $todayLogs['generated'] ?? 0;
                    $summary['rejected'] = $todayLogs['rejected'] ?? 0;
                    
                    // Get admin activity for today
                    $stmt = $ticketLogModel->pdo->prepare("
                        SELECT 
                            a.name,
                            COUNT(tl.id) as action_count
                        FROM ticket_logs tl
                        LEFT JOIN admins a ON tl.admin_id = a.id
                        WHERE DATE(tl.created_at) = ? AND tl.admin_id IS NOT NULL
                        GROUP BY tl.admin_id
                        ORDER BY action_count DESC
                        LIMIT 5
                    ");
                    $stmt->execute([$today]);
                    $activeAdmins = $stmt->fetchAll();
                    
                    echo json_encode([
                        'success' => true,
                        'data' => $summary,
                        'admin_activity' => $activeAdmins,
                        'debug' => [
                            'today' => $today,
                            'log_counts' => $todayLogs
                        ]
                    ]);
                    break;
                    
                case 'export_activity':
                    // Export activity data as CSV
                    $statusFilter = $_GET['status'] ?? '';
                    $format = $_GET['format'] ?? 'csv';
                    $exportType = $_GET['type'] ?? 'tickets'; // 'tickets' or 'logs'
                    
                    if ($exportType === 'logs' && $format === 'csv') {
                        // Export logs data
                        $recentLogs = $ticketLogModel->getRecentLogs(1000); // Get more for export
                        
                        header('Content-Type: text/csv');
                        header('Content-Disposition: attachment; filename="logs_export_' . date('Y-m-d') . '.csv"');
                        
                        $output = fopen('php://output', 'w');
                        
                        // CSV headers for logs
                        fputcsv($output, [
                            'Ticket Code',
                            'Action',
                            'Admin Name',
                            'Note',
                            'Date'
                        ]);
                        
                        // CSV data for logs
                        foreach ($recentLogs as $log) {
                            fputcsv($output, [
                                $log['ticket_code'],
                                $log['action'],
                                $log['admin_name'] ?? 'Sistem',
                                $log['note'] ?? '',
                                $log['created_at']
                            ]);
                        }
                        
                        fclose($output);
                        exit;
                    } else {
                        // Export tickets data (original functionality)
                        $filters = [];
                        if (!empty($statusFilter)) {
                            $filters['status'] = $statusFilter;
                        }
                        
                        // Get all activities (no pagination for export)
                        $result = $ticketModel->getAll($filters, 1, 1000);
                        
                        if ($result['success'] && $format === 'csv') {
                            header('Content-Type: text/csv');
                            header('Content-Disposition: attachment; filename="tickets_export_' . date('Y-m-d') . '.csv"');
                            
                            $output = fopen('php://output', 'w');
                            
                            // CSV headers
                            fputcsv($output, [
                                'Ticket Code',
                                'Name',
                                'Letter Type',
                                'Status',
                                'Latest Note',
                                'Admin Name',
                                'Created Date',
                                'Updated Date'
                            ]);
                            
                            // CSV data
                            foreach ($result['data'] as $ticket) {
                                // Get latest log for this ticket
                                $latestLog = $ticketLogModel->getLatestByTicketId($ticket['id']);
                                
                                fputcsv($output, [
                                    $ticket['ticket_code'],
                                    $ticket['nama'],
                                    $ticket['jenis_surat'],
                                    $ticket['status'],
                                    $latestLog['note'] ?? '',
                                    $latestLog['admin_name'] ?? 'Sistem',
                                    $ticket['created_at'],
                                    $ticket['updated_at']
                                ]);
                            }
                            
                            fclose($output);
                            exit;
                        } else {
                            echo json_encode(['success' => false, 'error' => 'Export failed']);
                        }
                    }
                    break;
                    
                case 'admin_stats':
                    // Get admin performance statistics
                    $adminId = $_GET['admin_id'] ?? null;
                    $period = $_GET['period'] ?? '30'; // days
                    
                    if ($adminId) {
                        // Get specific admin stats
                        $adminLogs = $ticketLogModel->getByAdminId($adminId, 1000);
                        
                        $stats = [
                            'total_actions' => count($adminLogs),
                            'actions_breakdown' => [],
                            'recent_activity' => array_slice($adminLogs, 0, 10)
                        ];
                        
                        // Count actions by type
                        foreach ($adminLogs as $log) {
                            $action = $log['action'];
                            $stats['actions_breakdown'][$action] = ($stats['actions_breakdown'][$action] ?? 0) + 1;
                        }
                        
                        echo json_encode([
                            'success' => true,
                            'data' => $stats
                        ]);
                    } else {
                        // Get all admin stats
                        $logStats = $ticketLogModel->getStatistics();
                        echo json_encode([
                            'success' => true,
                            'data' => [
                                'active_admins' => $logStats['active_admins'],
                                'total_actions' => $logStats['total'],
                                'period_actions' => $logStats['this_week']
                            ]
                        ]);
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
        'submitted' => ['icon' => 'bi-file-earmark-plus', 'color' => 'info'],
        'in_review' => ['icon' => 'bi-hourglass-split', 'color' => 'warning'],
        'valid' => ['icon' => 'bi-check-circle', 'color' => 'success'],
        'rejected' => ['icon' => 'bi-x-circle', 'color' => 'danger'],
        'generated' => ['icon' => 'bi-file-earmark-check', 'color' => 'primary']
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

/**
 * Helper function to get log title
 */
function getLogTitle($log) {
    $adminName = $log['admin_name'] ?? 'Sistem';
    
    switch ($log['action']) {
        case 'submitted':
            return "Ticket submitted";
        case 'in_review':
            return "{$adminName} started reviewing ticket";
        case 'valid':
            return "{$adminName} approved ticket";
        case 'rejected':
            return "{$adminName} rejected ticket";
        case 'generated':
            return "{$adminName} generated letter for {$log['ticket_code']}";
        default:
            return "{$adminName} updated ticket";
    }
}
?>
