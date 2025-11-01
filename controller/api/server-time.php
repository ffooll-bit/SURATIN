<?php
require_once __DIR__ . '/../config/app.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $response = [
        'success' => true,
        'time' => date('H:i:s'),
        'date' => date('d M Y'),
        'timestamp' => time(),
        'timezone' => date_default_timezone_get()
    ];
    
    echo json_encode($response);
} catch (Exception $e) {
    $response = [
        'success' => false,
        'error' => 'Failed to get server time',
        'message' => $e->getMessage()
    ];
    
    http_response_code(500);
    echo json_encode($response);
}
?>
