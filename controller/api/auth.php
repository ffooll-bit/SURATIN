<?php
session_start();

header('Content-Type: application/json');

// Include app configuration
require_once '../config/app.php';

// Include database connection
require_once '../config/database.php';

// Function to authenticate user
function authenticateUser($username, $password, $pdo) {
    $stmt = $pdo->prepare("
        SELECT id, username, password_hash, name, email, role, active, last_login 
        FROM admins 
        WHERE username = ? AND active = 1
    ");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        return false;
    }
    
    // Verify password
    if (password_verify($password, $user['password_hash']) || 
        $user['password_hash'] === $password) { // Fallback for plain text (development only)
        
        // Update last login
        $updateStmt = $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
        $updateStmt->execute([$user['id']]);
        
        return $user;
    }
    
    return false;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    $pdo = getDbConnection();
    
    switch ($method) {
        case 'POST':
            // Handle login
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                $input = $_POST;
            }
            
            $username = trim($input['username'] ?? '');
            $password = $input['password'] ?? '';
            
            if (empty($username) || empty($password)) {
                http_response_code(400);
                echo json_encode(['error' => 'Username dan password wajib diisi']);
                exit;
            }
            
            $user = authenticateUser($username, $password, $pdo);
            
            if ($user) {
                // Set session
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_name'] = $user['name'];
                $_SESSION['admin_role'] = $user['role'];
                $_SESSION['admin_email'] = $user['email'];
                $_SESSION['login_time'] = time();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Login berhasil',
                    'user' => [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'name' => $user['name'],
                        'role' => $user['role'],
                        'email' => $user['email']
                    ]
                ]);
            } else {
                http_response_code(401);
                echo json_encode(['error' => 'Username atau password salah, atau akun tidak aktif']);
            }
            break;
            
        case 'DELETE':
            // Handle logout
            session_unset();
            session_destroy();
            echo json_encode(['success' => true, 'message' => 'Logout berhasil']);
            break;
            
        case 'GET':
            // Check current session
            if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
                echo json_encode([
                    'authenticated' => true,
                    'user' => [
                        'id' => $_SESSION['admin_id'],
                        'username' => $_SESSION['admin_username'],
                        'name' => $_SESSION['admin_name'],
                        'role' => $_SESSION['admin_role'],
                        'email' => $_SESSION['admin_email'],
                        'login_time' => $_SESSION['login_time']
                    ]
                ]);
            } else {
                echo json_encode(['authenticated' => false]);
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
?>
