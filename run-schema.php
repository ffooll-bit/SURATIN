<?php
/**
 * SURATIN Schema Runner
 * CLI script untuk menjalankan create-schema.sql
 * 
 * Usage: php run-schema.php
 */

// Database configuration
$config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'suratin_db'
];

echo "SURATIN Schema Runner\n";
echo "====================\n\n";

try {
    // Read SQL file
    $sqlFile = 'sql/create-schema.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception("File $sqlFile tidak ditemukan!");
    }
    
    echo "Reading SQL file: $sqlFile\n";
    $sql = file_get_contents($sqlFile);
    
    if (empty($sql)) {
        throw new Exception("File SQL kosong atau tidak dapat dibaca!");
    }
    
    // Connect to MySQL (without database first) with buffered queries
    echo "Connecting to MySQL server...\n";
    $pdo = new PDO("mysql:host={$config['host']}", 
                   $config['username'], $config['password'], [
                       PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                       PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
                   ]);
    
    // Clean and split SQL statements
    $sql = preg_replace('/--.*$/m', '', $sql); // Remove comments
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    echo "Executing " . count($statements) . " SQL statements...\n";
    
    $executed = 0;
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $pdo->exec($statement);
                $executed++;
                echo ".";
            } catch (PDOException $e) {
                echo "\nWarning: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n\nSchema creation completed!\n";
    echo "Executed: $executed statements\n";
    
    // Verify database and tables with new connection
    echo "\nVerifying database structure...\n";
    $pdo = null;
    $pdo = new PDO("mysql:host={$config['host']};dbname={$config['database']}", 
                   $config['username'], $config['password'], [
                       PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                       PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
                   ]);
    
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!empty($tables)) {
        echo "Database created successfully!\n";
        echo "Tables found:\n";
        foreach ($tables as $table) {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM `$table`");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $count = $result['count'];
            echo "- $table ($count records)\n";
        }
    } else {
        echo "Warning: No tables found in database!\n";
    }
    
} catch (Exception $e) {
    echo "\nERROR: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nDone!\n";
?>
