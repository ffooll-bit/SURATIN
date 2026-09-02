<?php
/**
 * Tickets API Controller
 * Provides CRUD operations for ticket management
 */

session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

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
            // Get all tickets with optional filters and pagination
            $page = intval($_GET['page'] ?? 1);
            $limit = intval($_GET['limit'] ?? 50);
            $statusFilter = $_GET['status'] ?? '';
            
            // Build filters
            $filters = [];
            if (!empty($statusFilter)) {
                $filters['status'] = $statusFilter;
            }
            
            $result = $ticketModel->getAll($filters, $page, $limit);
            
            if ($result['success']) {
                // Transform data to match expected API format and include logs
                $transformedData = [];
                foreach ($result['data'] as $ticket) {
                    // Get logs for this ticket
                    $ticketLogs = $ticketModel->ticketLog->getTicketTimeline($ticket['id']);
                    
                    $transformedData[] = [
                        'id' => $ticket['id'],
                        'ticket_number' => $ticket['ticket_code'],
                        'name' => $ticket['nama'],
                        'npm' => $ticket['npm'],
                        'prodi' => $ticket['prodi'],
                        'email' => $ticket['email'],
                        'wa' => $ticket['wa'],
                        'letter_type' => $ticket['jenis_surat'],
                        'data' => is_string($ticket['data']) ? json_decode($ticket['data'], true) : $ticket['data'],
                        'attachments' => is_string($ticket['attachments']) ? json_decode($ticket['attachments'], true) : $ticket['attachments'],
                        'status' => $ticket['status'],
                        'logs' => $ticketLogs,
                        'created_at' => $ticket['created_at'],
                        'updated_at' => $ticket['updated_at']
                    ];
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Tickets retrieved successfully',
                    'data' => $transformedData,
                    'pagination' => [
                        'current_page' => $result['page'],
                        'per_page' => $result['limit'],
                        'total' => $result['total'],
                        'last_page' => $result['total_pages']
                    ]
                ]);
            } else {
                throw new Exception($result['error']);
            }
            break;
            
        case 'POST':
            // Create new ticket
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields based on database schema
            $required = ['nama', 'npm', 'prodi', 'jenis_surat', 'email'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    throw new Exception("Field '$field' is required");
                }
            }
            
            // Validate NPM format (adjust as needed)
            if (!preg_match('/^\d{8,12}$/', $input['npm'])) {
                throw new Exception('NPM must be 8-12 digits');
            }
            
            // Validate email format
            if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email format');
            }
            
            // Prepare data for model
            $ticketData = [
                'nama' => $input['nama'],
                'npm' => $input['npm'],
                'prodi' => $input['prodi'],
                'jenis_surat' => $input['jenis_surat'],
                'email' => $input['email'],
                'wa' => $input['wa'] ?? null,
                'data' => !empty($input['data']) ? json_encode($input['data']) : null,
                'attachments' => !empty($input['attachments']) ? json_encode($input['attachments']) : null
            ];
            
            $result = $ticketModel->create($ticketData);
            
            if ($result['success']) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Ticket created successfully',
                    'data' => [
                        'id' => $result['id'],
                        'ticket_number' => $result['ticket_code']
                    ]
                ]);
            } else {
                throw new Exception($result['error']);
            }
            break;
            
        case 'PATCH':
            // Update ticket status
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Check if it's a bulk update
            if (isset($input['ids']) && is_array($input['ids'])) {
                // Bulk update
                $ticketIds = $input['ids'];
                $status = $input['status'];
                
                if (empty($ticketIds) || empty($status)) {
                    throw new Exception('Ticket IDs and status are required');
                }
                
                // Validate status
                if (!$ticketModel->isValidStatus($status)) {
                    throw new Exception('Invalid status');
                }
                
                // For bulk updates, we need to update each ticket individually to create logs
                $adminId = $_SESSION['admin_id'] ?? 1;
                $note = $input['note'] ?? null;
                $successCount = 0;
                
                foreach ($ticketIds as $ticketId) {
                    $result = $ticketModel->updateStatus($ticketId, $status, $adminId, $note);
                    if ($result['success']) {
                        $successCount++;
                    }
                }
                
                $result = [
                    'success' => $successCount > 0,
                    'affected_rows' => $successCount
                ];
                
                if ($result['success']) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Tickets updated successfully',
                        'data' => ['affected_rows' => $result['affected_rows']]
                    ]);
                } else {
                    throw new Exception($result['error']);
                }
            } else {
                // Single ticket update
                $ticketId = $_GET['id'] ?? null;
                if (!$ticketId) {
                    throw new Exception('Ticket ID is required');
                }
                
                if (empty($input['status'])) {
                    throw new Exception('Status is required');
                }
                
                // Validate status
                if (!$ticketModel->isValidStatus($input['status'])) {
                    throw new Exception('Invalid status');
                }
                
                $adminId = $_SESSION['admin_id'] ?? 1; // Get admin ID from session
                $note = $input['note'] ?? null; // Use 'note' instead of 'admin_note'
                
                $result = $ticketModel->updateStatus($ticketId, $input['status'], $adminId, $note);
                
                if ($result['success']) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Ticket status updated successfully'
                    ]);
                } else {
                    throw new Exception($result['error']);
                }
            }
            break;
            
        case 'DELETE':
            // Delete ticket
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Check if it's a bulk delete
            if (isset($input['ids']) && is_array($input['ids'])) {
                // Bulk delete
                $ticketIds = $input['ids'];
                
                if (empty($ticketIds)) {
                    throw new Exception('Ticket IDs are required');
                }
                
                $result = $ticketModel->bulkDelete($ticketIds);
                
                if ($result['success']) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Tickets deleted successfully',
                        'data' => ['affected_rows' => $result['affected_rows']]
                    ]);
                } else {
                    throw new Exception($result['error']);
                }
            } else {
                // Single ticket delete
                $ticketId = $_GET['id'] ?? null;
                if (!$ticketId) {
                    throw new Exception('Ticket ID is required');
                }
                
                $result = $ticketModel->delete($ticketId);
                
                if ($result['success']) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Ticket deleted successfully'
                    ]);
                } else {
                    throw new Exception($result['error']);
                }
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>