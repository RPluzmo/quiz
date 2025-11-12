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
    public $password; // Šeit glabāsies hešs vai lietotāja ievadītais teksts
    public $role;
    public $created_at;

    /**
     * Constructor
     */
    public function __construct($db) {
        $this->conn = $db;
        // Pārliecināmies, ka sesija ir sākusies, jo login/logout/isLoggedIn to izmanto
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    // --- AUTENTIFIKĀCIJAS FUNKCIJAS (UZLABOTĀ DROŠĪBA) ---

    /**
     * Register a new user
     */
    public function register() {
        
        // 1. LIETOTĀJVĀRDA VALIDĀCIJA (JAUNUMS)
        $username_validation = $this->validateUsername($this->username);
        if (!$username_validation['success']) {
            return $username_validation;
        }

        // --- PĀRBAUDE VAI LIETOTĀJVĀRDS JAU PASTĀV (KRITISKS LABOJUMS) ---
        if ($this->usernameExists()) {
            return [
                'success' => false,
                'message' => 'O oo.. lietotājvārds jau ir aizņemts'
            ];
        }
        // ------------------------------------------------------------------

        // 2. STINGRA PAROLES VALIDĀCIJA
        $validation_result = $this->validatePasswordStrength($this->password);
        if (!$validation_result['success']) {
            return $validation_result;
        }

        // 3. PAROLES HĒŠOŠANA (KRITISKĀ DROŠĪBAS IZMAIŅA)
        // Nekad neglabājam paroli kā "plain text"!
        $hashed_password = password_hash($this->password, PASSWORD_DEFAULT); 

        // 4. Ievietošanas (INSERT) vaicājums
        $query = "INSERT INTO " . $this->table_name . " 
                  (username, password, role) 
                  VALUES (:username, :password, :role)";

        $stmt = $this->conn->prepare($query);

        // Bind values
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':password', $hashed_password); // Piesaistām HĒŠU
        $role = $this->role ?? 'user';
        $stmt->bindParam(':role', $role);

        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Reģistrācija izdevās'
            ];
        }

        return [
            'success' => false,
            'message' => 'Reģistrācija NEizdevās'
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
        $hashed_password = $row['password'];

        // 1. MĒĢINĀT DROŠO PĀRBAUDI (password_verify)
        if (password_verify($this->password, $hashed_password)) {
            
            // PĀREJAS LOGIKA: JA PAROLE VĒL NAV HEŠOTA, TAGAD TO HEŠO UN ATJAUNINA DB
            // ŠIS IR IETEICAMS DROŠĪBAS SOLIS, LAI PĀRNESTU VECOS LIETOTĀJUS UZ HEŠIEM
            if (password_needs_rehash($hashed_password, PASSWORD_DEFAULT)) {
                $new_hashed_password = password_hash($this->password, PASSWORD_DEFAULT);
                $this->updatePasswordHash($row['id'], $new_hashed_password);
            }
            
            // Ielogoties, ja pārbaude bija veiksmīga
            $this->setSessionVariables($row);
            return ['success' => true, 'message' => 'Login successful', 'user' => $this->getUserData($row)];
        } 
        
        // 2. IZŅĒMUMS (NEDROŠS): PĀRBAUDĪT VECO, PARASTO TEKSTA PAROLI
        // ŠIS BLOKS BŪS AKTĪVS TIKAI TĀDĀM PAROLĒM KĀ 'admin', KAS NAV HEŠOTAS
        if ($this->password === $hashed_password && !password_get_info($hashed_password)['algo']) {
            
            // DROŠĪBAS UZLABOJUMS: AUTOMĀTISKI HEŠO UN SAGLABĀ ŠO PAROLI!
            $new_hashed_password = password_hash($this->password, PASSWORD_DEFAULT);
            $this->updatePasswordHash($row['id'], $new_hashed_password);
            
            // Ielogoties ar veco paroli un uzstādīt sesiju
            $this->setSessionVariables($row);
            return ['success' => true, 'message' => 'Login successful (Legacy Access)', 'user' => $this->getUserData($row)];
        }
    }

    // Kļūda, ja neviens variants nesanāk
    return [
        'success' => false,
        'message' => 'Neatbilstošs lietotājvārds vai parole'
    ];
}

private function updatePasswordHash($user_id, $new_hash) {
    $query = "UPDATE " . $this->table_name . " SET password = :password WHERE id = :id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':password', $new_hash);
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
}

private function setSessionVariables($row) {
    $this->id = $row['id'];
    $this->username = $row['username'];
    $this->role = $row['role'];
    $_SESSION['user_id'] = $this->id;
    $_SESSION['username'] = $this->username;
    $_SESSION['role'] = $this->role;
    $_SESSION['logged_in'] = true;
}

private function getUserData($row) {
    return [
        'id' => $row['id'],
        'username' => $row['username'],
        'role' => $row['role']
    ];
}
private function validateUsername($username)
    {
        if (empty($username)) {
            return ['success' => false, 'message' => 'Lietotājvārds nedrīkst būt tukšs.'];
        }
        
        // Pārbauda, vai lietotājvārds satur TIKAI burtus (latviešu un latīņu) un atstarpes.
        // /^[a-zA-ZāčēģīķļņšūžĀČĒĢĪĶĻŅŠŪŽ\s]+$/u
        // 'u' modifikators ir kritisks, lai atpazītu UTF-8 rakstzīmes (garumzīmes).
        if (!preg_match('/^[a-zA-ZāčēģīķļņšūžĀČĒĢĪĶĻŅŠŪŽ\s]+$/u', $username)) {
            return [
                'success' => false, 
                'message' => 'Lietotājvārdā drīkst izmantot tikai latviešu burtus un atstarpes (bez cipariem un citiem simboliem).'
            ];
        }
        return ['success' => true];
    }
    /**
     * Validē paroles atbilstību stingriem drošības kritērijiem.
     * Pievienots labākai drošībai reģistrācijā.
     */
    private function validatePasswordStrength($password)
    {
        if (strlen($password) < 8) {
            return ['success' => false, 'message' => 'Parolei jāsastāv vismaz no 8 simboliem.. yknow burtiem and stuff'];
        }
        if (!preg_match('/[A-Z]/', $password)) {
            return ['success' => false, 'message' => 'Parolē jābūt vismaz vienam LIELAM burtam.'];
        }
        if (!preg_match('/[a-z]/', $password)) {
            return ['success' => false, 'message' => 'Parolē jābūt vismaz vienam mazam burtam.'];
        }
        if (!preg_match('/[0-9]/', $password)) {
            return ['success' => false, 'message' => 'Parolē jābūt vismaz vienam ciparam.'];
        }
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            return ['success' => false, 'message' => 'Parolē jābūt vismaz vienam speciālam simbolam (piemēram, !, @, #, $).'];
        }
        
        return ['success' => true];
    }

    // --- PĀRĒJĀS FUNKCIJAS (SAGLABĀTAS) ---

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
        $query = "SELECT id, username, role, created_at, password 
                  FROM " . $this->table_name . " 
                  WHERE id = :id 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Update user (username/role)
     */
    public function updateUser($id, $username, $role) {
        
        $username_validation = $this->validateUsername($username);
        if (!$username_validation['success']) {
            return $username_validation;
        }

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
                'message' => 'O oo.. Lietotājvārds jau ir aizņemts.'
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
            
            // --- KRITISKS LABOJUMS: SESIJAS ATJAUNINĀŠANA PĒC DATU IZMAIŅAS ---
            // Pārbauda, vai maināmā lietotāja ID sakrīt ar ielogotā lietotāja ID
            if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$id) {
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $role;
            }
            // ------------------------------------------------------------------

            return [
                'success' => true,
                'message' => 'Lietotājs atjaunināts'
            ];
        }

        return [
            'success' => false,
            'message' => 'Kautkā nesanāca atjaunot lietotāju..'
        ];
    }
    
    /**
     * Change user role
     */
    public function changeRole($id, $new_role) {
        // Get current user role
        $user = $this->getUserById($id);
        
        // If changing from admin to user, check if this is the last admin
        if ($user && $user['role'] === 'admin' && $new_role === 'user') {
            if ($this->isLastAdmin($id)) {
                return [
                    'success' => false,
                    'message' => 'Nedrīkst noņemt Admin statusu pēdējam tā lietotājam, atdod to kādam citam un mēģini vēlreiz'
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
            
            // --- KRITISKS LABOJUMS: SESIJAS ATJAUNINĀŠANA PĒC LOMAS IZMAIŅAS ---
            // Pārbauda, vai maināmā lietotāja ID sakrīt ar ielogotā lietotāja ID
            if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$id) {
                $_SESSION['role'] = $new_role;
            }
            // ------------------------------------------------------------------

            return [
                'success' => true,
                'message' => 'Lietotāja status jeb tiesības nomainītas'
            ];
        }

        return [
            'success' => false,
            'message' => 'Neizdevās nomainīt lietotāja statusu'
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
     * Delete user
     */
    public function deleteUser($id) {
        // Check if this is the last admin (logic used in changeRole)
        if ($this->isLastAdmin($id)) {
            return [
                'success' => false,
                'message' => 'Nedrīkst noņemt Admin statusu pēdējam tā lietotājam, atdod to kādam citam un mēģini vēlreiz'
            ];
        }

        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Lietotājs izdzēsts'
            ];
        }

        return [
            'success' => false,
            'message' => 'Neizdevās izdzēst'
        ];
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