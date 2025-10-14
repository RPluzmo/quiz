<?php
/**
 * User Class - Handles user authentication and management
 */

class User {
    private $conn;
    private $table_name = "users";

    // User properties
    public $id;
    public $username;
    public $password;
    public $role;
    public $created_at;

    /**
     * Constructor
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Register a new user
     */
    public function register() {
        // Check if username already exists
        if ($this->usernameExists()) {
            return [
                'success' => false,
                'message' => 'Username already exists'
            ];
        }

        // Insert user (password stored as plain text)
        $query = "INSERT INTO " . $this->table_name . " 
                  (username, password, role) 
                  VALUES (:username, :password, :role)";

        $stmt = $this->conn->prepare($query);

        // Bind values
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':password', $this->password);
        $role = $this->role ?? 'user';
        $stmt->bindParam(':role', $role);

        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Registration successful'
            ];
        }

        return [
            'success' => false,
            'message' => 'Registration failed'
        ];
    }

    /**
     * Login user
     */
    public function login() {
        $query = "SELECT id, username, password, role 
                  FROM " . $this->table_name . " 
                  WHERE username = :username 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $this->username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Direct password comparison (no hashing)
            if ($this->password === $row['password']) {
                $this->id = $row['id'];
                $this->role = $row['role'];

                // Set session variables
                $_SESSION['user_id'] = $this->id;
                $_SESSION['username'] = $this->username;
                $_SESSION['role'] = $this->role;
                $_SESSION['logged_in'] = true;

                return [
                    'success' => true,
                    'message' => 'Login successful',
                    'user' => [
                        'id' => $this->id,
                        'username' => $this->username,
                        'role' => $this->role
                    ]
                ];
            }
        }

        return [
            'success' => false,
            'message' => 'Invalid username or password'
        ];
    }

    /**
     * Check if username exists
     */
    private function usernameExists() {
        $query = "SELECT id FROM " . $this->table_name . " 
                  WHERE username = :username 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $this->username);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * Get all users
     */
    public function getAllUsers() {
        $query = "SELECT id, username, role, created_at 
                  FROM " . $this->table_name . " 
                  ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get user by ID
     */
    public function getUserById($id) {
        $query = "SELECT id, username, role, created_at 
                  FROM " . $this->table_name . " 
                  WHERE id = :id 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Update user
     */
    public function updateUser($id, $username, $role) {
        // Check if trying to change username to existing one
        $query = "SELECT id FROM " . $this->table_name . " 
                  WHERE username = :username AND id != :id 
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return [
                'success' => false,
                'message' => 'Username already exists'
            ];
        }

        // Update user
        $query = "UPDATE " . $this->table_name . " 
                  SET username = :username, role = :role 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'User updated successfully'
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to update user'
        ];
    }

    /**
     * Delete user
     */
    public function deleteUser($id) {
        // Check if this is the last admin
        if ($this->isLastAdmin($id)) {
            return [
                'success' => false,
                'message' => 'Cannot delete the last admin user'
            ];
        }

        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'User deleted successfully'
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to delete user'
        ];
    }

    /**
     * Change user role
     */
    public function changeRole($id, $new_role) {
        // Get current user role
        $user = $this->getUserById($id);
        
        // If changing from admin to user, check if this is the last admin
        if ($user['role'] === 'admin' && $new_role === 'user') {
            if ($this->isLastAdmin($id)) {
                return [
                    'success' => false,
                    'message' => 'Cannot remove admin privileges from the last admin'
                ];
            }
        }

        $query = "UPDATE " . $this->table_name . " 
                  SET role = :role 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':role', $new_role);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Role changed successfully'
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to change role'
        ];
    }

    /**
     * Check if user is the last admin
     */
    private function isLastAdmin($user_id) {
        // Get the user's role
        $user = $this->getUserById($user_id);
        
        // If user is not admin, they can be deleted
        if ($user['role'] !== 'admin') {
            return false;
        }

        // Count total admins
        $query = "SELECT COUNT(*) as admin_count 
                  FROM " . $this->table_name . " 
                  WHERE role = 'admin'";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // If there's only one admin and it's this user, return true
        return $result['admin_count'] <= 1;
    }

    /**
     * Logout user
     */
    public static function logout() {
        session_unset();
        session_destroy();
    }

    /**
     * Check if user is logged in
     */
    public static function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    /**
     * Check if user is admin
     */
    public static function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    /**
     * Require login
     */
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: index.php');
            exit();
        }
    }

    /**
     * Require admin
     */
    public static function requireAdmin() {
        self::requireLogin();
        if (!self::isAdmin()) {
            header('Location: dashboard.php');
            exit();
        }
    }
}
?>