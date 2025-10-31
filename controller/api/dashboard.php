<?php
/**
 * Dashboard API Controller
 * Provides statistics and activity data for admin dashboard
 */

session_start();

header('Content-Type: application/json');

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
                    // Get recent activity
                    $limit = intval($_GET['limit'] ?? 10);
                    $activities = $ticketModel->getRecentActivity($limit);
                    
                    // Format activities for display
                    $formattedActivities = [];
                    foreach ($activities as $activity) {
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
                            'created_at' => $activity['created_at'],
                            'updated_at' => $activity['updated_at']
                        ];
                    }
                    
                    echo json_encode([
                        'success' => true,
                        'data' => $formattedActivities,
                        'count' => count($formattedActivities)
                    ]);
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
 * Helper function to calculate time ago
 */
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'Just now';
    if ($time < 3600) return floor($time/60) . ' min ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    
    return date('M j, Y', strtotime($datetime));
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
