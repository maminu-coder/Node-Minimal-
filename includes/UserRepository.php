<?php
/**
 * User/Auth Repository
 * 
 * Handles all database operations related to user authentication.
 * Uses prepared statements for SQL injection prevention.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

class UserRepository {
    private Database $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Find user by email
     * 
     * @param string $email The email address
     * @return array|null The user data or null if not found
     */
    public function findByEmail(string $email): ?array {
        $sql = "SELECT * FROM users WHERE email = ?";
        $result = $this->db->select($sql, 's', [$email]);
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    /**
     * Find user by ID
     * 
     * @param int $id The user ID
     * @return array|null The user data or null if not found
     */
    public function findById(int $id): ?array {
        $sql = "SELECT id, email, name, role, avatar_url, created_at FROM users WHERE id = ?";
        $result = $this->db->select($sql, 'i', [$id]);
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    /**
     * Create a new user
     * 
     * @param array $data The user data
     * @return int|false The new user ID or false on failure
     */
    public function createUser(array $data) {
        // Hash the password
        $passwordHash = password_hash($data['password'] ?? '', PASSWORD_DEFAULT, ['cost' => HASH_COST]);
        
        $sql = "INSERT INTO users (email, password_hash, name, role, avatar_url) VALUES (?, ?, ?, ?, ?)";
        
        $success = $this->db->execute(
            $sql,
            'sssss',
            [
                strtolower(trim($data['email'] ?? '')),
                $passwordHash,
                $data['name'] ?? '',
                $data['role'] ?? 'editor',
                $data['avatar_url'] ?? null
            ]
        );
        
        return $success ? $this->db->getLastInsertId() : false;
    }
    
    /**
     * Update user information
     * 
     * @param int $id The user ID
     * @param array $data The user data to update
     * @return bool True on success, false on failure
     */
    public function updateUser(int $id, array $data): bool {
        $fields = [];
        $params = [];
        $types = '';
        
        // Name
        if (array_key_exists('name', $data)) {
            $fields[] = "name = ?";
            $params[] = $data['name'];
            $types .= 's';
        }
        
        // Email
        if (array_key_exists('email', $data)) {
            $fields[] = "email = ?";
            $params[] = strtolower(trim($data['email']));
            $types .= 's';
        }
        
        // Password (only if provided)
        if (!empty($data['password'])) {
            $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT, ['cost' => HASH_COST]);
            $fields[] = "password_hash = ?";
            $params[] = $passwordHash;
            $types .= 's';
        }
        
        // Avatar URL
        if (array_key_exists('avatar_url', $data)) {
            $fields[] = "avatar_url = ?";
            $params[] = $data['avatar_url'];
            $types .= 's';
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $id;
        $types .= 'i';
        
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        return $this->db->execute($sql, $types, $params);
    }
    
    /**
     * Delete a user
     * 
     * @param int $id The user ID
     * @return bool True on success, false on failure
     */
    public function deleteUser(int $id): bool {
        $sql = "DELETE FROM users WHERE id = ?";
        return $this->db->execute($sql, 'i', [$id]);
    }
    
    /**
     * Verify user credentials
     * 
     * @param string $email The email address
     * @param string $password The plain text password
     * @return array|null The user data if valid, null otherwise
     */
    public function verifyCredentials(string $email, string $password): ?array {
        $user = $this->findByEmail($email);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Regenerate session ID for security
            session_regenerate_id(true);
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['login_time'] = time();
            
            // Log activity
            logActivity('USER_LOGIN', "User {$user['email']} logged in successfully");
            
            return $user;
        }
        
        // Log failed attempt
        logActivity('LOGIN_FAILED', "Failed login attempt for email: {$email}");
        
        return null;
    }
    
    /**
     * Check if email exists
     * 
     * @param string $email The email address
     * @return bool True if exists, false otherwise
     */
    public function emailExists(string $email): bool {
        $user = $this->findByEmail($email);
        return $user !== null;
    }
    
    /**
     * Get all users
     * 
     * @return array Array of user data (without password hashes)
     */
    public function getAllUsers(): array {
        $sql = "SELECT id, email, name, role, avatar_url, created_at FROM users ORDER BY created_at DESC";
        $result = $this->db->select($sql);
        
        $users = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
        }
        
        return $users;
    }
    
    /**
     * Logout current user
     */
    public function logout(): void {
        logActivity('USER_LOGOUT', "User {$_SESSION['user_email']} logged out");
        
        // Clear session variables
        $_SESSION = [];
        
        // Destroy the session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        // Destroy the session
        session_destroy();
    }
}
