<?php
/**
 * SURATIN TicketLog Model
 * Model untuk operasi CRUD data ticket_logs
 */

require_once __DIR__ . '/../controller/config/database.php';

class TicketLog {
    public $pdo;
    
    public function __construct() {
        $this->pdo = getDbConnection();
    }
    
    /**
     * Create new ticket log entry
     */
    public function create($ticketId, $adminId, $action, $note = null) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO ticket_logs (ticket_id, admin_id, action, note, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            
            // Allow admin_id to be NULL for initial submissions
            $adminIdValue = $adminId === null ? null : $adminId;
            $result = $stmt->execute([$ticketId, $adminIdValue, $action, $note]);
            
            if ($result) {
                return [
                    'success' => true,
                    'id' => $this->pdo->lastInsertId()
                ];
            }
            
            return ['success' => false, 'error' => 'Failed to create log entry'];
            
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get all logs for a specific ticket
     */
    public function getByTicketId($ticketId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    tl.*,
                    a.name as admin_name,
                    a.username as admin_username
                FROM ticket_logs tl
                LEFT JOIN admins a ON tl.admin_id = a.id
                WHERE tl.ticket_id = ?
                ORDER BY tl.created_at ASC
            ");
            $stmt->execute([$ticketId]);
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Get recent logs across all tickets
     */
    public function getRecentLogs($limit = 10) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    tl.*,
                    t.ticket_code,
                    t.nama as ticket_name,
                    t.jenis_surat,
                    a.name as admin_name,
                    a.username as admin_username
                FROM ticket_logs tl
                LEFT JOIN tickets t ON tl.ticket_id = t.id
                LEFT JOIN admins a ON tl.admin_id = a.id
                ORDER BY tl.created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Get logs by admin ID
     */
    public function getByAdminId($adminId, $limit = 50) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    tl.*,
                    t.ticket_code,
                    t.nama as ticket_name,
                    t.jenis_surat
                FROM ticket_logs tl
                LEFT JOIN tickets t ON tl.ticket_id = t.id
                WHERE tl.admin_id = ?
                ORDER BY tl.created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$adminId, $limit]);
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Get latest log for a ticket (most recent status change)
     */
    public function getLatestByTicketId($ticketId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    tl.*,
                    a.name as admin_name,
                    a.username as admin_username
                FROM ticket_logs tl
                LEFT JOIN admins a ON tl.admin_id = a.id
                WHERE tl.ticket_id = ?
                ORDER BY tl.created_at DESC
                LIMIT 1
            ");
            $stmt->execute([$ticketId]);
            
            return $stmt->fetch();
            
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Delete logs for a specific ticket (used when ticket is deleted)
     */
    public function deleteByTicketId($ticketId) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM ticket_logs WHERE ticket_id = ?");
            $result = $stmt->execute([$ticketId]);
            
            return ['success' => $result];
            
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get log statistics
     */
    public function getStatistics() {
        try {
            // Get total logs
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM ticket_logs");
            $total = $stmt->fetch()['total'];
            
            // Get logs by action
            $stmt = $this->pdo->query("
                SELECT 
                    action,
                    COUNT(*) as count
                FROM ticket_logs 
                GROUP BY action
            ");
            $actionStats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            // Get today's logs
            $stmt = $this->pdo->query("
                SELECT COUNT(*) as count 
                FROM ticket_logs 
                WHERE DATE(created_at) = CURDATE()
            ");
            $today = $stmt->fetch()['count'];
            
            // Get this week's logs
            $stmt = $this->pdo->query("
                SELECT COUNT(*) as count 
                FROM ticket_logs 
                WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)
            ");
            $thisWeek = $stmt->fetch()['count'];
            
            // Get most active admins
            $stmt = $this->pdo->query("
                SELECT 
                    a.name,
                    a.username,
                    COUNT(tl.id) as log_count
                FROM ticket_logs tl
                LEFT JOIN admins a ON tl.admin_id = a.id
                WHERE tl.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY tl.admin_id
                ORDER BY log_count DESC
                LIMIT 5
            ");
            $activeAdmins = $stmt->fetchAll();
            
            return [
                'total' => $total,
                'today' => $today,
                'this_week' => $thisWeek,
                'by_action' => $actionStats,
                'active_admins' => $activeAdmins
            ];
            
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Validate ticket action
     */
    public function isValidAction($action) {
        $validActions = ['submitted', 'in_review', 'valid', 'rejected', 'generated'];
        return in_array($action, $validActions);
    }
    
    /**
     * Get action history timeline for a ticket (formatted for display)
     */
    public function getTicketTimeline($ticketId) {
        try {
            $logs = $this->getByTicketId($ticketId);
            $timeline = [];
            
            foreach ($logs as $log) {
                $timeline[] = [
                    'id' => $log['id'],
                    'action' => $log['action'],
                    'action_label' => $this->getActionLabel($log['action']),
                    'note' => $log['note'],
                    'admin_name' => $log['admin_name'],
                    'admin_username' => $log['admin_username'],
                    'created_at' => $log['created_at'],
                    'formatted_date' => date('d/m/Y H:i', strtotime($log['created_at']))
                ];
            }
            
            return $timeline;
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Get human-readable action labels
     */
    private function getActionLabel($action) {
        $labels = [
            'submitted' => 'Disubmit',
            'in_review' => 'Sedang Direview',
            'valid' => 'Valid/Disetujui', 
            'rejected' => 'Ditolak',
            'generated' => 'Surat Diterbitkan'
        ];
        
        return $labels[$action] ?? $action;
    }
}
?>