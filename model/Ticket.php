<?php
/**
 * SURATIN Ticket Model
 * Model untuk operasi CRUD data tickets
 */

require_once __DIR__ . '/../controller/config/database.php';
require_once __DIR__ . '/TicketLog.php';

class Ticket {
    public $pdo; // Change from private to public for dashboard controller access
    public $ticketLog; // Make public for API access
    
    public function __construct() {
        $this->pdo = getDbConnection();
        $this->ticketLog = new TicketLog();
    }
    
    /**
     * Generate unique ticket code
     */
    public function generateTicketCode() {
        $date = date('Ymd');
        $prefix = "TCK-{$date}-";
        
        // Get last ticket number for today
        $stmt = $this->pdo->prepare("
            SELECT ticket_code 
            FROM tickets 
            WHERE ticket_code LIKE ? 
            ORDER BY ticket_code DESC 
            LIMIT 1
        ");
        $stmt->execute(["{$prefix}%"]);
        $lastTicket = $stmt->fetch();
        
        if ($lastTicket) {
            // Extract number from last ticket
            $lastNumber = intval(substr($lastTicket['ticket_code'], -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Create new ticket
     */
    public function create($data) {
        try {
            $ticketCode = $this->generateTicketCode();
            
            $stmt = $this->pdo->prepare("
                INSERT INTO tickets (
                    ticket_code, nama, npm, prodi, jenis_surat, 
                    data, attachments, email, wa, status, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'submitted', NOW())
            ");
            
            $result = $stmt->execute([
                $ticketCode,
                $data['nama'],
                $data['npm'],
                $data['prodi'],
                $data['jenis_surat'],
                json_encode($data['data'] ?? []),
                json_encode($data['attachments'] ?? []),
                $data['email'],
                $data['wa'] ?? null
            ]);
            
            if ($result) {
                $ticketId = $this->pdo->lastInsertId();
                
                // Create initial log entry for submission
                // admin_id is NULL for initial submissions since no admin has handled it yet
                $this->ticketLog->create($ticketId, null, 'submitted');
                
                return [
                    'success' => true,
                    'ticket_code' => $ticketCode,
                    'id' => $ticketId
                ];
            }
            
            return ['success' => false, 'error' => 'Failed to create ticket'];
            
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get ticket by ID
     */
    public function getById($id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM tickets WHERE id = ?
            ");
            $stmt->execute([$id]);
            $ticket = $stmt->fetch();
            
            if ($ticket) {
                // Decode JSON fields
                $ticket['data'] = json_decode($ticket['data'], true) ?? [];
                $ticket['attachments'] = json_decode($ticket['attachments'], true) ?? [];
            }
            
            return $ticket;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Get ticket by ticket code
     */
    public function getByTicketCode($ticketCode) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM tickets WHERE ticket_code = ?
            ");
            $stmt->execute([$ticketCode]);
            $ticket = $stmt->fetch();
            
            if ($ticket) {
                // Decode JSON fields
                $ticket['data'] = json_decode($ticket['data'], true) ?? [];
                $ticket['attachments'] = json_decode($ticket['attachments'], true) ?? [];
            }
            
            return $ticket;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Get tickets with filters and pagination
     */
    public function getAll($filters = [], $page = 1, $limit = 10, $orderBy = 'created_at DESC') {
        try {
            $offset = ($page - 1) * $limit;
            $whereConditions = [];
            $params = [];
            
            // Build WHERE clause based on filters
            if (!empty($filters['status'])) {
                $whereConditions[] = "status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['jenis_surat'])) {
                $whereConditions[] = "jenis_surat = ?";
                $params[] = $filters['jenis_surat'];
            }
            
            if (!empty($filters['search'])) {
                $whereConditions[] = "(nama LIKE ? OR npm LIKE ? OR ticket_code LIKE ?)";
                $searchTerm = "%{$filters['search']}%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            if (!empty($filters['date_from'])) {
                $whereConditions[] = "DATE(created_at) >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $whereConditions[] = "DATE(created_at) <= ?";
                $params[] = $filters['date_to'];
            }
            
            $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";
            
            // Get total count
            $countQuery = "SELECT COUNT(*) as total FROM tickets {$whereClause}";
            $countStmt = $this->pdo->prepare($countQuery);
            $countStmt->execute($params);
            $total = $countStmt->fetch()['total'];
            
            // Get tickets
            $query = "
                SELECT * FROM tickets 
                {$whereClause} 
                ORDER BY {$orderBy}
                LIMIT {$limit} OFFSET {$offset}
            ";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            $tickets = $stmt->fetchAll();
            
            // Decode JSON fields for each ticket
            foreach ($tickets as &$ticket) {
                $ticket['data'] = json_decode($ticket['data'], true) ?? [];
                $ticket['attachments'] = json_decode($ticket['attachments'], true) ?? [];
            }
            
            return [
                'success' => true,
                'data' => $tickets,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit)
            ];
            
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Update ticket status and create log entry
     */
    public function updateStatus($id, $status, $adminId, $note = null) {
        try {
            // Validate status
            if (!$this->isValidStatus($status)) {
                return ['success' => false, 'error' => 'Invalid status'];
            }
            
            $stmt = $this->pdo->prepare("
                UPDATE tickets 
                SET status = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            
            $result = $stmt->execute([$status, $id]);
            
            if ($result) {
                // Create log entry for this status change
                $logResult = $this->ticketLog->create($id, $adminId, $status, $note);
                
                if (!$logResult['success']) {
                    // Log creation failed, but ticket update succeeded
                    // You might want to handle this differently based on requirements
                    error_log('Failed to create ticket log: ' . $logResult['error']);
                }
                
                return ['success' => true];
            }
            
            return ['success' => false, 'error' => 'Failed to update ticket status'];
            
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Update ticket data
     */
    public function update($id, $data) {
        try {
            $fields = [];
            $params = [];
            
            // Build dynamic update query
            foreach ($data as $field => $value) {
                if (in_array($field, ['nama', 'npm', 'prodi', 'jenis_surat', 'email', 'wa', 'status'])) {
                    $fields[] = "{$field} = ?";
                    $params[] = $value;
                } elseif ($field === 'data' || $field === 'attachments') {
                    $fields[] = "{$field} = ?";
                    $params[] = json_encode($value);
                }
            }
            
            if (empty($fields)) {
                return ['success' => false, 'error' => 'No valid fields to update'];
            }
            
            $fields[] = "updated_at = NOW()";
            $params[] = $id;
            
            $query = "UPDATE tickets SET " . implode(", ", $fields) . " WHERE id = ?";
            $stmt = $this->pdo->prepare($query);
            $result = $stmt->execute($params);
            
            return ['success' => $result];
            
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Delete ticket
     */
    public function delete($id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM tickets WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            return ['success' => $result];
            
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get statistics
     */
    public function getStatistics() {
        try {
            // Get counts by status
            $stmt = $this->pdo->query("
                SELECT 
                    status,
                    COUNT(*) as count
                FROM tickets 
                GROUP BY status
            ");
            $statusStats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            // Get total
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM tickets");
            $total = $stmt->fetch()['total'];
            
            // Get today's tickets
            $stmt = $this->pdo->query("
                SELECT COUNT(*) as count 
                FROM tickets 
                WHERE DATE(created_at) = CURDATE()
            ");
            $today = $stmt->fetch()['count'];
            
            // Get this week's tickets
            $stmt = $this->pdo->query("
                SELECT COUNT(*) as count 
                FROM tickets 
                WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)
            ");
            $thisWeek = $stmt->fetch()['count'];
            
            return [
                'total' => $total,
                'today' => $today,
                'this_week' => $thisWeek,
                'by_status' => $statusStats,
                'submitted' => $statusStats['submitted'] ?? 0,
                'in_review' => $statusStats['in_review'] ?? 0,
                'valid' => $statusStats['valid'] ?? 0,
                'rejected' => $statusStats['rejected'] ?? 0,
                'generated' => $statusStats['generated'] ?? 0
            ];
            
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get recent activity
     */
    public function getRecentActivity($limit = 10) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    t.ticket_code, 
                    t.nama, 
                    t.jenis_surat, 
                    t.status, 
                    t.created_at, 
                    t.updated_at,
                    tl.note as latest_note,
                    tl.created_at as log_created_at,
                    a.name as admin_name
                FROM tickets t
                LEFT JOIN ticket_logs tl ON (
                    tl.ticket_id = t.id AND 
                    tl.id = (
                        SELECT MAX(id) 
                        FROM ticket_logs 
                        WHERE ticket_id = t.id
                    )
                )
                LEFT JOIN admins a ON tl.admin_id = a.id
                ORDER BY t.updated_at DESC 
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Check if ticket exists by email and ticket code
     */
    public function verifyTicketAccess($ticketCode, $email) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id FROM tickets 
                WHERE ticket_code = ? AND email = ?
            ");
            $stmt->execute([$ticketCode, $email]);
            
            return $stmt->fetch() !== false;
            
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Bulk update ticket status
     */
    public function bulkUpdateStatus($ticketIds, $status) {
        try {
            if (!$this->isValidStatus($status)) {
                return ['success' => false, 'error' => 'Invalid status'];
            }
            
            $placeholders = str_repeat('?,', count($ticketIds) - 1) . '?';
            $query = "UPDATE tickets SET status = ?, updated_at = NOW() WHERE id IN ($placeholders)";
            
            $params = array_merge([$status], $ticketIds);
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            
            return [
                'success' => true,
                'affected_rows' => $stmt->rowCount()
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Bulk delete tickets
     */
    public function bulkDelete($ticketIds) {
        try {
            $placeholders = str_repeat('?,', count($ticketIds) - 1) . '?';
            $query = "DELETE FROM tickets WHERE id IN ($placeholders)";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($ticketIds);
            
            return [
                'success' => true,
                'affected_rows' => $stmt->rowCount()
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get ticket by ID with logs
     */
    public function getByIdWithLogs($id) {
        try {
            $ticket = $this->getById($id);
            if ($ticket) {
                $ticket['logs'] = $this->ticketLog->getTicketTimeline($id);
            }
            return $ticket;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get ticket by ticket code with logs
     */
    public function getByTicketCodeWithLogs($ticketCode) {
        try {
            $ticket = $this->getByTicketCode($ticketCode);
            if ($ticket) {
                $ticket['logs'] = $this->ticketLog->getTicketTimeline($ticket['id']);
            }
            return $ticket;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Validate ticket status
     */
    public function isValidStatus($status) {
        $validStatuses = ['submitted', 'in_review', 'valid', 'rejected', 'generated'];
        return in_array($status, $validStatuses);
    }
}
?>
