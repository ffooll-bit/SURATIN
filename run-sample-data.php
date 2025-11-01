<?php
/**
 * SURATIN Sample Data Runner
 * CLI script untuk menjalankan sample-data.sql
 * 
 * Usage: php run-sample-data.php
 */

// Include database configuration
require_once __DIR__ . '/controller/config/database.php';

echo "SURATIN Sample Data Runner\n";
echo "==========================\n\n";

try {
    // Check if database exists with buffered queries enabled
    echo "Checking database connection...\n";
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_DATABASE, 
                   DB_USERNAME, DB_PASSWORD, [
                       PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                       PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
                   ]);
    
    // Read SQL file
    $sqlFile = 'sql/sample-data.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception("File $sqlFile tidak ditemukan!");
    }
    
    echo "Reading SQL file: $sqlFile\n";
    $sql = file_get_contents($sqlFile);
    
    if (empty($sql)) {
        throw new Exception("File SQL kosong atau tidak dapat dibaca!");
    }
    
    // Check if tables exist
    $stmt = $pdo->query("SHOW TABLES LIKE 'tickets'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (empty($tables)) {
        throw new Exception("Tabel 'tickets' tidak ditemukan! Jalankan run-schema.php terlebih dahulu.");
    }
    
    // Get current record count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM tickets");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $beforeCount = $result['count'];
    echo "Current records in tickets table: $beforeCount\n";
    
    // Clean up the SQL content
    $sql = preg_replace('/--.*$/m', '', $sql); // Remove comments
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql); // Remove multi-line comments
    
    // Split SQL statements more carefully
    $statements = [];
    $currentStatement = '';
    $lines = explode("\n", $sql);
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        // Skip USE statements and comments
        if (stripos($line, 'USE ') === 0 || 
            stripos($line, '--') === 0 || 
            stripos($line, 'SELECT') === 0) {
            continue;
        }
        
        $currentStatement .= $line . ' ';
        
        // If line ends with semicolon, it's end of statement
        if (substr(rtrim($line), -1) === ';') {
            $statements[] = trim($currentStatement);
            $currentStatement = '';
        }
    }
    
    // Add any remaining statement
    if (!empty(trim($currentStatement))) {
        $statements[] = trim($currentStatement);
    }
    
    // Filter out empty statements
    $statements = array_filter($statements, function($stmt) {
        return !empty(trim($stmt));
    });
    
    echo "Executing " . count($statements) . " SQL statements...\n";
    
    $executed = 0;
    $pdo->beginTransaction();
    
    try {
        foreach ($statements as $statement) {
            if (!empty(trim($statement))) {
                $stmt = $pdo->prepare($statement);
                $stmt->execute();
                $executed++;
                echo ".";
                
                // Free the statement to avoid buffering issues
                $stmt = null;
            }
        }
        
        $pdo->commit();
        echo "\n\nSample data insertion completed!\n";
        echo "Executed: $executed statements\n";
        
    } catch (PDOException $e) {
        $pdo->rollback();
        throw $e;
    }
    
    // Check final record count with new connection to avoid buffering issues
    $pdo = null;
    $pdo = new PDO("mysql:host={$config['host']};dbname={$config['database']}", 
                   $config['username'], $config['password'], [
                       PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                       PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
                   ]);
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM tickets");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $afterCount = $result['count'];
    $newRecords = $afterCount - $beforeCount;
    
    echo "Records after insertion: $afterCount\n";
    echo "New records added: $newRecords\n";
    
    // Show sample records by status
    echo "\nData summary by status:\n";
    $stmt = $pdo->query("
        SELECT status, COUNT(*) as count 
        FROM tickets 
        GROUP BY status 
        ORDER BY count DESC
    ");
    
    $statusData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($statusData as $row) {
        echo "- {$row['status']}: {$row['count']} records\n";
    }
    
} catch (Exception $e) {
    echo "\nERROR: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nDone!\n";
?>
