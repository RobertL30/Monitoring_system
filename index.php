<?php
// index.php - Main entry point with authentication
session_start();
require_once 'config.php';
require_once 'monitoring_functions.php';

// Authentication functions
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ?page=login');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        die('Access denied. Administrator privileges required.');
    }
}

function login($username, $password, $db) {
    // Check for account lockout
    $stmt = $db->prepare("SELECT failed_attempts, locked_until FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user_security = $stmt->fetch();
    
    if ($user_security && $user_security['locked_until']) {
        $locked_until = new DateTime($user_security['locked_until']);
        if ($locked_until > new DateTime()) {
            return ['success' => false, 'message' => 'Account temporarily locked. Try again later.'];
        }
    }
    
    // Verify credentials
    $stmt = $db->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        // Successful login
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['login_time'] = time();
        
        // Reset failed attempts
        $stmt = $db->prepare("UPDATE users SET failed_attempts = 0, locked_until = NULL, last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        return ['success' => true, 'message' => 'Login successful'];
    } else {
        // Failed login
        if ($user) {
            $new_attempts = ($user_security['failed_attempts'] ?? 0) + 1;
            $locked_until = null;
            
            if ($new_attempts >= 5) {
                $locked_until = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            }
            
            $stmt = $db->prepare("UPDATE users SET failed_attempts = ?, locked_until = ? WHERE username = ?");
            $stmt->execute([$new_attempts, $locked_until, $username]);
        }
        
        return ['success' => false, 'message' => 'Invalid username or password'];
    }
}

function logout() {
    session_destroy();
    header('Location: ?page=login');
    exit;
}

// Check session timeout
function checkSessionTimeout() {
    global $config;
    if (isLoggedIn()) {
        $login_time = $_SESSION['login_time'] ?? 0;
        if (time() - $login_time > $config['session_timeout']) {
            session_destroy();
            header('Location: ?page=login&timeout=1');
            exit;
        }
    }
}

// Initialize database
$db = getDatabase();

// Handle logout
if (isset($_GET['logout'])) {
    logout();
}

// Check session timeout
checkSessionTimeout();

// Get current page
$page = $_GET['page'] ?? 'dashboard';
$action = $_GET['action'] ?? '';

// Handle login
if ($page === 'login') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        $result = login($username, $password, $db);
        
        if ($result['success']) {
            header('Location: ?page=dashboard');
            exit;
        } else {
            $login_error = $result['message'];
        }
    }
    
    include 'login.php';
    exit;
}

// Require authentication for all other pages
requireLogin();

// Handle AJAX actions
if ($action) {
    header('Content-Type: application/json');
    
    switch ($action) {
        case 'monitor_all':
            $stmt = $db->query("SELECT * FROM devices WHERE enabled = 1");
            $devices = $stmt->fetchAll();
            
            $results = [];
            foreach ($devices as $device) {
                $result = monitorDevice($device, $db);
                $results[] = [
                    'device' => $device['name'],
                    'status' => $result['status'],
                    'response_time' => $result['response_time']
                ];
            }
            
            echo json_encode(['results' => $results, 'total' => count($devices)]);
            exit;
            
        case 'get_dashboard_data':
            $stmt = $db->query("
                SELECT d.*, ds.status, ds.response_time, ds.packet_loss, ds.consecutive_failures, 
                       ds.baseline_response, ds.last_check, ds.error_message
                FROM devices d
                LEFT JOIN device_status ds ON d.id = ds.device_id
                WHERE d.enabled = 1
                ORDER BY d.device_group, d.name
            ");
            $devices = $stmt->fetchAll();
            
            // Group devices by category
            $grouped_devices = [];
            foreach ($devices as $device) {
                $group = $device['device_group'];
                if (!isset($grouped_devices[$group])) {
                    $grouped_devices[$group] = [];
                }
                $grouped_devices[$group][] = $device;
            }
            
            // Calculate statistics
            $total = count($devices);
            $operational = count(array_filter($devices, fn($d) => $d['status'] === 'operational'));
            $degraded = count(array_filter($devices, fn($d) => $d['status'] === 'degraded'));
            $down = count(array_filter($devices, fn($d) => $d['status'] === 'down'));
            
            // Get recent alerts
            $stmt = $db->query("
                SELECT a.*, d.name as device_name 
                FROM alerts a
                JOIN devices d ON a.device_id = d.id
                WHERE a.acknowledged = 0
                ORDER BY a.created_at DESC
                LIMIT 10
            ");
            $alerts = $stmt->fetchAll();
            
            echo json_encode([
                'devices' => $grouped_devices,
                'device_groups' => $device_groups,
                'stats' => [
                    'total' => $total,
                    'operational' => $operational,
                    'degraded' => $degraded,
                    'down' => $down
                ],
                'alerts' => $alerts,
                'last_updated' => date('Y-m-d H:i:s'),
                'user' => [
                    'username' => $_SESSION['username'],
                    'role' => $_SESSION['role']
                ]
            ]);
            exit;
            
        case 'add_device':
    requireAdmin();
    
    $name = trim($_POST['name'] ?? '');
    $ip = trim($_POST['ip_address'] ?? '');
    $group = $_POST['device_group'] ?? '';
    $type = $_POST['monitor_type'] ?? '';
    $location = trim($_POST['location'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $critical = isset($_POST['critical_device']) ? 1 : 0;
    
    // Handle port properly - convert empty string to NULL
    $port = null;
    if (!empty($_POST['port']) && is_numeric($_POST['port'])) {
        $port = (int)$_POST['port'];
    }
    
    // Validate required fields
    if (empty($name) || empty($ip) || empty($group) || empty($type)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
        exit;
    }
    
    try {
        // Insert with explicit field names to avoid any ordering issues
        $stmt = $db->prepare("
            INSERT INTO devices
            SET name = ?,
                ip_address = ?,
                device_group = ?,
                monitor_type = ?,
                port = ?,
                location = ?,
                description = ?,
                critical_device = ?,
                enabled = 1,
                created_at = NOW(),
                updated_at = NOW()
        ");
        
        $result = $stmt->execute([
            $name,
            $ip,
            $group,
            $type,
            $port,
            $location,
            $description,
            $critical
        ]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Device added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to insert device']);
        }
        
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            echo json_encode(['success' => false, 'message' => 'IP address already exists']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }
    exit;
            
        case 'delete_device':
            requireAdmin();
            
            $device_id = $_POST['device_id'] ?? 0;
            
            try {
                $stmt = $db->prepare("DELETE FROM devices WHERE id = ?");
                $stmt->execute([$device_id]);
                
                echo json_encode(['success' => true, 'message' => 'Device deleted successfully']);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
            exit;
            
        case 'get_device_history':
            $device_id = $_GET['device_id'] ?? 0;
            $hours = $_GET['hours'] ?? 24;
            
            $stmt = $db->prepare("
                SELECT timestamp, success, response_time, packet_loss
                FROM monitoring_history 
                WHERE device_id = ? AND timestamp > DATE_SUB(NOW(), INTERVAL ? HOUR)
                ORDER BY timestamp ASC
            ");
            $stmt->execute([$device_id, $hours]);
            $history = $stmt->fetchAll();
            
            echo json_encode($history);
            exit;
    }
}

// Route to appropriate page
switch ($page) {
    case 'dashboard':
        include 'dashboard.php';
        break;
    case 'devices':
        include 'devices.php';
        break;
    case 'reports':
        include 'reports.php';
        break;
    default:
        include 'dashboard.php';
}
?>