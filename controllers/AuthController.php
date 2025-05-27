<?php
class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new UserModel();
    }
    
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']) && isset($_SESSION['username']);
    }
    
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: ?page=login');
            exit;
        }
    }
    
    public static function requireAdmin() {
        self::requireLogin();
        if ($_SESSION['role'] !== 'admin') {
            die('Access denied. Administrator privileges required.');
        }
    }
    
    public static function logout() {
        session_destroy();
        header('Location: ?page=login');
        exit;
    }
    
    public static function checkSessionTimeout() {
        if (self::isLoggedIn()) {
            $login_time = $_SESSION['login_time'] ?? 0;
            if (time() - $login_time > Config::$config['session_timeout']) {
                session_destroy();
                header('Location: ?page=login&timeout=1');
                exit;
            }
        }
    }
    
    public function login() {
        $login_error = null;
        $timeout_message = isset($_GET['timeout']) ? 'Your session has expired. Please log in again.' : '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            $result = $this->processLogin($username, $password);
            
            if ($result['success']) {
                header('Location: ?page=dashboard');
                exit;
            } else {
                $login_error = $result['message'];
            }
        }
        
        require_once 'views/auth/login.php';
    }
    
    private function processLogin($username, $password) {
        // Check for account lockout
        $user_security = $this->userModel->getUserSecurity($username);
        
        if ($user_security && $user_security['locked_until']) {
            $locked_until = new DateTime($user_security['locked_until']);
            if ($locked_until > new DateTime()) {
                return ['success' => false, 'message' => 'Account temporarily locked. Try again later.'];
            }
        }
        
        // Verify credentials
        $user = $this->userModel->getUserByUsername($username);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Successful login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['login_time'] = time();
            
            $this->userModel->updateLoginSuccess($user['id']);
            
            return ['success' => true, 'message' => 'Login successful'];
        } else {
            // Failed login
            if ($user) {
                $new_attempts = ($user_security['failed_attempts'] ?? 0) + 1;
                $locked_until = null;
                
                if ($new_attempts >= 5) {
                    $locked_until = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                }
                
                $this->userModel->updateLoginFailure($username, $new_attempts, $locked_until);
            }
            
            return ['success' => false, 'message' => 'Invalid username or password'];
        }
    }
}