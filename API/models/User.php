<?php
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $first_name;
    public $last_name;
    public $email;
    public $phone;
    public $address;
    public $password;
    public $role;
    public $is_active;
    public $email_verified;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create new user
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (first_name, last_name, email, phone, address, password, role, is_active, email_verified, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, 1, 0, NOW())";
        
        $stmt = $this->conn->prepare($query);
        
        // Hash the password
        $hashed_password = password_hash($this->password, PASSWORD_DEFAULT);
        
        return $stmt->execute([
            $this->first_name,
            $this->last_name,
            $this->email,
            $this->phone,
            $this->address,
            $hashed_password,
            $this->role
        ]);
    }

    // Login user
    public function login() {
        $query = "SELECT id, first_name, last_name, email, phone, address, password, role, is_active 
                  FROM " . $this->table_name . " 
                  WHERE email = ? AND is_active = 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->email]);
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verify password
            if (password_verify($this->password, $row['password'])) {
                // Set user properties
                $this->id = $row['id'];
                $this->first_name = $row['first_name'];
                $this->last_name = $row['last_name'];
                $this->email = $row['email'];
                $this->phone = $row['phone'];
                $this->address = $row['address'];
                $this->role = $row['role'];
                $this->is_active = $row['is_active'];
                
                return true;
            }
        }
        
        return false;
    }

    // Check if email exists
    public function emailExists($email) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$email]);
        
        return $stmt->rowCount() > 0;
    }

    // Get user by ID
    public function readOne($id) {
        $query = "SELECT id, first_name, last_name, email, phone, address, role, is_active, email_verified, created_at 
                  FROM " . $this->table_name . " 
                  WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->id = $row['id'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->email = $row['email'];
            $this->phone = $row['phone'];
            $this->address = $row['address'];
            $this->role = $row['role'];
            $this->is_active = $row['is_active'];
            $this->email_verified = $row['email_verified'];
            $this->created_at = $row['created_at'];
            
            return true;
        }
        
        return false;
    }

    // Update user
    public function update($id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET first_name = ?, last_name = ?, phone = ?, address = ?, updated_at = NOW() 
                  WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([
            $this->first_name,
            $this->last_name,
            $this->phone,
            $this->address,
            $id
        ]);
    }

    // Change password
    public function changePassword($id, $new_password) {
        $query = "UPDATE " . $this->table_name . " 
                  SET password = ?, updated_at = NOW() 
                  WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        return $stmt->execute([$hashed_password, $id]);
    }

    // Verify email
    public function verifyEmail($email) {
        $query = "UPDATE " . $this->table_name . " 
                  SET email_verified = 1, updated_at = NOW() 
                  WHERE email = ?";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$email]);
    }

    // Deactivate user
    public function deactivate($id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET is_active = 0, updated_at = NOW() 
                  WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    // Get all users (for admin)
    public function readAll() {
        $query = "SELECT id, first_name, last_name, email, phone, role, is_active, email_verified, created_at 
                  FROM " . $this->table_name . " 
                  ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Search users
    public function search($search_term) {
        $query = "SELECT id, first_name, last_name, email, phone, role, is_active, created_at 
                  FROM " . $this->table_name . " 
                  WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ? 
                  ORDER BY created_at DESC";
        
        $search_pattern = "%{$search_term}%";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$search_pattern, $search_pattern, $search_pattern]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?> 