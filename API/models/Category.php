<?php
class Category {
    private $conn;
    private $table_name = "categories";

    public $id;
    public $name;
    public $slug;
    public $description;
    public $image;
    public $parent_id;
    public $sort_order;
    public $is_active;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Read all categories
    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE is_active = 1 ORDER BY sort_order ASC, name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Read main categories (no parent)
    public function readMainCategories() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE parent_id IS NULL AND is_active = 1 ORDER BY sort_order ASC, name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Read subcategories by parent
    public function readSubcategories($parent_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE parent_id = :parent_id AND is_active = 1 ORDER BY sort_order ASC, name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':parent_id', $parent_id);
        $stmt->execute();
        return $stmt;
    }

    // Read single category
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id AND is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->name = $row['name'];
            $this->slug = $row['slug'];
            $this->description = $row['description'];
            $this->image = $row['image'];
            $this->parent_id = $row['parent_id'];
            $this->sort_order = $row['sort_order'];
            $this->is_active = $row['is_active'];
            return true;
        }

        return false;
    }
}
?>
