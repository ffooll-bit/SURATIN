<?php
/**
 * SURATIN Database Connection Handler
 * Centralized database configuration and connection management
 */

// Include app configuration
require_once __DIR__ . '/app.php';

// Database configuration constants
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_DATABASE', 'suratin_db');
define('DB_CHARSET', 'utf8mb4');

class Database {
    private static $instance = null;
    private $pdo = null;
    
    // Database configuration using constants
    private $config = [
        'host' => DB_HOST,
        'username' => DB_USERNAME,
        'password' => DB_PASSWORD,
        'database' => DB_DATABASE,
        'charset' => DB_CHARSET,
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    ];
    
    private function __construct() {
        // Set timezone for database connections
        $this->config['options'][PDO::MYSQL_ATTR_INIT_COMMAND] = "SET time_zone = '" . APP_UTC_TIMEZONE . "'";
    }
    
    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get database connection
     */
    public function getConnection() {
        if ($this->pdo === null) {
            $this->connect();
        }
        return $this->pdo;
    }
    
    /**
     * Establish database connection
     */
    private function connect() {
        try {
            $dsn = "mysql:host={$this->config['host']};dbname={$this->config['database']};charset={$this->config['charset']}";
            $this->pdo = new PDO($dsn, $this->config['username'], $this->config['password'], $this->config['options']);
        } catch (PDOException $e) {
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Test database connection
     */
    public function testConnection() {
        try {
            $pdo = $this->getConnection();
            $stmt = $pdo->query("SELECT 1");
            return $stmt !== false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get database info
     */
    public function getDatabaseInfo() {
        try {
            $pdo = $this->getConnection();
            $info = [];
            
            // Get database name
            $stmt = $pdo->query("SELECT DATABASE() as db_name");
            $info['database'] = $stmt->fetch()['db_name'];
            
            // Get tables
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $info['tables'] = [];
            foreach ($tables as $table) {
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM `$table`");
                $count = $stmt->fetch()['count'];
                $info['tables'][$table] = $count;
            }
            
            return $info;
        } catch (Exception $e) {
            throw new Exception('Failed to get database info: ' . $e->getMessage());
        }
    }
    
    /**
     * Close connection
     */
    public function close() {
        $this->pdo = null;
    }
    
    /**
     * Prevent cloning
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

/**
 * Helper function to get database connection quickly
 */
function getDbConnection() {
    return Database::getInstance()->getConnection();
}
?>
