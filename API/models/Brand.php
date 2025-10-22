<?php
class Brand {
    private $conn;
    private $table_name = "brands";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Read all brands
    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE is_active = 1 ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Read one brand by ID
    public function readOne($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Create new brand
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " (name, description, logo, status, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($query);
        
        if ($stmt->execute([
            $data['name'],
            $data['description'] ?? null,
            $data['logo'] ?? null,
            $data['status'] ?? 'active'
        ])) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Update brand
    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . " SET name = ?, description = ?, logo = ?, status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([
            $data['name'],
            $data['description'] ?? null,
            $data['logo'] ?? null,
            $data['status'] ?? 'active',
            $id
        ]);
    }

    // Delete brand
    public function delete($id) {
        // Check if brand has associated products
        $checkQuery = "SELECT COUNT(*) as count FROM products WHERE brand_id = ?";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->execute([$id]);
        $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            throw new Exception("Cannot delete brand. It has " . $result['count'] . " associated product(s).");
        }
        
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    // Check if brand exists
    public function exists($id) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    // Get brands by status
    public function getByStatus($status) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE status = ? ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$status]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Search brands
    public function search($searchTerm) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE name LIKE ? OR description LIKE ? ORDER BY name ASC";
        $searchPattern = "%{$searchTerm}%";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$searchPattern, $searchPattern]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
