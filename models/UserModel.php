
<?php
class UserModel {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getUserByUsername($username) {
        $stmt = $this->db->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }
    
    public function getUserSecurity($username) {
        $stmt = $this->db->prepare("SELECT failed_attempts, locked_until FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }
    
    public function updateLoginSuccess($userId) {
        $stmt = $this->db->prepare("UPDATE users SET failed_attempts = 0, locked_until = NULL, last_login = NOW() WHERE id = ?");
        return $stmt->execute([$userId]);
    }
    
    public function updateLoginFailure($username, $attempts, $lockedUntil = null) {
        $stmt = $this->db->prepare("UPDATE users SET failed_attempts = ?, locked_until = ? WHERE username = ?");
        return $stmt->execute([$attempts, $lockedUntil, $username]);
    }
}
