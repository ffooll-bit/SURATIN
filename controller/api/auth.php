<?php
session_start();

header('Content-Type: application/json');

// Mock user credentials (in real app, this would be from database)
$mockUsers = [
    'admin' => [
        'password' => 'admin123',
        'name' => 'Administrator',
        'role' => 'admin',
        'email' => 'admin@universitas.ac.id'
    ],
    'super' => [
        'password' => 'super123',
        'name' => 'Super Administrator',
        'role' => 'super',
        'email' => 'super@universitas.ac.id'
    ]
];

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        // Handle login
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            $input = $_POST;
        }
        
        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            http_response_code(400);
            echo json_encode(['error' => 'Username dan password wajib diisi']);
            exit;
        }
        
        $user = $mockUsers[strtolower($username)] ?? null;
        
        if ($user && $user['password'] === $password) {
            // Set session
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = strtolower($username);
            $_SESSION['admin_name'] = $user['name'];
            $_SESSION['admin_role'] = $user['role'];
            $_SESSION['admin_email'] = $user['email'];
            $_SESSION['login_time'] = time();
            
            echo json_encode([
                'success' => true,
                'message' => 'Login berhasil',
                'user' => [
                    'username' => strtolower($username),
                    'name' => $user['name'],
                    'role' => $user['role'],
                    'email' => $user['email']
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Username atau password salah']);
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
                    'username' => $_SESSION['admin_username'],
                    'name' => $_SESSION['admin_name'],
                    'role' => $_SESSION['admin_role'],
                    'email' => $_SESSION['admin_email']
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
?>
